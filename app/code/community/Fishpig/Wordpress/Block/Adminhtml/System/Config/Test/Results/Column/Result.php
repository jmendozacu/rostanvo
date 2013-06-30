<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Adminhtml_System_Config_Test_Results_Column_Result extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/**
	 * Retrieve the value for the column for $row
	 *
	 * @param Varien_Object $row
	 * @return string
	 */
	public function _getValue(Varien_Object $row)
	{
		$styles = array(
			'background-color:transparent',
			'background-position:3px 1px',
			'border:0px solid', 'display:block',
			'width:23px',
		);
		
		return sprintf('<span id="%s" class="%s" style="%s">&nbsp</span>%s', 
			$this->getSpanId($row), 
			parent::_getValue($row), 
			implode(' !important;', $styles), 
			$this->_isHiddenHtml($row)
		);
	}
	
	/**
	 * Retrieve the unique ID for each row
	 *
	 * @param Varien_Object $row
	 * @return string
	 */
	public function getSpanId(Varien_Object $row)
	{
		return 'wp-' . md5($row->getTitle());
	}
	
	/**
	 * Retrieve the HTML/JS to hide the row
	 *
	 * @return string
	 */
	protected function _isHiddenHtml(Varien_Object $row)
	{
		if ($row->getIsHidden()) {
			return sprintf("<script type=\"text/javascript\">$('%s').up('tr').hide();</script>", $this->getSpanId($row));
		}
		
		return '';
	}
	
	/**
	 * Retrieve the modue name
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		return parent::getModuleName() . '_Adminhtml';
	}
}