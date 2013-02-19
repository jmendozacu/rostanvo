<?php

class Magestore_Storepickup_Model_Mysql4_Store extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the storepickup_id refers to the key field in your database table.
        $this->_init('storepickup/store', 'store_id');
    }
	
	public function getValidTime($date,$store_id)
	{
		if(Mage::getModel('storepickup/holiday')->isHoliday($date,$store_id))
			return;
			
		$listTime = array();
		
			//prepare sql
		$date = substr($date,6,4) .'-'. substr($date,0,3) . substr($date,3,2);
		$timestamp = strtotime($date);	
		$prefixTable = Mage::helper('storepickup')->getTablePrefix();	
		$date_field = date('l',$timestamp);
		$date_field = strtolower($date_field);
		
		$sql = $this->_getReadAdapter()->select()
					->distinct()
					->from(array('ss'=> $prefixTable .'storepickup_store'), array( $date_field ."_open as open_time" ,$date_field ."_close as close_time", "minimum_gap" ))
					->where('store_id=?',$store_id);
		
		$options = $this->_getReadAdapter()->fetchAll($sql);
			
		return $options;
	}

}