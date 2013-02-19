<?php

class Magestore_Storepickup_Helper_Email extends Mage_Core_Helper_Abstract
{	
	const XML_PATH_ADMIN_EMAIL_IDENTITY = "trans_email/ident_general";
	const XML_PATH_SALES_EMAIL_IDENTITY = "trans_email/ident_sales";
	const XML_PATH_NEW_ORDER_TO_ADMIN_EMAIL = 'carriers/storepickup/shopadmin_email_template';
	const XML_PATH_NEW_ORDER_TO_STORE_OWNER_EMAIL = 'carriers/storepickup/storeowner_email_template';
	
	public function sendNoticeEmailToAdmin($order)
	{
		$store = $order->getStore();
		
        $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);

        $paymentBlock->getMethod()->setStore($store->getId());
				
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		
		$template = Mage::getStoreConfig(self::XML_PATH_NEW_ORDER_TO_ADMIN_EMAIL,$store->getId());
		
        $sendTo = array(
            Mage::getStoreConfig(self::XML_PATH_ADMIN_EMAIL_IDENTITY, $store->getId())
        );
		$mailTemplate = Mage::getModel('core/email_template');
		 
        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$store->getId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_PATH_SALES_EMAIL_IDENTITY, $store->getId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'order'         => $order->setAdminName($recipient['name']),
                        'billing'       => $order->getBillingAddress(),
                        'payment_html'  => $paymentBlock->toHtml(),
						//'pickup_time'   => Mage::helper('core')->formatDate($order->getPickupTime(),'medium',false),
                    )
                );
		}

		$translate->setTranslateInline(true);			
		
		return $this;		
	}
	
	public function sendNoticeEmailToStoreOwner($order)
	{
		$order_id = $order->getId();
		$storeLocation = Mage::helper('storepickup')->getStorepickupByOrderId($order_id);
		if(!$storeLocation)
			return;	
		
		$store = $order->getStore();
		
        $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);

        $paymentBlock->getMethod()->setStore($store->getId());
				
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		
		$template = Mage::getStoreConfig(self::XML_PATH_NEW_ORDER_TO_STORE_OWNER_EMAIL,$store->getId());
		
        $sendTo = array(
            array(
				'name' => $storeLocation->getStoreManager(),
				'email' => $storeLocation->getStoreEmail(),
			)
        );
		$mailTemplate = Mage::getModel('core/email_template');
		 
        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$store->getId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_PATH_SALES_EMAIL_IDENTITY, $store->getId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'order'         => $order->setStoreOwnerName($recipient['name']),
                        'billing'       => $order->getBillingAddress(),
                        'payment_html'  => $paymentBlock->toHtml(),
                    )
                );
		}

		$translate->setTranslateInline(true);			
		
		return $this;			
	}
}

?>