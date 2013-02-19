<?php

class Magestore_Customerreward_Block_Adminhtml_Transaction_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'customerreward';
        $this->_controller = 'adminhtml_transaction';
        
        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        if(Mage::registry('transaction_data') && Mage::registry('transaction_data')->getId()) {
            return Mage::helper('customerreward')->__("Transaction #%s | Create at %s", Mage::registry('transaction_data')->getId(),Mage::getModel('core/date')->date('M d, Y H:i:s', Mage::registry('transaction_data')->getCreateAt()));
        }
        return ;
    }
}