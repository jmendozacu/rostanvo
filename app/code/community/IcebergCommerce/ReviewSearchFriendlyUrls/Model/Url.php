<?php
/**
 * Iceberg Commerce
 *
 * @author     IcebergCommerce
 * @package    IcebergCommerce_ReviewSearchFriendlyUrls
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */

class IcebergCommerce_ReviewSearchFriendlyUrls_Model_Url extends Mage_Catalog_Model_Url
{
    /**
     * Refresh rewrite urls
     *
     * @param int $storeId
     * @return Mage_Catalog_Model_Url
     */
    public function refreshRewrites($storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshRewrites($store->getId());
            }
            return $this;
        }

        $this->refreshProductReviewRewrites($storeId);

        return $this;
    }

    /**
     * Refresh product rewrite
     *
     * @param Varien_Object $product
     * @param Varien_Object $category
     * @return Mage_Catalog_Model_Url
     */
    public function addUrlRewrite(Varien_Object $product )
    {
        if ($product->getUrlKey() == '') {
            $urlKey = $this->getProductModel()->formatUrlKey($product->getName());
        }
        else {
            $urlKey = $this->getProductModel()->formatUrlKey($product->getUrlKey());
        }

        $idPath      = $this->generateReviewsPath('id', $product );
        $targetPath  = $this->generateReviewsPath('target', $product);
        $requestPath = $this->getUnusedPath( $product->getStoreId() , $this->generateReviewsPath('request' , $product ) , $idPath );

        $categoryId = null;
        $categoryId = $product->getCategoryId() ? $product->getCategoryId()  :  null;
        $updateKeys = true;

        $rewriteData = array(
            'store_id'      => $product->getStoreId(),
            'category_id'   => $categoryId,
            'product_id'    => $product->getId(),
            'id_path'       => $idPath,
            'request_path'  => $requestPath,
            'target_path'   => $targetPath,
            'is_system'     => 0
        );

        $this->getResource()->saveRewrite($rewriteData, $this->getResource()->getRewriteByIdPath($idPath , $product->getStoreId() ));

        return $requestPath;
    }
    
    /**
     * Make sure path is not in use.
     * 
     * @param $storeId
     * @param $requestPath
     * @param $idPath
     */
	public function getUnusedPath($storeId, $requestPath, $idPath)
    {
        $suffix = $this->getProductUrlSuffix($storeId);

        if (empty($requestPath)) {
            $requestPath = '-';
        } elseif ($requestPath == $suffix) {
            $requestPath = '-' . $suffix;
        }

        /**
         * Validate maximum length of request path
         */
        if (strlen($requestPath) > self::MAX_REQUEST_PATH_LENGTH + self::ALLOWED_REQUEST_PATH_OVERFLOW) {
            $requestPath = substr($requestPath, 0, self::MAX_REQUEST_PATH_LENGTH);
        }

        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            if ($this->_rewrites[$idPath]->getRequestPath() == $requestPath) {
                return $requestPath;
            }
        }
        else {
            $this->_rewrite = null;
        }

        $rewrite = $this->getResource()->getRewriteByRequestPath($requestPath, $storeId);
        if ($rewrite && $rewrite->getId()) {
            if ($rewrite->getIdPath() == $idPath) {
                $this->_rewrite = $rewrite;
                return $requestPath;
            }
            // match request_url abcdef1234(-12)(.html) pattern
            $match = array();
            if (!preg_match('#^([0-9a-z/-]+?)(-([0-9]+))?('.preg_quote($suffix).')?$#i', $requestPath, $match)) {
                return $this->getUnusedPath($storeId, '-', $idPath);
            }
            $requestPath = $match[1].(isset($match[3])?'-'.($match[3]+1):'-1').(isset($match[4])?$match[4]:'');
            return $this->getUnusedPath($storeId, $requestPath, $idPath);
        }
        else {
            return $requestPath;
        }
    }




    /**
     * Generate either id path, request path or target path for product reviews list
     *
     * @param string $type
     * @param Varien_Object $product
     * @return string
     * @throws Mage_Core_Exception
     */
    public function generateReviewsPath($type = 'target', $product = null)
    {
        if (!$product) {
            Mage::throwException(Mage::helper('core')->__('Specify either category or product, or both.'));
        }

        // generate id_path
        if ('id' === $type) {
            if (!$product) {
            	Mage::throwException(Mage::helper('core')->__('Specify a product.'));
                //return 'reviews/' . $category->getId();
            }
            return 'reviews/' . $product->getId();
        }

        // generate request_path
        if ('request' === $type) {

            if ($product->getUrlKey() == '') {
                $urlKey = $this->getProductModel()->formatUrlKey($product->getName());
            }
            else {
                $urlKey = $this->getProductModel()->formatUrlKey($product->getUrlKey());
            }
            $productUrlSuffix  = $this->getProductUrlSuffix($product->getStoreId());

            return $this->getUnusedPath($product->getStoreId(), 'reviews/' . $urlKey . $productUrlSuffix,
                $this->generatePath('id', $product)
            );
        }

        return 'review/product/list/id/' . $product->getId();
    }
    
    public function addSingleReviewUrlRewrite( $review , $product )
    {
    	$idPath      = $this->generateSinglePath('id', $review , $product );
        $targetPath  = $this->generateSinglePath('target', $review , $product );
        $requestPath = $this->getUnusedPath( $review->getStoreId() , $this->generateSinglePath('request' , $review , $product ) , $idPath );
    	
        $categoryId = $product->getCategoryId() ? $product->getCategoryId()  :  null;
        
    	$rewriteData = array(
            'store_id'      => $review->getStoreId(),
    		'category_id'	=> $categoryId,
            'product_id'    => $product->getId(),
            'id_path'       => $idPath,
            'request_path'  => $requestPath,
            'target_path'   => $targetPath,
            'is_system'     => 0
        );
        

        $this->getResource()->saveRewrite($rewriteData, $this->getResource()->getRewriteByIdPath($idPath , $review->getStoreId() ));

    	return $requestPath;
    }
    
	/**
     * Generate either id path, request path or target path for product review
     *
     * @param string $type
     * @param Varien_Object $product
     * @return string
     * @throws Mage_Core_Exception
     */
    public function generateSinglePath($type = 'target', $review , $product )
    {
        // generate id_path
        if ('id' === $type) {
            return 'review/' . $review->getId();
        }

        // generate request_path
        if ('request' === $type) {
        	if ($product->getUrlKey() == '') {
                $pUrlKey = $this->getProductModel()->formatUrlKey($product->getName());
            }
            else {
                $pUrlKey = $this->getProductModel()->formatUrlKey($product->getUrlKey());
            }
            
            $urlKey = $this->getProductModel()->formatUrlKey( $review->getTitle() );
            
            if( !$urlKey)
            	$urlKey = 'review';
            
            $productUrlSuffix  = $this->getProductUrlSuffix($review->getStoreId());
            
            return $this->getUnusedPath($review->getStoreId(), 'reviews/' . $pUrlKey . '/' . $urlKey . $productUrlSuffix,
                $this->generateSinglePath('id', $review , $product )
            );
        }
        
        return 'review/product/view/id/' . $review->getId();
    }
}
