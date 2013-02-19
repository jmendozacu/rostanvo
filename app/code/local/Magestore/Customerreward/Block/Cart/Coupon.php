<?php
class Magestore_Customerreward_Block_Cart_Coupon extends Mage_Checkout_Block_Cart_Coupon
{
	public function getCouponCode(){
		if (Mage::helper('customerreward')->getReferConfig('coupon')
			&& $key = Mage::getSingleton('core/cookie')->get('customerreward_offer_key')){
			$count = Mage::getModel('customerreward/count')->loadByKey($key);
			if ($count && $count->getCoupon())
				return $count->getCoupon();
		}
        return $this->getQuote()->getCouponCode();
    }
}