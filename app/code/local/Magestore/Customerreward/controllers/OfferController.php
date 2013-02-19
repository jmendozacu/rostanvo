<?php
class Magestore_Customerreward_OfferController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
    	if (Mage::getSingleton('customer/session')->getCustomerId()){
    		$order = new Varien_Object(array(
    			'created_at'	=> now(),
    			'reward_product_id'	=> $this->getRequest()->getParam('id')
    		));
			$offer = Mage::getModel('customerreward/offer')->loadByCustomerOrder(Mage::getSingleton('customer/session')->getCustomer(),$order);
			if ($offer->getId()){
				$this->loadLayout();
				$this->getLayout()->getBlock('head')->setTitle($offer->getTitle());
				$this->_initLayoutMessages('customer/session');
				$this->renderLayout();
			}else{
				$this->_redirect('sendfriend/product/send',array(
					'id'	=> $this->getRequest()->getParam('id'),
					'cat_id'=> $this->getRequest()->getParam('cat_id'),
				));
			}
    	}else{
    		Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/'));
    		$this->_redirect('customer/account/login');
    	}
    }
    
    public function emailAction(){
    	if (!Mage::getSingleton('customer/session')->getCustomerId()){
    		Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/email'));
    		$this->_redirect('customer/account/login');
    	}
    	$this->loadLayout();
    	$this->getLayout()->getBlock('head')->setTitle(Mage::helper('customerreward')->__('Send email to friend'));
    	$this->_initLayoutMessages('catalog/session');
    	$this->renderLayout();
    }
    
    public function sendAction(){
    	if (!$this->_validateFormKey()){
    		return $this->_redirect('*/*/email', array('_current' => true));
    	}
		try{
			$this->_sendEmail();
			Mage::getSingleton('catalog/session')->addSuccess($this->__('The offer had been sent to your friends.'));
			$this->_redirectSuccess(Mage::getUrl('*/*/email', array('_current' => true)));
			return ;
		}catch(Exception $e){
			Mage::getSingleton('catalog/session')->addError($e->getMessage());
			$this->_redirectError(Mage::getUrl('*/*/email', array('_current' => true)));
		}
    }
    
    protected function _sendEmail(){
    	$data = $this->getRequest()->getParams();
		$senderInfo = $data['sender'];
		$recipients = $data['recipients'];
		$recipientEmail = $recipients['email'];
		$recipientName = $recipients['name'];
		
		$offer = Mage::getModel('customerreward/offer')->load($this->getRequest()->getParam('offer_id'));
		$offer->setCurrentDate(Mage::helper('core')->formatDate(now(),'long'));
		$description = Mage::getBlockSingleton('customerreward/offer_view')->setOffer($offer)->getTitleDescriptionHtml();

		$offer->setTitleHtml($description['title']);
		$offer->setDescription($description['description']);
		$offer->setImageUrl(Mage::getBaseUrl('media').$offer->getImage());
        if (Mage::helper('customerreward')->getReferConfig('coupon'))
            $offer->setCoupon($data['coupon']);
		
		//send email
		$translate = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		
		$mailTemplate = Mage::getModel('core/email_template');
		$message = nl2br(htmlspecialchars($senderInfo['message']));
		$sender = array(
			'name'	=> Mage::helper('customerreward')->htmlEscape($senderInfo['name']),
			'email'	=> Mage::helper('customerreward')->htmlEscape($senderInfo['email']),
		);
		$mailTemplate->setDesignConfig(array(
			'area'	=> 'frontend',
			'store'	=> Mage::app()->getStore()->getId(),
		));
		$template = Mage::helper('customerreward')->getEmailConfig('sendfriend');

		foreach ($recipientEmail as $k => $email){
			$name = $recipientName[$k];
			$mailTemplate->sendTransactional(
				$template,
				'sales',
				$email,
				$name,
				array(
					'store' => Mage::app()->getStore(),
					'name'	=> $name,
					'email'	=> $email,
					'message'	=> $message,
					'sender_name'	=> $sender['name'],
					'sender_email'	=> $sender['email'],
					'title'	=> $data['title'],
					'url'	=> $data['url'],
					'offer'	=> $offer
				)
			);
		}
		
		$translate->setTranslateInline(true);
		return $this;
    }
    
    public function viewAction(){
        if (Mage::helper('customerreward')->getReferConfig('coupon')){
            $this->_redirectUrl(Mage::getBaseUrl());
            return;
        }
    	$this->loadLayout();
    	$this->renderLayout();
    }
}