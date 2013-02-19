<?php
class Magestore_Storepickup_Block_Adminhtml_Sales_Order_Create_Shipping_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
	protected function _toHtml(){
		$this->setTemplate('storepickup/form.phtml');
		return parent::_toHtml();
	}
}