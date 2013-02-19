<?php

/**
 * Customersguests Container
 *
 * @author      Alexandr Martynov <joyview@gmail.com>
 */

class Domore_Customersguests_Block_Map extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
    	
        $this->_controller = 'map';
        $this->_blockGroup = 'customersguests';
        $this->_headerText = Mage::helper('customersguests')->__('Customers & guests');
        parent::__construct();
        $this->_removeButton('add');

    }
}
