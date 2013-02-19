<?php

class Magestore_Customerreward_Model_Transaction extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/transaction');
    }
    
    public function getCustomer(){
    	return Mage::getModel('customer/customer')->load($this->getCustomerId());
    }
    
    public function createTransaction($amount = 0, $action = '', $title = '', $customer = null, $rewardCustomer = null, $extraContent = array()){
    	$expiration_date = null;
    	if ($amount > 0) $expiration_date = isset($extraContent['expiration_date']) ? $extraContent['expiration_date'] : $this->getExpirationDate();
    	
    	if (isset($extraContent['notice'])) $extraContent['notice'] = htmlspecialchars($extraContent['notice']);
    	
    	if (is_array($extraContent['extra_content'])){
    		$extra_content = new Varien_Object($extraContent['extra_content']);
    		$extraContent['extra_content'] = $extra_content->serialize(null,'=','&','');
    	}
    	
    	$this->addData($extraContent)
    		->setTitle($title)
    		->setCustomerId($customer->getId())
    		->setAction($action)
    		->setCreateAt(Mage::getModel('core/date')->gmtDate())
   			->setExpirationDate($expiration_date)
   			->setPointsChange($amount)
   			->setPointsSpent(0)
   			->setIsExpired(0)
			->save();
    	
    	if (is_array($extraContent['customer_extra_content'])){
   			parse_str($rewardCustomer->getExtraContent(),$current_customer_extra_content);
   			$customerExtraContent = new Varien_Object($current_customer_extra_content);
    		$customerExtraContent->addData($extraContent['customer_extra_content']);
    		$rewardCustomer->setExtraContent($customerExtraContent->serialize(null,'=','&',''));
    	}
    	$rewardCustomer->setTotalPoints($rewardCustomer->getTotalPoints()+$amount)->save();
    	
    	return $this;
    }
    
    public function getExpirationDate(){
    	if ( $timeLife = Mage::helper('customerreward')->getEarnConfig('expire')){
    		//$currentDate = Mage::getModel('core/date')->gmtDate();
    		$expire = new Zend_Date();//$currentDate);
    		$expire->addDay($timeLife);
    		return $expire->toString('YYYY-MM-dd HH:mm:ss');
    	}
    	return null;
    }
    
    public function getActionInstance(){
    	return Magestore_Customerreward_Model_Actions_Abstract::getInstance($this->getAction(),$this->getCustomer())->setTransaction($this);
    }
    
    public function loadByOrder($order,$action = 'spend'){
    	$this->getResource()->loadByOrder($this, $order, $action);
    	return $this;
    }
    
    public function loadByProductId($customerId,$productId,$action = 'review'){
    	$this->getResource()->loadByProductId($this,$customerId,$productId,$action);
    	return $this;
    }
}