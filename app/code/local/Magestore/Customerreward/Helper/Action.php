<?php

class Magestore_Customerreward_Helper_Action extends Mage_Core_Helper_Abstract
{
	public function addTransaction($action='' ,$customer = null , $object =null , $extraContent = array()){
		if (Mage::helper('customerreward')->isDisabled()) return false;
		try{
			Magestore_Customerreward_Model_Actions_Abstract::getInstance($action,$customer,$object,$extraContent)->addTransaction()->additionAction();
		} catch(Exception $e){
			return false;
		}
		return $this;
	}
}