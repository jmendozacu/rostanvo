<?php
class Magestore_Customerreward_Block_Product extends Mage_Catalog_Block_Product_List
{
	protected function _getProductCollection(){
		if (is_null($this->_productCollection)){
			$productIds = array();
			$count = Mage::getModel('customerreward/count')->loadByKey(Mage::getSingleton('core/cookie')->get('customerreward_offer_key'));
			if ($count && $count->getId()){
				$offer = Mage::getModel('customerreward/offer')->loadByCount($count);
				if ($offer && $offer->getId())
					$productIds = $offer->getProductIds();
			}
			$this->_productCollection = Mage::getResourceModel('catalog/product_collection') 
				->setStoreId($this->getStoreId())
				->addAttributeToSelect('*')
				->addAttributeToFilter('entity_id',array('in' => $productIds))
				->addMinimalPrice()
				->addTaxPercents()
				->addStoreFilter();
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_productCollection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->_productCollection);
		}
		return $this->_productCollection;
	}
}