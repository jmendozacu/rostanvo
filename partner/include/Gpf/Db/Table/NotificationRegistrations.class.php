<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Andrej Harsani
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *   $Id: Accounts.class.php 28865 2010-07-21 08:24:14Z iivanco $
 *
 *   Licensed under the Quality Unit, s.r.o. Dual License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/gpf
 *
 */

/**
 * @package GwtPhpFramework
 */
class Gpf_Db_Table_NotificationRegistrations extends Gpf_DbEngine_Table {
	const NOTIFICATION_ID = 'notificationid';
	const ACCOUNT_USER_ID = 'accountuserid';
	const REGISTRATION_TIME = 'registration_time';
        const TYPE = 'rtype';

	private static $instance;

	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	protected function initName() {
		$this->setName('g_notification_registrations');
	}

	public static function getName() {
		return self::getInstance()->name();
	}

	protected function initColumns() {
		$this->createPrimaryColumn(self::NOTIFICATION_ID, self::CHAR, 255);
		$this->createColumn(self::ACCOUNT_USER_ID, self::CHAR, 8);
		$this->createColumn(self::TYPE, self::CHAR, 1);
                $this->createColumn(self::REGISTRATION_TIME, self::DATETIME);
	}
}

?>
