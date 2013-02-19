<?php

class Magestore_Customerreward_Model_Offer extends Mage_Rule_Model_Rule
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/offer');
        $this->setIdFieldName('offer_id');
    }
    
    public function getConditionsInstance(){
    	return Mage::getModel('customerreward/rule_condition_combine');
    }
    
    public function checkRule($order){
    	if ($this->getIsActive()){
    		$this->afterLoad();
    		if (!$order->getId()){
    			$conditions = $this->getConditions()->asArray();
    			if (array_key_exists('conditions',$conditions)) return false;
    		}
    		return $this->validate($order);
    	}
    	return false;
    }
    
    public function toString($format=''){
		$str = Mage::helper('customerreward')->__('Name: %s', $this->getTitle()) ."\n"
	 		.Mage::helper('customerreward')->__('Start at: %s', $this->getFromDate()) ."\n"
			.Mage::helper('customerreward')->__('Expire at: %s', $this->getToDate()) ."\n\n"
			.$this->getConditions()->toStringRecursive() ."\n\n";
		return $str;
	}
	
	public function loadByCustomerOrder($customer,$order,$referalOffer=null){
		$offerCollection = $this->getCollection()
			->getAvailableOffer($customer,$order);
		$orderProductIds = $this->_getProductIds($order);
		if ($referalOffer && $referalOffer->getId()){
			$productIds = $referalOffer->getProductIds();
			foreach ($orderProductIds as $key => $value)
				if (in_array($value,$productIds))
					unset($orderProductIds[$key]);
		}
		foreach ($offerCollection as $offer)
			if ($offer->checkRule($order))
				foreach ($orderProductIds as $productId)
					if (in_array($productId,$offer->getProductIds()))
						return $offer;
		return $this;
	}
	
	public function loadByCount($countModel){
		if (!$countModel->getId()) return $this;
		$customer = Mage::getModel('customer/customer')->load($countModel->getCustomerId());
		$order = new Varien_Object(array('created_at'=>now()));
		$offer = $this->getCollection()
			->getAvailableOffer($customer,$order)
			->addFieldToFilter('offer_id',$countModel->getOfferId())
			->getFirstItem();
        if (Mage::helper('customerreward')->getReferConfig('coupon'))
            $offer->setDiscountMethod(Magestore_Customerreward_Helper_Offer::OFFER_METHOD_DISCOUNT)
                ->setDiscountShow(Magestore_Customerreward_Helper_Offer::SHOW_OFFER_IN_CART);
        return $offer;
	}
	
	public function getProductIds(){
		$productIds = explode(',',$this->getProducts());
		$categoryIds = array_unique(explode(',',$this->getCategories()));
		foreach ($categoryIds as $categoryId)
			if ($categoryId != '')
				$productIds = array_merge($productIds,Mage::getModel('catalog/category')->load($categoryId)->getProductCollection()->getAllIds());
		return $productIds;
	}
	
	public function getProductIdsByOrder($order){
		$productIds = explode(',',$this->getProducts());
		$categoryIds = array_unique(explode(',',$this->getCategories()));
		foreach ($categoryIds as $categoryId)
			if ($categoryId != ''){
				$category = Mage::getModel('catalog/category')->load($categoryId);
				$productCollection = Mage::getResourceModel('catalog/product_collection')
					->setStoreId($order->getStoreId())
					->addCategoryFilter($category);
				$productIds = array_merge($productIds,$productCollection->getAllIds());
			}
		return $productIds;
	}
    
    protected function _getProductIds($order){
    	$productIds = array();
    	if ($order->getRewardProductId())
    		$productIds[] = $order->getRewardProductId();
    	elseif ($order->getId())
	    	foreach ($order->getItemsCollection() as $item)
	    		if (!$item->isDeleted())
	    			$productIds[] = $item->getProductId();
    	return $productIds;
    }
}