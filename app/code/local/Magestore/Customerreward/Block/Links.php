<?php
class Magestore_Customerreward_Block_Links extends Mage_Core_Block_Template
{
	protected function _construct(){
		parent::_construct();
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$collection = Mage::getModel('customerreward/count')->getCollection()
			->joinOffer()
			->addFieldToFilter('customer_id',$customer->getId())
			->setOrder('visit_count','DESC');
		$this->setCollection($collection);
	}
	public function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager','links_pager')->setCollection($this->getCollection());
		$this->setChild('links_pager',$pager);
		return $this;
    }
    
    public function getPagerHtml(){
    	return $this->getChildHtml('links_pager');
    }
    
    public function getShareUrl($key){
    	return $this->getUrl('customerreward/offer/view',array('k' => $key));//.'?k='.$key;
    }
    
    public function getDayFormat($day){
    	return Mage::getModel('core/date')->date('M d, Y',$day);
    }
}