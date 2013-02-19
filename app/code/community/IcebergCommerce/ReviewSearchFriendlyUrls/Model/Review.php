<?php
/**
 * Iceberg Commerce
 *
 * @author     IcebergCommerce
 * @package    IcebergCommerce_ReviewSearchFriendlyUrls
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */

/**
 * Rewrite the Mage_Review_Model_Review Class so that we can redefine the getReviewsUrl() and getReviewUrl() methods.
 * 
 * We are returning a link that looks like:
 * 		http://store.com/reviews/product_name 
 * instead of
 * 		http://store.com/review/product/list/id/X
 */
class IcebergCommerce_ReviewSearchFriendlyUrls_Model_Review extends Mage_Review_Model_Review
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
    
	/**
     * Retrieve URL Instance
     *
     * @return Mage_Core_Model_Url
     */
    public function getUrlInstance()
    {
        if (!self::$_url) {
            self::$_url = Mage::getModel('core/url');
        }
        return self::$_url;
    }
    
    /**
     * Rewriting this method to output new urls.
     * Checks if a rewrite already exists, otherwise creates it.
     */
    public function getReviewUrl(){
    	$product = $this->getProduct();
    	
    	$routePath      = '';
        $routeParams    = array();

        
        $storeId = Mage::app()->getStore()->getId();
        $this->setStoreId( $storeId ); 
        
    	if( !$product )
    	{
    		if( $this->getEntityId() != 1)
    			return parent::getReviewUrl();
    			
    		$product = Mage::getModel('catalog/product')->load( $this->getEntityPkValue() );
    	}
    	
    	$product->setStoreId( $storeId );
       
        $idPath = sprintf('review/%d', $this->getId() );

        $rewrite = $this->getUrlRewrite();
        $rewrite->setStoreId($storeId)
                ->loadByIdPath($idPath);

                
        $storeUrl = Mage::getBaseUrl();
    	
    	if (substr($storeUrl,strlen($storeUrl)-1,1) != '/')
    	{
    		$storeUrl .= '/';
    	}
        
        if ($rewrite->getId()) 
        {
        	// REWRITE RULE EXISTS
            $url = $storeUrl . $rewrite->getRequestPath();
        }
        else
        {
        	// CREATE REWRITE RULE
            $model = Mage::getModel('reviewsearchfriendlyurls/url');
            $url = $storeUrl . $model->addSingleReviewUrlRewrite( $this , $product);
        }
        
        return $url;
    }
}