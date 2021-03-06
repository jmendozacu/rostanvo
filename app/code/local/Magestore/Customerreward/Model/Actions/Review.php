<?php

class Magestore_Customerreward_Model_Actions_Review extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'review';
	protected $_title = 'Receive point(s) from your review of %s';
	
	protected function _calculateAmount(){
		$review = $this->getActionObject();
		$storeId = $review->getStoreId();
		
		$amount = (int)Mage::helper('customerreward')->getEarnConfig('review',$storeId);
		$amountPerDay = (int)Mage::helper('customerreward')->getEarnConfig('review_limit',$storeId);
		
		$amountOfDay = Mage::helper('customerreward')->getAmountOfDay($this->getCustomer()->getId(),$this->getAction(),Mage::getModel('core/date')->gmtTimestamp($review->getCreatedAt()));
		
		if ($amount > 0
			&& ($amountOfDay + $amount) > $amountPerDay){ 
			if ($amountOfDay < $amountPerDay )
				$amount = $amountPerDay - $amountOfDay;
			else
				$amount = 0;
		}
		
		$this->setAmount($amount);
		return $this;
	}
	
	public function getTitle(){
		return Mage::helper('customerreward')->__($this->_title,$this->_extraContent['product_name']);
	}
	
	public function getTitleHtml($isAdminArea = false){
		parse_str($this->getTransaction()->getData('extra_content'),$product);
		if ($isAdminArea){
			$url = Mage::getUrl('adminhtml/catalog_product/edit',array('id' => $product['product_id']));
		}else{
			$url = Mage::getUrl('review/product/list',array('id' => $product['product_id']));
		}
		return Mage::helper('customerreward')->__($this->_title,'<a href="'.$url.'">'.$product['product_name'].'</a>');
	}
}