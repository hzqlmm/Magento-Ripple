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

class Appmerce_Ripple_Model_Config extends Mage_Payment_Model_Config
{
    const API_CONTROLLER_PATH = 'ripple/api/';

    // Default order statuses
    const DEFAULT_STATUS_PENDING = 'pending';
    const DEFAULT_STATUS_PENDING_PAYMENT = 'pending_payment';
    const DEFAULT_STATUS_PROCESSING = 'processing';

    /**
     * Return order description
     *
     * @param Mage_Sales_Model_Order
     * @return string
     */
    public function getOrderDescription($order)
    {
        return Mage::helper('ripple')->__('%s - %s', $order->getStoreName(), $order->getIncrementId());
    }

    /**
     * Get (company) name
     */
    public function getName($order)
    {
        $billingAddress = $order->getBillingAddress();
        $company = $billingAddress->getCompany();
        $name = $billingAddress->getName();
        return $company ? $company : $name;
    }

    /**
     * Return URLs
     */
    public function getApiUrl($key, $storeId = null)
    {
        return Mage::getUrl(self::API_CONTROLLER_PATH . $key, array(
            '_store' => $storeId,
            '_secure' => true
        ));
    }

    public function getPushUrl($key, $storeId = null)
    {
        return Mage::getUrl(self::PUSH_CONTROLLER_PATH . $key, array(
            '_store' => $storeId,
            '_secure' => true
        ));
    }

}
