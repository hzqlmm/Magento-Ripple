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
 * @category    Magento Commerce
 * @package     Appmerce_Ripple
 * @copyright   Copyright (c) 2011-2013 Appmerce (http://www.appmerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Appmerce_Ripple_Controller_Common extends Mage_Core_Controller_Front_Action
{
    /**
     * Return checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

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
     * Return order instance by LastOrderId
     *
     * @return  Mage_Sales_Model_Order object
     */
    protected function getLastRealOrder()
    {
        $order = Mage::getModel('sales/order');
        $order->load($this->getCheckout()->getLastRealOrderId(), 'increment_id');
        return $order;
    }

    /**
     * Return payment API model
     *
     * @return Appmerce_Ripple_Model_Api
     */
    protected function getApi()
    {
        return Mage::getSingleton('ripple/api');
    }

    /**
     * Save checkout session
     */
    public function saveCheckoutSession()
    {
        $this->getCheckout()->setRippleQuoteId($this->getCheckout()->getLastSuccessQuoteId());
        $this->getCheckout()->setRippleOrderId($this->getCheckout()->getLastOrderId(true));
    }

}
