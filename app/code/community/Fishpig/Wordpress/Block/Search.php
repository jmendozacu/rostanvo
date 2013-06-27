<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Search extends Fishpig_Wordpress_Block_Post_List_Wrapper_Abstract
{
	/**
	 * URL base for the search system
	 *
	 * @const string
	 */
	const URL_BASE = 'search';
	
	/**
	 * Minimum word length
	 *
	 * @const int
	 */
	const SEARCH_MIN_WORD_LENGTH = 3;
	
	/**
	 * Retrieve the raw query string text
	 *
	 * @return string|null
	 */
	public function getRawSearchString()
	{
		$str = trim(Mage::app()->getRequest()->getParam('s'));
		
		$str = urldecode($str ? $str : trim(Mage::app()->getRequest()->getParam('search')));
		
		return str_replace('-', ' ', $str);
	}

	/**
	 * Retrieve the escaped query string text
	 *
	 * @return string|null
	 */	
	public function getEscapedSearchString()
	{
		return $this->escapeHtml($this->getRawSearchString());
	}
	
	/**
	 * Retrieve a parsed version of the search string
	 * If search by single word, string will be split on each space
	 *
	 * @return array
	 */
	public function getParsedSearchString()
	{
		$words = explode(' ', $this->getRawSearchString());

		foreach($words as $it => $word) {
			if (strlen($word) < self::SEARCH_MIN_WORD_LENGTH) {
				unset($words[$it]);
			}
		}
		
		return $words;
	}

	/**
	 * Retrieve the results URL
	 *
	 * @param string $query = ''
	 * @return string
	 */
	public function getResultsUrl($query = '')
	{
		if ($this->getEscapedSearchString()) {
			return $this->getUrl('search/' . $this->getEscapedSearchString() . '/');
		}
		
		return '';
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */
	protected function _getPostCollection()
	{
		if (is_null($this->_postCollection)) {
			$this->_postCollection = parent::_getPostCollection()
				->addPostTypeFilter(array('post', 'page'))
				->addSearchStringFilter($this->getParsedSearchString(), array('post_title', 'post_content', 'post_excerpt'), 'OR');
		}
		
		return $this->_postCollection;
	}
	
	/**
	 * Retrieve the custom no result text
	 * 
	 * @return string
	 */
	public function getNoResultText()
	{
		return Mage::helper('wordpress')->__($this->getData('no_result_text'));
	}
}
