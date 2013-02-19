<?php

class Magestore_Storepickup_Model_Holiday extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('storepickup/holiday');
    }
	
	public function isHoliday($date,$store_id)
	{	
		$date = substr($date,6,4) .'-'. substr($date,0,3) . substr($date,3,2);
			
		$collection = $this->getCollection()
				->addFieldToFilter('store_id',$store_id)
				->addFieldToFilter('date',$date);
		
		if(count($collection))
			return true;
		else 
			return false;
	}
}