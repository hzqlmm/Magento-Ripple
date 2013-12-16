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

class Appmerce_Ripple_ApiController extends Appmerce_Ripple_Controller_Common
{
    /**
     * Render redirect form and set New Order Status
     *
     * @see ripple/api/redirect
     */
    public function redirectAction()
    {
        $this->saveCheckoutSession();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Info action
     * @todo make this more useful
     *
     * @see ripple/api/info
     */
    public function infoAction()
    {
        $this->getProcess()->repeat();
        $this->_redirect('checkout/cart', array('_secure' => true));
    }

    /**
     * Return action
     *
     * @see ripple/api/return
     */
    public function returnAction()
    {
        $this->getProcess()->done();
        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }

    /**
     * Abort action
     *
     * @see ripple/api/abort
     */
    public function abortAction()
    {
        $this->getProcess()->repeat();
        $this->_redirect('checkout/cart', array('_secure' => true));
    }

}
