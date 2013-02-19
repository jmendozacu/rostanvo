<?php
/**
 * Iceberg Commerce
 *
 * @author     IcebergCommerce
 * @package    IcebergCommerce_ReviewSearchFriendlyUrls
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */

class IcebergCommerce_ReviewSearchFriendlyUrls_Model_Observer extends Mage_Core_Model_Abstract
{  
	static $_urlRewrite;
	
	/**
     * Retrieve URL Rewrite Instance
     *
     * @return Mage_Core_Model_Url_Rewrite
     */
    public function getUrlRewrite()
    {
        if (!self::$_urlRewrite) {
            self::$_urlRewrite = Mage::getModel('core/url_rewrite');
        }
        return self::$_urlRewrite;
    }
    
	public function deleteReview( Varien_Event_Observer $observer )
	{
		$review = $observer->getObject();
		
		$storeId    = $review->getStoreId();
       
        $idPath = sprintf('review/%d', $review->getId() );

        $rewrite = $this->getUrlRewrite();
        $rewrite->setStoreId($storeId)
                ->loadByIdPath($idPath);
                
        if ( $rewrite->getId() )
        {
        	$rewrite->delete();
        }
	}
	
	public function deleteProduct( Varien_Event_Observer $observer )
	{
		$product 	= $observer->getObject();
		
		$storeCollection = Mage::getModel('core/store')
            ->getCollection();
        
        foreach ( $storeCollection as $store )
        {
        	$idPath = sprintf('reviews/%d', $product->getId() );

	        $rewrite = $this->getUrlRewrite();
	        $rewrite->setStoreId( $store->getId() )
	                ->loadByIdPath($idPath);

	        if ( $rewrite->getId() )
	        {
	       		$rewrite->delete();
	        }
        }
	}

}