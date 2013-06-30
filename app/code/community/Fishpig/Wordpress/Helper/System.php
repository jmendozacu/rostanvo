<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_System extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * Retrieve a collection of integration results
	 *
	 * @return Varien_Data_Collection
	 */
	public function getIntegrationTestResults()
	{
		if (!Mage::helper('wordpress')->isEnabled()) {
			return false;
		}

		if (!$this->_isCached('integration_results')) {
			$results = new Varien_Data_Collection();
		
			$results->addItem($this->_isConnected());
			
			if (Mage::helper('wordpress')->isFullyIntegrated()) {
				$validWpUrls = $this->_hasValidWordPressUrls();
				
				$results->addItem($validWpUrls);
				
				if ($validWpUrls->getIsError()) {
					$results->addItem($this->_createErrorTestResultObject($this->__('Blog Route')));	
				}
				else {
					$results->addItem($this->_hasValidBlogRoute());
				}
				
				$results->addItem($this->_hasValidWordPressPath());
			}
			
//			if (($result = $this->_getUpsellTestResult()) !== false) {
//				$results->addItem($result);
//			}
			
			$results->addItem($this->_addUpdateTestResult());
			
			Mage::dispatchEvent('wordpress_integration_test_results_after', array('results' => $results));

			$this->_cache('integration_results', $results);
		}
		
		return $this->_cached('integration_results');
	}

	/**
	 * Determine whether the integration tests encountered errors
	 *
	 * @return bool
	 */
	public function integrationHasErrors()
	{
		if ($results = $this->getIntegrationTestResults()) {
			foreach($results as $result) {
				if ($result->getIsError() && $result->getResult() === 'error-msg') {
					return true;
				}
			}
			
			return false;			
		}
		
		return true;
	}
	
	/**
	 * Determine whether the database is connected
	 *
	 * @return Varien_Object
	 */
	protected function _isConnected()
	{
		$title = 'Database';

		if (!Mage::helper('wordpress/database')->isConnected()) {
			$response = $this->__("Ensure the WordPress database details entered below match the details in wp-config.php"); 
			
			return $this->_createTestResultObject($title, $response, false);
		}
		
		return $this->_createTestResultObject($title);
	}
	
	/**
	  * Determine whether the database is queryable
	  *
	  * @return Varien_Object
	  */
	protected function _isQueryable()
	{
		$title = 'Database Query';

		if (!Mage::helper('wordpress/database')->isQueryable()) {
			if ($prefix = Mage::helper('wordpress/database')->getTablePrefix()) {
				$response = $this->__("Unable to query the WordPress database using the table prefix '%s'. Ensure the details entered below match those in wp-config.php", $prefix); 
			}
			else {
				$response = $this->__("Unable to query the WordPress database using no table prefix. Ensure the details entered below match those in wp-config.php");
			}

			return $this->_createTestResultObject($title, $response, false);
		}
		
		return $this->_createTestResultObject($title);
	}
	
	/**
	 * Determine whether the Wordpress URL's are valid
	 *
	 * @return Varien_Object
	 */
	protected function _hasValidWordPressUrls()
	{
		$title = "WordPress Install Location";
		
		if (Mage::helper('wordpress/database')->isQueryable()) {
			$helper = Mage::helper('wordpress');
			
			$blogUrl 	 = rtrim(str_replace('/index.php', '', $helper->getUrl()), '/');
			$installUrl = rtrim(str_replace('/index.php', '', $helper->getWpOption('siteurl')), '/');
			
			if (!$installUrl) {
				$response = $this->__("Unable to determine your WordPress install URL");
	
				return $this->_createTestResultObject($title, $response, false);			
			}
			else if ($blogUrl == $installUrl) {
				$response = $this->__('Your WordPress install URL matches your integrated blog URL (Magento URL + Blog Route). Either change your blog route below (you will need to update the WordPress option Site Address) or move WordPress to a different sub-directory.');
	
				return $this->_createTestResultObject($title, $response, false);		
			}
		}
		else {
			return $this->_createTestResultObject($title, '--', false);	
		}
		
		return $this->_createTestResultObject($title);	
	}
	
	/**
	 * Determine whether the blog route is valid
	 *
	 * @return Varien_Object
	 */
	protected function _hasValidBlogRoute()
	{
		$title = "Blog Route";	
		
		if (Mage::helper('wordpress/database')->isQueryable()) {
			$helper = Mage::helper('wordpress');

			$wpBlogUrl 	  = rtrim($helper->getWpOption('home'), '/');
			$mageBlogUrl = rtrim(($helper->getUrl()), '/');			
			$response = false;
			
			if (trim($helper->getBlogRoute(), '/') === '') {
				$response = $this->__('Your blog route cannot be empty.');
			}
			else if (strpos($helper->getBlogRoute(), '/') !== false) {
				$response = $this->__('Your blog route cannot contain a slash character.');
			}
			else if ($wpBlogUrl != $mageBlogUrl) {
				$response = $this->__("Go to WP Admin > Settings > General and set the Site address field to '%s'", $mageBlogUrl);

				if (Mage::helper('wordpress')->isWordPressMU()) {
					if (Mage::helper('wpmultisite')->isDefaultBlog()) {
						$response = $this->__("Change your 'home' option in the '%s' table to <strong>%s</strong>' using phpMyAdmin.", $helper->getTableName('wordpress/option'), $mageBlogUrl);	
					}
					else {
						$response = $this->__("Go to WP Admin > Network Admin > All Sites. Find the Site and click Edit. Select the Settings tab and change the field 'Home' to <strong>%s</strong>", $mageBlogUrl);	
					}
				}
			}
			
			if ($response !== false) {
				return $this->_createTestResultObject($title, $response, false);
			}
		}
		else {
			return $this->_createTestResultObject($title, '--', false);		
		}

		return $this->_createTestResultObject($title);		
	}
	
	protected function _addUpdateTestResult()
	{
		$msg = $this->__('A new version of WordPress Integration is available.');
		
		return $this->_createTestResultObject('Upgrade Extension', $msg, 'warning-msg')
			->setIsHidden(true);
	}
	
	protected function _getUpsellTestResult()
	{
		$packages = array(
			'multisite' => array(
				'package' => 'Fishpig_Wordpress_Addon_Multisite',
				'title' => 'WordPress Multisite Integration',
				'description' => 'Upgrade WordPress Integration to %s to have a blog per store.',
				'url' => 'http://fishpig.co.uk/wordpress-multisite-integration.html',
			),
			'disqus' => array(
				'package' => 'Fishpig_Wordpress_Addon_Disqus',
				'title' => 'Disqus Comments',
				'description' => 'Increase your blog with %s and let your customers comment with their Facebook and Twitter accounts.',
				'url' => 'http://fishpig.co.uk/wordpress-integration-disqus-comments.html',
			),
		);
		
		if (Mage::app()->isSingleStoreMode()) {
			unset($packages['multisite']);
		}
		
		$modules = (array)Mage::getConfig()->getNode('modules')->children();
		$tracking = urlencode('utm_source=magento&utm_medium=warning&utm_campaign=wp-int-test-results');
		
		foreach($packages as $package) {
			if (!isset($modules[$package['package']])) {
				$msg = sprintf($package['description'], sprintf('<a href="%s?%s" target="_blank">%s</a>', $package['url'], $tracking, $package['title']));
				
				return $this->_createTestResultObject('Improve your blog', $msg, 'warning-msg');
			}
		}
		
		return false;
	}
	
	/**
	 * Determine whether the WordPress path is valid
	 *
	 * @return Varien_Object
	 */
	protected function _hasValidWordPressPath()
	{
		$title = "WordPress Path";	
		
		if (Mage::helper('wordpress')->getWordPressPath() === false) {
			$response = $this->__('The WordPress path field is empty. Please enter the sub-directory that WordPress is installed in.');
		}
		else if (!$this->hasValidWordPressPath()) {
			$response = $this->__('Unable to find a WordPress installation using the path you entered below.');
		}
		else {
			$file = rtrim(Mage::helper('wordpress')->getWordPressPath(), DS) . DS . 'wp-content' . DS . 'plugins' . DS . 'fishpigs-wordpress-mu-integration.php';
			
			if (is_file($file)) {
				@unlink($file);
			}

			return $this->_createTestResultObject($title);	
		}
		
		return $this->_createTestResultObject($title, $response, false);
	}
	
	public function hasValidWordPressPath()
	{
		$path = Mage::helper('wordpress')->getWordPressPath();
		
		if ($path !== false) {
			return $path && is_dir($path) && is_file(rtrim($path, '/\\') . DS . 'wp-config.php');
		}
		
		return false;
	}

	/**
	 * Create a test result object
	 *
	 * @param string $title
	 * @param string $response = ': )'
	 * @param mixed $result = true
	 * @return Varien_Object
	 */
	protected function _createTestResultObject($title, $response = ': )', $result = true)
	{
		$resultClass = $result;
		
		$isError = ($result !== true);

		if ($result === true) {
			$resultClass = 'success-msg';
		}
		else if ($result === false) {
			$resultClass = 'error-msg';
		}
		
		return new Varien_Object(array(
			'title' => $this->__($title), 
			'response' => $response, 
			'is_error' => $isError, 
			'result' => $resultClass
		));
	}
	
	protected function _createErrorTestResultObject($title)
	{
		return $this->_createTestResultObject($title, '--', false);
	}
	
	/**
	 * Retrieve the database host
	 *
	 * @return string
	 */
	protected function _getDatabaseHost()
	{
		$helper = Mage::helper('wordpress');
		
		if (!$helper->isSameDatabase()) {
			return $helper->getConfigValue('wordpress/database/host');
		}
		
		return '';
	}
	
	/**
	 * Retrieve the database name
	 *
	 * @return string
	 */
	protected function _getDatabaseName()
	{
		$helper = Mage::helper('wordpress');
		
		if (!$helper->isSameDatabase()) {
			if ($dbname = $helper->getConfigValue('wordpress/database/dbname')) {
				return Mage::helper('core')->decrypt($dbname);
			}
		}
		
		return '';
	}
	
	/**
	  * Determine whether the ACL is valid
	  *
	  * @return bool
	  */
	public function isAclValid()
	{
		try {
			$session = Mage::getSingleton('admin/session');
			$resourceId = $session->getData('acl')->get("admin/system/config/wordpress")->getResourceId();
			return $session->isAllowed($resourceId);	
		}
		catch (Exception $e) { }
		
		return false;
	}
	
	/**
	 * Send a HTTP Post request
	 *
	 * @param string $url
	 * @param array $data = array
	 * @return false|string
	 */
	public function makeHttpPostRequest($url, array $data = array())
	{
		if (!method_exists('Varien_Http_Adapter_Curl', 'addOption')) {
			return $this->_makeLegacyHttpPostRequest($url, $data);
		}
		
		$curl = new Varien_Http_Adapter_Curl();

		$curl->setConfig(array(
			'verifypeer' => strpos($url, 'https://') !== false,
			'header' => true,
			'timeout' => 10,
		));
		
		$curl->addOption(CURLOPT_FOLLOWLOCATION, false);
		$curl->addOption(CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

		$curl->write(Zend_Http_Client::POST, $url, '1.1', array(), $data);

		$response = $curl->read();
		
		if ($curl->getErrno() || $curl->getError()) {
			throw new Exception(Mage::helper('wordpress')->__('CURL (%s): %s', $curl->getErrno(), $curl->getError()));
		}

		$curl->close();
		
		return $response;
	}

	/**
	 * Send a HTTP Post request through older Magento
	 * Please upgrade Magento!
	 *
	 * @param string $url
	 * @param array $data = array
	 * @return false|string
	 */
	protected function _makeLegacyHttpPostRequest($url, array $data = array())
	{
		foreach($data as $key => $value) {
			$data[$key] = urlencode($key) . '=' . urlencode($value);
		}
		
		$body = implode('&', $data);
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($data));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if (strpos($url, 'https://') !== false) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		
		$response = curl_exec($ch);

		if (curl_errno($ch) || curl_error($ch)) {
			throw new Exception(Mage::helper('wordpress')->__('CURL (%s): %s', curl_errno($ch), curl_error($ch)));
		}

		curl_close($ch);	

		return $response;
	}

	/**
	 * Attempt to login to WordPress
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $destination
	 * @return bool
	 */
	public function loginToWordPress($username, $password, $destination = null)
	{
		$url = Mage::helper('wordpress')->getBaseUrl('wp-login.php');
		
		if (is_null($destination)) {
			$destination = $this->getUrl();
		}

		$result = $this->makeHttpPostRequest($url, array(
			'log' => $username,
			'pwd'	 => $password,
			'rememberme' => 'forever',
			'redirect_to' => $destination,
			'testcookie' => 1
		));
	
		if (strpos($result, 'Location: ') === false) {
			$header = substr($result, 0, strpos($result, "\r\n\r\n"));
	
			throw new Exception('WordPress Auto Login Failed: ' . $header);
		}

		foreach(explode("\n", $result) as $line) {
			if (substr(ltrim($line), 0, 1) !== '<') {
				header($line, false);
			}
			else {
				break;
			}
		}

		return true;
	}
	
	public function getExtensionVersion($extension = 'Fishpig_Wordpress')
	{
		return (string)Mage::getConfig()->getNode('modules/' . $extension . '/version');
	}
}
