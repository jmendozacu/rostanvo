<?php

class Magestore_Customerreward_Block_Adminhtml_Rule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'customerreward';
        $this->_controller = 'adminhtml_rule';
        
        $this->_updateButton('save', 'label', Mage::helper('customerreward')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('customerreward')->__('Delete Rule'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('customerreward_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'customerreward_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'customerreward_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('rule_data') && Mage::registry('rule_data')->getId() ) {
            return Mage::helper('customerreward')->__("Edit Rule '%s'", $this->htmlEscape(Mage::registry('rule_data')->getTitle()));
        } else {
            return Mage::helper('customerreward')->__('Add Rule');
        }
    }
}