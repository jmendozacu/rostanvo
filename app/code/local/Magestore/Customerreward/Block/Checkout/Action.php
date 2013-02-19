<?php
class Magestore_Customerreward_Block_Checkout_Action extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		return parent::_prepareLayout();
	}
	
	public function isShow(){
		return (!Mage::helper('customerreward')->isDisabled());// && Mage::helper('customerreward')->getReferConfig('enable'));
	}
}