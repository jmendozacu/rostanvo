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
class Pap_Mobile_Main extends Gpf_Object {

	const SESSION_PARAM = 'session';
	const USERNAME_PARAM = 'username';
	const PASSWORD_PARAM = 'password';

	public function execute(){
		$response = '';
		try {
			if(isset($_REQUEST['action'])){
				$action = @$_REQUEST['action'];
				$response = $this->createResponse($action);
			}else {
				$response = new Pap_Mobile_Response(false);
				$response->exception = Gpf_Lang::_('Unsupported action');
			}
		} catch (Gpf_Rpc_SessionExpiredException $e) {
			$response = new Pap_Mobile_Response(false);
			$response->exception = 'session_expired';
		} catch (Exception $e) {
			$response = new Pap_Mobile_Response(false);
			$response->exception = Gpf_Lang::_('Response failed on server: ').$e->getMessage();
			// 			$response->trace = $e->getTraceAsString();
		}
		$json = new Gpf_Rpc_Json();
		echo $json->encode($response);
	}

	private function createResponse($action){
		$responseManager = new Pap_Mobile_ResponseManager();
		if($action=='login'){
			$response = $responseManager->login($_REQUEST[self::USERNAME_PARAM],
			$_REQUEST[self::PASSWORD_PARAM]);
		} else if($action=='widget'){
			$response = $responseManager->widget($_REQUEST[self::USERNAME_PARAM],
			$_REQUEST[self::PASSWORD_PARAM]);
		} else if($action=='sell_notification'){
			if(isset($_REQUEST['fromtime']))
			$response = $responseManager->sellNotification($this->getSession(), $_REQUEST['fromtime']);
			else $response = $responseManager->sellNotification($this->getSession());
		} else if($action=='waiting_tasks'){
			$response = $responseManager->waitingTasks($this->getSession());
		} else if($action=='load_transactions'){
			$from = $_REQUEST['from']; $to = $_REQUEST['to'];
			if(isset($_REQUEST['fromtime'])) {
				$response = $responseManager->loadTransactionsFromTime($this->getSession(),
				$_REQUEST['fromtime'], $from, $to);
			} else $response = $responseManager->loadTransactions($this->getSession(), $from, $to);
		} else if($action=='trafic_overview'){
			$isToday = $_REQUEST['period'] == 'today';
			$response = $responseManager->traficOverview($this->getSession(),$isToday);
		} else if($action=='notification_registration'){
			$id = $_REQUEST['id'];
			$isregister = $_REQUEST['register'] != 'false';
			$client_type = $_REQUEST['client_type'];
			if($isregister) $response = $responseManager->registerNotifications($this->getSession(),$id,$client_type);
			else $response = $responseManager->unregisterNotifications($this->getSession(),$id);
		} else {
			$response = new Pap_Mobile_Response(false);
			$response->exception = Gpf_Lang::_('Unsupported action');
		}
		return $response;
	}

	private function getSession(){
		return $_REQUEST[self::SESSION_PARAM];
	}
}
?>
