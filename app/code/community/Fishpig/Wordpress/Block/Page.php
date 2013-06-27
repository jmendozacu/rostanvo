<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Page extends Mage_Core_Block_Template
{
	/**
	 * Returns the currently loaded page model
	 *
	 * @return Fishpig_Wordpress_Model_Page
	 */
	public function getPage()
	{
		return $this->_getData('page') ? $this->_getData('page') : Mage::registry('wordpress_page');
	}
	
	/**
	  * Returns the HTML for the comments block
	  *
	  * @return string
	  */
	public function getCommentsHtml()
	{
		return $this->getChildHtml('comments');
	}

	/**
	 * Setup the comments block
	 *
	 */
	protected function _beforeToHtml()
	{
		if ($commentsBlock = $this->getChild('comments')) {
			$commentsBlock->setPost($this->getPage());
		}
		
		return parent::_beforeToHtml();
	}
	
	/**
	 * Retrieve the HTML for the password protect form
	 *
	 * @return string
	 */
	public function getPasswordProtectHtml()
	{
		return $this->getLayout()
			->createBlock('core/template')
			->setTemplate('wordpress/protected.phtml')
			->setEntityType('page')
			->setPage($this->getPage())
			->toHtml();
	}
}
