<?php

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('reward_count')};
DROP TABLE IF EXISTS {$this->getTable('reward_order')};
DROP TABLE IF EXISTS {$this->getTable('reward_transaction')};
DROP TABLE IF EXISTS {$this->getTable('reward_customer')};
DROP TABLE IF EXISTS {$this->getTable('reward_rate')};
DROP TABLE IF EXISTS {$this->getTable('reward_rule')};
DROP TABLE IF EXISTS {$this->getTable('reward_offer')};

CREATE TABLE {$this->getTable('reward_customer')} (
  `reward_customer_id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) unsigned NOT NULL default '0',
  `total_points` int(11) NOT NULL default '0',
  `is_notification` smallint(6) default '0',
  `extra_content` text default '',
  PRIMARY KEY (`reward_customer_id`),
  UNIQUE (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('reward_transaction')}(
  `transaction_id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) unsigned NOT NULL default '0',
  `title` varchar(255) default '',
  `store_id` smallint(6) NOT NULL default '0',
  `points_change` int(11) NOT NULL default '0',
  `points_spent` int(11) NOT NULL default '0',
  `is_expired` smallint(6) NOT NULL default '0',
  `action` varchar(63) default '',
  `notice` varchar(255) default '',
  `create_at` datetime NULL,
  `expiration_date` datetime NULL,
  `extra_content` text default '',
  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('reward_rate')}(
  `rate_id` int(11) unsigned NOT NULL auto_increment,
  `website_ids` text default '',
  `customer_group_ids` text default '',
  `direction` smallint(6) NOT NULL default '0',
  `points` int(11) NOT NULL default '0',
  `money` decimal(12,4) default '0',
  `sort_order` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (`rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('reward_rule')}(
  `rule_id` int(11) unsigned NOT NULL auto_increment,
  `sort_order` int(11) unsigned NOT NULL default '0',
  `title` varchar(255) default '',
  `description` text default '',
  `website_ids` text default '',
  `customer_group_ids` text default '',
  `from_date` datetime NULL,
  `to_date`	datetime NULL,
  `is_active` smallint(6) default '0',
  `conditions_serialized` mediumtext default '',
  `points_earned` int(11) NOT NULL default '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('reward_offer')}(
  `offer_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) default '',
  `description` text default '',
  `image` varchar(255) default '',
  `is_active` smallint(6) default '0',
  `from_date` datetime NULL,
  `to_date` datetime NULL,
  `sort_order` int(11) unsigned NOT NULL default '0',
  `website_ids` text default '',
  `customer_group_ids` text default '',
  `conditions_serialized` mediumtext default '',
  `commission_type` smallint(6) default '0',
  `commission` int(11) default '0',
  `discount_method` smallint(6) default '0',
  `discount_type` smallint(6) default '0',
  `discount` decimal(12,4) default '0',
  `discount_show` smallint(6) default '0',
  `categories` mediumtext default '',
  `products` mediumtext default '',
  PRIMARY KEY (`offer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('reward_order')}(
  `order_id` int(11) unsigned NOT NULL auto_increment,
  `order_increment_id` int(11) unsigned NOT NULL default '0',
  `customer_id` int(11) unsigned NOT NULL default '0',
  `offer_id` int(11) unsigned NOT NULL default '0',
  `offer_discount` decimal(12,4) default '0',
  `base_offer_discount` decimal(12,4) default '0',
  PRIMARY KEY (`order_id`),
  INDEX (`offer_id`),
  FOREIGN KEY (`offer_id`)
  REFERENCES {$this->getTable('reward_offer')} (`offer_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('reward_count')}(
  `id` int(11) unsigned NOT NULL auto_increment,
  `key` varchar(255) default '',
  `customer_id` int(11) unsigned NOT NULL default '0',
  `offer_id` int(11) unsigned NOT NULL default '0',
  `visit_count` int(11) unsigned NOT NULL default '0',
  `spent_visit_count` int(11) unsigned NOT NULL default '0',
  `unique_click` int(11) unsigned NOT NULL default '0',
  `spent_unique_click` int(11) unsigned NOT NULL default '0',
  `ip_list` longtext default '',
  PRIMARY KEY (`id`),
  INDEX (`offer_id`),
  FOREIGN KEY (`offer_id`)
  REFERENCES {$this->getTable('reward_offer')} (`offer_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();