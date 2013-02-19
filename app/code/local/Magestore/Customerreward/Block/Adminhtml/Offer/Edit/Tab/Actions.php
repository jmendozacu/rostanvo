<?php

class Magestore_Customerreward_Block_Adminhtml_Offer_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      if ( Mage::getSingleton('adminhtml/session')->getFormData()){
          $data = Mage::getSingleton('adminhtml/session')->getFormData();
          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('offer_data') ){
          $data = Mage::registry('offer_data')->getData();
      }
  	  
      $form = new Varien_Data_Form();
      $form->setHtmlIdPrefix('offer_');
      $this->setForm($form);
      $fieldset = $form->addFieldset('commission_fieldset', array('legend'=>Mage::helper('customerreward')->__('Commission')));
     
      $options = array(
	  	array(
 			'value'	=> Magestore_Customerreward_Helper_Offer::OFFER_TYPE_FIXED,
 			'label'	=> Mage::helper('customerreward')->__('Fixed'),
		  ),
	  	array(
	  		'value'	=> Magestore_Customerreward_Helper_Offer::OFFER_TYPE_PERCENT,
	  		'label'	=> Mage::helper('customerreward')->__('Percentage'),
		  ),
	  );
      
	  $fieldset->addField('commission_type', 'select', array(
          'label'     => Mage::helper('customerreward')->__('Type'),
          'title'	  => Mage::helper('customerreward')->__('Type'),
          'name'      => 'commission_type',
          'values'	  => $options,
      ));
      
      $fieldset->addField('commission', 'text', array(
          'label'     => Mage::helper('customerreward')->__('Value'),
          'title'	  => Mage::helper('customerreward')->__('Value'),
          'name'      => 'commission',
          'note'	  => Mage::helper('customerreward')->__('Point(s) or Percent'),
          'required'  => true,
      ));
      
      $fieldset = $form->addFieldset('discount_fieldset', array('legend'=>Mage::helper('customerreward')->__('Discount')));
      
      $fieldset->addField('discount_method', 'select', array(
          'label'     => Mage::helper('customerreward')->__('Type'),
          'title'	  => Mage::helper('customerreward')->__('Type'),
          'name'      => 'discount_method',
          'values'	  => array(
		  	array(
		  		'value'	=> Magestore_Customerreward_Helper_Offer::OFFER_METHOD_DISCOUNT,
		  		'label'	=> Mage::helper('customerreward')->__('Discount'),
			  ),
		  	array(
			  	'value'	=> Magestore_Customerreward_Helper_Offer::OFFER_METHOD_CASHBACK,
			  	'label'	=> Mage::helper('customerreward')->__('Reward point'),
			  ),
		  ),
          'note'    => Mage::helper('customerreward')->__('Only used when coupon is disable'),
      ));
      
      $fieldset->addField('discount_type', 'select', array(
          'label'     => Mage::helper('customerreward')->__('Fixed/Percentage'),
          'title'	  => Mage::helper('customerreward')->__('Fixed/Percentage'),
          'name'      => 'discount_type',
          'values'	  => $options,
      ));
      
      $fieldset->addField('discount', 'text', array(
          'label'     => Mage::helper('customerreward')->__('Value'),
          'title'	  => Mage::helper('customerreward')->__('Value'),
          'name'      => 'discount',
          'note'	  => Mage::helper('customerreward')->__('%s / Point(s) or Percent',Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE)),
          'required'  => true,
      ));
      
      $fieldset->addField('discount_show', 'select', array(
          'label'     => Mage::helper('customerreward')->__('Show in'),
          'title'	  => Mage::helper('customerreward')->__('Show in'),
          'name'      => 'discount_show',
          'required'  => true,
          'note'      => Mage::helper('customerreward')->__('Used when type is Discount.'),
          'values'    => array(
		  	array(
		  		'value'	=> Magestore_Customerreward_Helper_Offer::SHOW_OFFER_IN_CART,
		  		'label'	=> Mage::helper('customerreward')->__('Cart Page'),
			  ),
		  	array(
			  	'value'	=> Magestore_Customerreward_Helper_Offer::SHOW_OFFER_IN_PRODUCT,
			  	'label'	=> Mage::helper('customerreward')->__('Product View Page'),
			  ),
		  ),
      ));
      
      $form->setValues($data);
      return parent::_prepareForm();
  }
}