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

class Appmerce_Ripple_Model_Api extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'ripple';
    protected $_formBlockType = 'ripple/form';
    protected $_infoBlockType = 'ripple/info';

    // Magento features
    protected $_isGateway = false;
    protected $_canOrder = false;
    protected $_canAuthorize = false;
    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = true;
    protected $_canFetchTransactionInfo = false;
    protected $_canReviewPayment = false;
    protected $_canCreateBillingAgreement = false;
    protected $_canManageRecurringProfiles = false;

    // Restrictions
    protected $_allowCurrencyCode = array();

    // Local
    const RIPPLE_SEND_URL = 'https://ripple.com//send';

    public function __construct()
    {
        $this->_config = Mage::getSingleton('ripple/config');
        return $this;
    }

    /**
     * Return configuration instance
     *
     * @return Appmerce_Ripple_Model_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * getGatewayUrl
     */
    public function getGatewayUrl()
    {
        return self::RIPPLE_SEND_URL;
    }

    /**
     * Validate if payment is possible
     *  - check allowed currency codes
     *
     * @return bool
     */
    public function validate()
    {
        parent::validate();
        $currency_code = $this->getCurrencyCode();
        if (!empty($this->_allowCurrencyCode) && !in_array($currency_code, $this->_allowCurrencyCode)) {
            $errorMessage = Mage::helper('ripple')->__('Selected currency (%s) is not compatible with this payment method.', $currency_code);
            Mage::throwException($errorMessage);
        }
        return $this;
    }

    /**
     * Return order process instance
     *
     * @return Appmerce_Bitcoin_Model_Api_Ripple
     */
    public function getRipple()
    {
        return Mage::getSingleton('ripple/api_ripple');
    }

    /**
     * Get redirect URL after placing order
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->getConfig()->getApiUrl('redirect');
    }

    /**
     * Decide currency code type
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        if (is_null($this->_currencyCode)) {
            $this->_currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        return $this->_currencyCode;
    }

    /**
     * Generates array of fields for redirect form
     *
     * @return array
     */
    public function getRedirectUrl($order)
    {
        $urlFields = array();
        $storeId = $order->getStoreId();

        // Destination AccountID
        $urlFields['to'] = $this->getConfigData('to_address', $storeId);

        // Amount to send, including three letter currency code, e.g. 15/USD
        $urlFields['amount'] = round($order->getGrandTotal(), 2) . '/' . $this->getCurrencyCode();

        // Destination tag; integer in the range 0 to 2^32-1.
        // This value must be encoded in the payment for the user to be credited.
        // @Appmerce: we use the order auto_increment ID
        $urlFields['dt'] = $order->getId();

        // @todo supposedly supported?
        // http://bitcoin.stackexchange.com/questions/14149/is-there-a-way-to-specify-an-invoice-id-in-ripple-uri
        $urlFields['invoiceid'] = $order->getIncrementId();

        // @todo Source tag; integer in the range 0 to 2^32-1. This value must be encoded in the payment for the payment to be returned if necessary. When the payment is returned, this field is used as the destination_tag.
        //$urlFields['st'] = '';

        // A suggested name for this contact.
        $urlFields['name'] = $this->getConfig()->getName($order);

        // A message that will be displayed to the user making the payment.
        $urlFields['label'] = $this->getConfig()->getOrderDescription($order);

        // A URL with more information about the payment.
        $urlFields['info_url'] = $this->getConfig()->getApiUrl('info', $storeId);

        // Clients should send the user here after the payment is made.
        $urlFields['return_url'] = $this->getConfig()->getApiUrl('return', $storeId);

        // Client should send the user here if the payment is canceled.
        $urlFields['abort_url'] = $this->getConfig()->getApiUrl('abort', $storeId);

        // A message that should be included with the payment and displayed to the recipient.
        $urlFields['msg'] = $this->getConfig()->getOrderDescription($order);

        // The time when this payment must be completed by expressed as integer seconds since the POSIX epoch.
        $expiration = (int)$this->getConfigData('expiration');
        $urlFields['exp'] = time() + $expiration;

        // Debug
        if ($this->getConfigData('debug_flag')) {
            $debug_url = $this->getRequest()->getPathInfo();
            $data = print_r($urlFields, true);
            Mage::getModel('ripple/api_debug')->setDir('out')->setUrl($debug_url)->setData('data', $data)->save();
        }

        // URL for RFC 3986
        // @see https://ripple.com/wiki/Ripple_URIs
        $url = $this->getGatewayUrl();
        $query = $this->httpBuildQuery3986($urlFields);
        return $url . '?' . $query;
    }

    /**
     * http build query for RFC 3986
     * needed for PHP < 5.4 compatibility
     */
    public function httpBuildQuery3986(array $params, $sep = '&')
    {
        $parts = array();
        foreach ($params as $key => $value) {
            $parts[] = sprintf('%s=%s', $key, rawurlencode($value));
        }
        return implode($sep, $parts);
    }

    /**
     * Get order statuses
     */
    public function getOrderStatus()
    {
        $status = $this->getConfigData('order_status');
        if (empty($status)) {
            $status = Appmerce_Ripple_Model_Config::DEFAULT_STATUS_PENDING;
        }
        return $status;
    }

    public function getPendingStatus()
    {
        $status = $this->getConfigData('pending_status');
        if (empty($status)) {
            $status = Appmerce_Ripple_Model_Config::DEFAULT_STATUS_PENDING_PAYMENT;
        }
        return $status;
    }

    public function getProcessingStatus()
    {
        $status = $this->getConfigData('processing_status');
        if (empty($status)) {
            $status = Appmerce_Ripple_Model_Config::DEFAULT_STATUS_PROCESSING;
        }
        return $status;
    }

}
