<?php

class Magestore_Storepickup_Block_Adminhtml_Holiday_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'storepickup';
        $this->_controller = 'adminhtml_holiday';
        
        $this->_updateButton('save', 'label', Mage::helper('storepickup')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('storepickup')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }			
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('holiday_data') && Mage::registry('holiday_data')->getId()) {
            return Mage::helper('storepickup')->__("Edit holiday '%s'",$this->htmlEscape(Mage::registry('holiday_data')->getData('date')));
        } elseif($this->getRequest()->getParam('id')) {
			return Mage::helper('storepickup')->__("Edit holiday '%s'", Mage::getModel('storepickup/holiday')->load($this->getRequest()->getParam('id'))->getDate());
		}else{
            return Mage::helper('storepickup')->__('Add Holiday');
        }
    }
}