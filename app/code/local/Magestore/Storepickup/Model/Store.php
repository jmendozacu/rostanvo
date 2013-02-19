<?php

class Magestore_Storepickup_Model_Store extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('storepickup/store');
    }
	
	public function getFormatedAddress()
	{
		$address = $this->getAddress();
		
		return $address.', '. $this->getCity() .', '. $this->getRegion() .', '. $this->getZipcode() 
				. ', '. $this->getCountryName();
	}
	
	public function getFormatedAddressforMap()
	{
		$address = $this->getAddress();
		
		return $address.', <br>'. $this->getCity() .', '. $this->getRegion() .', '. $this->getZipcode() 
				. ', <br>'. $this->getCountryName();
	}
	
	public function getCountryName()
	{
		if($this->getCountry())
		if(! $this->hasData('country_name'))
		{
			$country = Mage::getModel('directory/country')
								->loadByCode($this->getCountry());
			$this->setData('country_name',$country->getName());
		}
		
		return $this->getData('country_name');
	}	
	
	public function getRegion()
	{
		if(! $this->getData('region'))
		{
			$this->setData('region',$this->getState());
		}
		
		return $this->getData('region');
	}
	
	public function getCity()
	{
		if(! $this->getData('city'))
		{
			$this->setData('city',$this->getCity());
		}
		
		return $this->getData('city');
	}
	
	public function getSuburb()
	{
		if(!$this->getData('suburb'))
		{
			$this->setData('suburb',$this->getName());
		}
		
		return $this->getData('suburb');
	}

	public function import()
	{
		$data = $this->getData();
		//prepare status
		$data['status'] = 1;
			//check exited store
		$collection = $this->getCollection()
						->addFieldToFilter('store_name',$data['store_name'])
						->addFieldToFilter('store_manager',$data['store_manager'])
						->addFieldToFilter('store_phone',$data['store_phone'])
						->addFieldToFilter('state',$data['state'])
						->addFieldToFilter('city',$data['city'])
						->addFieldToFilter('suburb',$data['suburb'])
						->addFieldToFilter('address',$data['address'])
						;
						
		if(count($collection))
			return false;
		
		$this->setData($data);
		$this->save();
		
		return $this->getId();
	}
	
	public function save()
	{
		if(!$this->getStoreLatitude() || ! $this->getStoreLongitude())
		{
			$address['street'] = $this->getAddress();
			$address['city'] = $this->getCity();
			$address['region'] = $this->getRegion();
			$address['zipcode'] = $this->getZipcode();
			$address['country'] = $this->getCountryName();
			
			$coordinates = Mage::getModel('storepickup/gmap')
								->getCoordinates($address);
			if($coordinates)
			{
				$this->setStoreLatitude($coordinates['lat']);
				$this->setStoreLongitude($coordinates['lng']);
			}else{
				$this->setStoreLatitude('0.000');
				$this->setStoreLongitude('0.000');			
			}
		}
		
		return parent::save();
	}
	
	public function getListStoreByCustomerAddress()
	{
		$options = array();

		$cart = Mage::getSingleton('checkout/cart');
		$shippingAddress = Mage::helper('storepickup')->getCustomerAddress();

		$collection = $this->getCollection()
						->addFieldToFilter('country',$shippingAddress->getCountryId())	
					;			
		if($shippingAddress->getPostcode())
		{
			$collection->addFieldToFilter('zipcode',$shippingAddress->getPostcode());			
		}		
				
		if(is_array($shippingAddress->getStreet()))
		{
			$street = $shippingAddress->getStreet();
			$suburb = trim(substr($street[0],strrpos($street[0],',')+1));	
			$collection->addFieldToFilter('suburb',$suburb);			
		} else if($shippingAddress->getCity()){
			$collection->addFieldToFilter('city',$shippingAddress->getCity());
		} else if($shippingAddress->getRegion()){
			$collection->addFieldToFilter('state',$shippingAddress->getRegion());		
		}
		
		if(count($collection))
		foreach($collection as $store)
		{
			$options[$store->getId()] = $store->getStoreName();
		}		
		return $options;
	}
	
	public function getStoresUseGAPI()
	{
		$options = array();

		$cart = Mage::getSingleton('checkout/cart');
		$shippingAddress = Mage::helper('storepickup')->getCustomerAddress();

		$collection = $this->getCollection()
						->addFieldToFilter('country',$shippingAddress->getCountryId())	
					;			
		
		if($shippingAddress->getPostcode()){
			$collection->addFieldToFilter('zipcode',$shippingAddress->getPostcode());			
		}
		if($shippingAddress->getCity()){
			$collection->addFieldToFilter('city',$shippingAddress->getCity())					
					;
		}
		
		$stores = $this->filterStoresUseGAPI($collection);
		
		if(count($stores))
		foreach($stores as $store)
		{
			$options[$store->getId()] = $store->getStoreName() .' ('.number_format($store->getDistance()).' m)';
		}		
		return $options;
			
	}
	
	public function convertToList()
	{
		$options = array();
		$stores = $this->getCollection()
						->addFieldToFilter('status',1)
						->setOrder('store_name','ASC');
		if(count($stores))
		foreach($stores as $store)
		{
			$options[$store->getId()]['label'] = $store->getStoreName();
			$options[$store->getId()]['info'] = $store;
		}		
		return $options;		
	}	
	
	public function filterStoresUseGAPI()
	{
		$stores = array();
		$temp_array=array();
		$storeID = Mage::app()->getStore()->getId();
		$size=Mage::getStoreConfig('carriers/storepickup/num_store_real_distance',$storeID);
		$size= $size ? $size:10;
		$_storecollection = $this->getCollection()
									->addFieldToFilter('status',1)
									;
		
		if(!count($_storecollection))
			return $stores;
		
		$shippingAddress = Mage::helper('storepickup')->getCustomerAddress();
		$oGmap = Mage::getModel('storepickup/gmap');
		
		$street = $shippingAddress->getStreet();
		if(strrpos($street[0],','))
			$address['street'] = trim(substr($street[0],0,strrpos($street[0],',')));				
		else
			$address['street'] = $street[0];
		
		$address['city'] = $shippingAddress->getCity();
		$address['region'] = $shippingAddress->getRegion();
		$address['zipcode'] = $shippingAddress->getPostcode();
		$address['country'] = $shippingAddress->getCountryId();	
		$coordinates = $oGmap->getCoordinates($address);
		if(!$coordinates){
			$address['street'] = trim(substr($street[0],strrpos($street[0],',')+1));
			$coordinates = $oGmap->getCoordinates($address);			
		}
		
		if(!$coordinates)
			return $this->convertToList($_storecollection);
		
		$spoint['lat'] = $coordinates['lat'];
		$spoint['lng'] = $coordinates['lng'];
		foreach($_storecollection as $_store)
		{
			$dpoint['lat'] = $_store->getStoreLatitude();
			$dpoint['lng'] = $_store->getStoreLongitude();
			$distance=$this->loadDistance($spoint,$dpoint);
			$distance = $distance ? $distance : 999999999;
			$_store->setData('distance',$distance);
			$stores[] = $_store;
		}
		$storeID = Mage::app()->getStore()->getId();
		$top_n = Mage::getStoreConfig('carriers/storepickup/num_top_store',$storeID);
		$top_n = $top_n ? $top_n : 5;
		
		$stores = Mage::helper('storepickup/location')->getTopStore($stores,$top_n);
		
		$options = array();
		
		if(count($stores))
		foreach($stores as $index => $store)
		{
			$storeTitle = ($store->getDistance() && $store->getDistance()!= 999999999) ? $store->getStoreName() .' ('.number_format($store->getDistance()).' m)' : $store->getStoreName();
			$options[$store->getId()]['label'] = $storeTitle;
			$options[$store->getId()]['info'] = $store;
		}	
		
		return $options;		

	}
	public function loadDistance($spoint,$dpoint){
		return sqrt(($spoint['lat']-$dpoint['lat'])*($spoint['lat']-$dpoint['lat'])+($spoint['lng']-$dpoint['lng'])*($spoint['lng']-$dpoint['lng']));
	}
	
}