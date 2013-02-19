<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Andrej Harsani
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *   $Id: Account.class.php 27046 2010-02-02 12:30:55Z mkendera $
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
class Gpf_Db_NotificationRegistration extends Gpf_DbEngine_Row {
	
	const  TYPE_ANDROID = 'a';
	const  TYPE_IOS = 'i';

	function init() {
		$this->setTable(Gpf_Db_Table_NotificationRegistrations::getInstance());
		parent::init();
	}

	function setNotificationId($id){
		$this->set(Gpf_Db_Table_NotificationRegistrations::NOTIFICATION_ID, $id);
	}

	function setAccountUserId($userId){
		$this->set(Gpf_Db_Table_NotificationRegistrations::ACCOUNT_USER_ID, $userId);
	}

	function setClientType($clientType){
		$this->set(Gpf_Db_Table_NotificationRegistrations::TYPE, $clientType);
	}
        
        function setRegistrationTime($time){
		$this->set(Gpf_Db_Table_NotificationRegistrations::REGISTRATION_TIME, $time);
	}

	function getNotificationId(){
		return $this->get(Gpf_Db_Table_NotificationRegistrations::NOTIFICATION_ID);
	}

	function getAccountUserId(){
		return $this->get(Gpf_Db_Table_NotificationRegistrations::ACCOUNT_USER_ID);
	}

	function getClientType(){
		return $this->get(Gpf_Db_Table_NotificationRegistrations::TYPE);
	}
        
        function getRegistrationTime(){
		return $this->get(Gpf_Db_Table_NotificationRegistrations::REGISTRATION_TIME);
	}
}
?>
