<?php

class Magestore_Customerreward_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isDisabled($store = null){
		return Mage::getStoreConfig('advanced/modules_disable_output/Magestore_Customerreward',$store);
	}
	
	public function getReferConfig($code,$store = null){
		return Mage::getStoreConfig('customerreward/refer/'.$code,$store);
	}
	
	public function getEarnConfig($code,$store = null){
		return Mage::getStoreConfig('customerreward/earn/'.$code,$store);
	}
	
	public function getEmailConfig($code,$store = null){
		return Mage::getStoreConfig('customerreward/email/'.$code,$store);
	}
	
	public function getExtraConfig($code,$store = null){
		return Mage::getStoreConfig('customerreward/extra/'.$code,$store);
	}
	
	public function getAmountOfDay($customerId,$action,$day){
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('core_read');
		$select = $read->select()
			->from($resource->getTableName('customerreward/transaction'),array('SUM(`points_change`) as total'))
			->where('customer_id = ?',$customerId)
			->where('action =? ',$action)
			->where('date(create_at) = ?',date('Y-m-d',$day));
		$result = $read->fetchRow($select);
		return $result['total'];
	}
	
	public function getCustomerrewardLabel(){
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
		$balance = (int)Mage::getModel('customerreward/customer')->loadByCustomerId($customer->getId())->getData('total_points');
    	$money = $balance * Mage::getModel('customerreward/rate')->getPointToMoneyRate($customer->getWebsiteId(),$customer->getGroupId());
    	$currentMoney = Mage::app()->getStore()->convertPrice($money);
    	$moneyText = Mage::app()->getStore()->formatPrice($money);
    	// if ($moneyText)
    		// $moneyText = '<span class="customerreward-money">'.$moneyText.'</span>';
    	$icon = $this->getIconImage();
		return $this->__('My Rewards %s %s',$moneyText,$icon);
	}
	
	public function getIcon(){
		return '<a href="'.$this->getInfoLink().'" class="customerreward-icon" title="'.$this->__('More information').'">'.$this->getIconImage().'</a>';
	}
	
	public function getIconImage(){
		//return '<img src="'.Mage::getDesign()->getSkinUrl('images/customerreward/point.png').'" />';
		return '<img src="'.Mage::getDesign()->getSkinUrl('images/dollar-icon-18x18.png').'" />';
	}
	
	public function getInfoLink(){
		return Mage::getUrl(null,array('_direct' => $this->getExtraConfig('info')));
	}
}