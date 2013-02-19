<?php
class Magestore_Customerreward_Block_Adminhtml_System_Configuration_Resetbutton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
		$this->setElement($element);
		$url = $this->getUrl('customerrewardadmin/adminhtml_transaction/reset');
		$comfirm = Mage::helper('customerreward')->__('Are you sure?');
		
		return $this->getLayout()->createBlock('adminhtml/widget_button','transaction_reset_button',array(
			'type'	=> 'button',
			'class'	=> 'delete',
			'label'	=> Mage::helper('customerreward')->__('Reset all transactions'),
			'onclick' => "if(confirm('".$comfirm."')) return setLocation('".$url."');",
		))->toHtml();
	}
}