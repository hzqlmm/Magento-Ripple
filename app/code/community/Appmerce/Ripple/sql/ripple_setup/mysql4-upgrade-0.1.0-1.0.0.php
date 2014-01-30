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

$installer = $this;
/* @var $installer Appmerce_Ripple_Model_Mysql4_Setup */

$installer->startSetup();

/**
 * Simple table to keep track of last ledger index screened
 */
$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('ripple/api_ledger')}`;
CREATE TABLE `{$this->getTable('ripple/api_ledger')}` (
  `id` int(10) unsigned NOT null auto_increment,
  `ledger_index_min` int(10) unsigned NOT null,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `{$this->getTable('ripple/api_ledger')}` (`id`, `ledger_index_min`) VALUES (1, 0);

");

$installer->endSetup();
