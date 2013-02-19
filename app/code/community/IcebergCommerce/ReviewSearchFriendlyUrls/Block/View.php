<?php
/**
 * Iceberg Commerce
 *
 * @author     IcebergCommerce
 * @package    IcebergCommerce_ReviewSearchFriendlyUrls
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */

class IcebergCommerce_ReviewSearchFriendlyUrls_Block_View extends Mage_Review_Block_View
{
	/**
     * Add meta information from product to head block
     * @return Mage_Catalog_Block_Product_View
     */
    protected function _prepareLayout()
    {
    	parent::_prepareLayout();
    	
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) 
        {
            $product = $this->getProduct();
            
            // SEO Page title
			$title = array();
			if ($product = $this->getProductData())
			{
				$title[] = $product->getName() . ' Review';
			}
			
			if ($review = $this->getReviewData())
			{
				$title[] = $review->getTitle();
			}
			
			$title = implode(' - ', $title);
			$headBlock->setTitle($title);

			
			// Remove Canonical Url if set
			if (method_exists($this->helper('catalog/product'), 'canUseCanonicalTag')) // Magento 1.3 Compat
			{
	            if ($this->helper('catalog/product')->canUseCanonicalTag()) 
	            {
	            	foreach ($headBlock->getItems() as $item)
	            	{
	            		if (isset($item['params']) && is_string($item['params']) && $item['params'] == 'rel="canonical"')
	            		{
	            			$headBlock->removeItem('link_rel', $item['name']);
	            		}
	            	}
	                //$params = array('_ignore_category'=>true);
	                //$headBlock->addLinkRel('canonical', Mage::registry('current_review')->getReviewUrl());
	            }
            }
        }

        return $this;
    }
    
	/**
     * Overwrite backURL button.
     *
     * @return string
     */
    public function getBackUrl()
    {
		$product = null;
		if ($this->getProduct())
		{
			$product = $this->getProduct();
		}
		elseif ($this->getProductData())
		{
			if ($this->getProductData()->getId() > 0)
			{
				$product = Mage::getModel("catalog/product")->load($this->getProductData()->getId());
			}
		}
		
		if ($product)
		{
			return Mage::getModel('reviewsearchfriendlyurls/reviews')->getReviewsUrl($product, $product->getCategory());
		}
		else
		{
			return parent::getBackUrl();
		}
    }	
}