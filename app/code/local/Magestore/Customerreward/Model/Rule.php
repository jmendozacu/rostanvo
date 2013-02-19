<?php

class Magestore_Customerreward_Model_Rule extends Mage_Rule_Model_Rule
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/rule');
        $this->setIdFieldName('rule_id');
    }
    
    public function getConditionsInstance(){
    	return Mage::getModel('customerreward/rule_condition_combine');
    }
    
    public function checkRule($order){
    	if ($this->getIsActive()){
    		$this->afterLoad();
    		return $this->validate($order);
    	}
    	return false;
    }
    
    public function loadByCustomerOrder($customer,$order){
    	$ruleColection = $this->getCollection()
    		->getAvailableRule($customer,$order);
   		foreach ($ruleColection as $rule){
   			if ($rule->checkRule($order)){
   				return $rule;
   			}
   		}
   		return $this;
    }
    
    public function toString($format=''){
		$str = Mage::helper('customerreward')->__('Name: %s', $this->getTitle()) ."\n"
	 		.Mage::helper('customerreward')->__('Start at: %s', $this->getFromDate()) ."\n"
			.Mage::helper('customerreward')->__('Expire at: %s', $this->getToDate()) ."\n"
			.Mage::helper('customerreward')->__('Description: %s', $this->getDescription()) ."\n\n"
			.$this->getConditions()->toStringRecursive() ."\n\n";
		return $str;
	}
}