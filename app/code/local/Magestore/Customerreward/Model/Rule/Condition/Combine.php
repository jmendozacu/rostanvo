<?php

class Magestore_Customerreward_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function _construct()
    {
        parent::_construct();
        $this->setType('customerreward/rule_condition_combine');
    }
    
    public function getNewChildSelectOptions(){
    	$orderAttributes = Mage::getModel('customerreward/rule_condition_order')
    		->loadAttributeOptions()
    		->getAttributeOption();
    	$attributes = array();
    	foreach ($orderAttributes as $attribute => $label){
    		$attributes[] = array(
    			'value'	=> 'customerreward/rule_condition_order|'.$attribute,
    			'label'	=> $label
    		);
    	}
    	$conditions = parent::getNewChildSelectOptions();
    	$conditions = array_merge_recursive($conditions,array(
    		array(
    			'value'	=> 'salesrule/rule_condition_product_found',
    			'label'	=> Mage::helper('customerreward')->__('Product attribute combination'),
    		),
    		array(
    			'value'	=> 'salesrule/rule_condition_product_subselect',
    			'label'	=> Mage::helper('customerreward')->__('Products subselection'),
    		),
    		array(
    			'value'	=> 'customerreward/rule_condition_combine',
    			'label'	=> Mage::helper('customerreward')->__('Conditions combination'),
    		),
    		array(
    			'value'	=> $attributes,
    			'label'	=> Mage::helper('customerreward')->__('Cart Order Attribute'),
    		),
    	));
    	
    	$additional = new Varien_Object();
    	Mage::dispatchEvent('customerreward_rule_condition_combine', array('additional' => $additional));
    	if ($additionalConditions = $additional->getConditions()){
    		$conditions = array_merge_recursive($conditions,$additionalConditions);
    	}
    	
    	return $conditions;
    }
}
