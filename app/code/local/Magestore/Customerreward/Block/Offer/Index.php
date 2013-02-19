<?php
class Magestore_Customerreward_Block_Offer_Index extends Magestore_Customerreward_Block_Offer
{
    protected function _getOrder(){
    	$order = new Varien_Object(array(
			'created_at'	=> now(),
			'reward_product_id'	=> $this->getRequest()->getParam('id')
		));
		return $order;
    }
    
    public function getOffer(){
    	if (!$this->_offer){
    		$order = $this->_getOrder();
    		$customer = $this->_getCustomer();
    		if ($order && $customer)
    			$this->_offer = Mage::getModel('customerreward/offer')->loadByCustomerOrder($customer,$order);
  		}
  		if ($this->getIsReferal()) return null;
    	return $this->_offer;
    }
}