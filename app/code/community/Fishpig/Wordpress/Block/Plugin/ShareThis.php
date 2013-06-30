<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Plugin_ShareThis extends Fishpig_Wordpress_Block_Plugin_Abstract
{
	/**
	 * Flag to determine whether to include JS
	 *
	 * @var static bool
	 */
	static $_jsIncluded = false;

	/**
	 * Determine whether the plugin is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return Mage::helper('wordpress')->isPluginEnabled('Share This');
	}

	/**
	 * Generate and return the ShareThis markup
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		if (!$this->getTemplate()) {
			if ($this->isEnabled() && $this->canDisplayOnPost()) {
				return $this->getJs() . $this->getHtml($this->getPost());
			}
		}
		
		return parent::_toHtml();
	}
	
	/**
	 * Determine whether to display icons on post page
	 * This is set in the ShareThis configuration in the WordPress Admin
	 *
	 * @return bool
	 */
	public function canDisplayOnPost()
	{
		return Mage::helper('wordpress')->getWpOption('st_add_to_content') == 'yes' ? true : false;
	}
	
	/**
	 * Retrieve the Javascript include HTML
	 *
	 * @return string
	 */
	public function getJs()
	{
		if (!self::$_jsIncluded) {
			self::$_jsIncluded = true;
			
			return Mage::helper('wordpress')->getWpOption('st_widget');
		}
	}
	
	/**
	 * Retrieve the icon HTML for the post
	 *
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return string
	 */
	public function getHtml(Fishpig_Wordpress_Model_Post $post)
	{
		$html = Mage::helper('wordpress')->getWpOption('st_tags');
		
		if (preg_match_all("/(<span.*><\/span>)/iU", $html, $matches)) {
			$tags = array();

			foreach($matches[1] as $match) {
				$class = $this->_patternMatch("/class='(.*)'/iU", $match);
				$displayText = $this->_patternMatch("/displayText='(.*)'/iU", $match);
				$stVia = trim($this->_patternMatch("/st_via='(.*)'/iU", $match));
				
				if ($displayText) {
					$displayText = ' displayText="' . $displayText . '" ';
				}
				
				if ($stVia) {
					$stVia = ' st_via="' . $stVia . '"';
				}

				$tag = sprintf('<span class="%s"%sst_title="%s" st_summary="%s" st_url="%s"%s></span>', 
								$class, $displayText, addslashes($post->getPostTitle()), trim(strip_tags(addslashes($post->getPostExcerpt()))), $post->getPermalink(), $stVia);
			
				if ($image = $post->getFeaturedImage()) {
					$tag = str_replace('></span>', ' st_image="' . $image->getAvailableImage() . '"></span>', $tag);
				}
				
				$tags[] = $tag;
			}

			return implode('', $tags);
		}
	}
	
	/**
	 * Match a pattern
	 *
	 * @param string $pattern
	 * @param string $string
	 * @param int $rturn
	 * @return false|string
	 */
	protected function _patternMatch($pattern, $string, $return = 1)
	{
		if (preg_match($pattern, $string, $match)) {
			return isset($match[$return]) ? $match[$return] : $match;
		}
		
		return false;	
	}
}
