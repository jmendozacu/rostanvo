<?php

class Magestore_Storepickup_Model_Observer
{
	
	public function update_shippingaddress($observer)
	{
		$datashipping = Mage::getSingleton('checkout/session')->getData('storepickup_session');
		
		if(isset($datashipping['store_id']) && $datashipping['store_id'])
		{
			$store = Mage::getModel('storepickup/store')->load($datashipping['store_id']);

			$datashipping['firstname'] = Mage::helper('storepickup')->__('Store');
			$datashipping['lastname'] = $store->getData('store_name');
			$datashipping['street'][0] = $store->getData('address');
			$datashipping['city'] = $store->getCity();
			$datashipping['region'] = $store->getState();
			$datashipping['region_id'] = $store->getData('state_id');
			$datashipping['postcode'] = $store->getData('zipcode');
			$datashipping['country_id'] = $store->getData('country');
			
			$datashipping['company'] = '';
			if($store->getStoreFax())
				$datashipping['fax'] = $store->getStoreFax();
			else
				unset($datashipping['fax']);
			if($store->getStorePhone())
				$datashipping['telephone'] = $store->getStorePhone();
			else
				unset($datashipping['telephone']);

			$datashipping['save_in_address_book'] = 1;		
		}
		try{
			$result = $this->saveShipping($datashipping,null);
		} catch(Exception $e){
		}
	}	
	
	public function saveShipping($data){
		if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('storepickup')->__('Invalid data.'));
        }
        $address = $this->getQuote()->getShippingAddress();
		unset($data['address_id']);
		$address->addData($data);
        
        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);
        if (($validateRes = $address->validate())!==true) {
            return array('error' => 1, 'message' => $validateRes);
        }

        $this->getQuote()->collectTotals()->save();
        return array();		
	}
	
	public function getQuote()
	{
		return Mage::getSingleton('checkout/session')->getQuote();
	}
	
	public function checkout_type_onepage_save_order_after($observer)
	{
		$order =  $observer['order'];
		$this->save_storeaddress($order);
		$this->send_mail($order);
		return $this;
	}
	public function adminhtml_sales_order_save_before($observer){
		$order = $observer->getOrder();
		$order_id = $order->getId();
		$shippingMethod = $order->getShippingMethod();
		$shippingMethod = explode('_',$shippingMethod);
		
		$shippingCode = $shippingMethod[0];
		if($shippingCode != "storepickup")
			return;
		$storepickup = Mage::getSingleton('adminhtml/session')->getData('storepickup_session');
		try {
			$shippingtime['time'] = isset($storepickup['time']) ? $storepickup['time'] : null;
			$shippingtime['date'] = isset($storepickup['date']) ? $storepickup['date'] : null;
			$store_id = isset($storepickup['store_id']) ? $storepickup['store_id'] : null;
			if(!$store_id)
				return;
				
			if (isset($storepickup['date']))
				$date = substr($shippingtime['date'],6,4) .'-'. substr($shippingtime['date'],0,2) .'-'. substr($shippingtime['date'],3,2);
			else $date = null;	
			$storeorder = Mage::getModel('storepickup/storeorder');
			$storeorder->setData('store_id',$store_id);
			$storeorder->setData('order_id',$order_id);
			$storeorder->setData('shipping_time',$shippingtime['time']);
			$storeorder->setData('shipping_date',$date);
			$storeorder->save();	
		
			$shippingdesct = $order->getShippingDescription();
			if ($shippingtime['time']!=null && $shippingtime['date']!=null)
				$shippingdesct .= '<br/>'.Mage::helper('storepickup')->__('Pickup Time: %s %s ',$shippingtime['date'],$shippingtime['time']);
			
			//IMAGE
			$store = Mage::helper('storepickup')->getStorepickupByOrderId($order->getId());
			if ($store) {
				$latitude = $store->getStoreLatitude();
				$longitude = $store->getStoreLongitude();
				if($latitude !=0 && $longitude !=0) {
					$shippingdesct .='<br/><img src=http://maps.google.com/maps/api/staticmap?center='.$latitude.','.$longitude.'&zoom=14&size=200x200&markers=color:red|label:S|'.$latitude.','.$longitude.'&sensor=false /><br/>';
				}	
			}
			
			$order->setShippingDescription($shippingdesct)
					->save()
					;
			
			
		} catch (Exception $e) {
		}			
		Mage::getSingleton('adminhtml/session')->unsetData('storepickup_session');
	}
	public function save_storeaddress($order)
	{	
		$order_id = $order->getId();
		
		$shippingMethod = $order->getShippingMethod();
		$shippingMethod = explode('_',$shippingMethod);
		
		$shippingCode = $shippingMethod[0];
		if($shippingCode != "storepickup")
			return;
					
		$storepickup = Mage::getSingleton('checkout/session')->getData('storepickup_session');
		if(isset($storepickup['is_storepickup']) && $storepickup['is_storepickup'] == '1')
		{
			try {
				$shippingtime['time'] = isset($storepickup['time']) ? $storepickup['time'] : null;
				$shippingtime['date'] = isset($storepickup['date']) ? $storepickup['date'] : null;
				$store_id = isset($storepickup['store_id']) ? $storepickup['store_id'] : null;
				if(!$store_id)
					return;
					
				if (isset($storepickup['date']))
					$date = substr($shippingtime['date'],6,4) .'-'. substr($shippingtime['date'],0,2) .'-'. substr($shippingtime['date'],3,2);
				else $date = null;	
				$storeorder = Mage::getModel('storepickup/storeorder');
				$storeorder->setData('store_id',$store_id);
				$storeorder->setData('order_id',$order_id);
				$storeorder->setData('shipping_time',$shippingtime['time']);
				$storeorder->setData('shipping_date',$date);
				$storeorder->save();	
			
				$shippingdesct = $order->getShippingDescription();
				if ($shippingtime['time']!=null && $shippingtime['date']!=null)
					$shippingdesct .= '<br/>'.Mage::helper('storepickup')->__('Pickup Time: %s %s ',$shippingtime['date'],$shippingtime['time']);
				
				//IMAGE
				$store = Mage::helper('storepickup')->getStorepickupByOrderId($order->getId());
				if ($store) {
					$latitude = $store->getStoreLatitude();
					$longitude = $store->getStoreLongitude();
					if($latitude !=0 && $longitude !=0) {
						$shippingdesct .='<br/><img src=http://maps.google.com/maps/api/staticmap?center='.$latitude.','.$longitude.'&zoom=14&size=200x200&markers=color:red|label:S|'.$latitude.','.$longitude.'&sensor=false /><br/>';
					}	
				}
				
				$order->setShippingDescription($shippingdesct)
						->save();
				
				
			} catch (Exception $e) {
			}			
			Mage::getSingleton('checkout/session')->unsetData('storepickup_session');
				
		}
	}
	
	public function storepickup_sales_convert_order_to_quote($observer){
		$order = $observer -> getOrder();
		$quote = $observer -> getQuote();
		$storeorder = Mage::helper('storepickup')->getStorepickupByOrderId($order->getId());
		if($order->getShippingMethod() == 'storepickup_storepickup')
			Mage::getSingleton('adminhtml/session') ->setStorepickupStore($storeorder->getId());
	}
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

	public function send_mail($order)
	{
		Mage::helper('storepickup/email')->sendNoticeEmailToStoreOwner($order);
		Mage::helper('storepickup/email')->sendNoticeEmailToAdmin($order);
		return $this;
	}	
}