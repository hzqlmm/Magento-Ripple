<?php
/**
 * Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 *
 * @extension   Ripple
 * @type        Payment method
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Magento
 * @package     Appmerce_Ripple
 * @copyright   Copyright (c) 2011-2014 Appmerce (http://www.appmerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Appmerce_Ripple_Model_Api_Pull extends Varien_Object
{
    protected $_code = 'ripple';

    /**
     * Return order process instance
     *
     * @return Appmerce_Ripple_Model_Process
     */
    public function getProcess()
    {
        return Mage::getSingleton('ripple/process');
    }

    /**
     * Return Api instance
     *
     * @return Appmerce_Ripple_Api
     */
    public function getApi()
    {
        return Mage::getSingleton('ripple/api');
    }

    /**
     * Cron transaction status check
     *
     * Check orders created in the last 24 hrs.
     * After that manual check is required.
     */
    public function transactionStatusCheck($shedule = null)
    {
        // Time preparations: from -24h until now
        $gmtStamp = Mage::getModel('core/date')->gmtTimestamp();
        $from = date('Y-m-d H:i:s', $gmtStamp - 86400);
        $to = date('Y-m-d H:i:s', $gmtStamp);

        // Database preparations
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        $orderPaymentTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');

        $result_orders = $db->query('SELECT sfo.entity_id, sfop.last_trans_id
            FROM ' . $orderTable . ' sfo 
            INNER JOIN ' . $orderPaymentTable . ' sfop 
            ON sfop.parent_id = sfo.entity_id 
            WHERE (sfo.state = "' . Mage_Sales_Model_Order::STATE_NEW . '" OR sfo.state = "' . Mage_Sales_Model_Order::STATE_PENDING_PAYMENT . '")
            AND sfo.created_at >= "' . $from . '"
            AND sfo.created_at <= "' . $to . '"
            AND sfop.method = "' . $this->_code . '"');

        if (!$result_orders) {
            return $this;
        }

        // Find initial ledger_index_min
        $ledger_min = -1;
        $ledgerTable = Mage::getSingleton('core/resource')->getTableName('ripple_api_ledger');
        $result_ledger = $db->query('SELECT ledger_index_min FROM ' . $ledgerTable . ' LIMIT 1');
        while ($row = $result_ledger->fetch(PDO::FETCH_ASSOC)) {
            if (!$row) {
                break;
            }
            $ledger_min = $row['ledger_index_min'];
        }

        // -10 for deliberate overlap to avoid (possible?) gaps
        if ($ledger_min > 10) {
            $ledger_min -= 10;
        }

        // Find latest transactions from ledger
        $account = $this->getApi()->getConfigData('to_address');
        $account_tx = $this->getApi()->getRipple()->getAccountTx($account, $ledger_min);
        if (!isset($account_tx['status']) || $account_tx['status'] != 'success') {
            return $this;
        }

        // Find transactions
        $ledger_max = $account_tx['ledger_index_max'];
        if (!isset($account_tx['transactions']) || empty($account_tx['transactions'])) {
            $this->updateLedgerMin($db, $ledgerTable, $ledger_max);
            return $this;
        }

        // Match transactions with DestinationTag
        $txs = array();
        foreach ($account_tx['transactions'] as $key => $tx) {
            if (isset($tx['tx']['DestinationTag']) && $tx['tx']['DestinationTag'] > 0) {
                $dt = $tx['tx']['DestinationTag'];
                $txs[$dt] = $tx['tx'];
            }
        }

        // Update order statuses
        $order = Mage::getModel('sales/order');
        while ($row = $result_orders->fetch(PDO::FETCH_ASSOC)) {
            if (!$row) {
                break;
            }

            $order->reset();
            $order->load($row['entity_id']);

            // Check balance etc
            $id = $row['entity_id'];
            if (isset($txs[$id])) {
                $transactionId = $txs[$id]['hash'];

                // Build order history note with link to sender
                $note = Mage::helper('ripple')->__('Paid: %s/%s', $txs[$id]['Amount']['value'], $txs[$id]['Amount']['currency']);
                $note .= '<br />' . Mage::helper('ripple')->__('Ledger: %s', $txs[$id]['inLedger']);
                $note .= '<br />' . Mage::helper('ripple')->__('Account: <a href="https://ripple.com/graph/#%s">%s</a>', $txs[$id]['Account'], $txs[$id]['Account']);

                // Check if full amount was received for this order
                if (is_array($txs[$id]['Amount']) && $txs[$id]['Amount']['value'] == round($order->getGrandTotal(), 2) && $txs[$id]['Amount']['currency'] == $order->getOrderCurrencyCode()) {
                    $this->getProcess()->success($order, $note, $transactionId);
                }

                // Manual review required
                else {
                    $this->getProcess()->pending($order, $note, $transactionId);
                }
            }
        }

        // Update last ledger index we saw
        $this->updateLedgerMin($db, $ledgerTable, $ledger_max);
        return $this;
    }

    /**
     * Update new ledger min (= previous ledger max)
     */
    public function updateLedgerMin($db, $ledgerTable, $ledger_min)
    {
        $db->query('UPDATE ' . $ledgerTable . ' SET ledger_index_min = "' . $ledger_min . '" WHERE id = 1');
    }

}
