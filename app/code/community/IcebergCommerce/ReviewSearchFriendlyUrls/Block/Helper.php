<?php
/**
 * Iceberg Commerce
 *
 * @author     IcebergCommerce
 * @package    IcebergCommerce_ReviewSearchFriendlyUrls
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */

class IcebergCommerce_ReviewSearchFriendlyUrls_Block_Helper extends Mage_Review_Block_Helper
{
	public function getReviewsUrl()
    {
    	return Mage::getModel('reviewsearchfriendlyurls/reviews')->getReviewsUrl($this->getProduct() , $this->getProduct()->getCategory() );
    }
}