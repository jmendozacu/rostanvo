<?php

class Magestore_Customerreward_Block_Adminhtml_Transaction_Renderer_Customer
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$customer = Mage::getModel('customer/customer')->load($row->getCustomerId());
    	if ($customer){
    		return sprintf('<a target="_blank" href="%s">%s</a>',$this->getUrl('adminhtml/customer/edit',array('id'=>$customer->getId())), $customer->getName());
    	}
    	return $row->getCustomerId();
    }
}