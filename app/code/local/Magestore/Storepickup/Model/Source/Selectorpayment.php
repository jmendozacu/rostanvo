<?php

class Magestore_Storepickup_Model_Source_Selectorpayment
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('storepickup')->__('All Allowed Payments')),
            array('value'=>1, 'label'=>Mage::helper('storepickup')->__('Specific Payments')),
        );
    }
}
