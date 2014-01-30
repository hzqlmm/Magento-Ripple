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

class Appmerce_Ripple_Model_Source_Expiration
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 300,
                'label' => Mage::helper('ripple')->__('5m')
            ),
            array(
                'value' => 600,
                'label' => Mage::helper('ripple')->__('10m')
            ),
            array(
                'value' => 900,
                'label' => Mage::helper('ripple')->__('15m')
            ),
            array(
                'value' => 1800,
                'label' => Mage::helper('ripple')->__('30m')
            ),
            array(
                'value' => 3600,
                'label' => Mage::helper('ripple')->__('1h')
            ),
        );
    }

}
