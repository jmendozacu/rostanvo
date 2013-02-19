<?php
class Magestore_Customerreward_Block_Orderrule extends Mage_Core_Block_Template
{
	protected function _construct(){
		parent::_construct();
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$day = new Varien_Object(array('created_at' => now()));
		$collection = Mage::getModel('customerreward/rule')->getCollection()
			->getAvailableRule($customer,$day);
		$this->setCollection($collection);
	}
	
	public function _prepareLayout(){
		return parent::_prepareLayout();
    }
}