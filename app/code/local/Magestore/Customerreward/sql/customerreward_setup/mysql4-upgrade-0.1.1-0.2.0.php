<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('reward_count_customer')};
CREATE TABLE {$this->getTable('reward_count_customer')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `count_id` int(11) unsigned NOT NULL,
  `customer_id` int(11) unsigned NOT NULL default '0',
  `used` int(11) unsigned default '0',
  PRIMARY KEY (`id`),
  INDEX (`count_id`),
  FOREIGN KEY (`count_id`) REFERENCES {$this->getTable('reward_count')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('reward_offer')}
  ADD COLUMN `coupon` varchar(255) default '' AFTER `customer_group_ids`,
  ADD COLUMN `uses_per_coupon` int(11) unsigned default '0' AFTER `coupon`,
  ADD COLUMN `uses_per_customer` int(11) unsigned default '0' AFTER `uses_per_coupon`;

ALTER TABLE {$this->getTable('reward_count')}
  ADD COLUMN `coupon` varchar(255) default '' AFTER `key`,
  ADD COLUMN `used` int(11) unsigned default '0' AFTER `coupon`;

");

$installer->endSetup();