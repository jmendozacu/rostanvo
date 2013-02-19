<?php

/**
 * Customerssales Container
 *
 * @author      Alexandr Martynov <joyview@gmail.com>
 */

class Domore_Customerssales_Block_Map extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
    	
        $this->_controller = 'map';
        $this->_blockGroup = 'customerssales';
        $this->_headerText = Mage::helper('customerssales')->__('Customers Sales Report');
        parent::__construct();
        $this->_removeButton('add');

    }
}
