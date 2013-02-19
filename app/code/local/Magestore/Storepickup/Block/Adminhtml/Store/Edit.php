<?php

class Magestore_Storepickup_Block_Adminhtml_Store_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'storepickup';
        $this->_controller = 'adminhtml_store';
        
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
			//edit back button in import
			function backEdit()
			{
				window.history.back();
			}
			
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('store_data') && Mage::registry('store_data')->getId() ) {
            return Mage::helper('storepickup')->__("Edit store '%s'", $this->htmlEscape(Mage::registry('store_data')->getData('store_name')));
        } else {
            return Mage::helper('storepickup')->__('Add Store');
        }
    }
	public function removeButton($button_name)
	{
		$this->_removeButton($button_name);
	}
	
}