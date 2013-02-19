<?php
/**
 * Iceberg Commerce
 *
 * @author     IcebergCommerce
 * @package    IcebergCommerce_ReviewSearchFriendlyUrls
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */

class IcebergCommerce_ReviewSearchFriendlyUrls_Block_List extends Mage_Review_Block_Product_View_List 
{
	protected function _prepareLayout()
    {
        parent::_prepareLayout();
    	
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock && Mage::app()->getRequest()->getModuleName()=='review') 
        {
            $product = Mage::registry('product');
            
            // SEO Page title
			if ($product)
			{
				$title = array();
				$headBlock->setTitle($product->getName() . ' - Product Reviews');
			}
			
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
	                //$headBlock->addLinkRel('canonical', Mage::getModel('reviewsearchfriendlyurls/reviews')->getReviewsUrl($product));
	            }
            }
            
        }

        return $this;
    }
    
	/**
	 * Get inidividual review URL for list.
	 * 
	 * @param review_id $id
	 */
	public function getReviewUrl( $id )
    {
		$model = Mage::getModel('review/review');
		$review = $model->load( $id );
		
		$product = Mage::registry('product');
		
		$m = $review->setProduct( $product );
    	
        return $m->getReviewUrl();
    }
}