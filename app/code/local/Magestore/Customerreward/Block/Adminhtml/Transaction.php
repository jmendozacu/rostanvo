<?php
class Magestore_Customerreward_Block_Adminhtml_Transaction extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_transaction';
    $this->_blockGroup = 'customerreward';
    $this->_headerText = Mage::helper('customerreward')->__('Transaction');
    parent::__construct();
    $this->_removeButton('add');
  }
}