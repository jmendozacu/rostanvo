<?php

class Magestore_Customerreward_Block_Offer_Email extends Mage_Sendfriend_Block_Send
{
	public function getSendUrl(){
		$sendUrl = $this->getUrl('*/*/send',array(
			'id'	=> $this->getProductId(),
			'cat_id'=> $this->getCategoryId(),
		));
		$title = urlencode($this->getRequest()->getParam('title'));
		$url = urlencode($this->getRequest()->getParam('url'));
		$offer_id = $this->getRequest()->getParam('offer_id');
		$emailUrl = sprintf('%s?offer_id=%s&title=%s&url=%s',$sendUrl,$offer_id,$title,$url);
        if (Mage::helper('customerreward')->getReferConfig('coupon'))
            $emailUrl .= '&coupon='.urlencode($this->getRequest()->getParam('coupon'));
        return $emailUrl;
	}
	
	public function canSend(){
		return true;
	}
}