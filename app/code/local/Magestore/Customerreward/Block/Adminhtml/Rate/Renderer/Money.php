<?php

class Magestore_Customerreward_Block_Adminhtml_Rate_Renderer_Money
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$direction = $row->getDirection();
    	switch ($direction){
    		case Magestore_Customerreward_Model_Rate::MONEY_TO_POINT :
    		case Magestore_Customerreward_Model_Rate::POINT_TO_MONEY :
    			return Mage::app()->getStore()->getBaseCurrency()->format($row->getMoney());
   			case Magestore_Customerreward_Model_Rate::CLICK_TO_POINT :
			case Magestore_Customerreward_Model_Rate::VISIT_TO_POINT :
				return sprintf("%.0f",$row->getMoney());
    	}
    }
}