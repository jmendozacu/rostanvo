<?php

class Magestore_Customerreward_Block_Adminhtml_Offer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'customerreward';
        $this->_controller = 'adminhtml_offer';
        
        $this->_updateButton('save', 'label', Mage::helper('customerreward')->__('Save Offer'));
        $this->_updateButton('delete', 'label', Mage::helper('customerreward')->__('Delete Offer'));
		
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
        if( Mage::registry('offer_data') && Mage::registry('offer_data')->getId() ) {
            return Mage::helper('customerreward')->__("Edit Offer '%s'", $this->htmlEscape(Mage::registry('offer_data')->getTitle()));
        } else {
            return Mage::helper('customerreward')->__('Add Offer');
        }
    }
}