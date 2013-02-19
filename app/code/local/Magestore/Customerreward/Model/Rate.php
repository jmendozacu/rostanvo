<?php

class Magestore_Customerreward_Model_Rate extends Mage_Core_Model_Abstract
{
	const POINT_TO_MONEY = 1;
	const MONEY_TO_POINT = 2;
	const VISIT_TO_POINT = 3;
	const CLICK_TO_POINT = 4;
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/rate');
    }
    
    public function getPointToMoneyRate($website = null, $customerGroup = null){
    	$rate = $this->getRate($website,$customerGroup,self::POINT_TO_MONEY);
    	if ($rate['points']){
    		return $rate['money'] / $rate['points'];
    	}
    	return 0;
    }
    
    public function getPointToMoney($website,$customerGroup){
    	return $this->getRate($website,$customerGroup,self::POINT_TO_MONEY);
    }
    
    public function getMoneyToPoint($website,$customerGroup){
    	return $this->getRate($website,$customerGroup,self::MONEY_TO_POINT);
    }
    
    public function getVisitToPoint($website,$customerGroup){
    	return $this->getRate($website,$customerGroup,self::VISIT_TO_POINT);
    }
    
    public function getClickToPoint($website,$customerGroup){
    	return $this->getRate($website,$customerGroup,self::CLICK_TO_POINT);
    }
    
    public function getRate($website, $customerGroup, $direction){
    	$rate = $this->getCollection()
			->addFieldToFilter('direction',$direction)
			->addFieldToFilter('website_ids',array('finset' => $website))
			->addFieldToFilter('customer_group_ids',array('finset' => $customerGroup))
			->addFieldToFilter('points',array('gt' => 0))
			->addFieldToFilter('money',array('gt' => 0))
			->setOrder('sort_order','DESC')
			->getFirstItem();
		if ($rate){
			return array(
					'points'	=> $rate->getPoints(),
					'money'		=> $rate->getMoney(),
				);
		}
		return array();
    }
}