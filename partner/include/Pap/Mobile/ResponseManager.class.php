<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Rene Dohan
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 *
 */

/**
 * @package PostAffiliate
 */
class Pap_Mobile_ResponseManager extends Gpf_Object {
	/**
	 * @var Pap_Common_User
	 */
	private $user;

	public function login($username, $password){
		try {
			$this->loginFromCredentials($username, $password);
		}catch (Exception $e){
			return new Pap_Mobile_Response(false);
		}
		if($this->isLogged()){
			$response = new Pap_Mobile_Response(true);
			$response->session = Gpf_Session::getInstance()->getId();
			if($this->isAffiliate()){
				$response->user_type = 'affiliate';
			} else {
				$response->user_type = 'merchant';
			}
			return $response;
		}else{
			return new Pap_Mobile_Response(false);
		}
	}

	public function widget($username, $password){
		try {
			$this->loginFromCredentials($username, $password);
		}catch (Exception $e){
			return new Pap_Mobile_Response(false);
		}
		$statParams = $this->getLastDayStatsParams();
		$response = new Pap_Mobile_Response(true);

		$clicks = new Pap_Stats_Clicks($statParams);
		$response->clicks = (int)$clicks->getCount()->getRaw();

		$sales = new Pap_Stats_Sales($statParams);
		$salesCount = $sales->getCount()->getApproved() + $sales->getCount()->getPending();
		$response->transactions = (int)$salesCount;

		return $response;
	}

	public function registerNotifications($session, $notificationId, $clientType){
		if($clientType=='android') $clientType = Gpf_Db_NotificationRegistration::TYPE_ANDROID;
		else $clientType = Gpf_Db_NotificationRegistration::TYPE_IOS;
		
		$this->loginFromSession($session);
		$notificationRegistration = new Gpf_Db_NotificationRegistration();
		$notificationRegistration->setNotificationId($notificationId);
		$notificationRegistration->setClientType($clientType);
		$notificationRegistration->setAccountUserId($this->user->getAccountUserId());
                $date = new Gpf_DateTime();
                $notificationRegistration->setRegistrationTime($date->toDateTime());
		$notificationRegistration->save();
		return new Pap_Mobile_Response(true);
	}

	public function unregisterNotifications($session, $notificationId){
		$this->loginFromSession($session);
		$notificationRegistration = new Gpf_Db_NotificationRegistration();
		$notificationRegistration->setNotificationId($notificationId);
		$notificationRegistration->delete();
		return new Pap_Mobile_Response(true);
	}

	public function sellNotification($session,$fromtime = null){
		$this->loginFromSession($session);
		$response = new Pap_Mobile_Response(true);
		$responseSells = array();
		if($fromtime){
			$select = $this->createSellNotificationSelect(Gpf_Common_DateUtils::getDateTime($fromtime));
			foreach ($select->getAllRows() as $row){
				$responseRow = new stdClass();
				$responseRow->campaign_name = (string)$row->get('campaign_name');
				$responseRow->commission = $this->toCurency($row->get('commission'));
				if($this->isMerchant()){
					$responseRow->total_cost = $this->toCurency($row->get('total_cost'));
					$responseRow->affiliate_name = $row->get('affname').' '.$row->get('affsurname');
				}
				$responseSells[] = $responseRow;
			}
		}
		$response->sells = $responseSells;
		$response->time = (string)Gpf_Common_DateUtils::getTimestamp(Gpf_Common_DateUtils::now());
		return $response;
	}

	/**
	 * @return Gpf_SqlBuilder_SelectBuilder
	 */
	private function createSellNotificationSelect($fromtime){
		$selectBuilder = new Gpf_SqlBuilder_SelectBuilder();
		$selectBuilder->select->add("c.".Pap_Db_Table_Campaigns::NAME,'campaign_name');
		$selectBuilder->select->add("t.".Pap_Db_Table_Transactions::DATE_INSERTED,'created');
		$selectBuilder->select->add("t.".Pap_Db_Table_Transactions::COMMISSION);
		$selectBuilder->from->add(Pap_Db_Table_Transactions::getName(), "t");
		$onCondition = "t.".Pap_Db_Table_Transactions::CAMPAIGN_ID." = c.".Pap_Db_Table_Campaigns::ID;
		$selectBuilder->from->addLeftJoin(Pap_Db_Table_Campaigns::getName(), 'c', $onCondition);
		if($this->isAffiliate()){
			$selectBuilder->where->add("t.".Pap_Db_Table_Transactions::USERID,'=',$this->user->getId());
		}
		if($this->isMerchant()){
			$selectBuilder->select->add("t.".Pap_Db_Table_Transactions::TOTAL_COST,'total_cost');
			$selectBuilder->select->add("au.".Gpf_Db_Table_AuthUsers::FIRSTNAME,'affname');
			$selectBuilder->select->add("au.".Gpf_Db_Table_AuthUsers::LASTNAME,'affsurname');
			$selectBuilder->from->addInnerJoin(Pap_Db_Table_Users::getName(), "pu", "t.userid = pu.userid");
			$selectBuilder->from->addInnerJoin(Gpf_Db_Table_Users::getName(), "gu", "pu.accountuserid = gu.accountuserid");
			$selectBuilder->from->addInnerJoin(Gpf_Db_Table_AuthUsers::getName(), "au", "gu.authid = au.authid");
		}
		$selectBuilder->orderBy->add("t.".Pap_Db_Table_Transactions::DATE_INSERTED, true);
		$selectBuilder->where->add("t.".Pap_Db_Table_Transactions::DATE_INSERTED, '>=', $fromtime);
		return $selectBuilder;
	}

	public function traficOverview($session,$today){
		$this->loginFromSession($session);
		$homeData = new Pap_Merchants_HomeData();
		if($today){
			$statParams = $homeData->getStatsParams('T');
		}else{
			$statParams = $homeData->getStatsParams('TM');
		}
		$response = new Pap_Mobile_Response(true);

		$impressions = new Pap_Stats_Impressions($statParams);
		$response->impressionsraw = (int)$impressions->getCount()->getRaw();
		$response->impressionsunique = (int)$impressions->getCount()->getUnique();

		$clicks = new Pap_Stats_Clicks($statParams);
		$response->clicksraw = (int)$clicks->getCount()->getRaw();
		$response->clicksunique = (int)$clicks->getCount()->getUnique();

		$sales = new Pap_Stats_Sales($statParams);

		$response->salespaid = (int)$sales->getCount()->getPaid();
		$response->salespending = (int)$sales->getCount()->getPending();
		$response->salesapproved = (int)$sales->getCount()->getApproved();

		$response->salescostpaid = $this->toCurency($sales->getTotalCost()->getPaid());
		$response->salescostpending = $this->toCurency($sales->getTotalCost()->getPending());
		$response->salescostapproved = $this->toCurency($sales->getTotalCost()->getApproved());

		$commisions = new Pap_Stats_Transactions($statParams);
		$response->commisionspaid = $this->toCurency($commisions->getCommission()->getPaid());
		$response->commisionspending = $this->toCurency($commisions->getCommission()->getPending());
		$response->commisionsapproved = $this->toCurency($commisions->getCommission()->getApproved());

		$refunds = new Pap_Stats_Refunds($statParams);
		$response->refundspaid = $this->toCurency($refunds->getCommission()->getPaid());
		$response->refundspending = $this->toCurency($refunds->getCommission()->getPending());
		$response->refundsapproved = $this->toCurency($refunds->getCommission()->getApproved());

		$chargebacks = new Pap_Stats_Chargebacks($statParams);
		$response->chargebackspaid = $this->toCurency($chargebacks->getCommission()->getPaid());
		$response->chargebackspending = $this->toCurency($chargebacks->getCommission()->getPending());
		$response->chargebacksapproved = $this->toCurency($chargebacks->getCommission()->getApproved());

		return $response;
	}

	public function loadTransactionsFromTime($session, $fromTime, $from , $to){
		$this->loginFromSession($session);
		$select = $this->createTransactionsSelect($from,$to);
		$select->where->add("t.".Pap_Db_Table_Transactions::DATE_INSERTED, '>=', Gpf_Common_DateUtils::getDateTime($fromTime));
		return $this->createTransactionResponse($select);
	}

	public function loadTransactions($session,$from , $to){
		$this->loginFromSession($session);

		$selectBuilder = new Gpf_SqlBuilder_SelectBuilder();
		$selectBuilder->select->add('count(*)', 'count');
		$selectBuilder->from->add(Pap_Db_Table_Transactions::getName(), "t");
		if($this->isAffiliate()){
			$selectBuilder->where->add("t.".Pap_Db_Table_Transactions::USERID,'=',$this->user->getId());
		}
		$count = $selectBuilder->getOneRow()->get('count');

		$fromtime = null;
		if($count > 0){
			$selectBuilder = new Gpf_SqlBuilder_SelectBuilder();
			$selectBuilder->select->add("t.".Pap_Db_Table_Transactions::DATE_INSERTED,'created');
			$selectBuilder->from->add(Pap_Db_Table_Transactions::getName(), "t");
			if($this->isAffiliate()){
				$selectBuilder->where->add("t.".Pap_Db_Table_Transactions::USERID,'=',$this->user->getId());
			}
			$selectBuilder->limit->set(0, 1);
			$selectBuilder->orderBy->add("t.".Pap_Db_Table_Transactions::DATE_INSERTED, true);
			$fromtime = $selectBuilder->getOneRow()->get('created');
		}

		$response = $this->createTransactionResponse($this->createTransactionsSelect($from,$to));
		$response->all_count = (int)$count;
		$response->fromtime = (string)Gpf_Common_DateUtils::getTimestamp($fromtime);
		return $response;
	}

	/**
	 * @return Gpf_SqlBuilder_SelectBuilder
	 */
	private function createTransactionsSelect($from, $to){
		$selectBuilder = new Gpf_SqlBuilder_SelectBuilder();
		$selectBuilder->select->add("c.".Pap_Db_Table_Campaigns::NAME,'campaign_name');
		$selectBuilder->select->add("t.".Pap_Db_Table_Transactions::DATE_INSERTED,'created');
		$selectBuilder->select->add("t.".Pap_Db_Table_Transactions::COMMISSION);
		$selectBuilder->select->add("t.".Pap_Db_Table_Transactions::PRODUCT_ID);
		$selectBuilder->select->add("t.".Pap_Db_Table_Transactions::ORDER_ID);
		$selectBuilder->select->add("t.".Pap_Db_Table_Transactions::R_STATUS);
		if($this->isAffiliate()){
			$selectBuilder->where->add("t.".Pap_Db_Table_Transactions::USERID,'=',$this->user->getId());
		}
		$selectBuilder->from->add(Pap_Db_Table_Transactions::getName(), "t");
		$onCondition = "t.".Pap_Db_Table_Transactions::CAMPAIGN_ID." = c.".Pap_Db_Table_Campaigns::ID;
		$selectBuilder->from->addLeftJoin(Pap_Db_Table_Campaigns::getName(), 'c', $onCondition);
		if($this->isMerchant()){
			$selectBuilder->select->add("au.".Gpf_Db_Table_AuthUsers::FIRSTNAME,'affname');
			$selectBuilder->select->add("au.".Gpf_Db_Table_AuthUsers::LASTNAME,'affsurname');
			$selectBuilder->from->addInnerJoin(Pap_Db_Table_Users::getName(), "pu", "t.userid = pu.userid");
			$selectBuilder->from->addInnerJoin(Gpf_Db_Table_Users::getName(), "gu", "pu.accountuserid = gu.accountuserid");
			$selectBuilder->from->addInnerJoin(Gpf_Db_Table_AuthUsers::getName(), "au", "gu.authid = au.authid");
		}
		$selectBuilder->orderBy->add("t.".Pap_Db_Table_Transactions::DATE_INSERTED, false);
		$selectBuilder->limit->set($from, $to - $from);
		return $selectBuilder;
	}

	private function createTransactionResponse(Gpf_SqlBuilder_SelectBuilder $select){
		$response = new Pap_Mobile_Response(true);
		$responseRows = array();
		foreach ($select->getAllRows() as $row){
			$responseRow = new stdClass();
			$responseRow->campaign_name = (string)$row->get('campaign_name');
			$responseRow->commision = $this->toCurency($row->get('commission'));
			$responseRow->order_id = (string)$row->get('orderid');
			$responseRow->product_id = (string)$row->get('productid');
			$responseRow->created = (string)$this->formatTransactionDate($row->get('created'));
			$responseRow->status = (string)$row->get('rstatus');
			if($this->isMerchant()) $responseRow->affiliate_name = $row->get('affname').' '.$row->get('affsurname');
			$responseRows[] = $responseRow;
		}
		$response->rows = $responseRows;
		return $response;
	}

	private function formatTransactionDate($created){
		$createdStamp = Gpf_Common_DateUtils::getTimeStamp($created);
		$datetime = new Gpf_DateTime();
		$today = $datetime->getDayStart();
		$yesterday = $datetime->getDayStart();
		$yesterday->addDay(-1);

		if($createdStamp >= $today->toTimeStamp()){
			return $this->_("today");
		}
		if($createdStamp >= $yesterday->toTimeStamp()){
			return $this->_("yesterday");
		}
		return Gpf_Common_DateUtils::getDate($createdStamp);
	}

	private function toCurency($value){
		return (string)Pap_Common_Utils_CurrencyUtils::toStandardCurrencyFormat($value);
	}

	public function waitingTasks($session){
		$this->loginFromSession($session);
		$pendingTasksGadget = new Pap_Merchants_ApplicationGadgets_PendingTasksGadgets();
		$response = new Pap_Mobile_Response(true);
		$response->pending_affiliates = (int)$pendingTasksGadget->getPendingAffiliatesCount();
		$response->pending_commisions = (int)$pendingTasksGadget->getPendingTransactionsInfo()->get("pendingCommissions");
		$response->pending_links = (int)$pendingTasksGadget->getPendingDirectLinksCount();
		return $response;
	}

	private function loginFromCredentials($username, $password){
		$papAlertData = new Pap_Alert_Data();
		$userId = $papAlertData->findUserId($username, $password);

		$user = new Pap_Common_User();
		$user->setId($userId);
		$user->load();
		$roleType = $user->getType();

		Gpf_Session::create();

		$authInfo = new Gpf_Auth_InfoUsernamePassword($username, $password,
		Gpf_Db_Account::DEFAULT_ACCOUNT_ID, $roleType);
		$authUser = new Pap_AuthUser();
		$authUser->load($authInfo);
		Gpf_Session::getInstance()->save($authUser);

		$this->user = $user;
		$this->initializeApplication();
	}

	private function initializeApplication(){
		setlocale(LC_ALL, 'en.UTF-8');
		$timezone = Gpf_Settings_Gpf::DEFAULT_TIMEZONE;
		try {
			$timezone = Gpf_Settings::get(Gpf_Settings_Gpf::TIMEZONE_NAME);
		} catch (Gpf_Exception $e) {
			Gpf_Log::error('Unable to load timezone: %s - using default one.', $e->getMessage());
		}
		if(false === @date_default_timezone_set($timezone)) {
			Gpf_Log::error('Unable to set timezone %s:', $timezone);
		}
		$papAlertData = new Pap_Alert_Data();
		$papAlertData->computeTimeOffset();
	}

	private function isLogged(){
		return Gpf_Session::getAuthUser()->isLogged();
	}

	private function loginFromSession($session){
		Gpf_Session::create(null, $session);
		$papAlertData = new Pap_Alert_Data();
		$papAlertData->computeTimeOffset();

		$this->user = new Pap_Common_User();
		try {
			$this->user->setId(Gpf_Session::getAuthUser()->getPapUserId());
		} catch (Exception $e) {
			throw new Gpf_Rpc_SessionExpiredException();
		}
		$this->user->load();
		$this->initializeApplication();
	}

	private function isMerchant(){
		return !$this->isAffiliate();
	}

	private function isAffiliate(){
		$role = $this->user->getRoleId();
		if($role=='pap_merc'){
			return false;
		}else if($role=='pap_aff'){
			return true;
		}
		throw new Exception('Unknown RoleTypeId '.Gpf_Session::getAuthUser()->getUserId());
	}

	/**
	 * @return Pap_Stats_Params
	 */
	private function getLastDayStatsParams() {
		$statsParams = new Pap_Stats_Params();
		$statsParams->setDateFrom(new Gpf_DateTime(time()-86400));
		$statsParams->setDateTo(new Gpf_DateTime(time()));
		return $statsParams;
	}
}
?>
