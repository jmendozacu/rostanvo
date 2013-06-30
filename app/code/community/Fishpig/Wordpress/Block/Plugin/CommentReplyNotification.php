<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Plugin_CommentReplyNotification extends Fishpig_Wordpress_Block_Plugin_Abstract
{
	/**
	 * If the plugin is enabled, display the form component
	 *
	 * @return $this
	 */
	protected function _beforeToHtml()
	{
		if ($this->helper('wordpress')->isPluginEnabled('comment-reply-notification')) {
			if (!$this->getTemplate()) {
				$this->setTemplate('wordpress/plugin/comment-reply-notification/form.phtml');
			}

			if ($options = Mage::helper('wordpress')->getWpOption('commentreplynotification')) {
				$options = unserialize($options);

				if (isset($options['mail_notify']) && in_array($options['mail_notify'], array('parent_check', 'parent_uncheck'))) {
					$this->setIsEnabled(true);
					$this->setOptInChecked($options['mail_notify'] === 'parent_check');
				}
			}
		}
		
		return parent::_beforeToHtml();		
	}
}
