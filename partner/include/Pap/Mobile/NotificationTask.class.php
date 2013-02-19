<?php

/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Rene Dohan
 *   @package PostAffiliatePro
 *   @since Version 1.0.0
 *   $Id: UpdateManager.class.php 18026 2008-05-14 08:07:20Z mbebjak $
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
class Pap_Mobile_NotificationTask extends Gpf_Tasks_LongTask {
	const GOOGLE_TOKEN = "google_auth_token";
	const FROM_TIME = "from_time";
	const MONTH = 2628000;

	private $androidNotifications = array();

	public function getName() {
		return 'Mobile notifications';
	}

	protected function execute() {
		try {
			$fromtime = $this->getFromTime();
			if ($fromtime) {
				$select = $this->createSellNotificationSelect(Gpf_Common_DateUtils::getDateTime($fromtime));
				foreach ($select->getAllRows() as $saleRow) {
					$this->createNotificationsForSale($saleRow);
				}
				if (!empty($this->androidNotifications))
				$this->sendGoogleNotifications();
			}
			$fromtime = (string) Gpf_Common_DateUtils::getTimestamp(Gpf_Common_DateUtils::now());
			$this->saveFromTime($fromtime);
			$this->cleanupOldRegistrations();
		} catch (Exception $e) {
			throw new Gpf_Exception('Pap_Mobile_NotificationTask failed ' . $e->getMessage());
		}
		$this->interrupt(10 * 60);
	}

	public function cleanupOldRegistrations() {
		$notificationRegistrationRow = new Gpf_Db_NotificationRegistration();
		$rowCollection = $notificationRegistrationRow->loadCollection();

		foreach ($rowCollection as $notificationRegistrationRow) {
			$notificationRegistrationTime = new Gpf_DateTime($notificationRegistrationRow->getRegistrationTime());
			if ($notificationRegistrationTime->toTimeStamp() < time() - self::MONTH * 3) {
				$notificationRegistrationRow->delete();
			}
		}
	}

	private function createNotificationsForSale($saleRow) {
		$notificationRegistrationRow = new Gpf_Db_NotificationRegistration();
		$rowCollection = $notificationRegistrationRow->loadCollection();
		foreach ($rowCollection as $notificationRegistrationRow) {
			$accountUserId = $notificationRegistrationRow->getAccountUserId();
			$notificationId = $notificationRegistrationRow->getNotificationId();
			$clientType = $notificationRegistrationRow->getClientType();

			$user = new Gpf_Db_User();
			$user->setId($accountUserId);
			$user->load();
			$isMerchant = $user->getRoleId() == 'pap_merc';

			if ($isMerchant || $accountUserId == $saleRow->get('accountuserid')) {
				$this->addnotification($clientType, $notificationId, $isMerchant, $saleRow);
			}
		}
	}

	private function addNotification($clientType, $notificationId, $isMerchant, Gpf_Data_Record $saleRow) {
		if ($clientType == Gpf_Db_NotificationRegistration::TYPE_ANDROID) {
			if (isset($this->androidNotifications[$notificationId])) {
				$idNotifications = $this->androidNotifications[$notificationId];
			}else
			$idNotifications = array();
			$idNotifications[] = new Pap_Mobile_MobileNotification($isMerchant, $saleRow);
			$this->androidNotifications[$notificationId] = $idNotifications;
		}else {
			//$this->iosNotifications[$notificationId] = new MobileNotification($isMerchant, $saleRow);
		}
	}

	private function sendGoogleNotifications() {
		$c2dm = new Pap_Mobile_AndroidC2DM();
		$googleAuth = $this->getGoogleAuthToken();
		if (!$googleAuth)
		$googleAuth = $this->googleAuthenticate($c2dm);

		foreach ($this->androidNotifications as $notificationId => $notifications) {
			if (count($notifications) == 1) {
				$this->sendGoogleNotification($c2dm, $googleAuth, $notificationId, 1, $notifications[0]->getCommission(), $notifications[0]->getCampaign(), $notifications[0]->getTotalCost(), $notifications[0]->getAffiliateName());
			} else {
				$this->sendGoogleNotification($c2dm, $googleAuth, $notificationId, count($notifications));
			}
		}
	}

	private function sendGoogleNotification(Pap_Mobile_AndroidC2DM $c2dm, $googleAuth, $notificationId, $count, $commission='', $campaign='', $totalCost='', $affiliateName='') {
		try {
			$c2dm->sendMessageToPhone($googleAuth, $notificationId, 'pap', $count, $commission, $campaign, $totalCost, $affiliateName);
		} catch (Exception $e) {
			$googleAuth = $this->googleAuthenticate();
			$c2dm->sendMessageToPhone($googleAuth, $notificationId, 'pap', $count, $commission, $campaign, $totalCost, $affiliateName);
		}
	}

	private function googleAuthenticate(Pap_Mobile_AndroidC2DM $c2dm) {
		$googleAuth = $c2dm->googleAuthenticate('support@qualityunit.com', 'QualityU11', 'QualityUnit-PapAndroid-1.05');
		$this->saveGoogleAuthToken($googleAuth);
		return $googleAuth;
	}

	private function saveGoogleAuthToken($token) {
		$params = $this->getDecodedParams();
		$params[self::GOOGLE_TOKEN] = $token;
		$this->saveParams($params);
	}

	private function saveFromTime($fromtime) {
		$params = $this->getDecodedParams();
		$params[self::FROM_TIME] = $fromtime;
		$this->saveParams($params);
	}

	/**
	 * @return array
	 */
	private function getDecodedParams() {
		try {
			$json = new Gpf_Rpc_Json();
			$encodedParams = $this->getParams();
			$stdObject = $json->decode($encodedParams);
			if($stdObject){
				$decodedParams = get_object_vars($stdObject);
				if ($decodedParams)
				return $decodedParams;
			}
		} catch (Exception $e) {

		}
		return array();
	}

	private function saveParams($params) {
		$json = new Gpf_Rpc_Json();
		$encoded = $json->encode($params);
		$this->setParams($encoded);
	}

	private function getGoogleAuthToken() {
		$params = $this->getDecodedParams();
		if (isset($params[self::GOOGLE_TOKEN])) {
			return $params[self::GOOGLE_TOKEN];
		}
		return null;
	}

	private function getFromTime() {
		$params = $this->getDecodedParams();
		if (isset($params[self::FROM_TIME])) {
			return $params[self::FROM_TIME];
		}
		return null;
	}

	private function createSellNotificationSelect($fromtime) {
		$selectBuilder = new Gpf_SqlBuilder_SelectBuilder();
		$selectBuilder->select->add("c." . Pap_Db_Table_Campaigns::NAME, 'campaign_name');
		$selectBuilder->select->add("t." . Pap_Db_Table_Transactions::DATE_INSERTED, 'created');
		$selectBuilder->select->add("t." . Pap_Db_Table_Transactions::COMMISSION);
		$selectBuilder->from->add(Pap_Db_Table_Transactions::getName(), "t");
		$onCondition = "t." . Pap_Db_Table_Transactions::CAMPAIGN_ID . " = c." . Pap_Db_Table_Campaigns::ID;
		$selectBuilder->from->addLeftJoin(Pap_Db_Table_Campaigns::getName(), 'c', $onCondition);

		$selectBuilder->select->add("t." . Pap_Db_Table_Transactions::TOTAL_COST, 'total_cost');
		$selectBuilder->from->addInnerJoin(Pap_Db_Table_Users::getName(), "pu", "t.userid = pu.userid");
		$selectBuilder->from->addInnerJoin(Gpf_Db_Table_Users::getName(), "gu", "pu.accountuserid = gu.accountuserid");
		$selectBuilder->from->addInnerJoin(Gpf_Db_Table_AuthUsers::getName(), "au", "gu.authid = au.authid");
		$selectBuilder->select->add("au." . Gpf_Db_Table_AuthUsers::FIRSTNAME, 'affname');
		$selectBuilder->select->add("au." . Gpf_Db_Table_AuthUsers::LASTNAME, 'affsurname');
		$selectBuilder->select->add("pu.accountuserid", 'accountuserid');

		$selectBuilder->orderBy->add("t." . Pap_Db_Table_Transactions::DATE_INSERTED, true);
		$selectBuilder->where->add("t." . Pap_Db_Table_Transactions::DATE_INSERTED, '>=', $fromtime);
		return $selectBuilder;
	}

}
