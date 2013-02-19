<?php

class Magestore_Storepickup_Block_Adminhtml_Store_Edit_Tab_Gmap extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareLayout()
	{
		$this->setTemplate('storepickup/gmap.phtml');
	}
	
	public function getStore()
	{
		if(!$this->hasData('store_data'))
		{
			if(Mage::registry('store_data')) {
				$this->setData('store_data',Mage::registry('store_data'));
			} else {	
				$store = Mage::getModel('storepickup/store')->load($this->getRequest()->getParam('id'));
				$this->setData('store_data',$store);
			}			
		}
		
		return $this->getData('store_data');
	}
	
	public function getCoordinates()
	{
		$store = $this->getStore();
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
	
	public function getGKey()
	{
		if(!$this->hasData('g_key'))
		{
			$this->setData('g_key',Mage::getModel('storepickup/gmap')->getGKey());
		}
		
		return $this->getData('g_key');	
	}
	
}