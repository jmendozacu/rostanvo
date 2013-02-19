<?php
class Magestore_Storepickup_Model_Shipping_Storepickup
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'storepickup';

   
	public function getCode()
	{
		return $this->_code;
	}
	
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {		
		if(!Mage::helper('magenotification')->checkLicenseKey('Storepickup')){return false;} 
		if (!$this->getConfigFlag('active')) 
		{
            return false;
        }
		
		$items = $request->getAllItems();
		
		if(! count($items))
			return;
			
		$result = Mage::getModel('shipping/rate_result');
		
		$method = Mage::getModel('shipping/rate_result_method');

		$method->setCarrier('storepickup');
					
		$method->setCarrierTitle($this->getConfigData('title'));
			
		$method->setMethod('storepickup');
					
		$method->setMethodTitle('Free StorePickup');
				
		$method->setPrice(0);
		
		$method->setCost(0);		
				
		$result->append($method);	
		
		return $result;		
    }

    public function getAllowedMethods()
    {
        return array('storepickup'=>'storepickup');
    }
}

