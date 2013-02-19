<?php

class Magestore_Customerreward_Model_Rule_Condition_Order extends Mage_Rule_Model_Condition_Abstract
{
	public function loadAttributeOptions(){
		$this->setAttributeOption(array(
			'base_subtotal'	=> Mage::helper('customerreward')->__('Subtotal'),
			'total_qty_ordered'	=> Mage::helper('customerreward')->__('Total Items Quantity'),
			'weight'		=> Mage::helper('customerreward')->__('Total Weight'),
			'method'		=> Mage::helper('customerreward')->__('Payment Method'),
			'shipping_method'	=> Mage::helper('customerreward')->__('Shipping Method'),
			'postcode'		=> Mage::helper('customerreward')->__('Shipping Postcode'),
			'region'		=> Mage::helper('customerreward')->__('Shipping Region'),
			'region_id'		=> Mage::helper('customerreward')->__('Shipping State/Province'),
			'country_id'	=> Mage::helper('customerreward')->__('Shipping Country'),
		));
		return $this;
	}
	
	public function getAttributeElement(){
		$attributeElement = parent::getAttributeElement();
		$attributeElement->setShowAsText(true);
		return $attributeElement;
	}
	
	public function getInputType(){
		switch ($this->getAttribute()){
			case 'base_subtotal':
			case 'total_qty_ordered':
			case 'weight':
				return 'numeric';
			case 'method':
			case 'shipping_method':
			case 'region_id':
			case 'country_id':
				return 'select';
		}
		return 'string';
	}
	
	public function getValueElementType(){
		switch ($this->getAttribute()){
			case 'method':
			case 'shipping_method':
			case 'region_id':
			case 'country_id':
				return 'select';
		}
		return 'text';
	}
	
	public function getValueSelectOptions(){
		if (!$this->hasData('value_select_options')){
			$options = array();
			switch ($this->getAttribute()){
				case 'method':
					$options = Mage::getModel('adminhtml/system_config_source_payment_allmethods')->toOptionArray();
					break;
				case 'shipping_method':
					$options = Mage::getModel('adminhtml/system_config_source_shipping_allmethods')->toOptionArray();
					break;
				case 'region_id':
					$options = Mage::getModel('adminhtml/system_config_source_allregion')->toOptionArray();
					break;
				case 'country_id':
					$options = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray();
			}
			$this->setData('value_select_options',$options);
		}
		return $this->getData('value_select_options');
	}
	
	public function validate(Varien_Object $order){
		if ($this->getAttribute() == 'method'){
			return parent::validate($order->getPayment());
		}
		if (in_array($this->getAttribute(),array('postcode','region','region_id','country_id'))){
			if ($order->getIsVirtual()){
				$address = $order->getBillingAddress();
			}else{
				$address = $order->getShippingAddress();
			}
			$countryId = $address->getCountryId();
			if (!is_null($countryId)){
				try{
					$regions = Mage::getModel('directory/country')
						->loadByCode($countryId)
						->getRegions()
						->getData();
				}catch(Exception $e){
					Mage::log($e->getMessage());
				}
				if (count($regions) == 0){
					$address->setRegionId('0');
				}
			}
			return parent::validate($address);
		}
		return parent::validate($order);
	}
}