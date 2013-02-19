<?php
/**
 * @version 0.2
 *
 * (c) 2011 Tomas Srnka, Relbit, s.r.o. <tomas.srnka@relbit.com>
 * (c) 2011 Jakub Jursa, Relbit, s.r.o. <jakub.jursa@relbit.com>
 */
  
if (!function_exists("curl_init")) {
	throw new Exception("Relbit needs the CURL PHP extension.");
}
if (!function_exists("json_decode")) {
	throw new Exception("Relbit needs the JSON PHP extension.");
}

class Relbit_API {
	private $acl_api_key;
	private $acl_user_id;
	
	private $debug;
	
	private $request;
	private $request_type;
	private $response;
	
	function __construct($config) {
		if (!is_array($config)) {
			throw new Exception ("Invalid config format.");
		}

		if (!array_key_exists("acl_api_key", $config)) {
			throw new Exception ("Missing ACL API key in config.");
		}
		
		if (array_key_exists("debug", $config)) {
			$this->debug = $config["debug"];
		} else {
			$this->debug = false;
		}
		
		$this->acl_api_key = $config["acl_api_key"];
	}
	
	public function send_request($type, $params = false) {
		$this->request["type"] = $type;
		$this->request["acl_api_key"] = $this->acl_api_key;
		
		if ($params) {
			foreach ($params as $key => $mixed) {
				$this->_add_param($key, $mixed);
			}
		}
		
		$this->_send();
		
		$response = $this->_get_response();
		$this->_reset_response();
		return $response;
	}
	
	private function _add_param($key, $mixed, $force = false) {
		if ($key == "type") {
			throw new Exception("'type' is a keyword, you can't use it as key name.");
		}
		
		if (array_key_exists($key, $this->request) && !$force) {
			throw new Exception("Key '{$key}' is already in response.");
		}
			
		$this->request[$key] = $mixed;
	}
	
	private function _send() {
		$this->_reset_response();
		
		$ch = curl_init();
		
		// set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, URL);//constant("Relbit_API::URL"));
		curl_setopt($ch,CURLOPT_POST, 1);
		//temporary fix to empty responses from relbit
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS, "request=" . urlencode(json_encode($this->request)));
		// TODO: weird CURL SSL handling
		
		// execute post
		$res = curl_exec($ch);
		if ($res === false) {
		    Gpf_Log::error('Curl error: ' . curl_error($ch));
		}
		
		if ($this->debug) {
			Gpf_Log::debug('RELBIT REQUEST: ' . print_r($this->request, true) . "\nRELBIT RESPONSE: " . print_r($res. true));
		}
		
		$this->response = json_decode($res, true);
		// close connection
		curl_close($ch);
		$this->_reset_request();
	}
	
	private function _get_response() {
		return $this->response;
	}
	
	private function _reset_request() {
		$this->request = array();
	}
	
	private function _reset_response() {
		$this->response = array();
	}
}
?>
