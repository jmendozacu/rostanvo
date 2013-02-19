<?php
class Magestore_Storepickup_Helper_Url extends Mage_Core_Helper_Abstract
{
	public function getResponseBody($url)
	{
		if(ini_get('allow_url_fopen') != 1) 
		{
			@ini_set('allow_url_fopen', '1');
		}
		if(ini_get('allow_url_fopen') == 1) 
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			$contents = curl_exec($ch);
			curl_close($ch);
		} else {
		   	$contents=file_get_contents($url);
		}

		return $contents;
	}
}
?>