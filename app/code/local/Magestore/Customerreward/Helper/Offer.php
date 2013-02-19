<?php

class Magestore_Customerreward_Helper_Offer extends Mage_Core_Helper_Abstract
{
	const OFFER_METHOD_DISCOUNT = 1;
	const OFFER_METHOD_CASHBACK = 2;
	
	const OFFER_TYPE_FIXED = 1;
	const OFFER_TYPE_PERCENT = 2;
	
	const SHOW_OFFER_IN_PRODUCT = 1;
	const SHOW_OFFER_IN_CART = 2;
}