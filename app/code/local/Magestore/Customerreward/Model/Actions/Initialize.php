<?php

class Magestore_Customerreward_Model_Actions_Initialize extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'initialize';
	protected $_title = 'Initial Point(s)';
	
	protected function _calculateAmount(){
		$this->setAmount((int)Mage::helper('customerreward')->getEarnConfig('initialize'));
		return $this;
	}
	/*
	public function getTitle(){
		return parent::getTitle();
	}
	
	public function getTitleHtml($isAdminArea = false){
		return parent::getTitleHtml();
	}
	
	public function additionAction(){
		return parent::additionAction();
	}
	*/
}