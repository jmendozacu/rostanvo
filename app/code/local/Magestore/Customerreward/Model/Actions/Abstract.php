<?php

abstract class Magestore_Customerreward_Model_Actions_Abstract
{
	const XML_CONFIG_ACTION_MODELS = 'customerreward/action_models/'; 
	
	protected $_action = '';
	protected $_title = '';
	
	protected $_customer;
	protected $_rewardCustomer;
	protected $_actionObject;
	protected $_transaction;
	
	protected $_amount;
	protected $_extraContent = array();
	
	//get action
	public function getAction(){
		return $this->_action;
	}
	
	//set and get title
	public function setTitle($title){
		$this->_title = $title;
		return $this;
	}
	public function getTitle(){
		return $this->_title;
	}
	
	//set and get customer
	public function setCustomer($customer){
		$this->_customer = $customer;
		return $this;
	}
	public function getCustomer(){
		return $this->_customer;
	}
	
	//set and get reward customer
	public function setRewardCustomer($rewardCustomer){
		$this->_rewardCustomer = $rewardCustomer;
		return $this;
	}
	public function getRewardCustomer(){
		return $this->_rewardCustomer;
	}
	
	//set and get oject attach to the action
	public function setActionObject($object){
		$this->_actionObject = $object;
		return $this;
	}
	public function getActionObject(){
		return $this->_actionObject;
	}
	
	//get and set transaction
	public function setTransaction($transaction){
		$this->_transaction = $transaction;
		return $this;
	}
	public function getTransaction(){
		return $this->_transaction;
	}
	
	//set and get amount
	public function setAmount($amount){
		$this->_amount = $amount;
		return $this;
	}
	public function getAmount(){
		return $this->_amount;
	}
	
	//set and get extra content for action
	public function setExtraContent($extraContent){
		$this->_extraContent = $extraContent;
		return $this;
	}
	public function getExtraContent(){
		return $this->_extraContent;
	}
	
	// calculate amount for action, need to rewrite of the instance class
	protected function _calculateAmount(){
		$this->setAmount(0);
		return $this;
	}
	
	// get Display title
	public function getTitleHtml($isAdminArea = false){
		return $this->getTitle();
	}
	
	// addition action
	public function additionAction(){
		return $this;
	}
	
	// get the instance of this class
	public static function getInstance($action='' ,$customer = null , $object =null , $extraContent = array()){
		$model_path = Mage::getStoreConfig(self::XML_CONFIG_ACTION_MODELS.$action);
		if (!$model_path) throw new Mage_Core_Exception(Mage::helper('customerreward')->__('Cannot find the instance for this action!'));
		
		$instance = Mage::getModel($model_path);
		if (!($instance instanceof Magestore_Customerreward_Model_Actions_Abstract)) throw new Mage_Core_Exception(Mage::helper('customerreward')->__('Cannot find the instance for this action!'));
		
		$instance->setCustomer($customer)
				->setActionObject($object)
				->setExtraContent($extraContent);
		
		return $instance;
	}
	
	// recalculate amount for action (apply the limit config)
	protected function _reCalculateAmount(){
		$maxPoints = Mage::helper('customerreward')->getEarnConfig('max');
		$this->_rewardCustomer = Mage::getModel('customerreward/customer')->loadByCustomerId($this->getCustomer()->getId());
		if ($this->getRewardCustomer() && $this->getRewardCustomer()->getId()){
			$currentPoints = $this->getRewardCustomer()->getData('total_points');
			if ($maxPoints
				&& $this->getAmount() > 0
				&& ($currentPoints + $this->getAmount()) > $maxPoints){
				if ($maxPoints > $currentPoints){
					return $maxPoints-$currentPoints;
				}
				return 0;
			}
			return $this->getAmount();
		}else{
			//Mage::getSingleton('customer/session')->addError(Mage::helper('customerreward')->__('You must login to the system to earn point(s)!'));
			throw new Mage_Core_Exception(Mage::helper('customerreward')->__('You must login to the system to earn point(s)!'));
		}
	}
	
	//add transaction
	public function addTransaction(){
		$this->_calculateAmount();
		$extraContent = $this->getExtraContent();
		if ($this->getActionObject() instanceof Varien_Object && $this->getActionObject()->getData('store_id')){
			$extraContent['store_id'] = $this->getActionObject()->getData('store_id');
		}else{
			$extraContent['store_id'] = Mage::app()->getStore()->getId();
		}
		
		if ($amount = $this->_reCalculateAmount()){
			$this->_transaction = Mage::getModel('customerreward/transaction')
				->createTransaction(
					$amount,
					$this->getAction(),
					$this->getTitle(),
					$this->getCustomer(),
					$this->getRewardCustomer(),
					$extraContent
				);
			if ($this->_transaction->getData('points_change') < 0){
				$this->getTransaction()->getCollection()
					->updatePointsSpent($this->_transaction->getData('points_change'),$this->getCustomer()->getId());
			}
			if (Mage::helper('customerreward')->getEmailConfig('enable')
				&& $this->getRewardCustomer()->getData('is_notification')){
				$this->_sendEmailNotification();
			}
			//return $this;
		}//else{
			return $this;
			//throw new Mage_Core_Exception(Mage::helper('customerreward')->__('Zero amount!'));
		//}
	}
	
	//send email notification
	protected function _sendEmailNotification(){
		$store = $this->getCustomer()->getStore();
		
		$translate = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		
		$mailTemplate = Mage::getModel('core/email_template')
			->setDesignConfig(array(
				'area'	=> 'frontend',
				'store'	=> $store->getStoreId()
			))
			->sendTransactional(
				Mage::helper('customerreward')->getEmailConfig('update_balance'),
				Mage::helper('customerreward')->getEmailConfig('sender'),
				$this->getCustomer()->getEmail(),
				$this->getCustomer()->getName(),
				array(
					'store'	=> $store,
					'customer'	=> $this->getCustomer(),
					'title'	=> $this->getTransaction()->getTitle(),
					'amount'	=> $this->getTransaction()->getPointsChange(),
					'total'	=> $this->getRewardCustomer()->getTotalPoints(),
				)
			);
		
		$translate->setTranslateInline(true);
		return $this;
	}
}