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
class IcebergCommerce_ReviewSearchFriendlyUrls_Model_Reviews extends Mage_Review_Model_Review
{
	static $_urlRewrite;
	static $_url;
	
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
     * 
     * @param product $product
     * @param category $category
     * @param array $params
     */
    public function getReviewsUrl( $product , $category = null , $params = array())
    {
    	$routePath      = '';
        $routeParams    = $params;

        $storeId    = $product->getStoreId();
        
        if( !$storeId ){
        	$storeId = Mage::app()->getStore()->getId();
    		$product->setStoreId( $storeId );
        }
        
        $categoryId = $product->getCategoryId() && !$product->getDoNotUseCategoryId()
                ? $product->getCategoryId() : null;

       
        $idPath = sprintf('reviews/%d', $product->getId());
        //if ($categoryId) {
        //     $idPath = sprintf('%s/%d', $idPath, $categoryId);
        //}
        
        $rewrite = $this->getUrlRewrite();
        $rewrite->setStoreId($storeId)
                ->loadByIdPath($idPath);
        
        $oldRequestPath = $product->getRequestPath(); 
        
        if ($rewrite->getId()) {
        	// REWRITE RULE EXISTS
            $requestPath = $rewrite->getRequestPath();
            $product->setRequestPath($requestPath);
        }else{
        	// CREATE REWRITE RULE
            $model = Mage::getModel('reviewsearchfriendlyurls/url');

            $requestPath = $model->addUrlRewrite( $product  );
        }
                
        if (isset($routeParams['_store'])) {
            $storeId = Mage::app()->getStore($routeParams['_store'])->getId();
        }

        if ($storeId != Mage::app()->getStore()->getId()) {
            $routeParams['_store_to_url'] = true;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        }
        else {
            $routePath = 'catalog/product/view';
            $routeParams['id']  = $product->getId();
            $routeParams['s']   = $product->getUrlKey();
            //if ($categoryId) {
             //   $routeParams['category'] = $categoryId;
            //}
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = array();
        }

        $url = $this->getUrlInstance()->setStore($storeId)->getUrl($routePath, $routeParams);
        $product->setRequestPath($oldRequestPath);
        
        return $url;
    }
}