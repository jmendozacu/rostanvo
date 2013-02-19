<?php
class Magestore_Storepickup_Block_Checkout_Store extends Mage_Core_Block_Template
{
	public function __construct()
	{	
		parent::__construct();
		
		$this->setData('shipping_model',Mage::getModel('storepickup/shipping_storepickup'));
	}
	
	public function _prepareLayout()
    {
		$return = parent::_prepareLayout();
		
		$listStore = $this->getStoreByLocation();
		$this->setListStoreLocation($listStore);
		
		$storeId = Mage::app()->getStore()->getId();
		$storemap = Mage::getStoreConfig("carriers/storepickup/store_map",$storeId);
		if($storemap == 1)
			$this->setTemplate('storepickup/storepickupmap.phtml');
		else 
			$this->setTemplate('storepickup/storepickup.phtml');
		
		return $return;
	}
	
	public function getListTime()
	{
		return Mage::helper('storepickup')->getListTime();		
	}
	
	public function has_stores()
	{
		return true;
	}
	
	public function getStoreByLocation()
	{
		if(! $this->hasData('storecollection'))
		{
			if($this->getShippingModel()->getConfigData('active_gapi'))	
			{
				$stores =  Mage::getSingleton('storepickup/store')->filterStoresUseGAPI();
			} else {
				$stores =  Mage::getSingleton('storepickup/store')->convertToList();
			}
			$this->setData('storecollection',$stores);
		}
		return $this->getData('storecollection');
	}	

	//add for Store map
	public function getStore()
	{
		if(!$this->hasData('store_data'))
		{
			$stores = Mage::getModel('storepickup/store')->getCollection()
							->addFieldToFilter('status',1);
			$this->setData('store_data',$stores);				
		}
		
		return $this->getData('store_data');
	}
	
	public function getCoordinates()
	{
		$store = $this->getStore()->getFirstItem();
		$address['street'] = $store->getSuburb();
		$address['street'] = '';
		$address['city'] = $store->getCity();
		$address['region'] = $store->getRegion();
		$address['zipcode'] = $store->getZipcode();
		$address['country'] = $store->getCountryName();
		
		$coordinates = Mage::getModel('storepickup/gmap')
							->getCoordinates($address);
		if(! $coordinates)
		{	
			$coordinates['lat'] = '0.000';
			$coordinates['lng'] = '0.000';			
		}

		return $coordinates;
	}

	public function getMapSize()
	{
		$storeId = Mage::app()->getStore()->getId();
		$mapsize = trim(Mage::getStoreConfig("carriers/storepickup/map_size",$storeId));
		$size = explode('*',$mapsize);
		return $size;
	}		
}