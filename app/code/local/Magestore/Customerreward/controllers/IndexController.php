<?php
class Magestore_Customerreward_IndexController extends Mage_Core_Controller_Front_Action
{

	public function testAction()
	{
		echo get_class(Mage::getBlockSingleton('checkout/onepage_payment_methods'));
	}
	
    public function indexAction(){
    	if (Mage::getSingleton('customer/session')->getCustomerId()){
			$this->loadLayout();
			$this->_initLayoutMessages('customer/session');
			$this->renderLayout();
    	}else{
    		Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/'));
    		$this->_redirect('customer/account/login');
    	}
    }
    
    public function saveAction(){
    	$isNotification = 0;
    	if ($this->getRequest()->getParam('is_notification')){
    		$isNotification = 1;
    	}
    	$rewardCustomer = Mage::getModel('customerreward/customer')->loadByCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
    	if ($rewardCustomer->getId()){
    		try{
    			$rewardCustomer->setIsNotification($isNotification)
    				->save();
   				Mage::getSingleton('customer/session')->addSuccess(Mage::helper('customerreward')->__('Your config was successfully saved!'));
    		}catch (Exception $e){
    			Mage::getSingleton('customer/session')->addError($e->getMessage());
    		}
    	}
    	$this->_redirect('*/*/');
    }
    
    public function offerAction(){
    	$this->loadLayout();
    	$offer = $this->getLayout()->getBlock('offer.view')->getOffer();
		
    	if ($offer && $offer->getId()){
			$this->getLayout()->getBlock('head')->setTitle($offer->getTitle());
    		// if ($offer->getDiscountMethod() == Magestore_Customerreward_Helper_Offer::OFFER_METHOD_DISCOUNT)
    			// $this->getLayout()->getBlock('head')->setTitle(Mage::helper('customerreward')->__('Discounted Product(s)'));
   			// else
   				// $this->getLayout()->getBlock('head')->setTitle(Mage::helper('customerreward')->__('Cash Backed Product(s)'));
    		
			$this->renderLayout();
    	}else
    		$this->_redirectUrl(Mage::getBaseUrl());
    }
}