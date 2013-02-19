<?php

class Magestore_Customerreward_Model_Mysql4_Transaction_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/transaction');
    }
    
    public function addCreateDayFilter($date){
    	$day = date('Y-m-d',$date);
    	$this->getSelect()->where('date(create_at) = ?',$day);
    	return $this;
    }
    
    public function addExpireAfterDaysFilter($dayBefore){
    	$date = Mage::getModel('core/date')->gmtDate();
    	$zendDate = new Zend_Date($date);
    	$dayAfter = $zendDate->addDay($dayBefore)->toString('YYYY-MM-dd');
    	$this->getSelect()->where('date(expiration_date) = ?',$dayAfter);
    	return $this;
    }
    
    public function addAvailableBalanceFilter(){
    	$this->getSelect()
    		->where('points_change > points_spent')
    		->where('points_change > 0');
    	return $this;
    }
    
    public function updatePointsSpent($amount = 0, $customerId = null){
    	if ($amount > 0) return $this;
    	
    	$this->addFieldToFilter('customer_id',$customerId)
    		->addFieldToFilter('is_expired',0)
    		->addAvailableBalanceFilter()
			->setOrder('expiration_date','ASC');
		
    	$totalAmount = -$amount;
    	
    	foreach ($this as $transaction){
    		$pointAvailable = $transaction->getPointsChange() - $transaction->getPointsSpent();
    		if ($totalAmount < $pointAvailable){
    			$transaction->setPointsSpent($transaction->getPointsSpent()+$totalAmount)->save();
    			return $this;
    		}else{
    			$totalAmount -= $pointAvailable;
    			$transaction->setPointsSpent($transaction->getPointsChange())->save();
    		}
    	}
    	return $this;
    }
}