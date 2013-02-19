<?php
class Magestore_Customerreward_Block_History extends Mage_Core_Block_Template
{
	protected function _construct(){
		parent::_construct();
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$collection = Mage::getModel('customerreward/transaction')->getCollection()
			->addFieldToFilter('customer_id',$customer->getId())
			->setOrder('create_at','DESC');
		$this->setCollection($collection);
	}
	public function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager','history_pager')->setCollection($this->getCollection());
		$this->setChild('history_pager',$pager);
		return $this;
    }
    
    public function getPagerHtml(){
    	return $this->getChildHtml('history_pager');
    }
}