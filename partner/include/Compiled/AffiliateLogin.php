<?php
/** *   @copyright Copyright (c) 2008-2009 Quality Unit s.r.o. *   @author Quality Unit *   @package Core classes *   @since Version 1.0.0 *    *   Licensed under the Quality Unit, s.r.o. Dual License Agreement, *   Version 1.0 (the "License"); you may not use this file except in compliance *   with the License. You may obtain a copy of the License at *   http://www.qualityunit.com/licenses/gpf *    */

if (!class_exists('Gpf_Object', false)) {
  class Gpf_Object {
  
      /**
       * @return Gpf_DbEngine_Database
       */
      protected function createDatabase() {
          return Gpf_DbEngine_Database::getDatabase();
      }
  
      /**
       * Translate input message into selected language.
       * If translation will not be found, return same message.
       *
       * @param string $message
       * @return string
       */
      public function _($message) {
          $args = func_get_args();
          return Gpf_Lang::_($message, $args);
      }
      
      /**
       * Translates text enclosed in ##any text##
       * This function is not parsed by language parser, because as input should be used e.g. texts loaded from database
       *
       * @param string $message String to translate
       * @return string Translated text
       */
      public function _localize($message) {
          return Gpf_Lang::_localizeRuntime($message);
      }
      
      /**
       * Translate input message into default language defined in language settings for account.
       * This function should be used in case message should be translated to default language (e.g. log messages written to event log)
       *
       * @param string $message
       * @return string
       */
      public function _sys($message) {
          $args = func_get_args();
          return Gpf_Lang::_sys($message, $args);
      }
  }

} //end Gpf_Object

if (!class_exists('Gpf_Install_Requirements', false)) {
  class Gpf_Install_Requirements extends Gpf_Object {
      const MYSQL_MIN_VERSION = '4.1';
      private $requirements = array();
      private static $info;
  
      protected function check() {
          $this->requirements = array();
          $this->checkAccountsWritable();
          $this->checkPhpIncludePath();
          if (!defined('CHECK_MYSQL_DISABLED')) {
              $this->checkMysql();
          }
          $this->checkGdLibrary();
          if (!defined('CHECK_MODSEC_DISABLED')) {
              $this->checkModSec();
          }
  
          $this->checkRuntimeRequirements();
      }
  
      protected function checkRuntimeRequirements() {
          $this->checkMemoryLimit();
          $this->checkCompatibilityMode();
          $this->checkDisabledFunctions();
          $this->checkStandardPHPLibrary();
          $this->checkSessionAutostart();
          $this->checkSessionSavePath();
      }
  
      public function checkRuntime() {
          $this->checkRuntimeRequirements();
          $message = "";
          foreach ($this->requirements as $requirement) {
              if (!$requirement->isValid()) {
                  $message .= $requirement->getFixDescription().', ';
              }
          }
          if($message != '') {
              die(rtrim($message, ', '));
          }
      }
  
      /**
       * Check if GD library is installed in php (required for e.g. Captcha images)
       *
       */
      protected function checkGdLibrary() {
          $requirement = new Gpf_Install_Requirement();
          $requirement->setResult(extension_loaded('gd') && Gpf_Php::isFunctionEnabled('gd_info'));
          $requirement->setPositiveName($this->_('GD extension is installed'));
          $requirement->setNegativeName($this->_('GD extension is not installed'));
          $requirement->setFixDescription($this->_('Please add support of gd2 extension in your php, otherwise e.g. captcha images will not work!'));
          $this->addRequirement($requirement);
      }
  
      private function makeServiceCall($string) {
          $request = new Gpf_Rpc_DataRequest('Gpf_Install_CheckModSecRpcCaller', 'check');
          $request->setUrl(Gpf_Paths::getInstance()->getFullScriptsUrl(). 'server.php');
  
          $request->setField('teststring',$string);
          try {
              $request->sendNow();
          } catch (Gpf_Exception $e) {
              return false;
          }
          $data = $request->getData();
          if ($data->getParam('status')!='OK') {
              return false;
          }
          if ($data->getParam('recieved')!=$string) {
              return false;
          }
          return true;
      }
  
      private function checkModSecCalls() {
          //mod security check, if you need another check just add it to string below
          //example: if (!$this->makeServiceCall('select ANOTHER STRING')) {
          if (!$this->makeServiceCall('select')) {
              return false;
          }
          return true;
      }
  
      protected function checkModSec() {
          $requirement = new Gpf_Install_Requirement();
          $requirement->setPositiveName($this->_('Server access configured properly'));
          $requirement->setNegativeName($this->_('Server access is probably not configured properly'));
          $requirement->setFixDescription($this->_('If you have Apache and mod_security module on it, it must be properly configured. If you notice some stability problems, please write to your hosting, that they turn off this module for location were PAP is installed. If you do not have Apache, then your server is probably not able to make requests to its self.'));
          $requirement->setResult($this->checkModSecCalls());
          $this->addRequirement($requirement);
      }
  
      protected function checkCompatibilityMode() {
          $requirement = new Gpf_Install_Requirement();
          $compatibilityMode = ini_get("zend.ze1_compatibility_mode");
          $requirement->setResult($compatibilityMode != 1 && $compatibilityMode != 'On');
          $requirement->setPositiveName($this->_('Compatibility mode is off'));
          $requirement->setNegativeName($this->_('Application requires compatibility mode off'));
          $requirement->setFixDescription($this->_('Please turn compatibility mode off in your php.ini'));
          $this->addRequirement($requirement);
      }
  
      protected function checkDisabledFunctions() {
          $requiredFunctions = array('tempnam', 'mkdir', 'imagettftext', 'imagejpeg');
          $missingFunctions = array();
          foreach ($requiredFunctions as $function) {
              if (!Gpf_Php::isFunctionEnabled($function)) {
                  $missingFunctions[] = $function;
              }
          }
          $requirement = new Gpf_Install_Requirement();
          $requirement->setResult(count($missingFunctions) == 0);
          $requirement->setPositiveName($this->_('All required functions are enabled'));
          $requirement->setNegativeName($this->_('Following requires functions are not enabled or available: %s', implode(', ', $missingFunctions)));
          $requirement->setFixDescription($this->_('Please enable following functions in your php.ini: %s', implode(', ', $missingFunctions)));
          $this->addRequirement($requirement);
      }
  
      /**
       * Check memory limit of php
       *
       */
      protected function checkMemoryLimit() {
          $requirement = new Gpf_Install_Requirement();
          if (self::getMemoryLimit() < 33554432) {
              @ini_set('memory_limit', '32M');
          }
          $requirement->setResult(self::getMemoryLimit() >= 33554432);
          $requirement->setPositiveName($this->_('Memory limit is %s bytes', self::getMemoryLimit()));
          $requirement->setNegativeName($this->_('Please increase memory_limit parameter to 32M in your php.ini'));
          $requirement->setFixDescription($this->_('Application require minimum 32MB of memory'));
          $this->addRequirement($requirement);
      }
  
      /**
       * Compute current memory limit of php
       *
       * @return int
       */
      public static function getMemoryLimit() {
          $memoryLimit = ini_get('memory_limit');
  
          if (!strlen(trim($memoryLimit)) || $memoryLimit <= 0) {
              $memoryLimit = '10g';
          }
          $last = strtolower($memoryLimit{strlen($memoryLimit)-1});
          switch($last) {
              case 'g':
                  $memoryLimit *= 1024;
              case 'm':
                  $memoryLimit *= 1024;
              case 'k':
                  $memoryLimit *= 1024;
          }
          return $memoryLimit;
      }
  
      protected function checkStandardPHPLibrary() {
          $requirement = new Gpf_Install_Requirement();
          $requirement->setResult(Gpf_Php::isExtensionLoaded('SPL'));
          $requirement->setPositiveName($this->_('Standard PHP Library is on'));
          $requirement->setNegativeName($this->_('Application requires Standard PHP Library extension'));
          $requirement->setFixDescription($this->_('Please recompile your PHP with Standard PHP Library extension'));
          $this->addRequirement($requirement);
      }
  
      protected function checkSessionAutostart() {
          $requirement = new Gpf_Install_Requirement();
          $requirement->setResult(ini_get('session.auto_start') == 0 || ini_get('session.auto_start') == false);
          $requirement->setPositiveName($this->_('Session autostart is off'));
          $requirement->setNegativeName($this->_('Application requires session.auto_start parameter off'));
          $requirement->setFixDescription($this->_('Please turn session.auto_start parameter off in your php.ini'));
          $this->addRequirement($requirement);
      }
  
      protected function checkSessionSavePath() {
          $requirement = new Gpf_Install_Requirement();
          $requirement->setResult(substr(ini_get('session.save_path'),0,2) != './');
          $requirement->setPositiveName($this->_('Session path is setup correctly'));
          $requirement->setNegativeName($this->_('Session path can not be set to script start path for this application'));
          $requirement->setFixDescription($this->_('Session path is setup incorrectly for this application. Please set session.save_path parameter to for example /tmp (unite to all scripts) in your php.ini'));
          $this->addRequirement($requirement);
      }
  
      private function deleteIfExistsTestFilesAndDir($accountDirectory) {
          if(@file_exists($accountDirectory . 'check/subcheck')) {
              if(@is_file($accountDirectory . 'check/subcheck')) {
                  @unlink($accountDirectory . 'check/subcheck');
              }
              if(@is_dir($accountDirectory . 'check/subcheck')) {
                  @rmdir($accountDirectory . 'check/subcheck');
              }
          }
          if(@file_exists($accountDirectory.'check')) {
              if(@is_file($accountDirectory.'check')) {
                  @unlink($accountDirectory.'check');
              }
              if(@is_dir($accountDirectory.'check')) {
                  @rmdir($accountDirectory.'check');
              }
          }
      }
  
      private function checkAccountsWritable() {
          $requirement = new Gpf_Install_Requirement();
          $requirement->setPositiveName($this->_('Configuration directory is writable'));
          $requirement->setNegativeName($this->_('Configuration directory has to be writable'));
  
          $accountDirectory = Gpf_Paths::getInstance()->getAccountsPath();
          $result = (@is_dir($accountDirectory) && is_writable($accountDirectory));
  
          if($result) {
              $this->deleteIfExistsTestFilesAndDir($accountDirectory);
              $testFile = new Gpf_Io_File($accountDirectory . 'check');
              $subTestFile = new Gpf_Io_File($accountDirectory . 'check/subcheck');
              try {
                  $testFile->open('w');
                  $testFile->close();
                  $testFile->delete();
              } catch (Exception $e) {
                  $result = false;
                  $requirement->setNegativeName($this->_('Could not create file inside %s directory', $accountDirectory));
              }
              try {
                  $testFile->mkdir();
                  $testFile->rmdir();
              } catch (Exception $e) {
                  $result = false;
                  $requirement->setNegativeName($this->_('Could not create directory inside %s directory', $accountDirectory));
              }
              try {
                  $testFile->mkdir();
                  $subTestFile->open('w');
                  $subTestFile->close();
                  $subTestFile->delete();
                  $subTestFile->mkdir();
                  $subTestFile->rmdir();
                  $testFile->rmdir();
              } catch (Exception $e) {
                  $result = false;
                  $requirement->setNegativeName($this->_('Could not create file or directory inside %s subdirectory. Probably safe mode is not properly configured.', $accountDirectory));
              }
          }
  
          $requirement->setResult($result);
          $description = $this->_('Please make directory %s and all subdirectories writable by webserver.', $accountDirectory);
  
          if(stripos(PHP_OS, 'win') === false) {
              $description .= $this->_('On unix-like systems you can type "chmod -R 777 %s".', $accountDirectory);
          }
  
          $description .= $this->_('On any system you can set write permissions using your favourite FTP client.');
          $requirement->setFixDescription($description);
          $this->addRequirement($requirement);
      }
  
      private function checkPhpIncludePath() {
          try {
              Gpf_Paths::getInstance()->setIncludePath();
              return;
          } catch (Exception $e) {
          }
  
          $requirement = new Gpf_Install_Requirement();
          $requirement->setResult(false);
          $requirement->setPositiveName($this->_('PHP include path'));
          $requirement->setNegativeName($this->_('Could not set PHP include path'));
  
          $description = $this->_('Please configure your PHP so that script is able to change include_path.');
          $description .= $this->_('Alternatively you can set include_path directly in your php.ini. include_path=%s', Gpf_Paths::getInstance()->getIncludePath());
          $requirement->setFixDescription($description);
          $this->addRequirement($requirement);
      }
  
      private function checkMysql() {
          $mysqlSupport = Gpf_Php::isFunctionEnabled('mysql_connect');
  
          $requirement = new Gpf_Install_Requirement();
          $requirement->setResult($mysqlSupport);
          $requirement->setPositiveName($this->_('MySQL extension is installed'));
          $requirement->setNegativeName($this->_('MySQL extension is not installed'));
          $requirement->setFixDescription($this->_('Please enable MySQL extension. More info http://php.net/mysql'));
          $this->addRequirement($requirement);
          if(!$mysqlSupport) {
              return;
          }
  
          $mysqlVersion = $this->getMysqlVersion();
          if($mysqlVersion === false) {
              return;
          }
          $mysqlVersionTest = (version_compare($mysqlVersion, self::MYSQL_MIN_VERSION) >= 0);
  
          $requirement = new Gpf_Install_Requirement();
          $requirement->setResult($mysqlVersionTest);
          $requirement->setPositiveName($this->_('MySQL version is %s or higher', self::MYSQL_MIN_VERSION));
          $requirement->setNegativeName($this->_('MySQL version is less then %s', self::MYSQL_MIN_VERSION));
          $requirement->setFixDescription($this->_('Please install MySQL version %s or higher. Your current version is %s. More info http://myqsl.net/',
          self::MYSQL_MIN_VERSION, $mysqlVersion));
          $this->addRequirement($requirement);
      }
  
      private function parseVersion($text) {
          $value = stristr($text, 'Client API version');
  
          if(1 == preg_match('/[1-9].[0-9].[1-9][0-9]/', $value, $match)) {
              return $match[0];
          }
          return false;
      }
  
      protected function getMysqlVersion() {
          if(self::$info === null) {
              //first we try to get info through special file because phpinfo with ob_start may cause problems/internal server errors on some servers
              self::$info = @file_get_contents(Gpf_Paths::getInstance()->getFullBaseServerUrl() . Gpf_Paths::SCRIPTS_DIR . 'modulesinfo.php');
              $version = $this->parseVersion(self::$info);
              if ($version !== false) {
                  return $version;
              }
              ob_start();
              phpinfo(INFO_MODULES);
              self::$info = ob_get_contents();
              ob_end_clean();
          }
          return $this->parseVersion(self::$info);
      }
  
      protected function addRequirement(Gpf_Install_Requirement $requirement) {
          $this->requirements[] = $requirement;
      }
  
      public function getRequirements() {
          $this->check();
          return $this->requirements;
      }
  
      public function isValid() {
          $this->check();
          foreach ($this->requirements as $requirement) {
              if(!$requirement->isValid()) {
                  return false;
              }
          }
          return true;
      }
  }
  
  class Gpf_Install_Requirement extends Gpf_Object {
      private $result = false;
      private $positiveName = '';
      private $negativeName = '';
  
      private $fixDescription = '';
  
      public function setResult($result) {
          $this->result = $result;
      }
  
      public function setFixDescription($description) {
          $this->fixDescription = $description;
      }
  
      public function getFixDescription() {
          return $this->fixDescription;
      }
  
      public function setPositiveName($name) {
          $this->positiveName = $name;
      }
  
      public function getName() {
          if($this->result) {
              return $this->positiveName;
          }
          return $this->negativeName;
      }
  
      public function setNegativeName($name) {
          $this->negativeName = $name;
      }
  
      public function isValid() {
          return $this->result;
      }
  }
  

} //end Gpf_Install_Requirements

if (!class_exists('Pap_Install_Requirements', false)) {
  class Pap_Install_Requirements extends Gpf_Install_Requirements {
  }
  

} //end Pap_Install_Requirements

if (!class_exists('Gpf_Lang', false)) {
  class Gpf_Lang {
      
      /**
       * Translate input message into selected language.
       * If translation will not be found, return same message.
       *
       * @param string $message
       * @return string
       */
      public static function _($message, $args = null, $langCode = '') {
          if (!is_array($args)) {
              $args = func_get_args();
          }
          $dictionary = Gpf_Lang_Dictionary::getInstance($langCode);
          return self::_replaceArgs($dictionary->get($message), $args);
      }
      
      /**
       * Replace arguments in message.
       *
       * @param string $message
       * @param $args
       * @return string
       */
      public static function _replaceArgs($message, $args = null) {
          if (!is_array($args)) {
              $args = func_get_args();
          }
          //problem ak sa v message nachadza viac samostatnych percent '%' ako je count($args) a sucasne count($args) > 1 co plati vzdy pri Gpf_Lang::_localizeRuntime("##a%a%a%##");
          //Warning: vsprintf() [function.vsprintf]: Too few arguments in D:\wamp\www\GwtPhpFramework\trunk\server\include\Gpf\Lang.class.php on line 51
          if(count($args) > 1 && substr_count($message, '%s') < count($args)) {
              array_shift($args);
              return vsprintf($message, $args);
          }
          return $message;
      }
      
      /**
       * Translate input message into default language defined in language settings for account.
       * This function should be used in case message should be translated to default language (e.g. log messages written to event log)
       *
       * @param string $message
       * @return string
       */
      public static function _sys($message, $args = null) {
          if (!is_array($args)) {
              $args = func_get_args();
          }
          $dictionary = Gpf_Lang_Dictionary::getInstance(Gpf_Lang_Dictionary::getDefaultSystemLanguage());
          return self::_replaceArgs($dictionary->get($message), $args);
      }
      
      /**
       * Encapsulate message as translated message with ## ##
       *
       * @param string $message
       * @return string
       */
      public static function _runtime($message) {
          return '##' . $message . '##';
      }
      
      /**
       * Translates text enclosed in ##any text##
       * This function is not parsed by language parser, because as input should be used e.g. texts loaded from database
       *
       * @param string $message String to translate
       * @return string Translated text
       */
      public static function _localizeRuntime($message, $langCode = '') {
          preg_match_all('/##(.+?)##/ms', $message, $attributes, PREG_OFFSET_CAPTURE);
          foreach ($attributes[1] as $index => $attribute) {
              $message = str_replace($attributes[0][$index][0], self::_($attribute[0], null, $langCode), $message);
          }
          return $message;
          
      }
  }
  

} //end Gpf_Lang

if (!class_exists('Gpf_Lang_Dictionary', false)) {
  class Gpf_Lang_Dictionary extends Gpf_Object {
      const LANGUAGE_DIRECTORY = 'lang/';
      const LANGUAGE_REQUEST_PARAMETER = 'l';
  
      /**
       * Array of language dictonary instances. For each language code can be here own instance.
       *
       * @var array
       */
      protected static $instances = array();
  
      /**
       * @var Gpf_Lang_Language
       */
      private $language;
  
      protected function __construct() {
      }
  
      /**
       * @param string $langCode language code for which you need instance
       * @return Gpf_Lang_Dictionary
       */
      public static function getInstance($langCode = '') {
          if(!array_key_exists($langCode, self::$instances)) {
              self::$instances[$langCode] = new Gpf_Lang_Dictionary();
              if ($langCode != '') {
                  try {
                      self::$instances[$langCode]->load($langCode);
                  } catch (Exception $e) {
                  }
              }
              setlocale(LC_ALL, 'en_US.UTF-8');
          }
          return self::$instances[$langCode];
      }
  
      /**
       * Compute default language in following order:
       * 1. try if language parameter is not set in request
       * 2. try if cookie doesn't contain language selection from the past
       * 3. try load language settings from browser preferences
       * 4. load default system language
       *
       * @return string Default language code
       */
      public static function getDefaultLanguage() {
          //try if language was not defined by language parameter in request
          if (isset($_REQUEST[self::LANGUAGE_REQUEST_PARAMETER]) && self::isLanguageSupported($_REQUEST[self::LANGUAGE_REQUEST_PARAMETER]) ) {
              return $_REQUEST[self::LANGUAGE_REQUEST_PARAMETER];
          }
  
          //try if language was not defined in cookie parameter
          if (isset($_COOKIE[Gpf_Auth_Service::COOKIE_LANGUAGE]) &&
          self::isLanguageSupported($_COOKIE[Gpf_Auth_Service::COOKIE_LANGUAGE])) {
              return $_COOKIE[Gpf_Auth_Service::COOKIE_LANGUAGE];
          }
  
          //try load language from browser
          if (($acceptLang = Gpf_Lang_Dictionary::getBrowserLanguage()) !== false) {
              return $acceptLang;
          }
  
          //use default system language
          return self::getDefaultSystemLanguage();
      }
  
      public static function getDefaultSystemLanguage() {
          try {
              $defaultLanguage = Gpf_Db_Table_Languages::getInstance()->getDefaultLanguage();
              $langCode = $defaultLanguage->getCode();
              if ($langCode != null) {
                  return $langCode;
              }
          } catch (Exception $e) {
          }
          return Gpf_Application::getInstance()->getDefaultLanguage();
      }
  
      public static function isLanguageSupported($langCode) {
          static $languages;
          if ($languages == null) {
              try {
                  $languagesObj = Gpf_Lang_Languages::getInstance();
                  $languages = $languagesObj->getActiveLanguagesNoRpc();
              } catch (Exception $e) {
                  return false;
              }
          }
          return $languages->existsRecord($langCode);
      }
  
      /**
       * Get first supported language browser
       *
       * @return string If none supported language was found, return false
       */
      private static function getBrowserLanguage() {
          if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
              $languages = explode(',', str_replace(' ', '', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
              foreach($languages as $language) {
                  $arrLang = explode(';', $language);
                  $langCode = self::decodeLanguageCode($arrLang[0]);
                  if (self::isLanguageSupported($langCode)) {
                      return $langCode;
                  }
              }
          }
          return false;
      }
  
      /**
       * @param String $browserLangCode
       * @return String
       */
      public static function decodeLanguageCode($browserLangCode) {
          $langCode = strtolower($browserLangCode);
          if (strlen($browserLangCode) > 2) {
              $langCode = substr($langCode, 0, 2) . strtoupper(substr($browserLangCode, 2));
          }
          return $langCode;
      }
  
      protected function isSupportedLanguage($languageCode) {
          return self::isLanguageSupported($languageCode);
      }
  
      public function load($languageCode) {
          if (!$this->isSupportedLanguage($languageCode)) {
              $languageCode = self::getDefaultLanguage();
          }
          $language = new Gpf_Lang_Language($languageCode);
          $language->load();
          $this->language = $language;
          self::$instances[$languageCode] = $this;
          return $languageCode;
      }
  
      public function getEncodedClientMessages() {
          if ($this->getLanguage() != null) {
              $langCode = $this->getLanguage()->getCode();
          } else {
              $langCode = $this->getDefaultSystemLanguage();
          }
          $file = new Gpf_Io_File(Gpf_Paths::getInstance()->getLanguageCacheDirectory()
          . Gpf_Application::getInstance()->getCode() . '_' .
          $langCode . '.c.php');
          return $file->getContents();
      }
  
      /**
       *
       * @return Gpf_Data_RecordSet
       */
      public function getClientMessages() {
          if($this->language === null) {
              $this->load(Gpf_Session::getAuthUser()->getLanguage());
          }
          $recordSet = new Gpf_Data_RecordSet();
          $recordSet->setHeader(array('source', 'translation'));
  
          foreach ($this->language->getClientMessages() as $source => $translation) {
              $recordSet->add(array($source, $translation));
          }
          return $recordSet;
      }
  
      public function get($message) {
          if($this->language === null) {
              return $message;
          }
          return $this->language->localize($message);
      }
  
      /**
       * return language definition
       *
       * @return Gpf_Lang_Language
       */
      public function getLanguage() {
          return $this->language;
      }
  }

} //end Gpf_Lang_Dictionary

if (!class_exists('Gpf_Php', false)) {
  class Gpf_Php {
  
      /**
       * Check if function is enabled and exists in php
       *
       * @param $functionName
       * @return boolean Returns true if function exists and is enabled
       */
      public static function isFunctionEnabled($functionName) {
          if (function_exists($functionName) && strstr(ini_get("disable_functions"), $functionName) === false) {
              return true;
          }
          return false;
      }
      
      /**
       * Check if extension is loaded
       * 
       * @param $extensionName
       * @return boolean Returns true if extension is loaded
       */
      public static function isExtensionLoaded($extensionName) {
          return extension_loaded($extensionName);
      }
  
  }

} //end Gpf_Php

if (!class_exists('Gpf_Application', false)) {
  abstract class Gpf_Application extends Gpf_Object {
      protected $installedVersion;
      private $gpfInstalledVersion;
  
      protected $rolePrivileges = array();
  
      /**
       * @var Gpf_Application
       */
      private static $instance;
  
      public static function create(Gpf_Application $application) {
          setlocale(LC_ALL, 'en.UTF-8');
          self::$instance = $application;
          self::$instance->registerRolePrivileges();
          self::$instance->initLogger();
          self::$instance->addSmartyPluginsDir();
          $timezone = Gpf_Settings_Gpf::DEFAULT_TIMEZONE;
          try {
              $timezone = Gpf_Settings::get(Gpf_Settings_Gpf::TIMEZONE_NAME);
          } catch (Gpf_Exception $e) {
              Gpf_Log::error('Unable to load timezone: %s - using default one.', $e->getMessage());
          }
          if(false === @date_default_timezone_set($timezone)) {
              Gpf_Log::error('Unable to set timezone %s:', $timezone);
          }
      }
  
      public function getDefaultLanguage() {
          return 'en-US';
      }
  
      /**
       * @return Gpf_Application
       */
      public static function getInstance() {
          if(self::$instance === null) {
              throw new Gpf_Exception('Application not initialize');
          }
          return self::$instance;
      }
  
      /**
       * @return String
       */
      public function getApiFileName() {
          throw new Gpf_Exception('Api is not supported');
      }
  
      public function createSettings() {
          return new Gpf_Settings_Gpf();
      }
  
      protected function addSmartyPluginsDir() {
          Gpf_Paths::getInstance()->addSmartyPluginPath(Gpf_Paths::getInstance()->getFrameworkPath() . 'include/Gpf/SmartyPlugins');
      }
  
      public function getInstalledVersion($gpf = false) {
          if($this->installedVersion === null) {
              $this->computeInstalledVersions();
          }
          if($gpf) {
              return $this->gpfInstalledVersion;
          }
          return $this->installedVersion;
      }
  
      public function getHelpUrl() {
          return '';
      }
  
      public static function getKnowledgeHelpUrl($path) {
          return self::getInstance()->getHelpUrl() . $path;
      }
  
      public function getAccountId() {
          return Gpf_Db_Account::DEFAULT_ACCOUNT_ID;
      }
  
      private function computeInstalledVersions() {
          $this->installedVersion = false;
          $this->gpfInstalledVersion = false;
          try {
              $this->installedVersion = $this->computeLatestInstalledApplicationVersion();
              $this->gpfInstalledVersion = Gpf_Db_Table_Versions::getInstance()->getLatestVersion(Gpf::CODE);
          } catch (Gpf_DbEngine_Exception $e) {
          	throw new Gpf_DbEngine_Exception($e->getMessage());
          } catch (Gpf_Exception $e) {
          	Gpf_Log::debug('Error during computing latest versions: ' . $e->getMessage());
          }
      }
  
      protected function computeLatestInstalledApplicationVersion() {
          return $this->installedVersion = Gpf_Db_Table_Versions::getInstance()->getLatestVersion($this->getCode());
      }
  
      public function isInstalled() {
          return $this->getInstalledVersion() !== false;
      }
  
      private static function getVersionWithoutBuild($version) {
          $parts = explode('.', $version);
          if(count($parts) <=3 ) {
              return $version;
          }
          return implode('.', array($parts[0], $parts[1], $parts[2]));
      }
  
      private function equalsVersions($version1, $version2) {
          if(Gpf_Paths::getInstance()->isDevelopementVersion()) {
              return self::getVersionWithoutBuild($version1) == self::getVersionWithoutBuild($version2);
          }
          return $version1 == $version2;
      }
  
      public function isUpdated() {
          return $this->equalsVersions($this->getVersion(),$this->getInstalledVersion())
          && $this->equalsVersions(Gpf::GPF_VERSION, $this->getInstalledVersion(true));
      }
  
      final public function isInMaintenanceMode() {
          try {
            return !$this->isInstalled() || !$this->isUpdated();
          } catch (Gpf_DbEngine_Exception $e) {
              Gpf_log::debug('Database error occured while computing latest installed application version: ' . $e->getMessage());
              return false;
          }
      }
  
      protected function readStatFile($file) {
          if (!file_exists($file) || !is_readable($file)) {
              throw new Gpf_Exception('Failed to read file ' . $file);
          }
          return @file_get_contents($file);
      }
  
      protected function getCpuCount() {
          $cpuinfo = $this->readStatFile('/proc/cpuinfo');
          preg_match_all('/processor\s*?:\s([0-9]*)/ms' ,$cpuinfo ,$matches);
          if (is_array($matches) && array_key_exists(1, $matches) && is_array($matches[1]) && count($matches[1]) > 0) {
              $maxCpuNr = $matches[1][count($matches[1]) - 1];
              if (strlen($maxCpuNr)) {
                  return $maxCpuNr + 1;
              }
          }
          throw new Gpf_Exception('Failed to read cpuinfo');
      }
  
      protected function getMaxLoad() {
          return max($this->getCpuCount()/2, Gpf_Settings::get(Gpf_Settings_Gpf::MAX_ALLOWED_SERVER_LOAD));
      }
  
      public function isServerOverloaded() {
          try {
              return max($this->getServerLoad(1), $this->getServerLoad(5)) > $this->getMaxLoad();
          } catch (Exception $e) {
              return false;
          }
      }
  
      protected function getServerLoad($time = 1) {
          $loads = preg_split("/ /",$this->readStatFile('/proc/loadavg'));
          $load = false;
          switch ($time) {
              case 1:
                  $load =  $loads[0];
                  break;
              case 5:
                  $load =  $loads[1];
                  break;
              case 10:
                  $load =  $loads[2];
                  break;
              default:
                  $load =  $loads[0];
          }
          if (is_numeric($load)) {
              return $load;
          }
          throw new Gpf_Exception('Failed to read server load');
      }
  
      abstract public function getVersion();
      abstract public function getCode();
  
      /**
       * Each application should define set of default roles and privileges classes
       * use function addRolePrivileges to register role
       */
      abstract public function registerRolePrivileges();
  
      protected function initLogger() {
      }
  
      /**
       * Add role and privilege class name to current application
       *
       * @param string $roleid
       * @param string $privilegesClassName
       */
      public function addRolePrivileges($roleid, $privilegesClassName) {
          $this->rolePrivileges[$roleid] = $privilegesClassName;
      }
  
      public function getRoleDefaultPrivileges($roleId) {
          if (!array_key_exists($roleId, $this->rolePrivileges)) {
              throw new Gpf_Exception("Privileges not registered for role $roleId. Please register in class " . get_class($this) . " privileges in method registerRolePrivileges by calling method addRolePrivileges");
          }
  
          $className = $this->rolePrivileges[$roleId];
          $objPrivileges = new $className;
          return $objPrivileges->getDefaultPrivileges();
      }
  
      /**
       * Return default privileges by role type
       *
       * @param string $roleType
       * @return Gpf_Privileges
       */
      public function getDefaultPrivilegesByRoleType($roleType) {
          foreach ($this->rolePrivileges as $roleid => $className) {
              $objRole = new Gpf_Db_Role();
              $objRole->setId($roleid);
              $objRole->load();
              if ($objRole->getRoleType() == $roleType) {
                  return new $className;
              }
          }
          return false;
      }
  
  
      public function getName() {
          return $this->_('Application Name');
      }
  
      abstract public function getAuthClass();
  
      /**
       * @return Gpf_Db_Account
       */
      abstract public function createAccount();
  
      /**
       * @return Gpf_Plugins_Definition
       */
      public function getApplicationPluginsDefinition() {
          return array(new Gpf_Definition());
      }
  
      public function getFeaturePathsDefinition() {
          return array();
      }
  
      public function initDatabase() {
      }
  
      protected function importPrivileges($roleId, $privilegeList) {
          foreach ($privilegeList as $object => $privileges) {
              foreach ($privileges as $privilege) {
                  $rolePrivilege = new Gpf_Db_RolePrivilege();
                  $rolePrivilege->setRoleId($roleId);
                  $rolePrivilege->setObject($object);
                  $rolePrivilege->setPrivilege($privilege);
                  $rolePrivilege->insert();
              }
          }
  
      }
  
      public static function isDemo() {
          return Gpf::YES == Gpf_Settings::get(Gpf_Settings_Gpf::DEMO_MODE);
      }
  
      public static function isDemoEntryId($id) {
          return substr($id, 0, 4) == "1111";
      }
  }

} //end Gpf_Application

if (!class_exists('Pap_Application', false)) {
  class Pap_Application extends Gpf_Application {
      const ROLETYPE_MERCHANT = 'M';
      const ROLETYPE_AFFILIATE = 'A';
  
      const DEFAULT_ROLE_MERCHANT = 'pap_merc';
      const DEFAULT_ROLE_AFFILIATE = 'pap_aff';
  
      public function getAuthClass() {
          return 'Pap_AuthUser';
      }
  
      public function getDefaultLanguage() {
          return Pap_Branding::DEFAULT_LANGUAGE_CODE;
      }
  
  
      public function initDatabase() {
          $role = new Gpf_Db_Role();
          $role->setId(self::DEFAULT_ROLE_MERCHANT);
          $role->setName('Merchant');
          $role->setRoleType(self::ROLETYPE_MERCHANT);
          $role->insert();
  
          $role = new Gpf_Db_Role();
          $role->setId(self::DEFAULT_ROLE_AFFILIATE);
          $role->setName('Affiliate');
          $role->setRoleType(self::ROLETYPE_AFFILIATE);
          $role->insert();
      }
  
      public function registerRolePrivileges() {
          $this->addRolePrivileges(self::DEFAULT_ROLE_MERCHANT, 'Pap_Privileges_Merchant');
          $this->addRolePrivileges(self::DEFAULT_ROLE_AFFILIATE, 'Pap_Privileges_Affiliate');
          Gpf_Plugins_Engine::extensionPoint('PostAffiliate.Application.registerRolePrivileges', $this);
      }
  
      public function createSettings($onlyFile = false) {
          return new Pap_Settings($onlyFile);
      }
  
  	protected function addSmartyPluginsDir() {
  		parent::addSmartyPluginsDir();
      	Gpf_Paths::getInstance()->addSmartyPluginPath(Gpf_Paths::getInstance()->getTopPath() . 'include/Pap/SmartyPlugins');
      }
  
      public function getVersion() {
          return '4.5.86.3';
      }
  
      public function getHelpUrl() {
      	if ($this->isInstalled()) {
      		return Gpf_Settings::get(Pap_Settings::BRANDING_KNOWLEDGEBASE_LINK);
      	}
          return parent::getHelpUrl();
      }
  
      protected function computeLatestInstalledApplicationVersion() {
          return Gpf_Db_Table_Versions::getInstance()->getLatestVersion(array($this->getCode(), 'paplite'));
      }
  
      public function getCode() {
          return 'pap';
      }
  
      public function getName() {
          return Gpf_Settings::get(Pap_Settings::BRANDING_TEXT_POST_AFFILIATE_PRO);
      }
  
      /**
       * @return Pap_Account
       */
      public function createAccount() {
          return new Pap_Account();
      }
  
      public function getApiFileName() {
      	return 'PapApi.class.php';
      }
  
      /**
       * @return Gpf_Plugins_Definition
       */
      public function getApplicationPluginsDefinition() {
          $plugins = parent::getApplicationPluginsDefinition();
          $plugins[] = new Pap_Definition();
          return $plugins;
      }
  
      public function getFeaturePathsDefinition() {
          return array_merge(parent::getFeaturePathsDefinition(),
                             array(Gpf_Paths::getInstance()->getTopPath().'include/Pap/Features/'));
      }
  
      protected function initLogger() {
      	try {
          	Gpf_Log::addLogger(Gpf_Log_LoggerDatabase::TYPE, Pap_Logger::getLogLevel());
          } catch (Gpf_Exception $e) {
          }
      }
  }

} //end Pap_Application

if (!class_exists('Gpf_Plugins_Engine', false)) {
  class Gpf_Plugins_Engine extends Gpf_Object {
  
      const PROCESS_CONTINUE = 'C';
      const PROCESS_STOP_EXTENSION_POINT = 'S';
      const PROCESS_STOP_ALL = 'A';
      const PROCESS_STOP_EXIT = 'E';
  
      /**
       * @var Gpf_Plugins_Engine
       */
      protected static $instance = null;
  
      /**
       * @var Gpf_Plugins_EngineSettings
       */
      private $configuration;
      /**
       * @var array of Gpf_Plugins_Definition
       */
      protected $availablePlugins;
  
      /**
       * constructs plugin engine instance
       * It loads config data from plugins_config.php and initializes the responsible plugins
       *
       */
      protected function __construct() {
          if (Gpf_Paths::getInstance()->isMissingAccountDirectory()) {
              $this->configuration = $this->generateConfiguration();
              return;
          }
          $config = new Gpf_Plugins_EngineConfigFile();
          try {
              $this->configuration = $config->loadConfiguration();
              return;
          } catch (Exception $e) {
              Gpf_Log::info($this->_('Engine config is not exists: %s', $e->getMessage()));
          }        
          $config->createEmpty();
          $this->configuration = $this->generateConfiguration();
          try {
              $config = new Gpf_Plugins_EngineConfigFile();
              $config->saveConfiguration($this->configuration);
          } catch (Exception $e) {
              Gpf_Log::error($this->_('Unable to save engine config file! %s', $e->getMessage()));
              throw $e;
          }
      }
  
      /**
       * returns actual plugin engine configuration loaded from the file
       *
       * @return Gpf_Plugins_EngineSettings
       */
      public function getConfiguration() {
          return $this->configuration;
      }
  
      /**
       * returns instance of plugins Engine class
       *
       * @return Gpf_Plugins_Engine
       */
      public static function getInstance() {
          if (self::$instance == null) {
              self::$instance = new Gpf_Plugins_Engine();
          }
          return self::$instance;
      }
  
      /**
       * @throws Gpf_Exception
       * returns array of plugins objects for all available plugins
       *
       * @return array of Gpf_Plugins_Definition
       */
      public function getAvailablePlugins() {
          if($this->availablePlugins === null) {
              $this->availablePlugins = array();
              $this->computeApplicationPlugins();
              $this->computeAvailableFeaturePlugins();
              $this->computeAvailablePlugins();
              $this->checkPluginsUnique();
          }
          return $this->availablePlugins;
      }
  
      /**
       * @throws Gpf_Exception
       */
      protected function checkPluginsUnique() {
          $plugins = array();
          foreach ($this->availablePlugins as $plugin) {
              if (in_array($plugin->getCodeName(), $plugins)) {
                  throw new Gpf_Exception($this->_("Too many plugins with code name '%s'", $plugin->getCodeName()));
              }
              $plugins[] = $plugin->getCodeName();
          }
      }
  
      private function computeApplicationPlugins() {
          $this->availablePlugins = array_merge($this->availablePlugins, Gpf_Application::getInstance()->getApplicationPluginsDefinition());
      }
  
      private function computeAvailableFeaturePlugins() {
          if (defined('ENABLE_ENGINECONFIG_LOG')) {
              Gpf_Log::info('computeAvailableFeaturePlugins - path:' . print_r(Gpf_Application::getInstance()->getFeaturePathsDefinition(), true));
          }
          $this->addPluginsFromPath(Gpf_Application::getInstance()->getFeaturePathsDefinition());
      }
  
      private function computeAvailablePlugins() {
          $this->addPluginsFromPath(Gpf_Paths::getInstance()->getPluginsPaths());
      }
  
      private function addPluginsFromPath($pluginDirectoriesPaths) {
          foreach($pluginDirectoriesPaths as $pluginDirectoryPath) {
              $iterator = new Gpf_Io_DirectoryIterator($pluginDirectoryPath, '', false, true);
              foreach ($iterator as $fullPath => $pluginName) {
                  if (defined('ENABLE_ENGINECONFIG_LOG')) {
                      Gpf_Log::info('addPluginsFromPath - path:' . $pluginDirectoriesPaths . ', fullpath: ' . $fullPath . ', pluginName: ' . $pluginName);
                  }
                  try {
                      $this->availablePlugins[] = $this->createPlugin($fullPath);
                  } catch(Gpf_Exception $e) {
                      if (defined('ENABLE_ENGINECONFIG_LOG')) {
                          Gpf_Log::error('error during loading plkugin from directory: ' . $e->getMessage());
                      }
                  }
              }
          }
      }
  
      /**
       *
       * @param unknown_type $path
       * @return Gpf_Plugins_Definition
       */
      private function createPlugin($path) {
          $className = '';
          while (basename($path) != rtrim(Gpf_Paths::PLUGINS_DIR, '/') && basename($path) != 'include') {
              $className =  basename($path) . '_' . $className;
              $path = dirname($path);
          }
          $className .= 'Definition';
          if (Gpf::existsClass($className) === false) {
              throw new Gpf_Exception("Plugin definition class is missing in directory '$path'");
          }
          return new $className;
      }
  
  
      /**
       * Executes given extension point, which means it will run
       * all its registered handlers.
       *
       * @param string $extensionPointName
       * @param object $context
       */
      public static function extensionPoint($extensionPointName, $context = null) {
          $pluginsEngine = self::getInstance();
          try {
              $definition = $pluginsEngine->getDefinitionForExtensionPoint($extensionPointName);
              $extensionPoint = Gpf_Plugins_ExtensionPoint::getInstance($extensionPointName, $definition);
          } catch(Gpf_Exception $e) {
              Gpf_Log::warning("Extension point $extensionPointName not defined (" . $e->getMessage() . ")", "plugins");
              return;
          }
  
          $extensionPoint->processHandlers($context);
      }
  
      /**
       * reads definition of this extension point (context & handlers) from engine configuration
       *
       * @param string $extensionPointName
       * @return array
       */
      private function getDefinitionForExtensionPoint($extensionPointName) {
          if($this->configuration === null) {
              throw new Gpf_Plugins_Exception("Plugins engine is not configured!");
          }
  
          $extPoints = $this->configuration->getExtensionPoints();
           
          if(!is_array($extPoints) || count($extPoints) == 0) {
              throw new Gpf_Plugins_Exception("Plugins engine extension points are not configured!");
          }
           
          if(!isset($extPoints[$extensionPointName])) {
              throw new Gpf_Plugins_Exception("Extension point '$extensionPointName' is not defined");
          }
           
          return $extPoints[$extensionPointName];
      }
      /**
       * Function generates configuration for the given active plugins.
       * It also checks if the configuration is correct, if the plugins given
       * really exist, etc.
       * Throws exception on error
       *
       * @param array $activePluginsCodes
       * @return Gpf_Plugins_EngineSettings
       */
      private function generateConfiguration($activePluginsCodes = array()) {
          $allPlugins = $this->getAvailablePlugins();
  
          $activePluginsObjects = array();
  
          //add system plugins
          foreach($allPlugins as $plugin) {
              if ($plugin->isSystemPlugin()) {
                  $activePluginsObjects[] = $plugin;
              }
          }
  
          if (defined('ENABLE_ENGINECONFIG_LOG')) {
              Gpf_Log::info('generateConfiguration - activating: ' . print_r($activePluginsCodes, true));
          }
  
          //add other active plugins
          foreach($activePluginsCodes as $activePluginCode) {
              $activePlugin = $this->findPlugin($activePluginCode);
              if($activePlugin === null) {
                  if (defined('ENABLE_ENGINECONFIG_LOG')) {
                      Gpf_Log::info('plugin is null for code: ' . $activePluginCode);
                  }
                  continue;
              }
              if (!$activePlugin->isSystemPlugin()) {
                  $activePluginsObjects[] = $activePlugin;
              }
          }
          if (defined('ENABLE_ENGINECONFIG_LOG')) {
              Gpf_Log::info('generateConfiguration - active plugin objects: ' . print_r($activePluginsObjects, true));
          }
          $configuration = new Gpf_Plugins_EngineSettings();
          $configuration->init($activePluginsObjects);
          if (defined('ENABLE_ENGINECONFIG_LOG')) {
              Gpf_Log::info('generateConfiguration - serialised configuration: ' . print_r($configuration, true));
          }
          return $configuration;
      }
  
      /**
       * Find plugin by code name in array of plugins
       *
       * @param string $codeName
       * @return Gpf_Plugins_Definition
       */
      public function findPlugin($codeName) {
          if (defined('ENABLE_ENGINECONFIG_LOG')) {
              Gpf_Log::info('findPlugin - ' . print_r($this->getAvailablePlugins(), true));
          }
          foreach($this->getAvailablePlugins() as $plugin) {
              if($codeName == $plugin->getCodeName()) {
                  return $plugin;
              }
          }
          return null;
      }
  
      /**
       * Function will activate or deactivate given plugin
       *
       * @param string $code
       * @param boolean $activate - if to activate or deactivate
       * @return boolean true/false
       */
      public function activate($codeName, $activate) {
          $plugin = $this->findPlugin($codeName);
          if ($plugin === null) {
              throw new Gpf_Exception($this->_('Plugin %s not found', $codeName));
          }
          $this->activatePlugin($plugin, $activate);
          return true;
      }
  
      public function saveConfiguration(){
          $config = new Gpf_Plugins_EngineConfigFile();
          $config->saveConfiguration( $this->configuration);
      }
  
      public function refreshConfiguration() {
          $config = new Gpf_Plugins_EngineConfigFile();
          $config->saveConfiguration($this->generateConfiguration($this->configuration->getActivePlugins()));
      }
  
      /**
       *  Configuration is not saved
       */
      public function clearConfiguration() {
          $this->configuration = $this->generateConfiguration();
          Gpf_Plugins_ExtensionPoint::clear();
      }
  
      /**
       * @param Gpf_Plugins_Definition $plugin
       * @param boolean $activate
       */
      protected function activatePlugin(Gpf_Plugins_Definition $plugin, $activate) {
          if($activate) {
              $plugin->check();
              $plugin->onActivate();
              if (defined('ENABLE_ENGINECONFIG_LOG')) {
                  Gpf_Log::info('Gpf_Plugins_Engine/activatePlugin: ' . $plugin->getName() . ' - activating');
              }
              // add to active plugins array
              $activePluginsCodes = $this->configuration->getActivePlugins();
              if(!in_array($plugin->getCodeName(), $activePluginsCodes)) {
                  $activePluginsCodes[$plugin->getCodeName()] = $plugin->getCodeName();
              }
          } else {
              $plugin->onDeactivate();
              // remove from active plugins array
              if (defined('ENABLE_ENGINECONFIG_LOG')) {
                  Gpf_Log::info('Gpf_Plugins_Engine/activatePlugin: ' . $plugin->getName() . ' - deactivating');
              }
              $activePluginsCodes = $this->configuration->getActivePlugins();
              if(array_key_exists($plugin->getCodeName(), $activePluginsCodes)) {
                  unset($activePluginsCodes[$plugin->getCodeName()]);
              }
          }
          $this->configuration = $this->generateConfiguration($activePluginsCodes);
      }
  }
  

} //end Gpf_Plugins_Engine

if (!class_exists('Gpf_File_Config', false)) {
  class Gpf_File_Config {
      protected $settingsFile;
      private $parameters = array();
      private $initialized = false;
  
      public function __construct($settingsFile) {
          $this->settingsFile = $settingsFile;
      }
  
      /**
       * @return array
       */
      public function getAll(Gpf_Io_File $file = null) {
          if(!$this->initialized) {
              $this->parameters = $this->readSettingsValues($file);
              $this->initialized = true;
          }
          return $this->parameters;
      }
  
      public function saveAll() {
          $this->writeSettingsValues();
      }
  
      public function hasSetting($name) {
          $this->getAll();
          return array_key_exists($name, $this->parameters);
      }
  
      public function forceReload($value = false) {
          $this->initialized = $value;
      }
  
      public function getSetting($name, Gpf_Io_File $file = null) {
          $this->getAll($file);
          if(array_key_exists($name, $this->parameters)) {
              return $this->parameters[$name];
          }
  
          throw new Gpf_Settings_UnknownSettingException($name);
      }
  
      public function getSettingWithDefaultValue($name, $defaultValue) {
          // obsolete
          // to be deleted
          $this->getAll();
          if(array_key_exists($name, $this->parameters)) {
              return $this->parameters[$name];
          }
  
          return $defaultValue;
      }
  
      public function setSetting($name, $value, $flush = true, Gpf_Io_File $file = null) {
          $this->getAll($file);
          if(array_key_exists($name, $this->parameters) && $this->parameters[$name] == $value) {
              return;
          }
          $this->parameters[$name] = $value;
          if($flush) {
              $this->writeSettingsValues($file);
          }
      }
  
      public function getSettingFileName() {
          return $this->settingsFile;
      }
  
      public function isExists() {
          $file = new Gpf_Io_File($this->settingsFile);        
          return $file->isExists();
      }
  
      public function removeSetting($settingName, $flush = true) {
          if (!$this->hasSetting($settingName)) {
              return;
          }
          unset($this->parameters[$settingName]);
          if ($flush) {
              $this->writeSettingsValues();
          }
      }
  
      protected function isFileContentOk($loadedArray) {
          return true;
      }
  
      private function readSettingsValues(Gpf_Io_File $file = null) {
          if (is_null($file)) {
              $file = new Gpf_Io_File($this->settingsFile);
          }
          if(!$file->isExists()) {
              return array();
          }
          $file->open();
  
          $values = array();
          $lines = $this->readFileAsArray($file);
  
          foreach($lines as $line) {
              if(false !== strpos($line, '<?') || false !== strpos($line, '?>')) {
                  continue;
              }
              $pos = strpos($line, '=');
              if($pos === false) {
                  continue;
              }
              $name = substr($line, 0, $pos);
              $value = substr($line, $pos + 1);
              $values[$name] = rtrim($value);
          }
          return $values;
      }
  
      private function readFileAsArray(Gpf_Io_File $file) {
          for ($i = 1; $i <= 5; $i++) {
              $lines = $file->readAsArray();
              if ($this->isFileContentOk($lines)) {
                  return $lines;
              }
              usleep(round(rand(0, 100)*1000));
          }
  
          throw new Gpf_Exception('Could not read settings file: ' . ' ' . $this->settingsFile);
      }
  
      protected function isSettingsFileOk(Gpf_Io_File $file) {
          try {
              return ($file->getSize() > 0) || ($this->getFileDataLength($file) > 0);
          } catch (Exception $e) {
              return false;
          }
      }
  
      private function getFileDataLength(Gpf_Io_File $file) {
          $data = file_get_contents($file->getFileName());
          return strlen($data);
      }
  
      private function writeSettingsValues(Gpf_Io_File $settingsFile = null) {
          $settingsTmpFile = new Gpf_Io_File($this->settingsFile . '_' . microtime() .'.tmp');
  
          $this->writeSettingToFile($settingsTmpFile);
  
          if ($this->isSettingsFileOk($settingsTmpFile)) {
              try {
                  if (is_null($settingsFile)) {
                      $settingsFile = new Gpf_Io_File($this->settingsFile);
                  }
                  $this->copyFile($settingsTmpFile, $settingsFile, 0777);
                  $settingsTmpFile->delete();
              } catch (Exception $e) {
                  try {
                      $this->writeSettingToFile($settingsFile);
  
                      if ($this->isSettingsFileOk($settingsFile)) {
                          $settingsTmpFile->delete();
                      } else {
                          throw new Gpf_Exception('Unable to save settings file! (Temp file is OK: '. $settingsTmpFile->getFileName().')');
                      }
                  } catch (Exception $e) {
                      throw $e;
                  }
              }
          } else {
              $settingsTmpFile->delete();
              throw new Gpf_Exception('Unable to save settings file! ' . date('Y-m-d H:i:s', time()));
          }
      }
  
      private function writeSettingToFile(Gpf_Io_File $file) {
          $file->setFilePermissions(0777);
  
          if (defined('ENABLE_ENGINECONFIG_LOG')) {
              Gpf_Log::info('(writeSettingsValues - before write) file ' . @$file->getFileName() . ' size: ' . @$file->getSize() . ', permissions: ' . @$file->getFilePermissions() . ', owner: ' . @$file->getFileOwner());
          }
          $file->open('w');
  
          $text = '<?php /*' . "\n";
          foreach($this->parameters as $key => $value) {
              $text .= $key . '=' . $value . "\r\n";
          }
          $text .= '*/ ?>';
          $file->write($text);
          $file->close();
  
          if (defined('ENABLE_ENGINECONFIG_LOG')) {
              Gpf_Log::info('(writeSettingsValues - after write) file ' . @$file->getFileName() . ' size: ' . @$file->getSize() . ', permissions: ' . @$file->getFilePermissions() . ', owner: ' . @$file->getFileOwner());
          }
      }
  
      protected function copyFile(Gpf_Io_File $source, Gpf_Io_File $target, $mode = null) {
          $target->open('w');
          $target->write($source->getContents());
          if($mode !== null) {
              @chmod($target->getFileName(), $mode);
          }
      }
  
      public function setSettingsFile($path) {
          $this->settingsFile = $path;
      }
  }
  

} //end Gpf_File_Config

if (!class_exists('Gpf_Plugins_EngineConfigFile', false)) {
  class Gpf_Plugins_EngineConfigFile extends Gpf_File_Config {
      const FILE_NAME = 'engineconfig.php';
      const CONFIGURATION = 'config';
  
      public function __construct() {
          parent::__construct(Gpf_Paths::getInstance()->getRealAccountConfigDirectoryPath(). self::FILE_NAME);
      }
      
      public function createEmpty() {
          $file = new Gpf_Io_File($this->getSettingFileName());
          $file->setFileMode('w');
          $file->setFilePermissions(0777);
          $file->write('');
          $file->close();
      }
      
      /**
       *
       * @return Gpf_Plugins_EngineSettings
       */
      public function loadConfiguration() {
  		$serialized = $this->getSetting(self::CONFIGURATION);
          $configuration = @unserialize($serialized);
          if(!($configuration instanceof Gpf_Plugins_EngineSettings)) {
              throw new Gpf_Exception('Unserialization error');    		
          }
          return $configuration;
      }
  
      public function saveConfiguration(Gpf_Plugins_EngineSettings $configuration) {
      	if (defined('ENABLE_ENGINECONFIG_LOG')) {
      		Gpf_Log::info('Writing configuration: ' . print_r($configuration, true));
      	}
          $this->setSetting(self::CONFIGURATION, serialize($configuration));
      }
  }
  

} //end Gpf_Plugins_EngineConfigFile

if (!interface_exists('Gpf_Data_Row', false)) {
  interface Gpf_Data_Row {
      public function get($name);
  
      public function set($name, $value);
  }

} //end Gpf_Data_Row

if (!interface_exists('Gpf_Templates_HasAttributes', false)) {
  interface Gpf_Templates_HasAttributes {
      function getAttributes();
  }

} //end Gpf_Templates_HasAttributes

if (!class_exists('Gpf_DbEngine_RowBase', false)) {
  abstract class Gpf_DbEngine_RowBase extends Gpf_Object implements Gpf_Data_Row, Gpf_Templates_HasAttributes {
      /**
       * @var boolean
       */
      protected $isPersistent;
          
      abstract public function prepareSelectClause(Gpf_SqlBuilder_SelectBuilder $select, $aliasPrefix = '');
      abstract public function fillFromSelect(Gpf_SqlBuilder_SelectBuilder $select);
      
      /**
       * @return boolean true if object has been loaded from database, otherwise false
       */
      public function isPersistent() {
          return $this->isPersistent;
      }
  
      public function setPersistent($persistent) {
          $this->isPersistent = $persistent;
      }
      
      /**
       * Inserts row
       *
       */
      public function insert() {
          throw new Gpf_Exception('Unimplemented');
      }
      
      /**
       *
       */
      public function update($updateColumns = array()) {
          throw new Gpf_Exception('Unimplemented');
      }
      
      /**
       *
       */
      public function load() {
          throw new Gpf_Exception('Unimplemented');
      }
  }
  

} //end Gpf_DbEngine_RowBase

if (!interface_exists('Gpf_Rpc_Serializable', false)) {
  interface Gpf_Rpc_Serializable {
  
      public function toObject();
  
      public function toText();
  }

} //end Gpf_Rpc_Serializable

if (!class_exists('Gpf_DbEngine_Row', false)) {
  class Gpf_DbEngine_Row extends Gpf_DbEngine_RowBase implements Iterator, Gpf_Rpc_Serializable, Gpf_Templates_HasAttributes  {
      const NULL = '_NULL_';
  
      /**
       * @var array
       */
      private $columns;
      /**
       * @var Gpf_DbEngine_Table
       */
      private $table;
  
      /**
       * @var Gpf_DbEngine_Database
       */
      private $db;
  
  
      /**
       * @var boolean
       */
      private $recordChanged = true;
  
      /**
       * iterator position
       *
       * @var int
       */
      private $position = 0;
  
      /**
       * @var array of Gpf_DbEngine_Row_Constraint
       */
      private $constraints = array();
  
      private $tableColumns;
  
      /**
       * Creates instance of Db_Row object and generates new primary key value
       */
      public function __construct() {
          $this->db = $this->createDatabase();
          $this->init();
      }
  
      /**
       * @return string text representation of Db_Row object
       */
      public function __toString() {
          return get_class($this) . " (" . $this->toText() . ')';
      }
  
      /**
       * Return array of attributes in form column -> value
       *
       * @return array
       */
      public function toArray() {
          $array = array();
          foreach ($this as $key => $value) {
              $array[$key] = $value;
          }
          return $array;
      }
  
      /**
       * Deletes row. Primary key value must be set before this function is called
       */
      public function delete() {
          if($this->isPrimaryKeyEmpty()) {
              throw new Gpf_Exception("Could not delete Row. Primary key values are empty");
          }
  
          foreach ($this->table->getDeleteConstraints() as $deleteConstraint) {
              $deleteConstraint->execute($this);
          }
  
          $deleteBuilder = new Gpf_SqlBuilder_DeleteBuilder();
          $deleteBuilder->from->add($this->table->name());
          $deleteBuilder->where = $this->getPrimaryWhereClause();
           
          $deleteBuilder->deleteOne();
      }
  
      /**
       * Updates row. Primary key value must be set before this function is called
       *
       * @param array $updateColumns list of columns that should be updated. if not set, all modified columns are update
       * @throws Gpf_DbEngine_Row_ConstraintException
       */
      public function update($updateColumns = array()) {
          if($this->isPrimaryKeyEmpty()) {
              throw new Gpf_Exception("Could not update Row. Primary key values are empty");
          }
  
          $this->beforeSaveCheck();
  
          $this->beforeSaveAction();
  
          $this->updateRow($updateColumns);
      }
  
      /**
       * Inserts row
       *
       * @throws Gpf_DbEngine_Row_ConstraintException
       * @throws Gpf_DbEngine_DuplicateEntryException
       */
      public function insert() {
          $this->beforeSaveCheck();
  
          $this->beforeSaveAction();
  
          $this->insertRow();
      }
  
      /**
       * Saves row. If row exists in table (was loaded before) it is updated,
       * otherwise new row is added
       *
       * @throws Gpf_DbEngine_Row_ConstraintException
       * @throws Gpf_DbEngine_DuplicateEntryException
       */
      public function save() { 	
          if ($this->isPersistent()) {
              if ($this->isChanged()) {
                  $this->update();
              }
          } else {
              $this->insert();
          }
      }
  
      /**
       * Loads row by primary key value
       *
       * @throws Gpf_DbEngine_NoRowException if selected row does not exist
       */
      public function load() {
          $this->loadRow($this->getPrimaryColumns());
      }
  
      /**
       * Loads row by attribute values that have been already set
       * If $loadColumns parameter is set, row is loaded by values in columns specified by $loadColumns parameter
       *
       * @param array $loadColumns list of column names
       * @throws Gpf_DbEngine_NoRowException
       * @throws Gpf_DbEngine_TooManyRowsException
       */
      public function loadFromData(array $loadColumns = array()) {
          $this->loadRow($this->getLoadKey($loadColumns), true);
      }
  
      /**
       * Loads collection of row objects by attribute values that have been already set
       * If $loadColumns parameter is set, collection is loaded by values in columns specified by $loadColumns parameter
       *
       * @param array $loadColumns
       * @return Gpf_DbEngine_Row_Collection
       */
      public function loadCollection(array $loadColumns = array()) {
          $select = $this->getLoadSelect($this->getLoadKey($loadColumns), true);
          return $this->loadCollectionFromRecordset($select->getAllRows());
      }
  
      /**
       * @param $rowsRecordSet
       * @return Gpf_DbEngine_Row_Collection
       */
      public function loadCollectionFromRecordset(Gpf_Data_RecordSet $rowsRecordSet) {
          return $this->fillCollectionFromRecordset(new Gpf_DbEngine_Row_Collection(), $rowsRecordSet);
      }
  
      /**
       * @return Gpf_DbEngine_Row_Collection
       */
      protected function fillCollectionFromRecordset(Gpf_DbEngine_Row_Collection $collection, Gpf_Data_RecordSet $rowsRecordSet) {
          foreach ($rowsRecordSet as $rowRecord) {
              $dbRow = clone $this;
              $dbRow->fillFromRecord($rowRecord);
              $dbRow->isPersistent = true;
              $collection->add($dbRow);
          }
          return $collection;
      }
  
      /**
       * Checks if row with primary key already exists
       *
       * @return true if row exists, otherwise false
       */
      public function rowExists() {
          try {
              $select = $this->getLoadSelect($this->getPrimaryColumns());
              $select->getOneRow();
          } catch (Gpf_Exception $e) {
              return false;
          }
          return true;
      }
  
      /**
       * Fills Db_Row from a record
       * Fields that are not part of the Db_Row are ignored
       *
       * @param Gpf_Data_Record $record
       */
      public function fillFromRecord(Gpf_Data_Record $record) {
          foreach ($this->tableColumns as $column) {
              $name = $column->name;
              try {
                  $this->set($name, $record->get($name));
              } catch (Gpf_Exception $e) {
              }
          }
          $this->afterLoad();
      }
  
      /**
       * Fills Db_Row from select. Select should return one row.
       *
       * @param Gpf_SqlBuilder_SelectBuilder $select
       * @throws Gpf_DbEngine_NoRowException
       * @throws Gpf_DbEngine_TooManyRowsException
       */
      public function fillFromSelect(Gpf_SqlBuilder_SelectBuilder $select) {
          $this->fillFromRecord($select->getOneRow());
          $this->isPersistent = true;
      }
  
      /**
       * Sets value of the primary key
       *
       * @param string $value
       * @throws Gpf_Exception if row has more than a one primary key
       */
      public function setPrimaryKeyValue($value) {
          $this->set($this->getSinglePrimaryKeyColumn()->getName(), $value);
      }
  
      /**
       * Gets value of the primary key
       *
       * @throws Gpf_Exception if row has more than a one primary key
       * @return string
       */
      public function getPrimaryKeyValue() {
          return $this->get($this->getSinglePrimaryKeyColumn()->getName());
      }
  
      /**
       * Performs explicit check on Db_Row
       *
       * @throws Gpf_DbEngine_Row_CheckException if there is some error
       */
      public function check() {
          $constraintExceptions = array();
  
          foreach ($this->table->getConstraints() as $constraint) {
              try {
                  $constraint->validate($this);
              } catch (Gpf_DbEngine_Row_ConstraintException $e) {
                  $constraintExceptions[] = $e;
              }
          }
          if (count($constraintExceptions) > 0) {
              throw new Gpf_DbEngine_Row_CheckException($constraintExceptions);
          }
      }
  
      /**
       * Sets value of the field to SQL NULL
       *
       * @param string $name
       * @throws Gpf_DbEngine_Row_MissingFieldException
       */
      public function setNull($name) {
          $this->set($name, self::NULL);
      }
  
      public function isPrimaryKeyEmpty() {
          return $this->isRowKeyEmpty($this->getPrimaryColumns());
      }
  
      /**
       *
       * @return array
       */
      public function getPrimaryColumns() {
          return $this->table->getPrimaryColumns();
      }
  
      public function prepareSelectClause(Gpf_SqlBuilder_SelectBuilder $select, $aliasPrefix = '') {
          $alias = rtrim($aliasPrefix, '_');
          foreach($this->tableColumns as $column) {
              if($aliasPrefix != '') {
                  $select->select->add($column->name, $aliasPrefix . $column->name, $alias);
              } else {
                  $select->select->add($column->name);
              }
          }
      }
  
      /**
       * @return Gpf_DbEngine_Table
       */
      public function getTable() {
          return $this->table;
      }
  
      /*************************************************************************/
      /********************** Interface: Gpf_Data_Row ************************/
      /*************************************************************************/
  
      /**
       * Sets value of the field
       *
       * @param string $name
       * @param mixed $value
       * @throws Gpf_DbEngine_Row_MissingFieldException
       */
      public function set($name, $value) {
          if (is_object($value)) {
              throw new Gpf_Exception("Value of column $name cannot be an object");
          }
          $value = (string) $value;
          if($this->get($name) === $value) {
              return;
          }
          $this->recordChanged = true;
  
          if ($value === '' && in_array($this->tableColumns[$name]->getType(),
          array(Gpf_DbEngine_Column::TYPE_NUMBER, Gpf_DbEngine_Column::TYPE_DATE))) {
              $this->setNull($name);
          } else {
              $this->columns[$name] = $value;
          }
      }
  
      public function setChanged($value) {
          $this->recordChanged = $value;
      }
  
      /**
       * Returns value of the field
       *
       * @param string $name name of the field
       * @return string
       * @throws Gpf_DbEngine_Row_MissingFieldException
       */
      public function get($name) {
          $value = $this->getInternalValue($name);
          if ($value == self::NULL) {
              return null;
          }
          return $value;
      }
  
      /*************************************************************************/
      /******************* Interface: Gpf_Rpc_Serializable ***********************/
      /*************************************************************************/
  
      public function toObject() {
          $obj = new stdClass();
          foreach ($this as $id => $val) {
              $obj->$id = $val;
          }
          return $obj;
      }
  
      public function toText() {
          $text = "";
          foreach ($this as $id => $value) {
              $text .= "$id = $value, ";
          }
          return rtrim($text, ", ");
      }
  
      /*************************************************************************/
      /************* Interface: Gpf_Templates_HasAttributes ******************/
      /*************************************************************************/
       
      public function getAttributes() {
          return $this->toArray();
      }
  
      /*************************************************************************/
      /************************* Interface: Iterator ***************************/
      /*************************************************************************/
       
  
      public function current() {
          $columns = $this->tableColumns;
          return $this->get($this->key());
      }
  
      public function key() {
          $columns = $this->tableColumns;
          $i=0;
          foreach ($columns as $id => $column) {
              if ($this->position == $i) {
                  return $id;
              }
              $i++;
          }
          return false;
      }
  
      public function next() {
          $this->position++;
      }
  
      public function rewind() {
          $this->position = 0;
      }
  
      public function valid() {
          return $this->position < count($this->tableColumns);
      }
  
      /**
       * Sets table of the Db_Row object
       *
       * @param Gpf_DbEngine_Table $table
       */
      protected function setTable(Gpf_DbEngine_Table $table) {
          $this->table = $table;
          $this->tableColumns = $table->getColumns();
      }
  
      /**
       * Inits Db_Row object
       *
       */
      protected function init() {
          $this->columns = array();
          $this->isPersistent = false;
      }
  
      /**
       * Generates new primary key value
       * Keys with already set values, don't change
       */
      protected function generatePrimaryKey() {
          foreach($this->table->getPrimaryColumns() as $column) {
              if($column->isAutogenerated() && $column->type == "String" && !strlen($this->get($column->name))) {
                  $this->set($column->name, Gpf_Common_String::generateId($column->length));
              }
          }
      }
  
      /**
       * This method is executed after row object is loaded from database
       */
      protected function afterLoad() {
      }
  
      /**
       * Performs any additional actions that are needed before row is saved
       */
      protected function beforeSaveAction() {
      }
  
      /**
       * Performs check before row is saved
       *
       * @throws Gpf_DbEngine_Row_ConstraintException
       */
      protected function beforeSaveCheck() {
          foreach ($this->table->getConstraints() as $constraint) {
              $constraint->validate($this);
          }
      }
  
      /**
       * @param string $name name of the field
       * @return string, null, self::NULL
       *   - null is returned when value for this field has not been set so far
       *   - self::NULL is returned when value of this field has to be set to null in DB
       * @throws Gpf_DbEngine_Row_MissingFieldException
       */
      private function getInternalValue($name) {
          if (@$this->tableColumns[$name] === null) {
              throw new Gpf_DbEngine_Row_MissingFieldException($name, get_class($this));
          }
          return @$this->columns[$name];
      }
  
      private function getPrimaryWhereClause() {
          return $this->getRowKeyWhereClause($this->getPrimaryColumns());
      }
  
      private function clearPrimaryKey() {
          $primaryKeyColumns = $this->getPrimaryColumns();
          foreach ($primaryKeyColumns as $column) {
              $this->set($column->getName(), null);
          }
      }
  
      private function getLoadKey(array $loadColumns = array()) {
          $rowKey = array();
          if (is_array($loadColumns) && count($loadColumns)) {
              foreach ($loadColumns as $columnName) {
                  $rowKey[] = $this->table->getColumn($columnName);
              }
          } else {
              foreach ($this->tableColumns as $index => $column) {
                  if($this->getInternalValue($column->name) !== null) {
                      $rowKey[$column->name] = $column;
                  }
              }
          }
          return $rowKey;
      }
  
      protected function getRowKeyWhereClause($rowKey) {
          $builder = new Gpf_SqlBuilder_SelectBuilder();
          foreach($rowKey as $column) {
              if($this->getInternalValue($column->name) == self::NULL) {
                  $builder->where->add($column->name, 'is', 'NULL', 'AND', false);
              } else {
                  $builder->where->add($column->name, '=', $this->get($column->name));
              }
          }
          return $builder->where;
      }
  
  
      /**
       * @throws Gpf_DbEngine_NoRowException
       * @throws Gpf_DbEngine_TooManyRowsException
       * @throws Gpf_Exception
       */
      private function loadRow($rowKey, $withAlternate = false) {
          $select = $this->getLoadSelect($rowKey, $withAlternate);
          $this->fillFromSelect($select);
          $this->recordChanged = false;
      }
  
      private function isRowKeyEmpty($rowKey) {
          foreach($rowKey as $column) {
              if($this->get($column->name) === null || $this->get($column->name) == "") {
                  return true;
              }
          }
          return false;
      }
  
      /**
       * @return Gpf_DbEngine_Column
       * @throws Gpf_Exception if row has more than a one primary key
       */
      private function getSinglePrimaryKeyColumn() {
          $primaryKeys = $this->getPrimaryColumns();
          if (count($primaryKeys) != 1) {
              throw new Gpf_Exception("Can not use setPrimaryKeyValue() method as "
              . get_class($this) . " has multiple column primary key");
          }
          reset($primaryKeys);
          return current($primaryKeys);
      }
  
      private function isChanged() {
          return $this->recordChanged;
      }
  
      private function hasAutoIncrementedKey() {
          return $this->table->hasAutoIncrementedKey();
      }
  
      /**
       *
       * @return Gpf_DbEngine_Column
       */
      private function getAutoIncrementedColumn() {
          return $this->table->getAutoIncrementedColumn();
      }
  
      private function hasAutogeneratedKey() {
          foreach($this->table->getPrimaryColumns() as $column) {
              if($column->isAutogenerated() && $column->type == Gpf_DbEngine_Column::TYPE_STRING) {
                  return true;
              }
          }
          return false;
      }
  
      /**
       * @throws Gpf_Exception
       * @return Gpf_SqlBuilder_SelectBuilder
       */
      protected function getLoadSelect($rowKey, $withAlternate = false) {
          if(!$withAlternate && $this->isRowKeyEmpty($rowKey)) {
              throw new Gpf_Exception("Could not load Row. Primary key values empty");
          }
  
          $select = $this->prepareLoadSelect();
          $select->where = $this->getRowKeyWhereClause($rowKey);
          return $select;
      }
  
      private $loadSelect = null;
  
      private function prepareLoadSelect() {
          if ($this->loadSelect === null) {
              $this->loadSelect = new Gpf_SqlBuilder_SelectBuilder();
              $this->prepareSelectClause($this->loadSelect);
              $this->loadSelect->from->add($this->table->name());
              return $this->loadSelect;
          }
          return clone $this->loadSelect;
      }
  
      /**
       * @return Gpf_SqlBuilder_UpdateBuilder
       */
      protected function createUpdateBuilder() {
          return new Gpf_SqlBuilder_UpdateBuilder();
      }
  
      private function updateRow($updateColumns = array()) {
          $updateBuilder = $this->createUpdateBuilder();
          $updateBuilder->from->add($this->table->name());
  
          foreach($this->tableColumns as $column) {
              if(count($updateColumns) > 0 && !in_array($column->name, $updateColumns, true)) {
                  continue;
              }
              $columnValue = $this->getInternalValue($column->name);
              if(!$this->table->isPrimary($column->name) &&  $columnValue !== null) {
                  if($columnValue == self::NULL) {
                      $updateBuilder->set->add($column->name, 'NULL', false);
                  } else {
                      $updateBuilder->set->add($column->name, $columnValue, $column->doQuote());
                  }
              }
          }
  
          $updateBuilder->where = $this->getPrimaryWhereClause();
          
          $updateBuilder->updateOne();
      }
      
      /**
       * @throws Gpf_DbEngine_DuplicateEntryException
       */
      private function insertRow() {
          if ($this->isPrimaryKeyEmpty()) {
              $this->generatePrimaryKey();
          }
  
          $this->executeInsertRow();
          $this->isPersistent = true;
      }
  
      /**
       * @return Gpf_SqlBuilder_InsertBuilder()
       */
      protected function createInsertBuilder() {
          return new Gpf_SqlBuilder_InsertBuilder();
      }
  
      /**
       * @throws Gpf_DbEngine_DuplicateEntryException
       */
      private function executeInsertRow() {
          $insertBuilder = $this->createInsertBuilder();
          $insertBuilder->setTable($this->table);
           
          if($this->hasAutoIncrementedKey() && !$this->get($this->getAutoIncrementedColumn()->getName())) {
              $this->set($this->getAutoIncrementedColumn()->getName(), 0);
          }
          foreach($this->tableColumns as $column) {
              $value = $this->getInternalValue($column->name);
              if ($value === null) {
                  continue;
              }
              if ($value == self::NULL) {
                  $insertBuilder->add($column->name, 'NULL', false);
                  continue;
              }
              $insertBuilder->add($column->name, $value, $column->doQuote());
          }
          if($this->hasAutoIncrementedKey() && !$this->get($this->getAutoIncrementedColumn()->getName())) {
              $statement = $insertBuilder->insertAutoincrement();
              $this->set($this->getAutoIncrementedColumn()->getName(), $statement->getAutoIncrementId());
          } else {
              $insertBuilder->insert();
          }
      }
  }
  

} //end Gpf_DbEngine_Row

if (!class_exists('Gpf_Db_Account', false)) {
  abstract class Gpf_Db_Account extends Gpf_DbEngine_Row {
      const DEFAULT_ACCOUNT_ID = 'default1';
      const APPROVED = 'A';
      const PENDING = 'P';
      const SUSPENDED = 'S';
      const DECLINED = 'D';
  
      private $password;
      private $firstname;
      private $lastname;
  
      function __construct(){
          parent::__construct();
          $this->setApplication(Gpf_Application::getInstance()->getCode());
          $date = new Gpf_DateTime();
          $this->setDateinserted($date->toDateTime());
      }
  
      function init() {
          $this->setTable(Gpf_Db_Table_Accounts::getInstance());
          parent::init();
      }
  
      function setId($id) {
          $this->set(Gpf_Db_Table_Accounts::ID, $id);
      }
  
      public function setDefaultId() {
          $this->setId(self::DEFAULT_ACCOUNT_ID);
      }
  
      public function getId() {
          return $this->get(Gpf_Db_Table_Accounts::ID);
      }
  
      public function setDateinserted($dateInserted) {
          $this->set(Gpf_Db_Table_Accounts::DATEINSERTED, $dateInserted);
      }
  
      public function getDateinserted() {
          return $this->get(Gpf_Db_Table_Accounts::DATEINSERTED);
      }
  
      /**
       *
       * @return Gpf_Install_CreateAccountTask
       */
      public function getCreateTask() {
          $task = new Gpf_Install_CreateAccountTask();
          $task->setAccount($this);
          return $task;
      }
  
      /**
       *
       * @return Gpf_Install_UpdateAccountTask
       */
      public function getUpdateTask() {
          $task = new Gpf_Install_UpdateAccountTask();
          $task->setAccount($this);
          return $task;
      }
  
      public function createTestAccount($email, $password, $firstName, $lastName) {
          $this->setDefaultId();
          $this->setEmail($email);
          $this->setPassword($password);
          $this->setFirstname($firstName);
          $this->setLastname($lastName);
          $this->getCreateTask()->run(Gpf_Tasks_LongTask::NO_INTERRUPT);
      }
  
      public function getEmail() {
          return $this->get(Gpf_Db_Table_Accounts::EMAIL);
      }
  
      public function getPassword() {
          return $this->password;
      }
  
      public function getStatus() {
          return $this->get(Gpf_Db_Table_Accounts::STATUS);
      }
  
      public function setPassword($password) {
          $this->password = $password;
      }
  
      public function setFirstname($name) {
          $this->firstname = $name;
          $this->setName($this->firstname . ' ' . $this->lastname);
      }
  
      public function setLastname($name) {
          $this->lastname = $name;
          $this->setName($this->firstname . ' ' . $this->lastname);
      }
  
      public function getFirstname() {
          return $this->firstname;
      }
  
      public function getLastname() {
          return $this->lastname;
      }
  
      public function  setName($name) {
          $this->set(Gpf_Db_Table_Accounts::NAME, $name);
      }
  
      public function  setEmail($email) {
          $this->set(Gpf_Db_Table_Accounts::EMAIL, $email);
      }
  
      public function setStatus($newStatus) {
          $this->set(Gpf_Db_Table_Accounts::STATUS, $newStatus);
      }
  
      public function setApplication($application) {
          $this->set(Gpf_Db_Table_Accounts::APPLICATION, $application);
      }
  
      public function getApplication() {
          return $this->get(Gpf_Db_Table_Accounts::APPLICATION);
      }
  
      public function getName() {
          return $this->get(Gpf_Db_Table_Accounts::NAME);
      }
  
      public function setAccountNote($accountNote) {
          $this->set(Gpf_Db_Table_Accounts::ACCOUNT_NOTE, $accountNote);
      }
  
      public function getAccountNote() {
          return $this->get(Gpf_Db_Table_Accounts::ACCOUNT_NOTE);
      }
  
      public function setSystemNote($systemNote) {
          $this->set(Gpf_Db_Table_Accounts::SYSTEM_NOTE, $systemNote);
      }
  
      public function getStystemNote() {
          return $this->get(Gpf_Db_Table_Accounts::SYSTEM_NOTE);
      }
  }

} //end Gpf_Db_Account

if (!interface_exists('Gpf_Common_Stream', false)) {
  interface Gpf_Common_Stream {
      public function getData();
  }
  

} //end Gpf_Common_Stream

if (!class_exists('Gpf_Io_File', false)) {
  class Gpf_Io_File extends Gpf_Object implements Gpf_Common_Stream {
      const BUFFER_SIZE = 4000;
  
      private $textFilesExtensions = array('html','php','tpl','stpl','css','sql','txt','TXT','js');
      private $textFileSpecialNames = array('.htaccess','htaccess');
  
      private $fileName;
      private $extension;
      private $fileMode = 'r';
      private $fileHandler;
      private $isOpened;
      private $filePermissions;
  
      public function __construct($fileName) {
          $this->fileName = $fileName;
          $this->fileHandler = false;
          $this->isOpened = false;
          $this->filePermissions = null;
      }
  
      public function __destruct() {
          $this->close();
      }
  
      public function setFileName($name) {
          $this->fileName = $name;
      }
  
      public function seek($offset) {
          if(-1 == fseek($this->getFileHandler(), $offset)) {
              throw new Gpf_Exception($this->_('Could not seek file', $this->fileName));
          }
      }
  
      public function tell() {
          return ftell($this->getFileHandler());
      }
  
      public function getFileName() {
          return $this->fileName;
      }
  
      /**
       * Set file mode for operations with file
       *
       * @param string $mode possible values are: 'r','r+','w','w+','a','a+','x','x+'
       */
      public function setFileMode($mode) {
          $this->fileMode = $mode;
      }
  
      /**
       * Set file permissions in octal mode.
       *
       * @param int
       */
      public function setFilePermissions($permissions) {
          $this->filePermissions = $permissions;
      }
  
      public function getFileHandler() {
          if($this->fileHandler === false) {
              return $this->open($this->fileMode);
          }
          return $this->fileHandler;
      }
  
      public function open($fileMode = 'r') {
          $this->fileMode = $fileMode;
          $this->fileHandler = null;
          $this->isOpened = false;
          if(!empty($this->fileName)) {
              if(false !== ($this->fileHandler = @fopen($this->fileName, $this->fileMode))) {
                  $this->isOpened = true;
                  return $this->fileHandler;
              }
          }
          throw new Gpf_Io_FileException($this->_('Could not open file') . ' ' . $this->fileName);
      }
  
      public function lockWrite() {
          return $this->lock(LOCK_EX);
      }
  
      public function lock($operation) {
          if (!$this->isOpened()) {
              throw new Gpf_Exception('Only opened file can be locked');
          }
          for ($i=1; $i<=10; $i++) {
              if (flock($this->fileHandler, $operation)) {
                  return true;
              }
              usleep($i);
          }
          return false;
      }
  
      private function matchPattern($mask){
          $pattern = '/^'.str_replace('/', '\/', str_replace('\*', '.*', preg_quote(trim($mask)))).'/';
          if (@preg_match($pattern, $this->fileName) > 0) {
              return true;
          }
          return false;
      }
  
      public function matchPatterns($filePatterns){
          if (is_array($filePatterns)) {
              foreach($filePatterns as $filePattern){
                  if ($this->matchPattern($filePattern)){
                      return true;
                  }
              }
              return false;
          }
          return $this->matchPattern($filePatterns);
      }
  
      public function close() {
          if($this->isOpened) {
              @fclose($this->fileHandler);
              $this->fileHandler = false;
              $this->isOpened = false;
          }
      }
  
      public function readLine($length = 0) {
          $fileHandler = $this->getFileHandler();
          if($length <= 0) {
              return fgets($fileHandler);
          }
          return fgets($fileHandler, $length);
      }
  
      public function isEof() {
          $fileHandler = $this->getFileHandler();
          return feof($fileHandler);
      }
  
      public function readAsArray() {
          $result = @file($this->fileName);
          if($result === false) {
              throw new Gpf_Exception($this->_('Could not read file') . ' ' . $this->fileName);
          }
          return $result;
      }
  
      public function writeLine($string) {
          $fileHandler = $this->getFileHandler();
          $this->changeFilePermissions();
          return fputs($fileHandler, $string);
      }
  
      public function getSize() {
          return filesize($this->fileName);
      }
  
      /**
       * Get file extension (computes from filename)
       *
       */
      public function getExtension() {
          if (isset($this->extension)) {
              return $this->extension;
          }
          $info = pathinfo($this->getFileName());
          if(isset($info['extension'])) {
              $this->extension = $info['extension'];
          }
          return $this->extension;
      }
  
      public function rewind() {
          $fileHandler = $this->getFileHandler();
          if (!@fseek($fileHandler, 0)) {
              throw new Gpf_Exception($this->_('Rewind unsupported in this file stream'));
          }
      }
  
      public function read($length = 0) {
          $fileHandler = $this->getFileHandler();
          if(true === feof($fileHandler)) {
              return false;
          }
          if($length == 0) {
              $length = $this->getSize();
          }
          return fread($fileHandler, $length);
      }
  
      public function write($string) {
          if(!($fileHandler = $this->getFileHandler())) {
              throw new Gpf_Exception('Could not write file' . ' ' . $this->fileName);
          }
          $this->changeFilePermissions();
          $result = @fwrite($fileHandler, $string);
          if($result === false || ($result == 0 && strlen($string) != 0)) {
              throw new Gpf_Exception('Could not write file' . ' ' . $this->fileName);
          }
          return $result;
      }
  
      public function writeCsv($array, $delimiter) {
          if($fileHandler = $this->getFileHandler()) {
              $this->changeFilePermissions();
              $result = @fputcsv($fileHandler, $array, $delimiter);
              if($result === false) {
                  throw new Gpf_Exception('Could not write file' . ' ' . $this->fileName);
              }
          }
      }
  
      public function readCsv($delimiter) {
          $fileHandler = $this->getFileHandler();
          if(true === feof($fileHandler)) {
              return false;
          }
          return fgetcsv($fileHandler, 0, $delimiter);
      }
  
      public function passthru() {
          $fileHandler = $this->getFileHandler();
          return fpassthru($fileHandler);
      }
  
      public function getContents() {
          if(!$this->isExists()) {
              throw new Gpf_Exception($this->_('File %s does not exist.', $this->fileName));
          }
          if (($content = @file_get_contents($this->fileName)) === false) {
              throw new Gpf_Exception($this->_('Failed to read file %s', $this->fileName));
          }
          return $content;
      }
  
      public function putContents($data) {
          if(!$this->isExists()) {
              throw new Gpf_Exception($this->_('File %s does not exist.', $this->fileName));
          }
          if ($content = file_put_contents($this->fileName, $data) === false) {
              throw new Gpf_Exception($this->_('Failed to write file %s', $this->fileName));
          }
          return true;
      }
  
      public function getCheckSum() {
          if (in_array($this->getFileName(), $this->textFileSpecialNames) || in_array($this->getExtension(), $this->textFilesExtensions)) {
              return md5(str_replace(array("\r\n", "\r"), "\n", $this->getContents()));
          }
          return md5($this->getContents());
      }
  
      /**
       * Checks if selected file exists
       *
       * @return boolean true if file exists, otherwise false
       */
      public function isExists() {
          return self::isFileExists($this->fileName);
      }
  
      public static function isFileExists($fileName) {
          return @file_exists($fileName);
      }
  
      public function isDirectory() {
          return @is_dir($this->fileName);
      }
  
      public function isWritable() {
          return is_writable($this->fileName);
      }
  
      public function emptyFiles($recursive = false, $excludeFiles = null) {
          if ($this->isDirectory()) {
              if ($recursive == true) {
                  $dir = new Gpf_Io_DirectoryIterator($this, '', false, true);
                  foreach ($dir as $fullFileName => $fileName) {
                      $file = new Gpf_Io_file($fullFileName);
                      $file->emptyFiles(true);
                      $file->rmdir();
                  }
              }
              $dir = new Gpf_Io_DirectoryIterator($this, '', false);
              foreach ($dir as $fullFileName => $fileName) {
                  $file = new Gpf_Io_file($fullFileName);
                   
                  if (!is_array($excludeFiles)) {
                      $file->delete();
                  }else{
                      if (!in_array($fileName,$excludeFiles)) {
                          $file->delete();
                      }
                  }
              }
          } else {
              throw new Gpf_Exception($this->_('%s is not directory!', $this->fileName));
          }
          return true;
      }
  
      public function rmdir() {
          if (!@rmdir($this->getFileName())) {
              throw new Gpf_Exception($this->_('Could not delete directory %s', $this->fileName));
          }
      }
  
      /**
       * @throws Gpf_Exception
       */
      public function mkdir($recursive = false, $mode = 0777) {
          $mkMode = $mode;
          if($mkMode === null) {
              $mkMode = 0777;
          }
          if(false === @mkdir($this->fileName, $mkMode, $recursive)) {
              throw new Gpf_Exception($this->_('Could not create directory %s', $this->fileName));
          }
          if($mode !== null) {
              @chmod($this->getFileName(), $mode);
          }
      }
  
      public function recursiveCopy(Gpf_Io_File $target, $mode = null){
          if ($this->isDirectory()) {
              $dir = new Gpf_Io_DirectoryIterator($this, '', false, true);
              foreach ($dir as $fullFileName => $fileName) {
                  $file = new Gpf_Io_File($fullFileName);
                  $targetDir = new Gpf_Io_File($target->getFileName() . '/' . $fileName);
                  $targetDir->mkdir();
                  $file->recursiveCopy($targetDir);
              }
              $dir = new Gpf_Io_DirectoryIterator($this, '', false);
              foreach ($dir as $fullFileName => $fileName) {
                  $srcFile = new Gpf_Io_File($fullFileName);
                  $dstFile = new Gpf_Io_File($target->getFileName() . '/' . $fileName);
  
                  $this->copy($srcFile, $dstFile);
              }
          } else {
              throw new Gpf_Exception($this->_('%s is not directory!', $this->fileName));
          }
          return true;
      }
  
      /**
       * @return Gpf_Io_File
       */
      public function getParent(){
          $slashIndex = strrpos($this, '/');
          if($slashIndex == strlen($this) - 1){
              $slashIndex = strrpos($this, '/', -2);
          }
          return new Gpf_Io_File(substr($this, 0, $slashIndex + 1));
      }
  
      /**
       * @throws Gpf_Exception
       */
      public static function copy(Gpf_Io_File $source, Gpf_Io_File $target, $mode = null) {
          if (Gpf_Php::isFunctionEnabled('copy')) {
              if(false === @copy($source->getFileName(), $target->getFileName())) {
                  throw new Gpf_Exception('Could not copy ' .
                  $source->getFileName() . ' to ' . $target->getFileName());
              }
          } else {
              $target->open('w');
              $target->write($source->getContents());
          }
          if($mode !== null) {
              @chmod($target->getFileName(), $mode);
          }
      }
  
      public function getData() {
          return $this->read(self::BUFFER_SIZE);
      }
  
      public function getInodeChangeTime() {
          clearstatcache();
          return filemtime($this->fileName);
      }
      /**
       * @return boolean
       */
      public function delete() {
          return @unlink($this->getFileName());
      }
  
      public function getFilePermissions() {
          if (function_exists('fileperms')) {
              return substr(sprintf('%o', @fileperms($this->fileName)), -4);
          }
          return 'not supported';
      }
  
      public function getFileOwner() {
          if (function_exists('fileowner')) {
              return @fileowner($this->fileName);
          }
          return 'not supported';
      }
  
      /**
       * @throws Gpf_Exception
       */
      protected function changeFilePermissions() {
          if ($this->filePermissions != null) {
              if (!@chmod($this->fileName, $this->filePermissions)) {
                  throw new Gpf_Exception($this->_("Could not change permissions %s", $this->fileName));
              }
              $this->filePermissions = null;
          }
      }
  
      /**
       * Return open status of file
       *
       * @return boolean Returns true if file is opened
       */
      public function isOpened() {
          return $this->isOpened;
      }
  
      /**
       * Outputs file to the output buffer
       */
      public function output() {
          if (@readfile($this->fileName) == null) {
              if (!Gpf_Php::isFunctionEnabled('fpassthru')) {
                  echo file_get_contents($this->fileName);
              } else {
                  $fp = fopen($this->fileName, 'r');
                  fpassthru($fp);
                  fclose($fp);
              }
          }
      }
  
      public function __toString(){
          return $this->getFileName();
      }
  
      public function getName() {
          return basename($this->fileName);
      }
  
      public function getMimeType() {
          return Gpf_Io_MimeTypes::getMimeType($this->getExtension());
      }
  }
  

} //end Gpf_Io_File

if (!class_exists('Gpf_Plugins_EngineSettings', false)) {
  class Gpf_Plugins_EngineSettings extends Gpf_Object {
      public $activePlugins = array();
      public $extensionPoints = array();
  
      public function __construct() {
      }
  
      public function getActivePlugins() {
          return $this->activePlugins;
      }
  
      public function isPluginActive($codename) {
          return in_array($codename, $this->activePlugins);
      }
  
      public function getExtensionPoints() {
          return $this->extensionPoints;
      }
  
      public function init(array $plugins) {
  
          $arrDefines = array();
          $arrImplements = array();
  
          foreach($plugins as $plugin) {
              $this->activePlugins[$plugin->getCodeName()] = $plugin->getCodeName();
  
              $arrDefines = $this->mergeDefines($arrDefines, $plugin->getDefines());
              $arrImplements = array_merge($arrImplements, $plugin->getImplements());
          }
  
          $this->extensionPoints = $this->generateExtensionPoints($arrDefines, $arrImplements);
      }
  
      private function mergeDefines($arr1, $arr2) {
          $arrMerged = $arr1;
  
          foreach($arr2 as $define) {
              if($this->checkExtensionPointExistsInArray($define->getExtensionPoint(), $arr1)) {
                  throw new Gpf_Exception("Extension point '".$define->getExtensionPoint()."' was already defined by another plugin, they cannot have duplicated names!");
              }
              $arrMerged[] = $define;
          }
  
          return $arrMerged;
      }
  
      private function checkExtensionPointExistsInArray($extensionPointName, $arr) {
          if(count($arr) == 0) {
              return false;
          }
  
          foreach($arr as $define) {
              if($define->getExtensionPoint() == $extensionPointName) {
                  return true;
              }
          }
  
          return false;
      }
  
      private function generateExtensionPoints($arrDefines, $arrImplements) {
          $extensionPoints = array();
  
          foreach($arrDefines as $define) {
              $extensionPointName = $define->getExtensionPoint();
              $contextClass = $define->getClassName();
  
              $extensionPoints[$extensionPointName]['context'] = $contextClass;
              $extensionPoints[$extensionPointName]['handlers'] = $this->getHandlersForExtensionPoint($extensionPointName, $arrImplements);
          }
  
          return $extensionPoints;
      }
  
      private function getHandlersForExtensionPoint($extensionPointName, $arrImplements) {
          $handlers = array();
          foreach($arrImplements as $implements) {
              if($implements->getExtensionPoint() != $extensionPointName) {
                  continue;
              }
  
              $temp = array();
              $temp['class'] = $implements->getClassName();
              $temp['method'] = $implements->getMethodName();
              $temp['priority'] = $implements->getPriority();
  
              $handlers[] = $temp;
          }
  
          usort($handlers, array("Gpf_Plugins_EngineSettings", "compareHandlers"));
          
          return $handlers;
      }
  
      static function compareHandlers($a, $b) {
          if ($a['priority'] == $b['priority']) {
              return 0;
          }
          return ($a['priority'] > $b['priority']) ? -1 : 1;
      }
  }
  

} //end Gpf_Plugins_EngineSettings

if (!class_exists('Gpf_Plugins_ExtensionPoint', false)) {
  class Gpf_Plugins_ExtensionPoint extends Gpf_Object {
      /**
       * @var instances of all extension points
       */
      static private $instances = array();
  
      /**
       * extension point name
       */
      private $extensionPointName;
  
      /**
       * name of context class.
       * It is first created on the first use. It must be singleton
       * with getInstance() method
       */
      private $contextClassName = "";
  
      /**
       * class of the context
       * must be singleton with getInstance() method
       */
      private $contextClassObj = null;
  
      /**
       * name of context class.
       * It is first created on the first use. It must be singleton
       * with getInstance() method
       */
      private $handlers = array();
  
      /**
       * array of all process plugins for this extension point
       */
      private $plugins = array();
  
      function __construct($extensionPointName, $definition) {
          $this->extensionPointName = $extensionPointName;
  
          if(!isset($definition['context'])) {
  	        throw Gpf_Plugins_Exception("Extension point '$extensionPointName' does not have context class defined");
          }
          $this->contextClassName = $definition['context'];
  
          if(!isset($definition['handlers']) || !is_array($definition['handlers'])) {
          	throw Gpf_Plugins_Exception("Extension point '$extensionPointName' does not have handlers defined");
          }
          $this->handlers = $definition['handlers'];
      }
  
      /**
       * returns instance of extention point class of given name
       *
       * @return Gpf_Plugins_ExtensionPoint
       */
      public static function getInstance($extensionPointName, $definition) {
      	if(!isset(self::$instances[$extensionPointName])) {
      		self::$instances[$extensionPointName] = new Gpf_Plugins_ExtensionPoint($extensionPointName, $definition);
      	}
          return self::$instances[$extensionPointName];
      }
      
      public static function clear() {
      	self::$instances = array();
      }
  
      /**
       * processes handlers reistered for this extension point
       *
       * @param object $context
       */
      public function processHandlers($context = null) {
      	if(!is_array($this->handlers)) {
      		throw Gpf_Plugins_Exception("Handlers for extension point '".$this->extensionPointName."' are null");
      	}
  
      	//check if definition of extension point contains same context class name as is used in context
      	if (!($context instanceof $this->contextClassName)) {
      	    throw new Gpf_Plugins_Exception("Context class name ($this->contextClassName) is not same as context object (" . get_class($context) . ")");
      	}
  
      	foreach($this->handlers as $handler) {
      	    if(!$this->callHandler($handler, $context)) {
                  break;
              }
      	}
      }
  
      private function callHandler($handler, $context) {
  		$handlerObject = $this->createHandlerObject($handler);
  		$handlerMethod = $this->getHandlerMethod($handler);
  
          try {
              if($context == null) {
                  $returnValue = $handlerObject->$handlerMethod();
              } else {
              	$returnValue = $handlerObject->$handlerMethod($context);
              }
          } catch(Exception $e) {
              throw new Gpf_Plugins_Exception("Unhalted exception: \"".$e->getMessage()."\" in class ".get_class($handlerObject).", STOPPING");
              exit;
          }
  
          if($returnValue === Gpf_Plugins_Engine::PROCESS_STOP_EXIT) {
              exit;
          }
          if($returnValue === Gpf_Plugins_Engine::PROCESS_STOP_EXTENSION_POINT) {
              return false;
          }
          if($returnValue != Gpf_Plugins_Engine::PROCESS_CONTINUE) {
          	// handler function does not need to return value,
          	// it is assumed that it means to continue
           	//   throw new Gpf_Exception("Handler ".get_class($handlerObject).".$handlerMethod() method has to return value PROCESS_CONTINUE / PROCESS_STOP_EXTENSION_POINT / PROCESS_STOP_ALL / PROCESS_STOP_EXIT!");
          }
  
          return true;
      }
  
      private function createHandlerObject($handler) {
      	if(!isset($handler['class'])) {
              throw new Gpf_Plugins_Exception("Handler class is nt defined!");
          }
  
          $className = $handler['class'];
      	// create context object
      	eval("\$obj = $className::getHandlerInstance();");
          return $obj;
      }
  
      private function getHandlerMethod($handler) {
      	if(!isset($handler['method'])) {
              throw new Gpf_Plugins_Exception("Handler method is nt defined!");
          }
  
          return $handler['method'];
      }
  }
  

} //end Gpf_Plugins_ExtensionPoint

if (!class_exists('Gpf_Plugins_Handler', false)) {
  abstract class Gpf_Plugins_Handler extends Gpf_Object {
  
      /**
       * returns instance of handler class.
       * Instance can be either singleton or can create new object for every call
       *
       * @return instance of Gpf_Plugins_Handler child class
       */
      //TODO: This generated warning - not supported in PHP 5.2.x, maybe in next releases of php it will be supported
      //abstract public static function getHandlerInstance();
  }
  

} //end Gpf_Plugins_Handler

if (!class_exists('Gpf_Plugins_Definition', false)) {
  class Gpf_Plugins_Definition extends Gpf_Object {
      const CODE = 'id';
      const NAME = 'name';
      const URL = 'url';
      const DESCRIPTION = 'description';
      const VERSION = 'version';
      const AUTHOR = 'author';
      const AUTHOR_URL = 'author_url';
      const ACTIVE = 'active';
      const HELP = 'help';
      const CONFIG_CLASS_NAME = 'conf_service';
  
      const PLUGIN_TYPE_SYSTEM = "S";
      const PLUGIN_TYPE_NORMAL = "N";
      const PLUGIN_TYPE_FEATURE = "F";
  
      protected $codeName;
      protected $name;
      protected $url;
      protected $description;
      protected $version;
      protected $author = 'Quality Unit, s.r.o.';
      protected $authorUrl;
  
      /**
       * Text of help
       *
       * @var unknown_type
       */
      protected $help;
  
      protected $configurationClassName;
  
      /**
       * System plugin will be not displayed in list of plugins and is always activated
       *
       * @var boolean
       */
      protected $pluginType = self::PLUGIN_TYPE_NORMAL;
  
      private $arrDefines = array();
      private $arrImplements = array();
      private $arrRequirements = array();
      private $arrRejected = array();
  
      /**
       * @param Gpf_Data_RecordSet $recordset
       * @return Gpf_Data_Record
       */
      public function toRecord(Gpf_Data_RecordSet $recordset) {
          $record = $recordset->createRecord();
  
          $record->set(self::CODE, $this->getCodeName());
          $record->set(self::NAME, $this->getName());
          $record->set(self::URL, $this->getUrl());
          $record->set(self::DESCRIPTION, $this->getDescription());
          $record->set(self::VERSION, $this->getVersion());
          $record->set(self::AUTHOR, $this->getAuthor());
          $record->set(self::AUTHOR_URL, $this->getAuthorUrl());
          $record->set(self::HELP, $this->getHelp());
          $record->set(self::ACTIVE, 'N');
          $record->set(self::CONFIG_CLASS_NAME, $this->getConfigurationClassName());
  
          return $record;
      }
  
      public function getCodeName() {
          return $this->codeName;
      }
  
      public function getName() {
          return $this->name;
      }
  
      public function getUrl() {
          return Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_TEXT_BASE_LINK);
      }
  
      public function getDescription() {
          return $this->description;
      }
  
      public function getVersion() {
          return $this->version;
      }
  
      public function getAuthor() {
          return $this->author;
      }
  
      public function getAuthorUrl() {
          return Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_TEXT_BASE_LINK);
      }
  
      public function getHelp() {
          return $this->help;
      }
  
      /**
       * Add extension point definition
       *
       * @param string $extensionPoint Name of extension point
       * @param string $className Class name of input context
       */
      protected function addDefine($extensionPoint, $className) {
           $this->arrDefines[] = new Gpf_Plugins_Definition_ExtensionPoint($extensionPoint, $className);
      }
  
      /**
       * Extension point with lower priority is executed later
       */
      protected function addImplementation($extensionPoint, $className, $methodName, $priority = 10) {
           $this->arrImplements[] = new Gpf_Plugins_Definition_ExtensionPoint($extensionPoint, $className, $methodName, $priority);;
      }
  
      protected function addRequirement($pluginCode, $minVersion) {
          $this->arrRequirements[] = new Gpf_Plugins_Definition_VersionRequirement($pluginCode, $minVersion);
      }
      
      protected function addRefuse($pluginCode) {    
      	$this->arrRejected[] = $pluginCode;	
      }
  
      /**
       * Return array of extension point definitions (Gpf_Plugins_Definition_ExtensionPoint)
       */
      public function getDefines() {
          return $this->arrDefines;
      }
  
      /**
       * Return array of extension point implementations (Gpf_Plugins_Definition_ExtensionPoint)
       */
      public function getImplements(){
          return $this->arrImplements;
      }
  
      /**
       * Get default priority value.
       * Overwrite this method if is required different priority level.
       *
       * @return int
       */
      public function getPriority() {
          return 10;
      }
  
      public function check() {
      	$this->checkRequirements();
          $this->checkRejectedPlugins();
      }
      
      public function checkRequirements() {
          foreach ($this->arrRequirements as $requirement) {
              $requirement->check();
          }
      }
      
      public function checkRejectedPlugins() {
          foreach ($this->arrRejected as $pluginCode) {
  			if (Gpf_Plugins_Engine::getInstance()->getConfiguration()->isPluginActive($pluginCode)) {
              	throw new Gpf_Exception($this->_("%s refused active %s plugin", $this->codeName, $pluginCode));
  			}
          }
      }
  
      /**
       * Method will be called, when plugin is activated. e.g. create some tables required by plugin.
       *
       * @throws Gpf_Exception when plugin can not be activated
       */
      public function onActivate() {
      }
  
      /**
       * Method will be called, when plugin is deactivated. e.g. drop some tables needed by plugin.
       *
       */
      public function onDeactivate() {
      }
  
      /**
       * Is plugin normal plugin ? If yes, it willnot  be displayed in list of plugins and will be always activated.
       *
       * @return boolean
       */
  
      public function isSystemPlugin() {
          return $this->pluginType == self::PLUGIN_TYPE_SYSTEM;
      }
  
      public function getPluginType() {
          return $this->pluginType;
      }
  
      public function getConfigurationClassName() {
          return $this->configurationClassName;
      }
  }
  
  class Gpf_Plugins_Definition_VersionRequirement extends Gpf_Object {
      private $pluginCode;
      private $minVersion;
  
      public function __construct($pluginCode, $minVersion) {
          $this->pluginCode = $pluginCode;
          $this->minVersion = $minVersion;
      }
  
      public function check() {
          if (!Gpf_Plugins_Engine::getInstance()->getConfiguration()->isPluginActive($this->pluginCode)) {
              throw new Gpf_Exception($this->_("Required plugin %s is not active", $this->pluginCode));
          }
          $plugin = Gpf_Plugins_Engine::getInstance()->findPlugin($this->pluginCode);
          if (version_compare($plugin->getVersion(), $this->minVersion) < 0) {
              throw new Gpf_Exception($this->_("Required plugin %s has to be in version %s or higher", $this->pluginCode, $this->minVersion));
          }
      }
  }

} //end Gpf_Plugins_Definition

if (!class_exists('Gpf_Log', false)) {
  class Gpf_Log  {
      const CRITICAL = 50;
      const ERROR = 40;
      const WARNING = 30;
      const INFO = 20;
      const DEBUG = 10;
      
      /**
       * @var Gpf_Log_Logger
       */
      private static $logger;
         
      /**
       * @return Gpf_Log_Logger
       */
      private static function getLogger() {
          if (self::$logger == null) {
              self::$logger = Gpf_Log_Logger::getInstance();
          }
          return self::$logger;
      }
      
      private function __construct() {
      }
      
      public static function disableType($type) {
          self::getLogger()->disableType($type);
      }
      
      public static function enableAllTypes() {
          self::getLogger()->enableAllTypes();
      }
      
      /**
       * logs message
       *
       * @param string $message
       * @param string $logLevel
       * @param string $logGroup
       */
      public static function log($message, $logLevel, $logGroup = null) {
          self::getLogger()->log($message, $logLevel, $logGroup);
      }
  
      /**
       * logs debug message
       *
       * @param string $message
       * @param string $logGroup
       */
      public static function debug($message, $logGroup = null) {
          self::getLogger()->debug($message, $logGroup);
      }
          
      /**
       * logs info message
       *
       * @param string $message
       * @param string $logGroup
       */
      public static function info($message, $logGroup = null) {
          self::getLogger()->info($message, $logGroup);
      }
      
      /**
       * logs warning message
       *
       * @param string $message
       * @param string $logGroup
       */
      public static function warning($message, $logGroup = null) {
          self::getLogger()->warning($message, $logGroup);
      }
      
      /**
       * logs error message
       *
       * @param string $message
       * @param string $logGroup
       */
      public static function error($message, $logGroup = null) {
          self::getLogger()->error($message, $logGroup);
      }
  
      /**
       * logs critical error message
       *
       * @param string $message
       * @param string $logGroup
       */
      public static function critical($message, $logGroup = null) {
          self::getLogger()->critical($message, $logGroup);
      }
  
      /**
       * Attach new log system
       *
       * @param string $type 
       *      Gpf_Log_LoggerDisplay::TYPE
       *      Gpf_Log_LoggerFile::TYPE
       *      Gpf_Log_LoggerDatabase::TYPE
       * @param string $logLevel
       *      Gpf_Log::CRITICAL
       *      Gpf_Log::ERROR
       *      Gpf_Log::WARNING
       *      Gpf_Log::INFO
       *      Gpf_Log::DEBUG
       * @return Gpf_Log_LoggerBase
       */
      public static function addLogger($type, $logLevel) {
          if($type instanceof Gpf_Log_LoggerBase) {
              return self::getLogger()->addLogger($type, $logLevel);
          }
          return self::getLogger()->add($type, $logLevel);        
      }
      
      public static function removeAll() {
          self::getLogger()->removeAll();
      }
  
      public static function isLogToDisplay() {
          return self::getLogger()->isLogToDisplay();
      }
  }

} //end Gpf_Log

if (!class_exists('Gpf_Log_LoggerBase', false)) {
  abstract class Gpf_Log_LoggerBase extends Gpf_Object {
  	private $logLevel = Gpf_Log::ERROR;
  	private $type = '';
  	
  	public function __construct($type) {
  	    $this->type = $type;
  	}
  	
      public function setLogLevel($level) {
      	$this->logLevel = $level;
      }
      
      public function getType() {
          return $this->type;
      }
      
      public function getLogLevel() {
      	return $this->logLevel;
      }
      
      public function logMessage($time, $message, $logLevel, $logGroup, $ip, $file, $line, $type = null) {
          if($logLevel < $this->getLogLevel()) {
              return;
          }
          $this->log($time, $message, $logLevel, $logGroup, $ip, $file, $line, $type);
      }
      
      abstract protected function log($time, $message, $logLevel, $logGroup, $ip, $file, $line, $type = null);
      
      /**
       * return name of log level as text
       *
       * @param const int $logLevel
       * @return string
       */
      protected function getLogLevelAsText($logLevel) {
      	switch($logLevel) {
      		case Gpf_Log::CRITICAL: return 'Critical';
      		case Gpf_Log::ERROR:    return 'Error';
      		case Gpf_Log::WARNING:  return 'Warning';
      		case Gpf_Log::INFO:     return 'Info';
      		case Gpf_Log::DEBUG:    return 'Debug';
      	}
      	
      	return ' Unknown';
      }
  }

} //end Gpf_Log_LoggerBase

if (!class_exists('Gpf_Log_LoggerDatabase', false)) {
  class Gpf_Log_LoggerDatabase extends Gpf_Log_LoggerBase {
      const TYPE = 'database';
  
      public function __construct() {
          parent::__construct(self::TYPE);
      }
  
      protected function log($time, $message, $logLevel, $logGroup, $ip, $file, $line, $type = null) {
          if($message == "") {
              return;
          }
          $timeString = strftime("Y-m-d H:i:s", $time);
           
          $userId = null;
  		try {
          	$userId = Gpf_Session::getAuthUser()->getUserId();
  		} catch(Gpf_Exception $e) {	}
          
          try {
              $dbLog = new Gpf_Db_Log();
              $dbLog->set('groupid', $logGroup);
              $dbLog->set('level', $logLevel);
              $dbLog->set('created', $timeString);
              $dbLog->set('filename', $file);
              $dbLog->set('message', $message);
              $dbLog->set('line', $line);
              $dbLog->set('ip', $ip);
              $dbLog->set('accountuserid', $userId);
              $dbLog->set(Gpf_Db_Table_Logs::TYPE, $type);
              $dbLog->save();
          } catch(Exception $e) {
              Gpf_Log::disableType(Gpf_Log_LoggerDatabase::TYPE);
              Gpf_Log::error($this->_sys("Database Logger Error. Logging on display: %s", $message));
              Gpf_Log::enableAllTypes();
          }
      }
  }

} //end Gpf_Log_LoggerDatabase

if (!class_exists('Pap_Logger', false)) {
  class Pap_Logger  {
      
      const SYSTEM_DEBUG_TYPE = 'O';
      
      private static function checkActionTypeInDebugTypes($type) {
          $debugTypes = Gpf_Settings::get(Pap_Settings::DEBUG_TYPES);
          if($debugTypes == '') {
              return false;
          }
           
          $arr = explode(",", $debugTypes);
          if(in_array($type, $arr)) {
              return true;
          }
          return false;
      }
      
      public static function getLogLevel($type = self::SYSTEM_DEBUG_TYPE) {
          $logLevel = Gpf_Settings::get(Gpf_Settings_Gpf::LOG_LEVEL_SETTING_NAME);
                    
          if(self::checkActionTypeInDebugTypes($type)) {
              $logLevel = Gpf_Log::DEBUG;
          }
          
          return $logLevel;
      }
  
      /**
       * @param $type
       * @return Gpf_Log_Logger
       */
      public static function create($type = self::SYSTEM_DEBUG_TYPE) {
          $logLevel = self::getLogLevel($type);
          
          $request = new Pap_Tracking_Request();
          if($request->getDebug() == Gpf::YES) {
              $logLevel = Gpf_Log::DEBUG;
          }
          
          $logger = Gpf_Log_Logger::getInstance($type);
          $logger->setGroup(substr($type, 0, 4) . '-' . Gpf_Common_String::generateId(10));
          $logger->setType($type);
          $logger->add(Gpf_Log_LoggerDatabase::TYPE, $logLevel);
  
          if($request->getDebug() == Gpf::YES) {
              $logger->add(Gpf_Log_LoggerDisplay::TYPE, $logLevel);
          }
           
          return $logger;
      }
  }

} //end Pap_Logger

if (!class_exists('Gpf_Settings', false)) {
  class Gpf_Settings extends Gpf_Object {
      
       /**
       * @var Gpf_Settings_AccountSettings instances
       */
      protected static $instances = array();
      protected static $instance;
      /**
       * @var Gpf_Settings_Define
       */
      protected $settings;
      
      protected function __construct() {
          $this->settings = Gpf_Application::getInstance()->createSettings();
      }
      
      private function init() {
          $this->settings->init();
      }
      
      /**
       * @return Gpf_Settings
       */
      private static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new Gpf_Settings();
              self::$instance->init();
          }
          return self::$instance;
      }
         
      public static function get($name) {
          return self::getInstance()->getSetting($name);
      }
      
      public static function set($name, $value) {
          self::getInstance()->setSetting($name, $value);
      }
      
      /**
       * @param $accountId
       * @return Gpf_Settings_AccountSettings
       */
      public static function getAccountSettings($accountId) {
          if (!array_key_exists($accountId, self::$instances)) {
              self::$instances[$accountId] = new Gpf_Settings_AccountSettings(self::getInstance()->getAccountSettingsInstance($accountId),
                                                                              self::getInstance()->getAccountSettingsInstance());
          }
          return self::$instances[$accountId];
      }
      
      /**
       * @param $accountId
       * @return Gpf_Settings_Base
       */
      protected function getAccountSettingsInstance($accountId = null) {
          return Gpf_Settings_Base::getInstance($this->settings, $accountId);
      }
      
      protected function getSetting($name) {
          return $this->getSettingsInstance($name)->readSetting($name);
      }
      
      protected function setSetting($name, $value) {
          $this->getSettingsInstance($name)->writeSetting($name, $value);
      }
      
      /**
       * @param $name
       * @return Gpf_Settings_Base
       */
      private function getSettingsInstance($name) {
          $this->settings->checkSetting($name);
          return $this->getAccountSettingsInstance($this->getAccountId($name));
      }
      
      private function getAccountId($name) {
          if ($this->settings->isAccountSetting($name)) {
              try {
                  return Gpf_Session::getAuthUser()->getAccountId();
              } catch (Gpf_Exception $e) {
              }
              return Gpf_Db_Account::DEFAULT_ACCOUNT_ID;
          }
          return null;
      }
  }

} //end Gpf_Settings

if (!class_exists('Gpf_Settings_Define', false)) {
  abstract class Gpf_Settings_Define extends Gpf_Object {
      const FILE = 'F';
      const DB = 'D';
      
      const LARGE_TEXT_SETTINGS_DIR = 'settings';
      const LARGE_TEXT_SETTING_TEMPLATE_FILE_EXTENSION = 'stpl';
      
      /**
       * array of setting types, indexed by setting name
       * each array item contains subarray with indexes
       * 'name' - setting name
       * 'defaultValue' - setting default value or null
       * 'type' - type of setting: file or DB
       * 'isAccount' - true or false
       */
      private $settings = array();
      
      public function init() {
      	$this->defineFileSettings();
      	$this->defineDbSettings();
      }
  
      public function writeDefaultFileSettings() {
      	foreach ($this->settings as $name => $value) {
      		if ($this->settings[$name]['type'] == self::FILE) {
      		    try {
      			    Gpf_Settings::set($name, Gpf_Settings::get($name), true);
      		    } catch (Gpf_Settings_UnknownSettingException $e) {
      		    }
      		}
      	}
      }
      
      public function addFileSetting($settingName, $defaultValue = null, $isAccountSetting = false) {
          $this->addSetting($settingName, $defaultValue, self::FILE, $isAccountSetting);
      }
      
      public function addDbSetting($settingName, $defaultValue = null, $isAccountSetting = false) {
          $this->addSetting($settingName, $defaultValue, self::DB, $isAccountSetting);
      }
      
      protected function addDefaultValue($name, $value) {
          if(!array_key_exists($name, $this->settings)) {
              throw new Gpf_Exception("Setting '$name' is not known, define it first in defineSettings() function!");
          }
          $this->settings[$name]['defaultValue'] = $value;
      }
      
      private function addSetting($settingName, $defaultValue, $type, $isAccountSetting) {
          $temp = array();
          $temp['name'] = $settingName;
          $temp['defaultValue'] = $defaultValue;
          $temp['type'] = $type;
          $temp['isAccount'] = $isAccountSetting;
          
          $this->settings[$settingName] = $temp;
      }
  
      public function getDefaultValue($name) {
          $defaultValue = $this->settings[$name]['defaultValue'];
          if($defaultValue === null) {
              $this->lazyInitDefaultValue($name);
              $defaultValue = $this->settings[$name]['defaultValue'];
          }
          if($defaultValue === null) {
              throw new Gpf_Settings_UnknownSettingException($name);
          }
          return $defaultValue;
      }
      
      public function isFileSetting($name) {
          return $this->settings[$name]['type'] == self::FILE;
      }
      
      public function isAccountSetting($name) {
      	return $this->settings[$name]['isAccount'] == true;
      }
      
      public function checkSetting($name) {
          if(!array_key_exists($name, $this->settings) || !is_array($this->settings[$name])) {
              throw new Gpf_Exception("Setting '$name' is not known, define it first in defineSettings() function!");
          }
      }
      
      protected function lazyInitDefaultValue($name) {
          try {
              $file = new Gpf_Io_File(Gpf_Paths::getInstance()->getResourcePath($name .
              '.' . self::LARGE_TEXT_SETTING_TEMPLATE_FILE_EXTENSION, self::LARGE_TEXT_SETTINGS_DIR));
          } catch (Gpf_ResourceNotFoundException $e) {
              return;
          }
          $this->addDefaultValue($name, $file->getContents());
      }
      
      abstract protected function defineFileSettings();
      
      abstract protected function defineDbSettings();
  }

} //end Gpf_Settings_Define

if (!class_exists('Gpf_Settings_Gpf', false)) {
  class Gpf_Settings_Gpf extends Gpf_Settings_Define {
      const PROGRAM_NAME = "programName";
      const DEMO_MODE = "DEMO_MODE";
      const TIMEZONE_NAME = 'TIMEZONE';
      const DEFAULT_TIMEZONE = 'America/Los_Angeles';
  
      const FTP_HOSTNAME  = 'FTP_HOSTNAME';
      const FTP_DIRECTORY = 'FTP_DIRECTORY';
      const FTP_USERNAME  = 'FTP_USERNAME';
      const FTP_PASSWORD  = 'FTP_PASSWORD';
  
      const DB_HOSTNAME = 'DB_HOSTNAME';
      const DB_USERNAME = 'DB_USERNAME';
      const DB_PASSWORD = 'DB_PASSWORD';
      const DB_DATABASE = 'DB_DATABASE';
  
      const BENCHMARK_ACTIVE = 'BENCHMARK_ACTIVE';
      const BENCHMARK_MIN_SQL_TIME = 'BENCHMARK_MIN_SQL_TIME';
      const BENCHMARK_MAX_FILE_SIZE = 'BENCHMARK_MAX_FILE_SIZE';
      const NETWORK_ENABLED = 'NETWORK';    
  
      const LICENSE = 'LICENSE';
      const VARIATION = 'VARIATION';
      const VARIATION_CODE = 'VARIATION_CODE';
  
      const CRON_RUN_INTERVAL = 'cronRunInterval';
      
      const PROXY_SERVER_SETTING_NAME = 'proxyServer';
      const PROXY_PORT_SETTING_NAME = 'proxyPort';
      const PROXY_USER_SETTING_NAME = 'proxyUser';
      const PROXY_PASSWORD_SETTING_NAME = 'proxyPassword';
  
      const QUICK_LAUNCH_SETTING_NAME = "quickLaunchSetting";
      const LOG_LEVEL_SETTING_NAME = 'log_level';
      const LAST_RUN_TIME_SETTING = 'cronLastRun';
  
      const MAX_ALLOWED_SERVER_LOAD = 'maxAllowedServerLoad';
      const SERVER_OVERLOAD_INTERRUPTIONS = 'serverOverloadInterruptions';
  
      const NOT_FORCE_EMAIL_USERNAMES = "notForceEmailUsernames";
      const AUTO_DELETE_EVENTS = "deleteeventdays";
      const AUTO_DELETE_EVENTS_RECORDS_NUM = "deleteeventrecords";
      const AUTO_DELETE_LOGINSHISTORY = "deleteloginshistorydays";
  
      const BRANDING_TEXT_BASE_LINK = "base_link";
      const BRANDING_FAVICON = "favicon";
      const BRANDING_QUALITYUNIT_ADDONS_LINK = "qualityunit_addons_link";
      const BRANDING_QUALITYUNIT_COMPANY_LINK = "qualityunit_company_link";
      const BRANDING_QUALITYUNIT_PRIVACY_POLICY_LINK = "qualityunit_privacy_policy_link";
      const BRANDING_QUALITYUNIT_CONTACT_US_LINK = "qualityunit_contact_us_link";
      const BRANDING_QUALITY_UNIT = "quality_unit";
      const BRANDING_QUALITYUNIT_SUPPORT_EMAIL = "qualityunit_support_email";
      const DEFAULT_COUNTRY = "defaultCountry";
  
      const PASSWORD_MIN_LENGTH = "password_min_len";
      const PASSWORD_MAX_LENGTH = "password_max_len";
      const PASSWORD_LETTERS = "password_letters";
      const PASSWORD_DIGITS = "password_digits";
      const PASSWORD_SPECIAL = "password_special";
  
      const SERVER_NAME = 'serverName';
      const SERVER_NAME_RESOLVE_FROM = 'serverNameResolveFrom';
      const BASE_SERVER_URL = 'baseServerUrl';
  
      const REGIONAL_SETTINGS_IS_DEFAULT = 'regional_settings_is_default';
      const REGIONAL_SETTINGS_DATE_FORMAT = 'dateformat';
      const REGIONAL_SETTINGS_TIME_FORMAT = 'timeformat';
      const REGIONAL_SETTINGS_THOUSANDS_SEPARATOR = 'thousandsseparator';
      const REGIONAL_SETTINGS_DECIMAL_SEPARATOR = 'decimalseparator';
  
      const DEFAULT_THOUANDS_SEPARATOR = ' ';
      const DEFAULT_DECIMAL_SEPARATOR = '.';
      const DEFAULT_DATE_FORMAT = 'MM/d/yyyy';
      const DEFAULT_TIME_FORMAT = 'HH:mm:ss';
  
      const SIDEBAR_DEFAULT_ONTOP = 'sidebar_default_ontop';
  
      protected function defineFileSettings() {
          $this->addFileSetting(self::DB_DATABASE);
          $this->addFileSetting(self::DB_HOSTNAME);
          $this->addFileSetting(self::DB_USERNAME);
          $this->addFileSetting(self::DB_PASSWORD);
  
          $this->addFileSetting(self::FTP_HOSTNAME,  'localhost');
          $this->addFileSetting(self::FTP_DIRECTORY, '/affiliate');
          $this->addFileSetting(self::FTP_USERNAME,  '');
          $this->addFileSetting(self::FTP_PASSWORD,  '');
  
          $this->addFileSetting(self::LAST_RUN_TIME_SETTING, '');
  
          $this->addFileSetting(self::MAX_ALLOWED_SERVER_LOAD, 0);
          $this->addFileSetting(self::SERVER_OVERLOAD_INTERRUPTIONS, 0);
  
          $this->addFileSetting(self::DEMO_MODE, Gpf::NO);
          $this->addFileSetting(self::TIMEZONE_NAME, self::DEFAULT_TIMEZONE);
          $this->addFileSetting(self::NETWORK_ENABLED, Gpf::YES);
  
          $this->addFileSetting(self::BENCHMARK_ACTIVE, Gpf::NO);
          $this->addFileSetting(self::BENCHMARK_MIN_SQL_TIME, 0);
          $this->addFileSetting(self::BENCHMARK_MAX_FILE_SIZE, 5);
          $this->addFileSetting(self::LICENSE, '');
  
          $this->addFileSetting(self::SERVER_NAME);
          $this->addFileSetting(self::SERVER_NAME_RESOLVE_FROM, 'SERVER_NAME');
          $this->addFileSetting(self::BASE_SERVER_URL);
      }
  
      protected function defineDbSettings() {
          $this->addDbSetting(self::VARIATION, '');
  
          $this->addDbSetting(self::PROXY_SERVER_SETTING_NAME, '');
          $this->addDbSetting(self::PROXY_PORT_SETTING_NAME, '');
          $this->addDbSetting(self::PROXY_USER_SETTING_NAME, '');
          $this->addDbSetting(self::PROXY_PASSWORD_SETTING_NAME, '');
  
          $this->addDbSetting(self::QUICK_LAUNCH_SETTING_NAME, "showDesktop");
          $this->addDbSetting(self::LOG_LEVEL_SETTING_NAME, 50); // Gpf_Log::CRITICAL - hardcoded because of optimalization
  
          $this->addDbSetting(self::NOT_FORCE_EMAIL_USERNAMES, Gpf::NO);
          $this->addDbSetting(self::AUTO_DELETE_EVENTS, 7);
          $this->addDbSetting(self::AUTO_DELETE_EVENTS_RECORDS_NUM, 500000);
  
          $this->addDbSetting(self::AUTO_DELETE_LOGINSHISTORY, 30);
  
          $this->addDbSetting(self::DEFAULT_COUNTRY, 'US');
          
          $this->addDbSetting(self::CRON_RUN_INTERVAL, '5');
  
          $this->addDbSetting(self::BRANDING_TEXT_BASE_LINK, 'http://www.qualityunit.com');
          $this->addDbSetting(self::BRANDING_FAVICON, '');
          $this->addDbSetting(self::BRANDING_QUALITYUNIT_ADDONS_LINK, 'http://addons.qualityunit.com');
          $this->addDbSetting(self::BRANDING_QUALITYUNIT_COMPANY_LINK,'http://www.qualityunit.com/company/');
          $this->addDbSetting(self::BRANDING_QUALITYUNIT_PRIVACY_POLICY_LINK,'http://www.qualityunit.com/company/privacy-policy-quality-unit');
          $this->addDbSetting(self::BRANDING_QUALITYUNIT_CONTACT_US_LINK,'http://www.qualityunit.com/company/contact-us');
          $this->addDbSetting(self::BRANDING_QUALITY_UNIT,'Quality Unit');
          $this->addDbSetting(self::BRANDING_QUALITYUNIT_SUPPORT_EMAIL, 'support@qualityunit.com');
  
  
          $this->addDbSetting(self::PASSWORD_MIN_LENGTH, 1);
          $this->addDbSetting(self::PASSWORD_MAX_LENGTH, 60);
          $this->addDbSetting(self::PASSWORD_LETTERS, 'N');
          $this->addDbSetting(self::PASSWORD_DIGITS, 'N');
          $this->addDbSetting(self::PASSWORD_SPECIAL, 'N');
  
          $this->addDbSetting(self::REGIONAL_SETTINGS_IS_DEFAULT, Gpf::YES);
          $this->addDbSetting(self::REGIONAL_SETTINGS_DATE_FORMAT, self::DEFAULT_DATE_FORMAT);
          $this->addDbSetting(self::REGIONAL_SETTINGS_TIME_FORMAT, self::DEFAULT_TIME_FORMAT);
          $this->addDbSetting(self::REGIONAL_SETTINGS_THOUSANDS_SEPARATOR, self::DEFAULT_THOUANDS_SEPARATOR);
          $this->addDbSetting(self::REGIONAL_SETTINGS_DECIMAL_SEPARATOR, self::DEFAULT_DECIMAL_SEPARATOR);
  
          $this->addDbSetting(self::SIDEBAR_DEFAULT_ONTOP, Gpf::NO);
  
          Gpf_Plugins_Engine::extensionPoint('Core.defineSettings', $this);
      }
  }

} //end Gpf_Settings_Gpf

if (!class_exists('Pap_Settings', false)) {
  class Pap_Settings extends Gpf_Settings_Gpf {
  
      const PARAM_NAME_USER_ID = 'param_name_user_id';
      const PARAM_NAME_BANNER_ID = 'param_name_banner_id';
      const PARAM_NAME_CAMPAIGN_ID = 'param_name_campaign_id';
      const PARAM_NAME_ROTATOR_ID = 'param_name_rotator_id';
      const PARAM_NAME_EXTRA_DATA = 'param_name_extra_data';
      const PARAM_NAME_DESTINATION_URL = 'param_name_dest_url';
  
      /* Default param names */
      const PARAM_AFFILIATE_ID_DEFAULT = 'a_aid';
      const PARAM_BANNER_ID_DEFAULT = 'a_bid';
      const PARAM_CAMPAIGN_ID_DEFAULT = 'a_cid';
      const PARAM_ROTATOR_ID_DEFAULT = 'a_rid';
      const PARAM_EXTRA_DATA_DEFAULT = 'data';
      const PARAM_DESTINATION_URL_DEFAULT = 'desturl';
  
      const PROGRAM_NAME = "programName";
      const PROGRAM_LOGO = "programLogo";
      const DEFAULT_MERCHANT_PANEL_THEME = 'defaultMerchantPanelTheme';
      const DEFAULT_AFFILIATE_PANEL_THEME = 'defaultAffiliatePanelTheme';
      const DEFAULT_AFFILIATE_SIGNUP_THEME = 'defaultAffiliateSignupTheme';
      const DEBUG_TYPES ='debug_types';
      const DELETE_COOKIE ='delete_cookie';
      const P3P_POLICY_COMPACT ='p3p_policy_compact';
      const URL_TO_P3P ='url_to_p3p';
      const OVERWRITE_COOKIE ='overwrite_cookie';
      const MAIN_SITE_URL ='mainSiteUrl';
      const GPF_VERSION = 'gpf_version';
      const PAP_VERSION = 'pap_version';
      const WELCOME_MESSAGE = 'welcomeMessage';
      const MULTIPLE_CURRENCIES = "multipleCurrencies";
      const COOKIE_DOMAIN = 'cookie_domain';
  
      const BRANDING_TEXT = "brandingText";
      const BRANDING_KNOWLEDGEBASE_LINK = "knowledgebase_url";
      const BRANDING_POST_AFFILIATE_PRO_HELP_LINK = "post_affiliate_pro_help";
      const BRANDING_QUALITYUNIT_POSTAFFILIATEPRO_LINK = "quality_unit_postaffiliate_link";
      const BRANDING_QUALITYUNIT_CHANGELOG_LINK = "qualityunit_changelog";
      const BRANDING_QUALITYUNIT_PAP = "qualityunit_pap";
      const BRANDING_TEXT_POST_AFFILIATE_PRO = "post_affiliate_pro";
      const BRANDING_TUTORIAL_VIDEOS_BASE_LINK = "qualityunit_tutorial_link";
      const BRANDING_TUTORIAL_VIDEOS_ENABLED = "qualityunit_tutorial_videos_enabled";
  
      const GETTING_STARTED_CHECKS = 'gettingStartedChecks';
      const GETTING_STARTED_SHOW = 'gettingStartedShow';
      const DEFAULT_PAYOUT_METHOD = 'defaultPayoutMethod';
      const AFFILIATE_APPROVAL = 'affiliate_approval';
      const AFFILIATE_LOGOUT_URL = 'affiliate_logout_url';
      const AFFILIATE_MENU = "affiliateMenu";
      const AFFILIATE_AFTER_LOGIN_SCREEN = 'affiliate_after_login_screen';
      const AFFILIATE_AFTER_LOGIN_SCREEN_MAXIMIZE = 'affiliate_after_login_screen_maximize';
      const EMPTY_MENU = "[]";
      const SUPPORT_DIRECT_LINKING = 'support_direct_linking';
      const SUPPORT_SHORT_ANCHOR_LINKING = 'support_short_anchor_linking';
      const DEFAULT_MERCHANT_ID = 'default_merchant_id';
      const DEFAULT_MERCHANT_ID_VALUE = '11112222';
      const TIERS_VISIBLE_TO_AFFILIATE = 'tiers_visible_to_affiliate';
      const AFFILIATE_CANNOT_CHANGE_HIS_USERNAME = 'affiliate_cannot_change_his_username';
      const POST_SIGNUP_TYPE_SETTING_NAME = "postSignupType";
      const POST_SIGNUP_URL_SETTING_NAME = "postSignupUrl";
      const SIGNUP_TERMS_SETTING_NAME = "termsAndConditions";
      const FORCE_TERMS_ACCEPTANCE_SETTING_NAME = "forceTermsAcceptance";
      const INCLUDE_PAYOUT_OPTIONS = "includePayoutOptions";
      const PAYOUT_OPTIONS = "payoutOptions";
      const FORCE_PAYOUT_OPTION = "forcePayoutOption";
      const OPTIONAL_PAYOUT_FIELDS = "optionalPayoutFields";
      const ASSIGN_NON_REFERRED_AFFILIATE_TO = "assignNonReferredAffiliateTo";
      const AUTO_DELETE_RAWCLICKS = "deleterawclicks";
      const AUTO_DELETE_EXPIRED_VISITORS = "deleteExpiredVisitors";
      const ALLOW_COMPUTE_NEGATIVE_COMMISSION = "allowComputeNegativeCommission";
  
      const FLASH_BANNER_DEFAULT_FORMAT = '<object width="{$width}" height="{$height}">
    <param name="movie" value="{$flashurl}?clickTAG={$targeturl_encoded}">
    <param name="menu" value="false"/>
    <param name="quality" value="medium"/>
    <param name="wmode" value="{$wmode}"/>
    <embed src="{$flashurl}?clickTAG={$targeturl_encoded}" width="{$width}" height="{$height}" loop="{$loop}" menu="false" swLiveConnect="FALSE" wmode="{$wmode}" allowscriptaccess="always"></embed>
  </object>
  {$impression_track}';
      const FLASH_BANNER_FORMAT_SETTING_NAME = "BannerFormatFlash";
  
      const IMAGE_BANNER_DEFAULT_FORMAT = '<a href="{$targeturl}" target="{$target_attribute}"><img src="{$image_src}" alt="{$alt}" title="{$alt}" width="{$width}" height="{$height}" /></a>{$impression_track}';
      const IMAGE_BANNER_FORMAT_SETTING_NAME = "BannerFormatImagebanner";
  
      const TEXT_BANNER_DEFAULT_FORMAT = '<a href="{$targeturl}" target="{$target_attribute}"><strong>{$title}</strong><br/>{$description}</a>{$impression_track}';
      const TEXT_BANNER_FORMAT_SETTING_NAME = "BannerFormatTextlink";
  
      const PAYOUT_INVOICE = 'payout_invoice';
      const GENERATE_INVOICES = 'generate_invoices';
      const SEND_PAYMENT_TO_AFFILIATE = 'send_payment_to_affiliate';
      const SEND_GENERATED_INVOICES_TO_MERCHANT = 'send_generated_invoices_to_merchant';
      const SEND_GENERATED_INVOICES_TO_AFFILIATE = 'send_generated_invoices_to_affiliates';
      const INVOICE_BCC_RECIPIENT = 'invoice_bcc_recipient';
      const DEFAULT_INVOICE = '<b>Invoice Number:</b> {$invoicenumber}<br/>
      <b>Invoice date:</b> {$date}<br/>
      <br/>
      <b>Affiliate Details:</b><br/>
      {$firstname} {$lastname} ({$username})<br/>
      {$data2}{*##Company name##*}<br/>
      {$data3}{*##Street##*}<br/>
      {$data7}{*##Zipcode##*} {$data4}{*##City##*}<br/>
      {$data5}{*##State##*} {$data6}{*##Country##*}<br/>
      <br/>
      <b>Payment Details:</b> Affiliate commissions<br/>
      Amount: {$payoutcurrency}{$payment}<br/>
      VAT ({$vat_percentage}%): {$payoutcurrency}{$payment_vat_part}<br/>
      <br/>
      <b>Note:</b><br/>
      {$affiliate_note}<br/>';
  
      const AFF_NOTIFICATION_ON_SUBAFF_SALE_DEFAULT_SETTING_NAME = 'aff_notification_on_subaff_sale_default';
      const AFF_NOTIFICATION_ON_SUBAFF_SALE_ENABLED_SETTING_NAME = 'aff_notification_on_subaff_sale_enabled';
  
      const AFF_NOTIFICATION_ON_CHANGE_COMMISSION_STATUS_DEFAULT_SETTING_NAME = 'aff_notification_on_change_comm_status_default';
      const AFF_NOTIFICATION_ON_CHANGE_COMMISSION_STATUS_ENABLED_SETTING_NAME = 'aff_notification_on_change_comm_status_enabled';
      const AFF_NOTIFICATION_ON_CHANGE_COMMISSION_STATUS_OPTION_STATUS = 'aff_notification_on_change_comm_status_option';
  
      const AFF_NOTIFICATION_ON_NEW_SALE_ENABLED_SETTING_NAME = 'aff_notification_on_new_sale_enabled';
      const AFF_NOTIFICATION_ON_NEW_SALE_STATUS = 'aff_notification_on_new_sale_status';
      const AFF_NOTIFICATION_ON_NEW_SALE_DEFAULT_SETTING_NAME = 'aff_notification_on_new_sale_default';
      const AFF_NOTIFICATION_ON_DIRECT_LINK_DEFAULT = 'aff_notification_on_direct_link_default';
      const AFF_NOTIFICATION_ON_DIRECT_LINK_ENABLED = 'aff_notification_on_direct_link_enabled';
  
      const AFF_NOTIFICATION_ON_SUBAFF_SIGNUP_DEFAULT_SETTING_NAME = 'aff_notification_on_subaff_signup_default';
      const AFF_NOTIFICATION_ON_SUBAFF_SIGNUP_ENABLED_SETTING_NAME = 'aff_notification_on_subaff_signup_enabled';
      
      const RECAPTCHA_PRIVATE_KEY = 'recaptcha_private_key';
      const RECAPTCHA_PUBLIC_KEY = 'recaptcha_public_key';
      const RECAPTCHA_ENABLED = 'recaptcha_enabled';
      const RECAPTCHA_THEME = 'recaptcha_theme';
      const RECAPTCHA_ACCOUNT_ENABLED = 'recaptcha_account_enabled';
      const RECAPTCHA_ACCOUNT_THEME = 'recaptcha_account_theme';
      
      const ACCOUNT_DEFAULT_CAMPAIGN_PRIVATE = 'account_default_campaign_private';
      
      const AFF_SEND_EMAILS_PER_MINUTE_SETTING_NAME = 'aff_send_emails_per_minute';
      const MAIL_TO_FRIEND_ALLOW_TO_USE_SYSTEM_EMAIL = 'MailToFriendAllowToUseSystemEmail';
  
      const NOTIFICATION_NEW_USER_SETTING_NAME = 'notification_new_user';
      const NOTIFICATION_ON_SALE = 'notification_on_sale';
      const NOTIFICATION_ON_SALE_STATUS = 'notification_on_sale_status';
      const NOTIFICATION_NEW_DIRECT_LINK = 'notification_new_direct_link';
  
      const NOTIFICATION_PAY_DAY_REMINDER = 'notification_pay_day_reminder';
      const NOTIFICATION_PAY_DAY_REMINDER_DAY_OF_MONTH = 'notification_pay_day_reminder_day_of_month';
      const NOTIFICATION_PAY_DAY_REMINDER_RECURRENCE_MONTH = 'notification_pay_day_reminder_recurrence_month';
  
      const NOTIFICATION_DAILY_REPORT = "notification_daily_report";
      const NOTIFICATION_WEEKLY_REPORT = "notification_weekly_report";
      const NOTIFICATION_WEEKLY_REPORT_START_DAY = "notification_weekly_report_start_day";
      const NOTIFICATION_WEEKLY_REPORT_SENT_ON = "notification_weekly_report_sent_on";
      const NOTIFICATION_MONTHLY_REPORT = "notification_monthly_report";
      const NOTIFICATION_MONTHLY_REPORT_SENT_ON = "notification_monthly_report_sent_on";    
  
      const AFF_NOTIFICATION_DAILY_REPORT_ENABLED = 'aff_notification_daily_report_enabled';
      const AFF_NOTIFICATION_WEEKLY_REPORT_ENABLED = 'aff_notification_weekly_report_enabled';
      const AFF_NOTIFICATION_MONTHLY_REPORT_ENABLED = 'aff_notification_monthly_report_enabled';
      const AFF_NOTIFICATION_DAILY_REPORT_DEFAULT = 'aff_notification_daily_report_default';
      const AFF_NOTIFICATION_WEEKLY_REPORT_DEFAULT = 'aff_notification_weekly_report_default';
      const AFF_NOTIFICATION_MONTHLY_REPORT_DEFAULT = 'aff_notification_monthly_report_default';
      
      const REPORTS_MAX_TRANSACTIONS_COUNT = 'notification_report_maxtransactions';
  
      const NOTIFICATION_ON_JOIN_TO_CAMPAIGN = 'notification_on_join_to_campaign';
      const NOTIFICATION_ON_COMMISSION_APPROVED = 'notification_on_commission_approved';
      const AFF_NOTIFICATION_ON_CHANGE_STATUS_FOR_CAMPAIGN = 'aff_notification_on_change_status_for_campaign';
      const AFF_NOTIFICATION_CAMPAIGN_INVITATION = 'aff_notification_campaign_invitation';
  
  
      const AFF_NOTOFICATION_BEFORE_APPROVAL = 'aff_notification_before_approval';
      const AFF_NOTOFICATION_SIGNUP_APPROVED_DECLINED = 'aff_notification_signup_approved_declined';
  
      const IP_VALIDITY_FORMAT_SETTING_NAME = 'ip_validity_format';
      const IP_VALIDITY_SETTING_NAME = 'ip_validity';
      const TRACK_BY_IP_SETTING_NAME = 'track_by_ip';
      const SAVE_UNREFERED_SALE_LEAD_SETTING_NAME = 'save_unrefered_sale_lead';
      const DEFAULT_AFFILIATE_SETTING_NAME = 'default_affiliate';
      const FORCE_CHOOSING_PRODUCTID_SETTING_NAME = 'force_choosing_productid';
  
      const PAYOUTS_MINIMUM_PAYOUT_SETTING_NAME = 'payouts_minimum_payout';
      const PAYOUTS_PAYOUT_OPTIONS_SETTING_NAME = 'payouts_payout_options';
      const DEFAULT_PAYOUT_OPTIONS = "100,200,300,400,500";
      const DEFAULT_MINIMUM_PAYOUT = "300";
  
      const MOD_REWRITE_PREFIX_SETTING_NAME = 'modrewrite_prefix';
      const MOD_REWRITE_SEPARATOR_SETTING_NAME = 'modrewrite_separator';
      const MOD_REWRITE_SUFIX_SETTING_NAME = 'modrewrite_suffix';
  
      const DEFAULT_PREFIX = 'ref/';
      const DEFAULT_SEPARATOR = '/';
      const DEFAULT_SUFFIX = '.html';
  
      const REPEATING_SIGNUPS_SETTING_NAME = 'repeating_signups';
      const REPEATING_SIGNUPS_ACTION_SETTING_NAME = 'repeating_signups_action';
      const REPEATING_SIGNUPS_SECONDS_SETTING_NAME = 'repeating_signups_seconds';
      const REPEATING_CLICKS_SETTING_NAME = 'repeating_clicks';
      const DUPLICATE_ORDERS_IP_SETTING_NAME = 'duplicate_orders_ip';
      const APPLY_TO_EMPTY_ID_SETTING_NAME = 'aplly_to_empty_orders_id';
      const DUPLICATE_ORDERS_ID_SETTING_NAME = 'duplicate_orders_id';
      const DUPLICATE_ORDERS_ID_ACTION_SETTING_NAME = 'duplicate_orders_id_action';
      const DUPLICATE_ORDERS_IP_SECONDS_SETTING_NAME = 'duplicate_orders_ip_seconds';
      const DUPLICATE_ORDERS_ID_MESSAGE_SETTING_NAME = 'duplicate_orders_id_message';
      const DUPLICATE_ORDERS_IP_ACTION_SETTING_NAME = 'duplicate_orders_ip_action';
      const DUPLICATE_ORDERS_IP_SAMECAMPAIGN_SETTING_NAME = 'duplicate_orders_ip_samecampaign';
      const DUPLICATE_ORDERS_IP_SAMEORDERID_SETTING_NAME = 'duplicate_orders_ip_sameorderid';
      const DUPLICATE_ORDER_ID_HOURS_SETTING_NAME = 'duplicate_order_id_hours';
      const DUPLICATE_ORDERS_IP_MESSAGE_SETTING_NAME = 'duplicate_orders_ip_message';
      const REPEATING_CLICKS_ACTION_SETTING_NAME = 'repeating_clicks_action';
      const REPEATING_CLICKS_SECONDS_SETTING_NAME = 'repeating_clicks_seconds';
      const REPEATING_BANNER_CLICKS = 'repeating_banner_clicks';
  
      const BANNEDIPS_CLICKS_FROM_IFRAME = 'bannedips_clicks_from_iframe';
      const BANNEDIPS_CLICKS = 'bannedips_clicks';
      const BANNEDIPS_LIST_CLICKS = 'bannedips_list_clicks';
      const BANNEDIPS_CLICKS_ACTION = 'bannedips_clicks_action';
      const BANNEDIPS_SALES = 'bannedips_sales';
      const BANNEDIPS_LIST_SALES = 'bannedips_list_sales';
      const BANNEDIPS_SALES_ACTION = 'bannedips_sales_action';
      const BANNEDIPS_SALES_MESSAGE = 'bannedips_sales_message';
      const BANNEDIPS_SIGNUPS = 'bannedips_signups';
      const BANNEDIPS_LIST_SIGNUPS = 'bannedips_list_signups';
      const BANNEDIPS_SIGNUPS_ACTION = 'bannedips_signups_action';
  
      const GEOIP_CLICKS = 'geoip_clicks';
      const GEOIP_CLICKS_BLACKLIST = 'clicks_countries_blacklist';
      const GEOIP_CLICKS_BLACKLIST_ACTION = 'clicks_countries_blacklist_action';
      const GEOIP_SALES = 'geoip_sales';
      const GEOIP_SALES_BLACKLIST = 'sales_countries_blacklist';
      const GEOIP_SALES_BLACKLIST_ACTION = 'sales_countries_blacklist_action';
      const GEOIP_AFFILIATES = 'geoip_affiliates';
      const GEOIP_AFFILIATES_BLACKLIST = 'affiliates_countries_blacklist';
      const GEOIP_AFFILIATES_BLACKLIST_ACTION = 'affiliates_countries_blacklist_action';
      const GEOIP_IMPRESSIONS_DISABLED = 'geoip_impressions_disabled';
  
      const DEFAULT_REPEATING_CLICKS = "N";
      const DEFAULT_REPEATING_CLICKS_SECONDS = 0;
      const DEFAULT_REPEATING_CLICKS_ACTION = "D";
      const DEFAULT_REPEATING_SIGNUPS = "N";
      const DEFAULT_REPEATING_SIGNUPS_SECONDS = 0;
      const DEFAULT_REPEATING_SIGNUPS_ACTION = "DS";
      const DEFAULT_DUPLICATE_ORDERS_IP = "N";
      const DEFAULT_DUPLICATE_ORDERS_IP_ACTION = "D";
      const DEFAULT_DUPLICATE_ORDERS_IP_SECONDS = "";
      const DEFAULT_DUPLICATE_ORDERS_IP_MESSAGE = "";
      const DEFAULT_DUPLICATE_ORDERS_IP_SAMECAMPAIGN = "N";
      const DEFAULT_DUPLICATE_ORDERS_IP_SAMEORDERID = "N";
      const DEFAULT_DUPLICATE_ORDERS_ID = "N";
      const DEFAULT_DUPLICATE_ORDERS_ID_ACTION = "D";
      const DEFAULT_DUPLICATE_ORDERS_ID_MESSAGE = "";
      const DEFAULT_DUPLICATE_ORDERS_ID_IN_HOURS = "N";
      const DEFAULT_DUPLICATE_ORDERS_ID_HOURS = "";
      const DEFAULT_APPLY_TO_EMPTY_ORDERS_ID = "";
  
      const SETTING_LINKING_METHOD = 'linking_method';
  
      const PAYOUT_INVOICE_WITH_VAT_SETTING_NAME = 'payout_invoice_with_vat';
      const VAT_COMPUTATION_SETTING_NAME = 'vat_computation';
      const VAT_PERCENTAGE_SETTING_NAME = 'vat_percentage';
      const SUPPORT_VAT_SETTING_NAME = 'support_vat';
  
      const SIGNUP_BONUS = 'signupBonus';
  
      const MATRIX_HEIGHT = 'matrix_height';
      const MATRIX_WIDTH = 'matrix_width';
      const FULL_FORCED_MATRIX = 'full_forced_matrix';
      const MATRIX_SPILLOVER = 'matrix_spillover';
      const MATRIX_AFFILIATE = 'matrix_affiliate';
      const MATRIX_EXPAND_HEIGHT = 'matrixExpandHeight';
      const MATRIX_EXPAND_WIDTH = 'matrixExpandWidth';
      const MATRIX_FILL_BONUS = 'matrixFillBonus';
      const MATRIX_OTHER_FILL_BONUS = 'matrixOtherFillBonus';
  
      const MATRIX_HEIGHT_DEFAULT_VALUE = 0;
      const MATRIX_WIDTH_DEFAULT_VALUE = 0;
      const MATRIX_EXPAND_HEIGHT_DEFAULT_VALUE = 1;
      const MATRIX_EXPAND_WIDTH_DEFAULT_VALUE = 0;
      const MATRIX_FILL_BONUS_DEFAULT_VALUE = 0;
      const MATRIX_OTHER_FILL_BONUS_DEFAULT_VALUE = 0;
  
      const NOT_SET_PARENT_AFFILIATE = 'notSetParentAffiliate';
  
      const IMPRESSIONS_TABLE_INPUT = 'impTableInput';
      const IMPRESSIONS_TABLE_PROCESS = 'impTableProcess';
  
      const VISITS_TABLE_INPUT = 'visitsTableInput';
      const VISITS_TABLE_PROCESS = 'visitsTableProcess';
      const VISIT_OFFLINE_PROCESSING_DISABLE = 'offlineVisitProcessingDisabled';
      const ONLINE_SALE_PROCESSING = 'onlineSaleProcessing';
  
      const MERCHANT_NOTIFICATION_EMAIL = 'merchant_notification_email';
      const LAST_BILLING_DATE = 'last_billing_date';
  
      protected function defineFileSettings() {
          parent::defineFileSettings();
          $this->addFileSetting(self::PARAM_NAME_USER_ID, self::PARAM_AFFILIATE_ID_DEFAULT);
          $this->addFileSetting(self::PARAM_NAME_BANNER_ID, self::PARAM_BANNER_ID_DEFAULT);
          $this->addFileSetting(self::PARAM_NAME_CAMPAIGN_ID, self::PARAM_CAMPAIGN_ID_DEFAULT);
          $this->addFileSetting(self::PARAM_NAME_ROTATOR_ID, self::PARAM_ROTATOR_ID_DEFAULT);
          $this->addFileSetting(self::PARAM_NAME_EXTRA_DATA, self::PARAM_EXTRA_DATA_DEFAULT);
          $this->addFileSetting(self::PARAM_NAME_EXTRA_DATA . '1', self::PARAM_EXTRA_DATA_DEFAULT . '1');
          $this->addFileSetting(self::PARAM_NAME_EXTRA_DATA . '2', self::PARAM_EXTRA_DATA_DEFAULT . '2');
          $this->addFileSetting(self::PARAM_NAME_DESTINATION_URL, self::PARAM_DESTINATION_URL_DEFAULT);
  
          $this->addFileSetting(self::DEBUG_TYPES, '');
          $this->addFileSetting(self::DELETE_COOKIE, 'N');
          $this->addFileSetting(self::P3P_POLICY_COMPACT, 'NOI NID ADMa DEVa PSAa OUR BUS ONL UNI COM STA OTC');
          $this->addFileSetting(self::URL_TO_P3P, '');
          $this->addFileSetting(self::OVERWRITE_COOKIE, 'N');
          $this->addFileSetting(self::COOKIE_DOMAIN, $this->getDefaultCookieDomainValidity());
  
          $this->addFileSetting(self::IMPRESSIONS_TABLE_INPUT, 0);
          $this->addFileSetting(self::IMPRESSIONS_TABLE_PROCESS, 2);
  
          $this->addFileSetting(self::VISITS_TABLE_INPUT, 0);
          $this->addFileSetting(self::VISITS_TABLE_PROCESS, 2);
          $this->addFileSetting(self::BANNEDIPS_CLICKS_FROM_IFRAME, Gpf::NO);
          $this->addFileSetting(self::VISIT_OFFLINE_PROCESSING_DISABLE, '');
          $this->addFileSetting(self::ONLINE_SALE_PROCESSING, '');
      }
  
      protected function defineDbSettings() {
          $this->addDbSetting(self::BRANDING_TEXT, Pap_Branding::DEFAULT_BRANDING_TEXT);
          $this->addDbSetting(self::DEFAULT_MERCHANT_PANEL_THEME, Pap_Branding::DEFAULT_MERCHANT_PANEL_THEME);
          $this->addDbSetting(self::DEFAULT_AFFILIATE_PANEL_THEME, Pap_Branding::DEFAULT_AFFILIATE_PANEL_THEME);
          $this->addDbSetting(self::DEFAULT_AFFILIATE_SIGNUP_THEME, Pap_Branding::DEFAULT_SIGNUP_THEME);
          $this->addDbSetting(self::PROGRAM_NAME, Gpf_Lang::_runtime('Affiliate program'));
          $this->addDbSetting(self::PROGRAM_LOGO);
          $this->addDbSetting(self::WELCOME_MESSAGE, Gpf_Lang::_runtime('Welcome to affiliate program'));
          $this->addDbSetting(self::GETTING_STARTED_CHECKS, '');
          $this->addDbSetting(self::GETTING_STARTED_SHOW, GPF::YES);
  
          $this->addDbSetting(self::MAIN_SITE_URL, '');
          $this->addDbSetting(self::DEFAULT_PAYOUT_METHOD, '');
          //TODO: extract 'M' to const
          $this->addDbSetting(self::AFFILIATE_APPROVAL, 'M');
          $this->addDbSetting(self::AFFILIATE_LOGOUT_URL, '../index.php');
          $this->addDbSetting(self::AFFILIATE_AFTER_LOGIN_SCREEN, 'Home');
          $this->addDbSetting(self::AFFILIATE_AFTER_LOGIN_SCREEN_MAXIMIZE, Gpf::NO);
          $this->addDbSetting(self::TIERS_VISIBLE_TO_AFFILIATE, -1);
          $this->addDbSetting(self::AFFILIATE_CANNOT_CHANGE_HIS_USERNAME, Gpf::NO);
          $this->addDbSetting(self::AFFILIATE_MENU, self::EMPTY_MENU);
          $this->addDbSetting(self::SUPPORT_DIRECT_LINKING, Gpf::YES);
          $this->addDbSetting(self::SUPPORT_SHORT_ANCHOR_LINKING, Gpf::NO);
          $this->addDbSetting(self::GPF_VERSION, '');
          $this->addDbSetting(self::PAP_VERSION, '');
  
          $this->addDbSetting(self::MULTIPLE_CURRENCIES, Gpf::NO);
          $this->addDbSetting(self::SIGNUP_TERMS_SETTING_NAME, '');
          $this->addDbSetting(self::POST_SIGNUP_TYPE_SETTING_NAME, 'page');
          $this->addDbSetting(self::POST_SIGNUP_URL_SETTING_NAME, '');
          $this->addDbSetting(self::FORCE_TERMS_ACCEPTANCE_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::INCLUDE_PAYOUT_OPTIONS, Gpf::NO);
          $this->addDbSetting(self::PAYOUT_OPTIONS, 'A');
          $this->addDbSetting(self::FORCE_PAYOUT_OPTION, Gpf::NO);
          $this->addDbSetting(self::ASSIGN_NON_REFERRED_AFFILIATE_TO, '');
  
          $this->addDbSetting(self::FLASH_BANNER_FORMAT_SETTING_NAME, self::FLASH_BANNER_DEFAULT_FORMAT);
          $this->addDbSetting(self::IMAGE_BANNER_FORMAT_SETTING_NAME, self::IMAGE_BANNER_DEFAULT_FORMAT);
          $this->addDbSetting(self::TEXT_BANNER_FORMAT_SETTING_NAME, self::TEXT_BANNER_DEFAULT_FORMAT);
  
          $this->addDbSetting(self::GENERATE_INVOICES, Gpf::NO);
          $this->addDbSetting(self::SEND_GENERATED_INVOICES_TO_MERCHANT, Gpf::NO, true);
          $this->addDbSetting(self::SEND_GENERATED_INVOICES_TO_AFFILIATE, Gpf::NO);
          $this->addDbSetting(self::SEND_PAYMENT_TO_AFFILIATE, Gpf::NO);
          $this->addDbSetting(self::PAYOUT_INVOICE);
          $this->addDbSetting(self::INVOICE_BCC_RECIPIENT, '', true);
  
          $this->addDbSetting(self::NOTIFICATION_ON_SALE, Gpf::NO, true);
          $this->addDbSetting(self::NOTIFICATION_ON_SALE_STATUS, 'A,P,D', true);
          $this->addDbSetting(self::AFF_NOTOFICATION_BEFORE_APPROVAL, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTOFICATION_SIGNUP_APPROVED_DECLINED, Gpf::YES);
          $this->addDbSetting(self::NOTIFICATION_NEW_DIRECT_LINK, Gpf::NO, true);
  
          $this->addDbSetting(self::NOTIFICATION_PAY_DAY_REMINDER, Gpf::NO, true);
          $this->addDbSetting(self::NOTIFICATION_PAY_DAY_REMINDER_DAY_OF_MONTH, '15', true);
          $this->addDbSetting(self::NOTIFICATION_PAY_DAY_REMINDER_RECURRENCE_MONTH, '1', true);
  
          $this->addDbSetting(self::NOTIFICATION_DAILY_REPORT, Gpf::NO, true);
          $this->addDbSetting(self::NOTIFICATION_WEEKLY_REPORT, Gpf::NO, true);
          $this->addDbSetting(self::NOTIFICATION_WEEKLY_REPORT_START_DAY, '0', true);
          $this->addDbSetting(self::NOTIFICATION_WEEKLY_REPORT_SENT_ON, '0', true);
          $this->addDbSetting(self::NOTIFICATION_MONTHLY_REPORT, Gpf::NO, true);
          $this->addDbSetting(self::NOTIFICATION_MONTHLY_REPORT_SENT_ON, '1', true);
  
          $this->addDbSetting(self::AFF_NOTIFICATION_DAILY_REPORT_ENABLED, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_WEEKLY_REPORT_ENABLED, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_MONTHLY_REPORT_ENABLED, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_DAILY_REPORT_DEFAULT, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_WEEKLY_REPORT_DEFAULT, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_MONTHLY_REPORT_DEFAULT, Gpf::NO);
          
          $this->addDbSetting(self::REPORTS_MAX_TRANSACTIONS_COUNT, 1000);
  
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_NEW_SALE_ENABLED_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_NEW_SALE_DEFAULT_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_NEW_SALE_STATUS, 'A,P,D');
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_SUBAFF_SALE_ENABLED_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_SUBAFF_SALE_DEFAULT_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_CHANGE_COMMISSION_STATUS_OPTION_STATUS, 'A,P,D');
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_CHANGE_COMMISSION_STATUS_DEFAULT_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_CHANGE_COMMISSION_STATUS_ENABLED_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_DIRECT_LINK_ENABLED, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_DIRECT_LINK_DEFAULT, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_SUBAFF_SIGNUP_DEFAULT_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_SUBAFF_SIGNUP_ENABLED_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::NOTIFICATION_NEW_USER_SETTING_NAME, Gpf::NO, true);
          $this->addDbSetting(self::MERCHANT_NOTIFICATION_EMAIL, '', true);
  
          $this->addDbSetting(self::NOTIFICATION_ON_JOIN_TO_CAMPAIGN, Gpf::NO, true);
          $this->addDbSetting(self::NOTIFICATION_ON_COMMISSION_APPROVED, Gpf::NO, true);
          $this->addDbSetting(self::AFF_NOTIFICATION_ON_CHANGE_STATUS_FOR_CAMPAIGN, Gpf::NO);
          $this->addDbSetting(self::AFF_NOTIFICATION_CAMPAIGN_INVITATION, Gpf::YES);
          
          $this->addDbSetting(self::AFF_SEND_EMAILS_PER_MINUTE_SETTING_NAME, 30);
          $this->addDbSetting(self::MAIL_TO_FRIEND_ALLOW_TO_USE_SYSTEM_EMAIL, Gpf::YES);
          
          $this->addDbSetting(self::RECAPTCHA_ENABLED, Gpf::NO);
          $this->addDbSetting(self::RECAPTCHA_THEME, 'white');
          $this->addDbSetting(self::RECAPTCHA_PRIVATE_KEY, '');
          $this->addDbSetting(self::RECAPTCHA_PUBLIC_KEY, '');
          $this->addDbSetting(self::RECAPTCHA_ACCOUNT_ENABLED, Gpf::NO);
          $this->addDbSetting(self::RECAPTCHA_ACCOUNT_THEME, 'white');
  
          $this->addDbSetting(self::ACCOUNT_DEFAULT_CAMPAIGN_PRIVATE, Gpf::NO);
  
          $this->addDbSetting(self::SAVE_UNREFERED_SALE_LEAD_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::TRACK_BY_IP_SETTING_NAME, Gpf::YES);
          $this->addDbSetting(self::IP_VALIDITY_SETTING_NAME, 2);
          $this->addDbSetting(self::IP_VALIDITY_FORMAT_SETTING_NAME, 'D');
          $this->addDbSetting(self::DEFAULT_AFFILIATE_SETTING_NAME, '');
          $this->addDbSetting(self::FORCE_CHOOSING_PRODUCTID_SETTING_NAME, Gpf::NO);
  
          $this->addDbSetting(self::PAYOUTS_PAYOUT_OPTIONS_SETTING_NAME, self::DEFAULT_PAYOUT_OPTIONS);
          $this->addDbSetting(self::PAYOUTS_MINIMUM_PAYOUT_SETTING_NAME, self::DEFAULT_MINIMUM_PAYOUT);
  
          $this->addDbSetting(self::MOD_REWRITE_PREFIX_SETTING_NAME, self::DEFAULT_PREFIX);
          $this->addDbSetting(self::MOD_REWRITE_SEPARATOR_SETTING_NAME, self::DEFAULT_SEPARATOR);
          $this->addDbSetting(self::MOD_REWRITE_SUFIX_SETTING_NAME, self::DEFAULT_SUFFIX);
  
          $this->addDbSetting(self::REPEATING_CLICKS_ACTION_SETTING_NAME, self::DEFAULT_REPEATING_CLICKS_ACTION);
          $this->addDbSetting(self::REPEATING_CLICKS_SECONDS_SETTING_NAME, self::DEFAULT_REPEATING_CLICKS_SECONDS);
          $this->addDbSetting(self::REPEATING_BANNER_CLICKS, Gpf::NO);
          $this->addDbSetting(self::REPEATING_CLICKS_SETTING_NAME, self::DEFAULT_REPEATING_CLICKS);
          $this->addDbSetting(self::REPEATING_SIGNUPS_ACTION_SETTING_NAME, self::DEFAULT_REPEATING_SIGNUPS_ACTION);
          $this->addDbSetting(self::REPEATING_SIGNUPS_SECONDS_SETTING_NAME, self::DEFAULT_REPEATING_SIGNUPS_SECONDS);
          $this->addDbSetting(self::REPEATING_SIGNUPS_SETTING_NAME, self::DEFAULT_REPEATING_SIGNUPS);
          $this->addDbSetting(self::DUPLICATE_ORDERS_IP_MESSAGE_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_IP_MESSAGE);
          $this->addDbSetting(self::DUPLICATE_ORDER_ID_HOURS_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_ID_HOURS);
          $this->addDbSetting(self::DUPLICATE_ORDERS_IP_ACTION_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_IP_ACTION);
          $this->addDbSetting(self::DUPLICATE_ORDERS_ID_MESSAGE_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_ID_MESSAGE);
          $this->addDbSetting(self::DUPLICATE_ORDERS_IP_SECONDS_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_IP_SECONDS);
          $this->addDbSetting(self::DUPLICATE_ORDERS_ID_ACTION_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_ID_ACTION);
          $this->addDbSetting(self::DUPLICATE_ORDERS_ID_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_ID);
          $this->addDbSetting(self::APPLY_TO_EMPTY_ID_SETTING_NAME, self::DEFAULT_APPLY_TO_EMPTY_ORDERS_ID);
          $this->addDbSetting(self::DUPLICATE_ORDERS_IP_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_IP);
          $this->addDbSetting(self::DUPLICATE_ORDERS_IP_SAMECAMPAIGN_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_IP_SAMECAMPAIGN);
          $this->addDbSetting(self::DUPLICATE_ORDERS_IP_SAMEORDERID_SETTING_NAME, self::DEFAULT_DUPLICATE_ORDERS_IP_SAMEORDERID);
  
          $this->addDbSetting(self::SETTING_LINKING_METHOD, 'P');
          $this->addDbSetting(self::AUTO_DELETE_RAWCLICKS, '0');
          $this->addDbSetting(self::AUTO_DELETE_EXPIRED_VISITORS, Gpf::NO);
          $this->addDbSetting(self::ALLOW_COMPUTE_NEGATIVE_COMMISSION, Gpf::NO);
  
          $this->addDbSetting(self::SUPPORT_VAT_SETTING_NAME, Gpf::NO);
          $this->addDbSetting(self::VAT_PERCENTAGE_SETTING_NAME, '0');
          $this->addDbSetting(self::VAT_COMPUTATION_SETTING_NAME, 'D');
          $this->addDbSetting(self::PAYOUT_INVOICE_WITH_VAT_SETTING_NAME);
  
          $this->addDbSetting(self::SIGNUP_BONUS, 0);
  
          $this->addDbSetting(self::MATRIX_WIDTH, self::MATRIX_WIDTH_DEFAULT_VALUE);
          $this->addDbSetting(self::MATRIX_HEIGHT, self::MATRIX_HEIGHT_DEFAULT_VALUE);
          $this->addDbSetting(self::FULL_FORCED_MATRIX, Gpf::NO);
          $this->addDbSetting(self::MATRIX_SPILLOVER, 'S');
          $this->addDbSetting(self::MATRIX_AFFILIATE, '');
          $this->addDbSetting(self::DEFAULT_MERCHANT_ID, self::DEFAULT_MERCHANT_ID_VALUE);
          $this->addDbSetting(self::MATRIX_EXPAND_WIDTH, self::MATRIX_EXPAND_WIDTH_DEFAULT_VALUE);
          $this->addDbSetting(self::MATRIX_EXPAND_HEIGHT, self::MATRIX_EXPAND_HEIGHT_DEFAULT_VALUE);
          $this->addDbSetting(self::MATRIX_FILL_BONUS, self::MATRIX_FILL_BONUS_DEFAULT_VALUE);  
          $this->addDbSetting(self::MATRIX_OTHER_FILL_BONUS, self::MATRIX_OTHER_FILL_BONUS_DEFAULT_VALUE);         
  
          $this->addDbSetting(self::NOT_SET_PARENT_AFFILIATE, Gpf::NO);
  
          $this->addDbSetting(self::BRANDING_KNOWLEDGEBASE_LINK, Pap_Branding::DEFAULT_BRANDING_KNOWLEDGEBASE_LINK);
          $this->addDbSetting(self::BRANDING_POST_AFFILIATE_PRO_HELP_LINK, Pap_Branding::DEFAULT_BRANDING_POST_AFFILIATE_PRO_HELP_LINK);
          $this->addDbSetting(self::BRANDING_QUALITYUNIT_POSTAFFILIATEPRO_LINK, Pap_Branding::DEFAULT_BRANDING_QUALITYUNIT_POSTAFFILIATEPRO_LINK);
          $this->addDbSetting(self::BRANDING_QUALITYUNIT_CHANGELOG_LINK, Pap_Branding::DEFAULT_BRANDING_QUALITYUNIT_CHANGELOG_LINK);
          $this->addDbSetting(self::BRANDING_QUALITYUNIT_PAP, Pap_Branding::DEFAULT_BRANDING_QUALITYUNIT_PAP);
          $this->addDbSetting(self::BRANDING_TEXT_POST_AFFILIATE_PRO, Pap_Branding::DEFAULT_BRANDING_TEXT_POST_AFFILIATE_PRO);
          $this->addDbSetting(self::BRANDING_TUTORIAL_VIDEOS_BASE_LINK, Pap_Branding::DEFAULT_BRANDING_TUTORIAL_VIDEOS_BASE_LINK);
          $this->addDbSetting(self::BRANDING_TUTORIAL_VIDEOS_ENABLED, Gpf::YES);
  
          $this->addDbSetting(self::GEOIP_CLICKS, Gpf::NO);
          $this->addDbSetting(self::GEOIP_SALES, Gpf::NO);
          $this->addDbSetting(self::GEOIP_AFFILIATES, Gpf::NO);
          $this->addDbSetting(self::GEOIP_CLICKS_BLACKLIST, '');
          $this->addDbSetting(self::GEOIP_SALES_BLACKLIST, '');
          $this->addDbSetting(self::GEOIP_AFFILIATES_BLACKLIST, '');
          $this->addDbSetting(self::GEOIP_CLICKS_BLACKLIST_ACTION, 'D');
          $this->addDbSetting(self::GEOIP_SALES_BLACKLIST_ACTION, 'D');
          $this->addDbSetting(self::GEOIP_AFFILIATES_BLACKLIST_ACTION, 'D');
          $this->addDbSetting(self::GEOIP_IMPRESSIONS_DISABLED, Gpf::NO);
  
          $this->addDbSetting(self::BANNEDIPS_CLICKS, Gpf::NO);
          $this->addDbSetting(self::BANNEDIPS_SALES, Gpf::NO);
          $this->addDbSetting(self::BANNEDIPS_SIGNUPS, Gpf::NO);
          $this->addDbSetting(self::BANNEDIPS_CLICKS_ACTION, 'D');
          $this->addDbSetting(self::BANNEDIPS_SALES_ACTION, 'D');
          $this->addDbSetting(self::BANNEDIPS_SIGNUPS_ACTION, 'D');
          $this->addDbSetting(self::BANNEDIPS_LIST_CLICKS, '');
          $this->addDbSetting(self::BANNEDIPS_LIST_SALES, '');
          $this->addDbSetting(self::BANNEDIPS_LIST_SIGNUPS, '');
          $this->addDbSetting(self::BANNEDIPS_SALES_MESSAGE, '');
          $this->addDbSetting(self::LAST_BILLING_DATE, '');
  
          parent::defineDbSettings();
      }
  
      private function getDefaultCookieDomainValidity() {
          $host = @$_SERVER['HTTP_HOST'];
          if($host == '' || $host == 'localhost') {
              return '';
          }
  
          $requiredParts = 3;
          if(strpos($host, '.co.') != false) {
              $requiredParts = 4;
          }
  
          $pos = strpos($host, 'www.');
          if( $pos !== false && $pos === 0) {
              $host = substr($host, 3);
          }
  
          $parts = count(explode('.', $host));
  
          if($parts < $requiredParts) {
              $host = '.'.$host;
          }
  
          return $host;
      }
  
      protected function lazyInitDefaultValue($name) {
          
          switch($name) {
              case self::PROGRAM_LOGO:
                  $this->addDefaultValue(self::PROGRAM_LOGO, Gpf_Paths::getInstance()->getImageUrl('logo_pap.gif', 'signup'));
                  break;
              default:
                  try {
                      $file = new Gpf_Io_File(Gpf_Paths::getInstance()->getResourcePath($name .
                      '.' . Gpf_Settings_Define::LARGE_TEXT_SETTING_TEMPLATE_FILE_EXTENSION, Gpf_Settings_Define::LARGE_TEXT_SETTINGS_DIR, 'merchants'));
                  } catch (Gpf_ResourceNotFoundException $e) {
                      return;
                  }
                  $this->addDefaultValue($name, $file->getContents());
          }
      }
  }

} //end Pap_Settings

if (!class_exists('Pap_Branding', false)) {
  class Pap_Branding {
      const DEFAULT_BRANDING_KNOWLEDGEBASE_LINK = 'http://support.qualityunit.com/';
      const DEFAULT_BRANDING_POST_AFFILIATE_PRO_HELP_LINK = 'http://support.qualityunit.com/690072-Post-Affiliate-Pro';
      const DEFAULT_BRANDING_QUALITYUNIT_POSTAFFILIATEPRO_LINK = 'http://www.qualityunit.com/postaffiliatepro/';
      const DEFAULT_BRANDING_QUALITYUNIT_CHANGELOG_LINK = 'http://bugs.qualityunit.com/mantis/changelog_page.php?project_id=2';
      const DEFAULT_BRANDING_QUALITYUNIT_PAP = 'PAP';
      const DEFAULT_BRANDING_TEXT_POST_AFFILIATE_PRO = 'Post Affiliate Pro';
      const DEFAULT_BRANDING_TUTORIAL_VIDEOS_BASE_LINK = 'http://paphelp.qualityunit.com/pap4/';
      const DEFAULT_BRANDING_TEXT = '<a class="papCopyright" href="http://www.qualityunit.com/postaffiliatepro/" target="_blank">Affiliate Software by Post Affiliate Pro</a>';
      
      const DEMO_MERCHANT_USERNAME = 'merchant@example.com';
      const DEMO_AFFILIATE_USERNAME = 'affiliate@example.com';
      const DEMO_PASSWORD = 'demo';
      
      const DEFAULT_MERCHANT_PANEL_THEME = 'blue_aero';
      const DEFAULT_AFFILIATE_PANEL_THEME = 'classic_wide';
      const DEFAULT_SIGNUP_THEME = 'classic';
      
      const DEFAULT_LANGUAGE_CODE = 'en-US';
      
      public static function initDefaultCurrency(Gpf_Db_Currency $currency) {
          $currency->setId('usd00000');
          $currency->setName('USD');
          $currency->setSymbol('$');
          $currency->setPrecision(2);
          $currency->setWhereDisplay(Gpf_Db_Currency::DISPLAY_LEFT);
      }
  }

} //end Pap_Branding

if (!class_exists('Gpf_Settings_Base', false)) {
  class Gpf_Settings_Base extends Gpf_Object {
  
      const MAX_RETRIES = 50;
      
      /**
       *
       * @var Gpf_File_Settings
       */
      private $file;
  
      private $accountId = '';
  
      /**
       * @var Gpf_Settings_Define
       */
      private $defines;
  
      /**
       * @var Gpf_GlobalSettings instances
       */
      static protected $instances = array();
  
      /**
       * Settings cache
       *
       * @var array
       */
      protected $cache = array();
      
      /**
       * 
       * @var Gpf_Settings_Driver_Locker
       */
      protected $driver;
  
      protected function __construct(Gpf_Settings_Define $defines, $accountId) {
          $this->file = new Gpf_File_Settings($accountId);
          $this->accountId = $accountId;
          $this->defines = $defines;
          $this->createLockDriver();
      }
      
      private function createLockDriver() {
          if (strtoupper(substr(PHP_OS, 0, 3)) === 'SUN') {
              $this->driver = new Gpf_Settings_Driver_LockSunos();
              return;
          }
          if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
              $this->driver = new Gpf_Settings_Driver_LockWindows();
              return;
          }
          if (strtoupper(substr(PHP_OS, 0, 7)) === 'FREEBSD') {
              $this->driver = new Gpf_Settings_Driver_LockFreeBSD();
              return;
          }
          $this->driver = new Gpf_Settings_Driver_Lock();
      }
  
      /**
       * returns instance of Gpf_Settings class
       *
       * @return Gpf_Settings_Base
       */
      public static function getInstance(Gpf_Settings_Define $defines, $accountId) {
          if (!array_key_exists($accountId, self::$instances)) {
              self::$instances[$accountId] = new Gpf_Settings_Base($defines, $accountId);
          }
          return self::$instances[$accountId];
      }
  
      public function readSetting($name) {
          $this->defines->checkSetting($name);
  
          if ($this->defines->isFileSetting($name)) {
              $file = $this->lock(LOCK_SH);
              try {
                  $this->file->forceReload();
                  $this->loadFileSetting($name, $file);
              } catch (Exception $e){
                  $this->unlock($file);
                  throw $e;
              }
              $this->unlock($file);
              
          } else {
              //Load setting from cache if it is already loaded in cache
              if (array_key_exists($name, $this->cache)) {
                 return $this->cache[$name];
              }
              $this->loadDbSetting($name);
          }
          return $this->cache[$name];
      }
  
      public function writeSetting($name, $value) {
          $this->defines->checkSetting($name);
  
          if ($this->defines->isFileSetting($name)) {
              $file = $this->lock(LOCK_EX);
  
              try {
                  $this->file->forceReload();
                  $this->saveFileSetting($name, $value, $file);
              } catch (Exception $e){
                  $this->unlock($file);
                  throw $e;
              }
              $this->unlock($file);
              return;
          }
  
          if (array_key_exists($name, $this->cache) && ($this->cache[$name] == $value)) {
              Gpf_Log::debug($this->_('Setting %s have the same value %s, no change needed - skipping', $name, $value));
              return;
          }
          //store setting also to settings cache
          $this->cache[$name] = $value;
          $this->saveDbSetting($name, $value);
      }
  
      private function lock($operation) {
          return $this->driver->lock($this->file->getFileName(), $operation);
      }
  
      private function unlock($file){
          $this->driver->unlock($file);
      }
  
      /**
       * @return Gpf_Settings_Define
       */
      public function getSettingsDefine() {
          return $this->defines;
      }
  
      protected function saveDbSetting($name, $value) {
          Gpf_Db_Table_Settings::setSetting($name, $value, $this->accountId);
      }
  
      protected function saveFileSetting($name, $value, Gpf_Io_File $file) {
          $this->file->setSetting($name, $value, true, $file);
      }
  
      protected function loadDbSetting($name) {
          try {
              $this->cache[$name] = Gpf_Db_Table_Settings::getSetting($name, $this->accountId);
          } catch (Gpf_Settings_UnknownSettingException $e) {
              $this->cache[$name] = $this->defines->getDefaultValue($name);
          }
      }
      protected function loadFileSetting($name, Gpf_Io_File $file) {
          try {
              $this->cache[$name] = $this->file->getSetting($name, $file);
          } catch (Gpf_Settings_UnknownSettingException $e) {
              $this->cache[$name] = $this->defines->getDefaultValue($name);
          }
      }
  }

} //end Gpf_Settings_Base

if (!class_exists('Gpf_File_Settings', false)) {
  class Gpf_File_Settings extends Gpf_File_Config {
      const SETTING_FILE_NAME = 'settings.php';
      private $accountId;
  
      public function __construct($accountId = null) {
          $this->accountId = $accountId;
          if ($accountId === null) {
              $settingsDirectory = Gpf_Paths::getInstance()->getAccountsPath();
          } else {
              $settingsDirectory = Gpf_Paths::getInstance()->getAccountsPath() . $accountId . '/';
          }
          parent::__construct($settingsDirectory . self::SETTING_FILE_NAME);
      }
  
      public function getFileName(){
          return $this->settingsFile;
      }
  
      protected function isAccountFileSettings() {
          if (is_null($this->accountId)) {
              return false;
          }
          return true;
      }
  
      private function writeEmpty(Gpf_Io_File $file) {
          $file->open('w');
          $file->setFilePermissions(0777);
          $file->write('<?PHP /* */ ?>');
          $file->close();
      }
  
      protected function isFileContentOk($loadedArray) {
          $isContentOk = parent::isFileContentOk($loadedArray);
          return $isContentOk && !empty($loadedArray);
      }
  
      protected function isSettingsFileOk(Gpf_Io_File $file) {
          $isFileOk = parent::isSettingsFileOk($file);
          if ($this->isAccountFileSettings()) {
              return $isFileOk;
          }
          return $isFileOk && $this->containsDbSettings($file);
      }
  
      private function containsDbSettings(Gpf_Io_File $file) {
          $content = $file->getContents();
          return strstr($content, Gpf_Settings_Gpf::DB_DATABASE) &&
          strstr($content, Gpf_Settings_Gpf::DB_HOSTNAME) &&
          strstr($content, Gpf_Settings_Gpf::DB_PASSWORD) &&
          strstr($content, Gpf_Settings_Gpf::DB_USERNAME);
      }
  }
  

} //end Gpf_File_Settings

if (!class_exists('Gpf_DbEngine_Table', false)) {
  abstract class Gpf_DbEngine_Table extends Gpf_Object {
      const CHAR = 'char';
      const INT = 'int';
      const FLOAT = 'float';
      const DATETIME = 'datetime';
  
      /**
       * @var array of Gpf_DbEngine_Column
       */
      private $columns;
      /**
       * @var array of Gpf_DbEngine_Column
       */
      private $primaryColumns;
      /**
       * @var array of Gpf_DbEngine_Row_Constraint
       */
      private $constraints;
      /**
       * @var array of Gpf_DbEngine_DeleteConstraint
       */
      private $deleteConstraints;
      
      private $autoIncrementedColumn = null;
      private $name;
      private $columnsInitialized;
      private $constraintsInitialized;
      
      final protected function __construct() {
          $this->initName();
          $this->columns = array();
          $this->primaryColumns = array();
          $this->constraints = array();
          $this->deleteConstraints = array();
          $this->columnsInitialized = false;
          $this->constraintsInitialized = false;
      }
      
      private function initColumnsIfNeeded() {
          if ($this->columnsInitialized === true) {
              return;
          }
          $this->initColumns();
          $this->columnsInitialized = true;
      }
          
     private function initConstraintsIfNeeded() {
          if ($this->constraintsInitialized === true) {
              return;
          }
          $this->initConstraints();
          $this->constraintsInitialized = true;
      }
      
      abstract protected function initName();
  
      protected abstract function initColumns();
      
      protected function initConstraints() {
      }
      
      protected function setName($name) {
          $this->name = $name;
      }
      
      public function name() {
          return DB_TABLE_PREFIX . $this->name;
      }
      
      public function addConstraint(Gpf_DbEngine_Row_Constraint $constraint) {
          $this->constraints[] = $constraint;
      }
      
      /**
       * @return array of Gpf_DbEngine_Column
       */
      public function getColumns() {
          $this->initColumnsIfNeeded();
          return $this->columns;
      }
  
      /**
       * @return array of Gpf_DbEngine_Column
       */
      public function getPrimaryColumns() {
          $this->initColumnsIfNeeded();
          return $this->primaryColumns;
      }
  
      /**
       * @param string $column
       * @return boolean
       */
      public function isPrimary($column) {
          $this->initColumnsIfNeeded();
          return array_key_exists($column, $this->primaryColumns);
      }
  
      public function createPrimaryColumn($name, $type, $lenght = 0, $autogenerated = false) {
          $column = $this->createColumn($name, $type, $lenght, true);
          
          if($column->type == Gpf_DbEngine_Column::TYPE_NUMBER && $autogenerated 
              && $this->autoIncrementedColumn !== null) {
              throw new Gpf_Exception("duplicate key");
          }
          
          if($column->type == Gpf_DbEngine_Column::TYPE_NUMBER && $autogenerated) {
              $this->autoIncrementedColumn = $column;
          }
          
          $column->setAutogenerated($autogenerated);
          $this->primaryColumns[$name] = $column;
      }
      
      /**
       * @param string $name
       * @param string $type
       * @param int $length
       * @param boolean $mandatory
       * @return Gpf_DbEngine_Column
       */
      public function createColumn($name, $type, $length = 0, $mandatory = false) {
          $column = new Gpf_DbEngine_Column($name, $type, $length, $mandatory);
          $this->columns[$name] = $column;
          if ($length != 0 && $column->getType() == Gpf_DbEngine_Column::TYPE_STRING) {
              $this->addConstraint(new Gpf_DbEngine_Row_LengthConstraint($column->getName(), 0, $column->getLength()));
          }
          if ($column->getType() == Gpf_DbEngine_Column::TYPE_NUMBER) {
              $this->addConstraint(new Gpf_DbEngine_Row_NumberConstraint($column->getName()));
          }
          return $column;
      }
      
      /**
       *
       * @return Gpf_DbEngine_Column
       */
      public function getAutoIncrementedColumn() {
          $this->initColumnsIfNeeded();
          return $this->autoIncrementedColumn;        
      }
      
      public function hasAutoIncrementedKey() {
          $this->initColumnsIfNeeded();
          return $this->autoIncrementedColumn !== null;        
      }
      
      public function hasColumn($columnName) {
          $this->initColumnsIfNeeded();
          return array_key_exists($columnName, $this->columns);        
      }
  
      public function getColumn($name) {
          $this->initColumnsIfNeeded();
          return $this->columns[$name];
      }
      
      /**
       * @return array of Gpf_DbEngine_Row_Constraint
       */
      public function getConstraints() {
          $this->initConstraintsIfNeeded();
          return $this->constraints;
      }
      
      public function truncate() {
      	$this->createDatabase()->execute('TRUNCATE ' . $this->name());
      }
      
      public function optimize() {
          $this->createDatabase()->execute('OPTIMIZE TABLE ' . $this->name());
      }
      
      public function addDeleteConstraint(Gpf_DbEngine_DeleteConstraint $deleteConstraint) {
          $this->deleteConstraints[] = $deleteConstraint; 
      }
      
      protected function addCascadeDeleteConstraint($selfColumns, $foreignColumns, Gpf_DbEngine_Row $foreignDbRow) {
          $this->addDeleteConstraint(new Gpf_DbEngine_CascadeDeleteConstraint($selfColumns, $foreignColumns, $foreignDbRow));
      }
      
      protected function addSetNullDeleteConstraint($selfColumns, $foreignColumns, Gpf_DbEngine_Row $foreignDbRow) {
          $this->addDeleteConstraint(new Gpf_DbEngine_SetNullDeleteConstraint($selfColumns, $foreignColumns, $foreignDbRow));
      }
      
      protected function addRestrictDeleteConstraint($selfColumns, $foreignColumns, Gpf_DbEngine_Row $foreignDbRow) {
          $this->addDeleteConstraint(new Gpf_DbEngine_RestrictDeleteConstraint($selfColumns, $foreignColumns, $foreignDbRow));
      }
      
      /**
       * @return array of Gpf_DbEngine_DeleteConstraint
       */
      public function getDeleteConstraints() {
          $this->initConstraintsIfNeeded();
          return $this->deleteConstraints;
      }
  }
  

} //end Gpf_DbEngine_Table

if (!class_exists('Gpf_Db_Table_Settings', false)) {
  class Gpf_Db_Table_Settings extends Gpf_DbEngine_Table {
      const ID = "settingid";
      const NAME = "name";
      const VALUE = "value";
      const ACCOUNTID = "accountid";
      
      private static $instance;
          
      public static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new self;
          }
          return self::$instance;
      }
          
      protected function initName() {
          $this->setName('g_settings');
      }
      
      public static function getName() {
          return self::getInstance()->name();
      }
      
      public static function getSetting($name, $accountId = null) {
          $setting = new Gpf_Db_Setting();
          return $setting->getSetting($name, $accountId);
      }
      
      public static function setSetting($name, $value, $accountId = null) {
          $setting = new Gpf_Db_Setting();
          $setting->set(self::NAME, $name);
          $setting->set(self::VALUE, $value);
          if ($accountId != null) {
              $setting->set(self::ACCOUNTID, $accountId);
          }
          $setting->save();
      }
      
      protected function initColumns() {
          $this->createPrimaryColumn(self::ID, 'char', 8, true);
          $this->createColumn(self::NAME, 'char', 50);
          $this->createColumn(self::VALUE, 'text');
          $this->createColumn(self::ACCOUNTID, 'char', 8);
      }
      
    	/**
    	 * returns recordset with setting values for given setting names (array) and given account
    	 *
    	 * @param  settingNamesArray array
    	 * @param  accountId
    	 * @return Gpf_Data_RecordSet
    	 */
      public function getSettings($settingNames, $accountId) {
      	if(!is_array($settingNames) || count($settingNames)<=0) {
      		throw new Gpf_Exception("getSettings(): parameter settingNames is empty or not an array!");
      	}
      	
      	$result = new Gpf_Data_IndexedRecordSet('name');
  
      	$selectBuilder = new Gpf_SqlBuilder_SelectBuilder();
      	$selectBuilder->select->add('name', 'name');
      	$selectBuilder->select->add('value', 'value');
      	$selectBuilder->from->add(self::getName());
      	$selectBuilder->where->add("accountid", "=", $accountId);
  
      	$names = "";
      	foreach($settingNames as $name) {
      		$names .= ($names != "" ? "," : '')."'".$name."'";
      	}
     	
      	$names = "(".$names.")";
  
      	$selectBuilder->where->add("name", 'in', $settingNames);
  
      	$result->load($selectBuilder);
      	return $result;
      }
  }
  

} //end Gpf_Db_Table_Settings

if (!class_exists('Gpf_Db_Setting', false)) {
  class Gpf_Db_Setting extends Gpf_DbEngine_Row {
  
      function init() {
          $this->setTable(Gpf_Db_Table_Settings::getInstance());
          parent::init();
      }
  
      public function getSetting($name, $accountId = null) {
      	try {
      		$this->setName($name);
      		if ($accountId != null) {
      		  $this->setAccountId($accountId);
      		} else {
      		    $this->setNull(Gpf_Db_Table_Settings::ACCOUNTID);
      		}
          	$this->loadFromData();
          	return $this->getValue();
      	} catch(Gpf_Exception $e) {
      		throw new Gpf_Settings_UnknownSettingException($name);
      	}
      }
  
      public function save() {
          $setting = new Gpf_Db_Setting();
          try {
              $setting->getSetting($this->getName(), $this->getAccountId());
              $this->setPrimaryKeyValue($setting->getPrimaryKeyValue());
              $this->update();
          } catch (Gpf_DbEngine_NoRowException $e) {
              $this->insert(); 
          } catch (Gpf_Settings_UnknownSettingException $e) {
              $this->insert(); 
          }
      }
      
      public function setName($name) {
          $this->set(Gpf_Db_Table_Settings::NAME, $name);
      }
      
      public function setAccountId($accountId) {
          $this->set(Gpf_Db_Table_Settings::ACCOUNTID, $accountId);
      }
      
      public function getName() {
          return $this->get(Gpf_Db_Table_Settings::NAME);
      }
      
      public function getAccountId() {
          return $this->get(Gpf_Db_Table_Settings::ACCOUNTID);
      }
      
      public function getValue() {
          return $this->get(Gpf_Db_Table_Settings::VALUE);
      }
      
      public function setValue($value) {
          $this->set(Gpf_Db_Table_Settings::VALUE, $value);
      }
  }
  

} //end Gpf_Db_Setting

if (!class_exists('Gpf_DbEngine_Database', false)) {
  abstract class Gpf_DbEngine_Database extends Gpf_Object {
      const MYSQL = 'Mysql';
  
      protected $connected;
  
      protected $host;
      protected $username;
      protected $password;
      protected $dbname;
      protected $newLink = false;
      
      private static $database;
  
      /**
       * @return Gpf_DbEngine_Database
       */
      public static function getDatabase() {
          if(self::$database === null) {
              self::create(Gpf_Settings::get(Gpf_Settings_Gpf::DB_HOSTNAME),
                           Gpf_Settings::get(Gpf_Settings_Gpf::DB_USERNAME),
                           Gpf_Settings::get(Gpf_Settings_Gpf::DB_PASSWORD),
                           Gpf_Settings::get(Gpf_Settings_Gpf::DB_DATABASE));
          }
          return self::$database;
      }
  
      abstract public function connect();
  
      /**
       * @return Gpf_DbEngine_Database
       */
      public static function create($hostname, $username, $password, $dbname) {
          self::$database = self::createDriver(self::MYSQL);
          self::$database->init($hostname, $username, $password, $dbname);
          return self::$database;
      }
  
      /**
       * @param string $type
       * @return Gpf_DbEngine_Database
       */
      public static function createDriver($type) {
          $class = 'Gpf_DbEngine_Driver_' . $type . '_Database';
          return new $class;
      }
  
      public function isConnected() {
          return $this->connected;
      }
  
      public function getHostname() {
          return $this->host;
      }
      public function getUsername() {
          return $this->username;
      }
      public function getPassword() {
          return $this->password;
      }
      public function getDbname() {
          return $this->dbname;
      }
  
      public function init($host, $username, $password, $database, $newLink = false) {
          $this->host = $host;
          $this->username = $username;
          $this->password = $password;
          $this->dbname = $database;
          $this->newLink = $newLink;
      }
  
      public function disconnect() {
          $this->connected = false;
      }
  
      public function createUniqueId($length = 8) {
          return substr(md5(uniqid(rand(), true)), 0, $length);
      }
  
      // TODO: move this method to Gpf_Common_Date class
      public static function getDateString($time = '') {
          if($time === '') {
              $time = time();
          }
          return strftime("%Y-%m-%d %H:%M:%S", $time);
      }
  
      public abstract function escapeString($str);
  
      /**
       *
       * @return Gpf_DbEngine_Driver_Mysql_Statement
       */
      public abstract function execute($sqlString);
  }
  

} //end Gpf_DbEngine_Database

if (!class_exists('Gpf_DbEngine_Driver_Mysql_Database', false)) {
  class Gpf_DbEngine_Driver_Mysql_Database extends Gpf_DbEngine_Database  {
      const BENCHMARK_CONNECT = 'db_connect';
      const BENCHMARK_EXECUTE = 'db_execute';
      const CR_SERVER_GONE_ERROR = 2006;
      const MAX_FAILED_CONNECTION_COUNT = 5;
  
      private $handle;
      private $failedConnections = 0;
  
      public function connect() {
          Gpf_Log_Benchmark::start(self::BENCHMARK_CONNECT);
  
          $handle = @mysql_connect($this->host, $this->username, $this->password, $this->newLink);
          if(!$handle) {
              Gpf_Log_Benchmark::end(self::BENCHMARK_CONNECT, "Unable to connect to database: " . mysql_error());
              throw new Gpf_DbEngine_Exception("Unable to connect to database: " . mysql_error());
          }
          if(!mysql_select_db($this->dbname, $handle)) {
              Gpf_Log_Benchmark::end(self::BENCHMARK_CONNECT, "Unable to select database ");
              throw new Gpf_DbEngine_Exception("Unable to select database " . $this->dbname . ' Reason:' . mysql_error());
          }
          mysql_query("SET NAMES utf8");
          mysql_query("SET CHARACTER_SET utf8");
          $this->handle = $handle;
          $this->connected = true;
  
          Gpf_Log_Benchmark::end(self::BENCHMARK_CONNECT, "Connected successfully");
          return true;
      }
  
      public function prepare($sqlString) {
          $sth = new Gpf_DbEngine_Driver_Mysql_Statement();
          $sth->init($sqlString, $this->handle);
          return $sth;
      }
  
      /**
       * @return Gpf_DbEngine_Driver_Mysql_Statement
       */
      public function execute($sqlString, $getAutoincrementId = false) {
          Gpf_Log_Benchmark::start(self::BENCHMARK_EXECUTE);
  
          if(!$this->isConnected()) {
              $this->connect();
          }
  
          $sth = $this->prepare($sqlString);
          $retval = $sth->execute();
          Gpf_Log_Benchmark::end(self::BENCHMARK_EXECUTE, "SQL [returned $retval]: " . $sqlString);
          if($retval !== false) {
              if($getAutoincrementId) {
                  $sth->loadAutoIncrementId();
              }
              $this->resetFailedConnectionsCount();
              return $sth;
          }
          
          try {
              $this->handleError($sth);
          } catch (Gpf_DbEngine_ConnectionGoneException $e) {
              if ($this->maxConnectionsCountRaised()) {
                  throw new Gpf_DbEngine_Exception($this->_sys('Maximum failed connection count %s reached. Giving up.', self::MAX_FAILED_CONNECTION_COUNT));
              }
              $this->reconnect();
              return $this->execute($sqlString, $getAutoincrementId);
          }
      }
  
  
      private function handleError($sth) {
          if($sth->getErrorCode() == self::CR_SERVER_GONE_ERROR) {
              Gpf_Log::disableType(Gpf_Log_LoggerDatabase::TYPE);
              Gpf_Log::info($this->_sys('MySql server has gone away: Reconnecting...'));
              Gpf_Log::enableAllTypes();
              throw new Gpf_DbEngine_ConnectionGoneException($this->_sys('MySql server has gone away.'));
          }
          
          Gpf_Log_Benchmark::end(self::BENCHMARK_EXECUTE, "SQL ERROR: ".$sth->getStatement());
          
          $this->resetFailedConnectionsCount();
          throw new Gpf_DbEngine_Driver_Mysql_SqlException($sth->getStatement(), $sth->getErrorMessage(), $sth->getErrorCode());
      }
  
  
      protected function resetFailedConnectionsCount() {
          $this->failedConnections = 0;
      }
  
      private function incFailedConnectionsCount() {
          $this->failedConnections++;
      }
  
      private function maxConnectionsCountRaised() {
          if ($this->failedConnections == self::MAX_FAILED_CONNECTION_COUNT) {
              return true;
          }
          return false;
      }
  
      private function reconnect() {
          $this->disconnect();
          $this->connect();
          $this->incFailedConnectionsCount();
      }
  
      public function disconnect() {
          @mysql_close($this->handle);
          $this->handle = null;
          parent::disconnect();
      }
  
      function escapeString($str) {
          if(!$this->isConnected()) {
              $this->connect();
          }
          return mysql_real_escape_string($str, $this->handle);
      }
  
      function getVersion() {
          return mysql_get_server_info($this->handle);
      }
  }
  

} //end Gpf_DbEngine_Driver_Mysql_Database

if (!class_exists('Gpf_DbEngine_Column', false)) {
  class Gpf_DbEngine_Column extends Gpf_Object {
  
      public $name;
      public $type;
      public $length;
      public $needed;
      private $_autogenerated = false;
  
      const TYPE_NUMBER = "Number";
      const TYPE_STRING = "String";
      const TYPE_DATE = "Date";
  
      function __construct($name, $type, $length = 0, $mandatory = false) {
          $this->name = $name;
          $this->setType($type);
          $this->length = $length;
          $this->needed = $mandatory;
      }
  
      public function setAutogenerated($autogenerated) {
          $this->_autogenerated = $autogenerated;
      }
  
      public function isAutogenerated() {
          return $this->_autogenerated;
      }
  
      private function setType($value) {
          switch(strtolower($value)) {
              case 'varchar':
              case 'char':
              case 'text':
              case 'string':
                  $this->type = self::TYPE_STRING;
                  break;
              case 'int':
              case 'integer':
              case 'number':
              case 'double':
              case 'float':
                  $this->type = self::TYPE_NUMBER;
                  break;
              case 'datetime':
              case 'date':
              case 'time':
                  $this->type = self::TYPE_DATE;
                  break;
          }
      }
  
      public function doQuote() {
          if ($this->type == 'Number') {
              return false;
          }
          return true;
      }
  
      public function getName() {
          return $this->name;
      }
      
      public function getType() {
          return $this->type;
      }
      
      public function getLength() {
          return $this->length;
      }
  }
  

} //end Gpf_DbEngine_Column

if (!interface_exists('Gpf_DbEngine_Row_Constraint', false)) {
  interface Gpf_DbEngine_Row_Constraint {
      
      /**
       * Validate Db_Row
       *
       * @param Gpf_DbEngine_Row $row
       * @throws Gpf_DbEngine_Row_ConstraintException
       */
      public function validate(Gpf_DbEngine_Row $row);
  }
  

} //end Gpf_DbEngine_Row_Constraint

if (!class_exists('Gpf_DbEngine_Row_LengthConstraint', false)) {
  class Gpf_DbEngine_Row_LengthConstraint extends Gpf_Object implements Gpf_DbEngine_Row_Constraint {
      
      private $columnName;
      private $minLength;
      private $maxLength;
      private $minMessage;
      private $maxMessage;
      
      /**
       * @param string $columnNames
       */
      public function __construct($columnName, $minLength, $maxLength, $minMessage = '', $maxMessage = '') {
          $this->columnName = $columnName;
          $this->minLength = $minLength;
          $this->maxLength = $maxLength;
          $this->minMessage = $minMessage;
          $this->maxMessage = $maxMessage;
      }
      
      /**
       * Validate Db_Row
       *
       * @param Gpf_DbEngine_Row $row
       * @throws Gpf_DbEngine_Row_ConstraintException
       */
      public function validate(Gpf_DbEngine_Row $row) {
          if ($this->minLength == 0 && $this->maxLength == 0) {
              return;
          }
          
          if ($this->minLength > 0 &&
              strlen($row->get($this->columnName)) < $this->minLength) {
              if ($this->minMessage == '') {
                  throw new Gpf_DbEngine_Row_ConstraintException($this->columnName,
                      $this->_('Minimum length of %s in %s is %s', $this->columnName, get_class($row), $this->minLength-1));
              } else {
                  throw new Gpf_DbEngine_Row_ConstraintException($this->columnName,
                      Gpf_Lang::_replaceArgs($this->minMessage, $this->minLength-1));   
              }
          }
          
          if ($this->maxLength > 0 &&
              strlen($row->get($this->columnName)) > $this->maxLength) {
              if ($this->maxMessage == '') {
                  throw new Gpf_DbEngine_Row_ConstraintException($this->columnName,
                      $this->_('Maximum length of %s in %s is %s', $this->columnName, get_class($row), $this->maxLength));
              } else {
                  throw new Gpf_DbEngine_Row_ConstraintException($this->columnName,
                      Gpf_Lang::_replaceArgs($this->maxMessage, $this->maxLength));    
              }
          }
      }
  }
} //end Gpf_DbEngine_Row_LengthConstraint

if (!class_exists('Gpf_SqlBuilder_SelectBuilder', false)) {
  class Gpf_SqlBuilder_SelectBuilder extends Gpf_Object {
      public $tableName;
  
      /**
       * @var Gpf_SqlBuilder_SelectClause
       */
      public $select;
      /**
       * @var Gpf_SqlBuilder_FromClause
       */
      public $from;
      /**
       * @var Gpf_SqlBuilder_WhereClause
       */
      public $where;
      /**
       * @var Gpf_SqlBuilder_GroupByClause
       */
      public $groupBy;
      /**
       * @var Gpf_SqlBuilder_OrderByClause
       */
      public $orderBy;
      /**
       * @var Gpf_SqlBuilder_LimitClause
       */
      public $limit;
      /**
       * @var Gpf_SqlBuilder_HavingClause
       */
      public $having;
  
      function __construct() {
          $this->select = new Gpf_SqlBuilder_SelectClause();
          $this->from = new Gpf_SqlBuilder_FromClause();
          $this->where = new Gpf_SqlBuilder_WhereClause();
          $this->groupBy = new Gpf_SqlBuilder_GroupByClause();
          $this->orderBy = new Gpf_SqlBuilder_OrderByClause();
          $this->limit = new Gpf_SqlBuilder_LimitClause();
          $this->having = new Gpf_SqlBuilder_HavingClause();
          $this->initSelect();
      }
  
      public function cloneObj($obj) {
          $this->select = clone $obj->select;
          $this->from = clone $obj->from;
          $this->where = clone $obj->where;
          $this->having = clone $obj->having;
          $this->groupBy = clone $obj->groupBy;
          $this->orderBy = clone $obj->orderBy;
          $this->limit = clone $obj->limit;
      }
  
      /**
       * @throws Gpf_DbEngine_TooManyRowsException
       * @throws Gpf_DbEngine_NoRowException
       * @return Gpf_Data_Record
       */
      public function getOneRow() {
          $sth = $this->execute();
  
          if ($sth->rowCount() > 1) {
              throw new Gpf_DbEngine_TooManyRowsException($this);
          }
  
          $row = $sth->fetchArray();
          return new Gpf_Data_Record(array_keys($row), array_values($row));
      }
  
      /**
       * @throws Gpf_DbEngine_NoRowException
       * @return Gpf_DbEngine_Driver_Mysql_Statement
       */
      private function execute() {
          $sth = $this->createDatabase()->execute($this->toString());
          if ($sth->rowCount() < 1) {
              throw new Gpf_DbEngine_NoRowException($this);
          }
          return $sth;
      }
  
      private function getAllRowsRecordSet(Gpf_Data_RecordSet $recordSet) {
          try {
              $sth = $this->execute();
          } catch (Gpf_DbEngine_NoRowException $e) {
              return $recordSet;
          }
          $row = $sth->fetchArray();
          $recordSet->setHeader(array_keys($row));
          $recordSet->add(array_values($row));
  
          while ($row = $sth->fetchRow()) {
              $recordSet->add($row);
          }
  
          return $recordSet;
      }
  
      /**
       * @return Gpf_SqlBuilder_SelectIterator
       */
      public function getAllRowsIterator() {
      	return new Gpf_SqlBuilder_SelectIterator($this->toString());
      }
  
      /**
       * @return Gpf_Data_RecordSet
       */
      public function getAllRows() {
          return $this->getAllRowsRecordSet(new Gpf_Data_RecordSet());
      }
  
      /**
       * @return Gpf_Data_IndexedRecordSet
       */
      public function getAllRowsIndexedBy($keyColumn) {
          return $this->getAllRowsRecordSet(new Gpf_Data_IndexedRecordSet($keyColumn));
      }
  
      /**
       * @param Gpf_SqlBuilder_SelectBuilder $selectBuilder
       * @return boolean
       */
      public function equals(Gpf_SqlBuilder_SelectBuilder	$selectBuilder) {
          return $selectBuilder->toString() == $this->toString();
      }
  
      public function toString() {
          return $this->select->toString().
          ($this->from->isEmpty() ? '' : "FROM ". $this->from->toString()).
          $this->where->toString().
          $this->groupBy->toString().
          $this->having->toString().
          $this->orderBy->toString().
          $this->limit->toString();
      }
  
      private function initSelect() {
          if(!empty($this->tableName)) {
              $this->from->add($this->tableName);
          }
      }
  }
  

} //end Gpf_SqlBuilder_SelectBuilder

if (!class_exists('Gpf_SqlBuilder_SelectClause', false)) {
  class Gpf_SqlBuilder_SelectClause extends Gpf_Object {
      private $clause = array();
      private $distinct = false;
  
      public function add($columnName, $columnAlias = '', $tablePrefix = '') {
          $column = new Gpf_SqlBuilder_SelectColumn($columnName, $columnAlias);
          $column->setTablePrefix($tablePrefix);
          if ($columnAlias == '') {
              $this->clause[] = $column;
          } else {
              $this->clause[$columnAlias] = $column;
          }
      }
  
      public function replaceColumn($oldColumnAlias, $columnName, $columnAlias = '', $tablePrefix = '') {
          if (array_key_exists($oldColumnAlias, $this->clause)) {
              $column = new Gpf_SqlBuilder_SelectColumn($columnName, $columnAlias);
              $column->setTablePrefix($tablePrefix);
              $this->clause[$oldColumnAlias] = $column;
          }
      }
  
      public function addConstant($constantValue, $alias = '') {
          $this->clause[] = new Gpf_SqlBuilder_SelectColumn($constantValue, $alias, true);
      }
  
      public function addAll(Gpf_DbEngine_Table $table, $tableAlias = '') {
          foreach ($table->getColumns() as $column) {
              $this->add($column->getName(), $column->getName(), $tableAlias);
          }
      }
  
      public function setDistinct() {
          $this->distinct = true;
      }
  
      public function toString() {
          $out = '';
          foreach ($this->clause as $key => $columnObj) {
              $out .= $out ? ',' : '';
              $out .= $columnObj->toString();
          }
          if(empty($out)) {
              return '';
          }
          $distinct = '';
          if($this->distinct) {
              $distinct = ' DISTINCT';
          }
          return "SELECT$distinct $out ";
      }
  
      public function getColumns() {
          return $this->clause;
      }
  
      public function existsAlias($alias) {
          return array_key_exists($alias, $this->clause);
      }
  
      public function equals(Gpf_SqlBuilder_SelectClause $select) {
          return $select->toString() == $this->toString();
      }
  }
  

} //end Gpf_SqlBuilder_SelectClause

if (!class_exists('Gpf_SqlBuilder_FromClause', false)) {
  class Gpf_SqlBuilder_FromClause extends Gpf_Object {
      protected $clause = array();
      const LEFT = "LEFT";
      const RIGHT = "RIGHT";
      const INNER = "INNER";
  
      public function add($tableName, $tableAlias = '') {
          $this->checkAlias($tableAlias);
  
          $this->clause[] = new Gpf_SqlBuilder_FromTable($tableName, $tableAlias);
      }
  
      private function addJoin($type, $tableName, $tableAlias, $onCondition) {
          $this->checkAlias($tableAlias);
  
          $this->clause[] = new Gpf_SqlBuilder_JoinTable($type, $tableName, $tableAlias, $onCondition);
      }
  
      public function addLeftJoin($tableName, $tableAlias, $onCondition) {
          $this->addJoin(self::LEFT, $tableName, $tableAlias, $onCondition);
      }
  
      public function addRightJoin($tableName, $tableAlias, $onCondition) {
          $this->addJoin(self::RIGHT, $tableName, $tableAlias, $onCondition);
      }
  
      public function addSubselect(Gpf_SqlBuilder_SelectBuilder $query, $tableAlias) {
          $this->checkAlias($tableAlias);
          $this->clause[] = new Gpf_SqlBuilder_SubSelectTable($query, $tableAlias);
      }
  
      public function replacePrimarySource(Gpf_SqlBuilder_SelectBuilder $query, $tableAlias) {
          $this->clause[0] = new Gpf_SqlBuilder_SubSelectTable($query, $tableAlias);
      }
  
      public function addClause(Gpf_SqlBuilder_FromClauseTable $clause) {
          $this->clause[] = $clause;
      }
  
      /**
       * @return array<Gpf_SqlBuilder_FromClauseTable>
       */
      public function getAllFroms() {
          return $this->clause;
      }
  
      /**
       * Removes unnecessary tables from the from clause
       *
       * @param $requiredPreffixes
       */
      public function prune(array $requestedPreffixes) {
          $requiredPreffixes = array();
          foreach ($requestedPreffixes as $preffix) {
              $requiredPreffixes[$preffix] = $preffix;
          }
          
          do {
              $requiredPreffixesCount = count($requiredPreffixes);
              $requiredPreffixes = array_merge($requiredPreffixes, $this->getJoinTableDependencies($requiredPreffixes));
          } while ($requiredPreffixesCount < count($requiredPreffixes));
          
          foreach ($this->clause as $i => $table) {
              if (!in_array($table->getAlias(), $requiredPreffixes)) {
                  unset($this->clause[$i]);
              }
          }
      }
      
      private function getJoinTableDependencies($requiredPreffixes) {
          $joinTableDependencies = array();
          foreach ($this->clause as $table) {
              if (in_array($table->getAlias(), $requiredPreffixes) && $table instanceof Gpf_SqlBuilder_JoinTable) {
                  $joinTableDependencies = array_merge($joinTableDependencies, $table->getRequiredPreffixes());
              }
          }
          return $joinTableDependencies;
      }
  
      /**
       *
       * @param $tableAlias
       * @return Gpf_SqlBuilder_SelectBuilder
       */
      public function getSubSelect($tableAlias) {
          if(empty($tableAlias)) {
              throw new Gpf_Exception('Could not return empty alias table');
          }
          $builderTable = $this->getFromClauseTable($tableAlias);
          if(!($builderTable instanceof Gpf_SqlBuilder_SubSelectTable)) {
              throw new Gpf_Exception('SubSelect table does not exist.');
          }
          return $builderTable->getSubSelect();
      }
  
      private function getFromClauseTable($tableAlias) {
          foreach ($this->clause as $key => $fromTableObj) {
              if($tableAlias == $fromTableObj->getAlias()) {
                  return $fromTableObj;
              }
          }
          throw new Gpf_Exception("Table alias $tableAlias does not exist.");
      }
  
      public function addInnerJoin($tableName, $tableAlias, $onCondition) {
          $this->addJoin(self::INNER, $tableName, $tableAlias, $onCondition);
      }
  
      public function isEmpty() {
          return count($this->clause) <= 0;
      }
  
      public function containsAlias($alias) {
          if(empty($alias)) {
              return false;
          }
          foreach ($this->clause as $key => $fromTableObj) {
              if($alias == $fromTableObj->getAlias()) {
                  return true;
              }
          }
          return false;
      }
  
      private function checkAlias($tableAlias) {
          if($this->containsAlias($tableAlias)) {
              throw new Gpf_Exception('Table alias already exists');
          }
      }
  
      public function toString() {
          $out = '';
          foreach ($this->clause as $key => $fromTableObj) {
              $out .= ($out && !$fromTableObj->isJoin()) ? ',' : '';
              $out .= $fromTableObj->toString();
          }
          return $out . " ";
      }
  
      public function equals(Gpf_SqlBuilder_FromClause  $from) {
          return $from->toString() == $this->toString();
      }
  }
  

} //end Gpf_SqlBuilder_FromClause

if (!class_exists('Gpf_SqlBuilder_WhereClause', false)) {
  class Gpf_SqlBuilder_WhereClause extends Gpf_Object {
      protected $clause = array();
  
      public function add($operand, $operator, $secondOperand = '', $logicOperator = 'AND', $doQuote = true, $binaryComparision = false) {
          $i = count($this->clause);
          $this->clause[$i]['obj'] = new Gpf_SqlBuilder_WhereCondition($operand, $operator, $secondOperand, $binaryComparision);
          $this->clause[$i]['operator'] = $logicOperator;
          $this->clause[$i]['obj']->doQuote = $doQuote;
      }
  
      public function addDontQuote($operand, $operator, $secondOperand = '', $logicOperator = 'AND', $binaryComparision = false) {
          $this->add($operand, $operator, $secondOperand, $logicOperator, false, $binaryComparision);
      }
  
      public function addCondition($condition, $logicOperator = 'AND') {
          $i = count($this->clause);
          $this->clause[$i]['obj'] = $condition;
          $this->clause[$i]['operator'] = $logicOperator;
      }
      
      public function getClause() {
      	return $this->clause;
      }
  
      public function toString() {
          $out = '';
          foreach ($this->clause as $key => $columnObj) {
              $out .= $out ? $columnObj['operator'] . ' ' : '';
              $out .= $columnObj['obj']->toString() . ' ';
          }
          if(empty($out)) {
              return '';
          }
          return "WHERE $out ";
      }
  
      public function equals(Gpf_SqlBuilder_WhereClause  $where) {
          return $where->toString() == $this->toString();
      }
  
      /**
       * @return array of table preffixes used in where clause
       */
      public function getUniqueTablePreffixes() {
          $preffixes = array();
          foreach ($this->clause as $clause) {
              $preffixes = array_merge($preffixes, $clause['obj']->getUniqueTablePreffixes());
          }
          return $preffixes;
      }
  }
  

} //end Gpf_SqlBuilder_WhereClause

if (!class_exists('Gpf_SqlBuilder_GroupByClause', false)) {
  class Gpf_SqlBuilder_GroupByClause extends Gpf_Object {
      private $clause = array();
  
      public function add($columnName) {
      	if($columnName != '') {
          	$this->clause[] = $columnName;
      	}
      }
      
      public function addAll(Gpf_DbEngine_Table $table, $tableAlias = '') {
          $alias = '';
          if($tableAlias != '') {
              $alias = $tableAlias . '.';
          }
          foreach ($table->getColumns() as $column) {
              $this->add($alias . $column->getName());            
          }
      }
      
      public function removeByName($columnName) {
          $clause = $this->clause;
          foreach ($clause as $key => $column) {
              if($columnName == $column) {
                  unset($this->clause[$key]);
              }
          }
      }
  
      public function toString() {
          $out = '';
          foreach ($this->clause as $key => $columnName) {
              $out .= $out ? ',' : '';
              $out .= $columnName;
          }
          if(empty($out)) {
              return '';
          }
          return "GROUP BY $out ";
      }
      
      public function equals(Gpf_SqlBuilder_GroupByClause $groupBy) {
          return $groupBy->toString() == $this->toString();
      }
  }
  

} //end Gpf_SqlBuilder_GroupByClause

if (!class_exists('Gpf_SqlBuilder_OrderByClause', false)) {
  class Gpf_SqlBuilder_OrderByClause extends Gpf_Object {
      private $clause = array();
  
      public function add($columnName, $asc = true, $tableName = '') {
          $this->clause[] = new Gpf_SqlBuilder_OrderByColumn($columnName, $asc, $tableName);
      }
  
      public function removeByName($columnName) {
          $clause = $this->clause;
          foreach ($clause as $key => $columnObj) {
              if($columnName == $columnObj->name) {
                  unset($this->clause[$key]);
              }
          }
      }
      
      public function getAllOrderColumns() {
          return $this->clause;
      }
  
      public function toString() {
          $out = '';
          foreach ($this->clause as $key => $columnObj) {
              $out .= $out ? ',' : '';
              $out .= $columnObj->toString();
          }
          if(empty($out)) {
              return '';
          }
          return "ORDER BY $out ";
      }
      
      public function isEmpty() {
          return count($this->clause) == 0;
      }
  }
  

} //end Gpf_SqlBuilder_OrderByClause

if (!class_exists('Gpf_SqlBuilder_LimitClause', false)) {
  class Gpf_SqlBuilder_LimitClause extends Gpf_Object {
      private $offset = '';
      private $limit = '';
  
      public function set($offset, $limit) {
          $this->offset = $offset;
          $this->limit = $limit;
      }
  
      public function toString() {
          $out = '';
          if ($this->limit !== '') {
              $out .= " LIMIT " . $this->limit;
          }
          if ($this->offset !== '') {
              $out .= " OFFSET " . $this->offset;
          }
          return $out . " ";
      }
      
      public function isEmpty() {
          return $this->offset === '' && $this->limit === '';
      }
  }
  

} //end Gpf_SqlBuilder_LimitClause

if (!class_exists('Gpf_SqlBuilder_HavingClause', false)) {
  class Gpf_SqlBuilder_HavingClause extends Gpf_SqlBuilder_WhereClause {
  
      public function toString() {
          $out = '';
          foreach ($this->clause as $key => $columnObj) {
              $out .= $out ? $columnObj['operator'] . ' ' : '';
              $out .= $columnObj['obj']->toString() . ' ';
          }
          if(empty($out)) {
              return '';
          }
          return "HAVING $out ";
      }
  
  }
  

} //end Gpf_SqlBuilder_HavingClause

if (!class_exists('Gpf_SqlBuilder_SelectColumn', false)) {
  class Gpf_SqlBuilder_SelectColumn extends Gpf_Object {
      private $name;
      private $alias;
      private $tableName;
      private $doQuote = false;
  
      function __construct($name, $alias = '', $doQuote = false) {
          $this->name = $name;
          $this->alias = $alias;
          $this->doQuote = $doQuote;
      }
      
      public function setTablePrefix($prefix) {
          if(strlen($prefix)) {
              $this->tableName = $prefix;
          }
      }
      
      public function toString() {
          $out = '';
          if(!empty($this->tableName)) {
              $out = $this->tableName . '.';
          }
          if ($this->doQuote) {
              $out .= "'" .  $this->name . "'";
          } else {
              $out .= $this->name;
          }
          if(!empty($this->alias)) {
              $out .= ' AS ' . $this->alias;
          }
          return $out;
      }
  
      public function getAlias() {
          return $this->alias;
      }
      
      public function getName() {
          return $this->name;
      }
  }
  

} //end Gpf_SqlBuilder_SelectColumn

if (!interface_exists('Gpf_SqlBuilder_FromClauseTable', false)) {
  interface Gpf_SqlBuilder_FromClauseTable {
      public function isJoin();
      public function toString();
      public function getAlias();
  }
  

} //end Gpf_SqlBuilder_FromClauseTable

if (!class_exists('Gpf_SqlBuilder_FromTable', false)) {
  class Gpf_SqlBuilder_FromTable extends Gpf_Object implements Gpf_SqlBuilder_FromClauseTable {
      private $name;
      private $alias;
  
      function __construct($name, $alias = '') {
          $this->name = $name;
          $this->alias = $alias;
      }
  
      public function getAlias() {
          return $this->alias;
      }
  
      public function getName() {
          return $this->name;
      }
  
      public function toString() {
          $out = $this->name;
          if(!empty($this->alias)) {
              $out .= ' ' . $this->alias;
          }
          return $out;
      }
  
      public function isJoin() {
          return false;
      }
  }
  

} //end Gpf_SqlBuilder_FromTable

if (!class_exists('Gpf_SqlBuilder_WhereCondition', false)) {
  class Gpf_SqlBuilder_WhereCondition extends Gpf_Object {
      private $operand;
      private $operator;
      private $secondOperand;
      public $doQuote = true;
      private $binaryComparision = false;
  
      function __construct($operand, $operator, $secondOperand = '', $binaryComparision = false) {
          $this->operand = $operand;
          $this->operator = $operator;
          $this->secondOperand = $secondOperand;
          $this->binaryComparision = $binaryComparision;
      }
  
      public function toString() {
          $out = $this->binaryComparision ? 'BINARY ' : '';
          $out .= $this->operand . ' ' . $this->operator . ' ';
          if($this->secondOperand === null) {
              if($this->operator == '=') {
                  return $this->operand . ' IS NULL';
              }
              if($this->operator == '!=') {
                  return $this->operand . ' IS NOT NULL';
              }
          }
          if (strtoupper($this->operator) == 'IN' || strtoupper($this->operator) == 'NOT IN') {
              $out .= '(' . $this->operandToInValue($this->secondOperand) . ') ';
          } else {
              if($this->doQuote) {
                  $r = "'" . $this->createDatabase()->escapeString($this->secondOperand) . "'";
              } else {
                  $r = $this->secondOperand;
              }
              $out .= ' ' .$r . ' ';
          }
          return $out;
      }
  
      public function operandToInValue($inValue) {
          if(is_array($inValue)) {
              $out = '';
              foreach ($inValue as $value) {
                  $out .= "'" . $this->createDatabase()->escapeString($value) . "',";
              }
              return rtrim($out, ',');
          }
  
          if ($inValue instanceof Gpf_SqlBuilder_SelectBuilder) {
              return $inValue->toString();
          }
  
          return '';
      }
      
      /**
       * @return array of table preffixes used in where clause
       */
      public function getUniqueTablePreffixes() {
          $preffixes = array();
          if (is_string($this->operand) && ($pos = strpos($this->operand, '.')) !== false) {
              $preffix = substr($this->operand, 0, $pos);
              $preffix = $this->fixPreffix($preffix);
              $preffixes[$preffix] = $preffix;
          }
          if (is_string($this->secondOperand) && ($pos = strpos($this->secondOperand, '.')) !== false) {
              $preffix = substr($this->secondOperand, 0, $pos);
              $preffix = $this->fixPreffix($preffix);
              $preffixes[$preffix] = $preffix;
          }
          return $preffixes;
      }
      
      private function fixPreffix($preffix) {
          if (($pos = strpos($preffix, '(')) !== false) {
              $preffix = substr($preffix, $pos+1);
          }
          return $preffix;
      }
  }
  

} //end Gpf_SqlBuilder_WhereCondition

if (!class_exists('Gpf_Log_Benchmark', false)) {
  class Gpf_Log_Benchmark extends Gpf_Object {
      /**
       * @var Gpf_Log_Benchmark
       */
      private static $instance;
      private $isActive = null;
      private $minSqlTime = 0;
      private $startTimes = array();
      /**
       *
       * @var Gpf_Log_Logger
       */
      private static $benchmarkLogger;
  
      private function __construct() {
      }
  
      /**
       * @return Gpf_Log_Benchmark
       */
      public static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new self;
          }
          return self::$instance;
      }
  
      public static function start($benchmarkName) {
          self::getInstance()->startBenchmark($benchmarkName);
      }
  
      private function startBenchmark($benchmarkName) {
          if(!$this->isActive()) {
              return;
          }
          $this->startTimes[$benchmarkName] = Gpf_Common_DateUtils::getNowSeconds();
      }
  
      /**
       * Return how many seconds is running benchmark already
       *
       * @param string $benchmarkName
       * @return int number of seconds
       */
      public function getBenchmarkTime($benchmarkName) {
          if(!$this->isActive()) {
              return 0;
          }
          return Gpf_Common_DateUtils::getNowSeconds() - $this->startTimes[$benchmarkName];
      }
  
      public static function end($benchmarkName, $message) {
          self::getInstance()->endBenchmark($benchmarkName, $message);
      }
  
      protected function getLogFileName() {
          $logDir = new Gpf_Io_File(Gpf_Paths::getInstance()->getAccountsPath());
          return $logDir->getFileName() . 'benchmark.log';
      }
  
      private function endBenchmark($benchmarkName, $message) {
          if(!$this->isActive()) {
              return;
          }
  
          if(!isset($this->startTimes[$benchmarkName])) {
              return;
          }
  
          $time = Gpf_Common_DateUtils::getNowSeconds() - $this->startTimes[$benchmarkName];
          unset($this->startTimes[$benchmarkName]);
  
          if(self::$benchmarkLogger === null) {
              try {
                  $this->initLogger();
              } catch (Exception $e) {
                  $this->isActive = false;
                  return;
              }
          }
  
          if ($time >= $this->minSqlTime) {
              self::$benchmarkLogger->debug($time . " secs. | " . $message);
          }
      }
  
      private function initLogger() {
          self::$benchmarkLogger = Gpf_Log_Logger::getInstance('benchmark');
          $fileLogger = new Gpf_Log_LoggerFile();
  
          $fileName = $this->getLogFileName();
          $fileLogger->setFileName($fileName);
          $this->checkResetFileSize($fileName);
          self::$benchmarkLogger->addLogger($fileLogger, Gpf_Log::DEBUG);
      }
  
      private function isActive() {
          if($this->isActive !== null) {
              return $this->isActive;
          }
          $this->isActive = false;
          $this->minSqlTime = 0;
          try {
              $this->isActive = Gpf_Settings::get(Gpf_Settings_Gpf::BENCHMARK_ACTIVE) == Gpf::YES;
              $this->minSqlTime = Gpf_Settings::get(Gpf_Settings_Gpf::BENCHMARK_MIN_SQL_TIME);
          } catch (Exception $e) {
          }
          return $this->isActive;
      }
  
      protected function checkResetFileSize($fileName) {
          $file = new Gpf_Io_File($fileName);
          $fileSize = $file->getSize();
          if ($fileSize/1024/1024 > Gpf_Settings::get(Gpf_Settings_Gpf::BENCHMARK_MAX_FILE_SIZE)) {
              $this->resetFile($file, $fileSize);
          }
      }
  
      private function resetFile(Gpf_Io_File $file, $fileSize) {
          $file->open('w');
          $file->write('File was truncated after exceeding size ' . $fileSize. " bytes. Now continuing...\n");
          $file->close();
      }
  }

} //end Gpf_Log_Benchmark

if (!class_exists('Gpf_Exception', false)) {
  class Gpf_Exception extends Exception {
  
      private $id;
  
      public function __construct($message,$code = null) {
          parent::__construct($message,$code);
      }
  
      protected function logException() {
          Gpf_Log::error($this->getMessage());
      }
  
      public function setId($id) {
          $this->id = $id;
      }
  
      public function getId() {
          return $this->id;
      }
  
  }

} //end Gpf_Exception

if (!class_exists('Gpf_Settings_UnknownSettingException', false)) {
  class Gpf_Settings_UnknownSettingException extends Gpf_Exception  {
  
      function __construct($name) {
          parent::__construct("Setting with name '$name' is not defined or has no default value");
      }
  
      protected function logException() {
      }
  }

} //end Gpf_Settings_UnknownSettingException

if (!class_exists('Gpf_DbEngine_Driver_Mysql_Statement', false)) {
  class Gpf_DbEngine_Driver_Mysql_Statement extends Gpf_Object {
      private $_handle;
      private $_statement;
      private $result;
      private $autoId = 0;
      
      function execute() {
          $this->result = mysql_query($this->_statement, $this->_handle);
          return $this->result;
      }
      
      public function getAutoIncrementId() {
          return $this->autoId;
      }
      
      public function loadAutoIncrementId() {
          $this->autoId = mysql_insert_id($this->_handle);
      }
      
      function init($statement, $handle) {
          $this->_statement = $statement;
          $this->_handle = $handle;
      }
  
      function getNames() {
          $numFields = mysql_num_fields($this->result);
          $names = array();
          for($i=0; $i<$numFields; $i++) {
              $names[] = mysql_field_name($this->result, $i);
          }
          return $names;
      }
  
      function getTypes() {
          $numFields = mysql_num_fields($this->result);
          $types = array();
          for($i=0; $i<$numFields; $i++) {
              $types[] = $this->translateType(mysql_field_type($this->result, $i));
          }
          return $types;
      }
  
      function fetchArray() {
          return mysql_fetch_assoc($this->result);
      }
  
      function fetchRow() {
          return mysql_fetch_row($this->result);
      }
  
      function fetchAllRows() {
          $rows = array();
          while($row = $this->fetchRow()) {
              $rows[] = $row;
          }
          return $rows;
      }
  
      function rowCount() {
          return mysql_num_rows($this->result);
      }
  
      function affectedRows() {
          return mysql_affected_rows($this->_handle);
      }
  
      function move($rowNumber) {
          return mysql_data_seek($this->result, $rowNumber);
      }
  
      function getErrorMessage() {
          switch(mysql_errno($this->_handle)) {
              case 1062:
                  return 'Duplicate record' . ' ' . $this->_statement;
                  break;
  
          }
          return mysql_error($this->_handle);
      }
  
      function getErrorCode() {
          return mysql_errno($this->_handle);
      }
      
      public function getStatement() {
          return $this->_statement;
      }
  }

} //end Gpf_DbEngine_Driver_Mysql_Statement

if (!class_exists('Gpf_DbEngine_NoRowException', false)) {
  class Gpf_DbEngine_NoRowException extends Gpf_Exception {
      public function __construct($builder) {
          parent::__construct('Row does not exist: ' . $builder->toString());
      }
      
      protected function logException() {
      }
  }

} //end Gpf_DbEngine_NoRowException

if (!class_exists('Gpf_Log_Logger', false)) {
  class Gpf_Log_Logger extends Gpf_Object {
      /**
       * @var array
       */
      static private $instances = array();
      /**
       * @var array
       */
      private $loggers = array();
  
      /**
       * array of custom parameters
       */
      private $customParameters = array();
      
      private $disabledTypes = array();
      
      private $group = null;
      private $type = null;
      private $logToDisplay = false;
      
      /**
       * returns instance of logger class.
       * You can add instance name, if you want to have multiple independent instances of logger
       *
       * @param string $instanceName
       * @return Gpf_Log_Logger
       */
      public static function getInstance($instanceName = '_') {
          if($instanceName == '') {
              $instanceName = '_';
          }
  
          if (!array_key_exists($instanceName, self::$instances)) {
              self::$instances[$instanceName] = new Gpf_Log_Logger();
          }
          $instance = self::$instances[$instanceName];
          return $instance;
      }
      
      public static function isLoggerInsert($sqlString) {
          return strpos($sqlString, 'INSERT INTO ' . Gpf_Db_Table_Logs::getName()) !== false;
      }
      
      /**
       * attachs new log system
       *
       * @param unknown_type $system
       * @return Gpf_Log_LoggerBase
       */
      public function add($type, $logLevel) {
      	if($type == Gpf_Log_LoggerDisplay::TYPE) {
      		$this->logToDisplay = true;
      	}
          return $this->addLogger($this->create($type), $logLevel);
      }
  
      /**
       * Checks if logger with te specified type was already initialized
       *
       * @param unknown_type $type
       * @return unknown
       */
      public function checkLoggerTypeExists($type) {
          if(array_key_exists($type, $this->loggers)) {
          	return true;
          }
      	
          return false;
      }
      
      /**
       * returns true if debugging writes log to display
       *
       * @return boolean
       */
      public function isLogToDisplay() {
      	return $this->logToDisplay && !in_array(Gpf_Log_LoggerDisplay::TYPE, $this->disabledTypes);
      }
      
      public function removeAll() {
          $this->loggers = array();
          $this->customParameters = array();
          $this->disabledTypes = array();
          $this->logToDisplay = false;
          $this->group = null;
      }
      
      /**
       *
       * @param Gpf_Log_LoggerBase $logger
       * @param int $logLevel
       * @return Gpf_Log_LoggerBase
       */
      public function addLogger(Gpf_Log_LoggerBase $logger, $logLevel) {
          $this->enableType($logger->getType());
          if($logger->getType() == Gpf_Log_LoggerDisplay::TYPE) {
              $this->logToDisplay = true;
          }
          if(!$this->checkLoggerTypeExists($logger->getType())) {
          	$logger->setLogLevel($logLevel);
          	$this->loggers[$logger->getType()] = $logger;
          	return $logger;
          } else {
          	$ll = new Gpf_Log_LoggerDatabase();
          	$existingLogger = $this->loggers[$logger->getType()];
          	if($existingLogger->getLogLevel() > $logLevel) {
          		$existingLogger->setLogLevel($logLevel);
          	}
          	return $existingLogger;
          }
      }
      
      public function getGroup() {
          return $this->group;
      }
          
      public function setGroup($group = null) {
          $this->group = $group;
          if($group === null) {
              $this->group = Gpf_Common_String::generateId(10);
          }
      }
      
      public function setType($type) {
          $this->type = $type;
      }
      
      /**
       * function sets custom parameter for the logger
       *
       * @param string $name
       * @param string $value
       */
      public function setCustomParameter($name, $value) {
          $this->customParameters[$name] = $value;
      }
  
      /**
       * returns custom parameter
       *
       * @param string $name
       * @return string
       */
      public function getCustomParameter($name) {
          if(isset($this->customParameters[$name])) {
              return $this->customParameters[$name];
          }
          return '';
      }
  
      /**
       * logs message
       *
       * @param string $message
       * @param string $logLevel
       * @param string $logGroup
       */
      public function log($message, $logLevel, $logGroup = null) {
          $time = time();
          $group = $logGroup;
          if($this->group !== null) {
              $group = $this->group;
              if($logGroup !== null) {
                  $group .= ' ' . $logGroup;
              }
          }
  	
          $callingFile = $this->findLogFile();
          $file = $callingFile['file'];
          if(isset($callingFile['classVariables'])) {
          	$file .= ' '.$callingFile['classVariables'];
          }
          $line = $callingFile['line'];
  
          $ip = Gpf_Http::getRemoteIp();
          if ($ip = '') {
              $ip = '127.0.0.1';
          }
  
          foreach ($this->loggers as $logger) {
          	if(!in_array($logger->getType(), $this->disabledTypes)) {
                  $logger->logMessage($time, $message, $logLevel, $group, $ip, $file, $line, $this->type);
              }
          }
      }
      
      /**
       * logs debug message
       *
       * @param string $message
       * @param string $logGroup
       */
      public function debug($message, $logGroup = null) {
          $this->log($message, Gpf_Log::DEBUG, $logGroup);
      }
  
      /**
       * logs info message
       *
       * @param string $message
       * @param string $logGroup
       */
      public function info($message, $logGroup = null) {
          $this->log($message, Gpf_Log::INFO, $logGroup);
      }
  
      /**
       * logs warning message
       *
       * @param string $message
       * @param string $logGroup
       */
      public function warning($message, $logGroup = null) {
          $this->log($message, Gpf_Log::WARNING, $logGroup);
      }
  
      /**
       * logs error message
       *
       * @param string $message
       * @param string $logGroup
       */
      public function error($message, $logGroup = null) {
          $this->log($message, Gpf_Log::ERROR, $logGroup);
      }
  
      /**
       * logs critical error message
       *
       * @param string $message
       * @param string $logGroup
       */
      public function critical($message, $logGroup = null) {
          $this->log($message, Gpf_Log::CRITICAL, $logGroup);
      }
  
      public function disableType($type) {
          $this->disabledTypes[$type] = $type;
      }
  
      public function enableType($type) {
          if(in_array($type, $this->disabledTypes)) {
              unset($this->disabledTypes[$type]);
          }
      }
      
      public function enableAllTypes() {
          $this->disabledTypes = array();
      }
      
      /**
       *
       * @return Gpf_Log_LoggerBase
       */
      private function create($type) {
          switch($type) {
              case Gpf_Log_LoggerDisplay::TYPE:
                  return new Gpf_Log_LoggerDisplay();
              case Gpf_Log_LoggerFile::TYPE:
                  return new Gpf_Log_LoggerFile();
              case Gpf_Log_LoggerDatabase::TYPE:
              case 'db':
                  return new Gpf_Log_LoggerDatabase();
          }
          throw new Gpf_Log_Exception("Log system '$type' does not exist");
      }
      
      private function findLogFile() {
          $calls = debug_backtrace();
          
          $foundObject = null;
          
          // special handling for sql benchmarks
          if($this->sqlBenchmarkFound($calls)) {
              $foundObject = $this->findFileBySqlBenchmark();
          }
  
          if($foundObject == null) {
              $foundObject = $this->findFileByCallingMethod($calls);
          }
          if($foundObject == null) {
              $foundObject = $this->findLatestObjectBeforeString("Logger.class.php");
          }
          if($foundObject == null) {
              $last = count($calls);
              $last -= 1;
              if($last <0) {
                  $last = 0;
              }
          
              $foundObject = $calls[$last];
          }
          
          return $foundObject;
      }
      
      private function sqlBenchmarkFound($calls) {
          foreach($calls as $obj) {
              if(isset($obj['function']) && $obj['function'] == "sqlBenchmarkEnd") {
                  return true;
              }
          }
          return false;
      }
      
      private function findFileBySqlBenchmark() {
          $foundFile = $this->findLatestObjectBeforeString("DbEngine");
          if($foundFile != null && is_object($foundFile['object'])) {
              $foundFile['classVariables'] = $this->getObjectVariables($foundFile['object']);
          }
          return $foundFile;
      }
      
      private function getObjectVariables($object) {
          if(is_object($object)) {
              $class = get_class($object);
              $methods = get_class_methods($class);
              if(in_array("__toString", $methods)) {
                  return $object->__toString();
              }
          }
          return '';
      }
      
      private function findFileByCallingMethod($calls) {
          $functionNames = array('debug', 'info', 'warning', 'error', 'critical', 'log');
          $foundObject = null;
          foreach($functionNames as $name) {
              $foundObject = $this->findCallingFile($calls, $name);
              if($foundObject != null) {
                  return $foundObject;
              }
          }
          
          return null;
      }
      
      private function findCallingFile($calls, $functionName) {
          foreach($calls as $obj) {
              if(isset($obj['function']) && $obj['function'] == $functionName) {
                  return $obj;
              }
          }
          
          return null;
      }
      
      private function findLatestObjectBeforeString($text) {
          $callsReversed = array_reverse( debug_backtrace() );
      
          $lastObject = null;
          foreach($callsReversed as $obj) {
              if(!isset($obj['file'])) {
                  continue;
              }
              $pos = strpos($obj['file'], $text);
              if($pos !== false && $lastObject != null) {
                  return $lastObject;
              }
              $lastObject = $obj;
          }
          return null;
      }
  }

} //end Gpf_Log_Logger

if (!class_exists('Gpf_Log_LoggerDisplay', false)) {
  class Gpf_Log_LoggerDisplay extends Gpf_Log_LoggerBase {
      const TYPE = 'display';
  
      private $lineFormat = "{TYPE}{GROUP} | {LEVEL} | {TIME} | {MESSAGE} | {IP} | {FILE} | {LINE}<br/>\n";
      private $timeFormat = "%Y-%m-%d %H:%M:%S";
      private $isHtml = true;
  
      public function __construct() {
          parent::__construct(self::TYPE);
      }
  
      public function setTimeFormat($format) {
          $this->timeFormat = $format;
      }
  
      public function setHtml($isHtml) {
          $this->isHtml = $isHtml;
      }
  
      public function setLineFormat($format) {
          $this->lineFormat = $format;
      }
      
      private function inBrowser() {
          return (empty($_SERVER['argv']));
      }
  
      protected function log($time, $message, $logLevel, $logGroup, $ip, $file, $line, $type = null) {
          if($message == "") {
              echo "<br/>";
              return;
          }
           
          if($this->isHtml) {
              $message = str_replace(' ', '&nbsp;', $message);
          }
           
          $timeString = strftime($this->timeFormat, $time);
          $str = $this->lineFormat;
          $str = str_replace('{GROUP}', $logGroup, $str);
          $str = str_replace('{LEVEL}', $this->getLogLevelAsText($logLevel), $str);
          $str = str_replace('{TIME}', $timeString, $str);
          $str = str_replace('{MESSAGE}', $message, $str);
          $str = str_replace('{IP}', $ip, $str);
          $str = str_replace('{FILE}', $file, $str);
          $str = str_replace('{LINE}', $line, $str);
          $str = str_replace('{TYPE}', $type, $str);
          if (!$this->inBrowser()) {
              echo str_replace(array('&nbsp;', '<br/>'), array(' ',"\n"), $str);
          } else {
              echo $str;
          }
      }
  }

} //end Gpf_Log_LoggerDisplay

if (!class_exists('Gpf_Log_LoggerFile', false)) {
  class Gpf_Log_LoggerFile extends Gpf_Log_LoggerBase {
      const TYPE = 'file';
  
      private $lineFormat = "{TYPE}{GROUP} | {LEVEL} | {TIME} | {MESSAGE} | {IP} | {FILE} | {LINE}\r\n";
      private $timeFormat = "%b %d %H:%M:%S";
      private $fileName;
      
      public function __construct() {
          parent::__construct(self::TYPE);
      }
      
      public function setFileName($fileName) {
          $this->fileName = $fileName;
          $this->checkFileIsWritable();
      }
      
      protected function log($time, $message, $logLevel, $logGroup, $ip, $file, $line, $type = null) {
      	$timeString = strftime($this->timeFormat, $time);
  
      	$str = $this->lineFormat;
      	$str = str_replace('{GROUP}', $logGroup, $str);
      	$str = str_replace('{LEVEL}', $this->getLogLevelAsText($logLevel), $str);
      	$str = str_replace('{TIME}', $timeString, $str);
      	$str = str_replace('{MESSAGE}', $message, $str);
      	$str = str_replace('{IP}', $ip, $str);
      	$str = str_replace('{FILE}', $file, $str);
      	$str = str_replace('{LINE}', $line, $str);
      	$str = str_replace('{TYPE}', $type, $str);
      	
      	if($message == "") {
      		$str = " ";
      	}
      	
      	$file = new Gpf_Io_File($this->fileName);
      	try {
      	    $file->open('a');
      	    $file->write($str);
      	} catch (Exception $e) {
              throw new Gpf_Log_Exception("File logging error: " . $e->getMessage());
      	}
      }
      
      private function checkFileIsWritable() {
  	    $file = new Gpf_Io_File($this->fileName);
  	    $file->open('a');
  	}
  }

} //end Gpf_Log_LoggerFile

if (!class_exists('Gpf_ModuleBase', false)) {
  abstract class Gpf_ModuleBase extends Gpf_Object {
      const ERROR_URL = 'errorUrl';
      const SUCCESS_URL = 'successUrl';
  
      const LICENSE = 'license';
      const SESSION = 'session';
      const PLUGINS = 'activePlugins';
  
      protected $styleSheets = array();
      protected $gwtModuleName;
      protected $roleType;
      protected $panelName;
      protected $body;
      private $defaultTheme = '';
  
      public function __construct($gwtModuleName, $panelName, $roleType = '') {
          $this->gwtModuleName = $gwtModuleName;
          $this->panelName = $panelName;
          $this->roleType = $roleType;
      }
  
      /**
       * @return name of the panel (name of directory in which templates for this panel are located
       */
      public function getPanelName() {
          return $this->panelName;
      }
  
      public function getRoleType() {
          return $this->roleType;
      }
  
      protected function getRequestParam($name) {
          if (isset($_REQUEST[$name])) {
              return $_REQUEST[$name];
          }
          return null;
      }
  
      protected function setError(Exception $e) {
          $this->gwtModuleName = '';
          try {
              $template = new Gpf_Templates_Template('start_error.tpl');
              $template->assign('errorMessage', $e->getMessage());
              $this->body = $template->getHTML();
          } catch(Gpf_ResourceNotFoundException $e){
              Gpf_Http::setHeader(Gpf_Net_Server_Http_Response::LOCATION, Gpf_Paths::getInstance()->getInstallDirectoryPath());
          } catch (Exception $outerException) {
              die(sprintf("Fatal startup error: %s (%s)", $e->getMessage(), $outerException->getMessage()));
          }
      }
  
      protected function addJsResource($name) {
          Gpf_Contexts_Module::getContextInstance()->addJsResource(
              Gpf_Paths::getInstance()->getResourceUrl($name));
      }
      
      protected function initJsResources() {
          Gpf_Plugins_Engine::extensionPoint('Core.initJsResources', Gpf_Contexts_Module::getContextInstance());
      }
  
      protected function initStyleSheets() {
          $this->styleSheets = array();
          $this->addStyleSheet("gpf.css");
      }
  
      protected function initData() {
          Gpf_Rpc_CachedResponse::reset();
          $this->initCachedData();
          $this->addSessionInfoToCache();
          $this->addLicenseToCache();
          $this->addPlugins();
      }
  
      
      /**
       * @return boolean
       */
      protected function isAuthUserLogged() {
      	return Gpf_Session::getAuthUser()->isLogged() && Gpf_Session::getAuthUser()->isExists();
      }
      
      private function addLicenseToCache() {
          try {
              $license = Gpf_Settings::get(Gpf_Settings_Gpf::LICENSE);
          } catch (Exception $e) {
              return;
          }
          $data = new Gpf_Rpc_Data();
          $data->setValue(self::LICENSE, $license);
          Gpf_Rpc_CachedResponse::addById($data, self::LICENSE);
      }
  
      private function addPlugins() {
          $plugins = new Gpf_Data_IndexedRecordSet('name');
          $plugins->setHeader(array('name'));
          foreach (Gpf_Plugins_Engine::getInstance()->getConfiguration()->getActivePlugins() as $pluginCode) {
              $plugins->add(array($pluginCode));
          }
          Gpf_Rpc_CachedResponse::addById($plugins, self::PLUGINS);
      }
  
      private function addSessionInfoToCache() {
          $sessionInfo = new Gpf_Rpc_Data();
          $sessionInfo->setValue(Gpf_Rpc_Params::SESSION_ID, Gpf_Session::getInstance()->getId());
          $sessionInfo->setValue('loggedUser', Gpf_Session::getAuthUser()->getFirstName()
          . ' ' . Gpf_Session::getAuthUser()->getLastName());
          $sessionInfo->setValue('loggedUserEmail', Gpf_Session::getAuthUser()->getUsername());
          $sessionInfo->setValue('serverTime', Gpf_DbEngine_Database::getDateString());
          $sessionInfo->setValue('baseUrl', rtrim(Gpf_Paths::getInstance()->getBaseServerUrl(), '/'));
          $this->setSessionInfo($sessionInfo);
          Gpf_Rpc_CachedResponse::addById($sessionInfo, self::SESSION);
      }
  
      protected function setSessionInfo(Gpf_Rpc_Data $sessionInfo) {
      }
  
      protected function initCachedData() {
          $this->renderDictionaryRequest();
          $this->renderTemplatesRequest();
          Gpf_Plugins_Engine::extensionPoint("GpfModuleBase.initCachedData", $this);
      }
      
      protected function renderPermissionsRequest() {
          Gpf_Rpc_CachedResponse::addById(Gpf_Session::getAuthUser()->getPrivileges(), 'permissions');
      }
  
      protected function renderDictionaryRequest() {
          $dictionary = Gpf_Lang_Dictionary::getInstance();
          Gpf_Rpc_CachedResponse::addEncodedById($dictionary->getEncodedClientMessages(), 'langDictionary');
      }
  
      protected function renderTemplatesRequest() {
          $templates = new Gpf_Templates_Templates();
          $templates->addToCache($this->getCachedTemplateNames());
      }
  
      protected function getCachedTemplateNames() {
          return array('notification_window', 'desktop','taskbar','start_button','item_panel',
          'context_menu','quick_launch','sidebar_closed','sidebar','icon_button',
          'main_menu', 'menu_entry', 'menu_section','sub_menu_section',
          'desktop_main_menu','system_menu','desktop_gadget','quick_stats_actions_panel',
          'popup_actions_panel','link_button','gadget_window','gadget_window_topleft',
          'gadget_window_left','gadget_window_bottomleft','gadget_header',
          'item', 'gadget_footer','listbox','listbox_popup','grid_pager',
          'window','window_left','window_header','window_header_refresh','window_bottom_left',
          'window_empty_content','task','page_header','tab_panel','tab_item',
          'grid','grid_nodata','grid_topbuttons','grid_bottombuttons','button',
          'grid_gridline','grid_selector','wallpaper_entry','loading_screen',
          'theme_panel', 'window_move_panel', 'single_content_panel',
          'expired_session_dialog');
      }
  
      protected function addStyleSheets(array $styleSheets) {
          foreach ($styleSheets as $styleSheet) {
              $this->addStyleSheet($styleSheet);
          }
      }
  
      protected function addStyleSheet($styleSheet) {
          $defaultStyle = $this->setStyleSheetPath($styleSheet);
          $this->setStyleSheetPath($styleSheet, false, $defaultStyle);
          
      }
      
      private function setStyleSheetPath($styleSheet, $default = true, $defaultTheme = '') {
          try {
              $themeStyleSheet = Gpf_Paths::getInstance()->getStyleSheetUrl($styleSheet, $default);
              if ($default) {
              	$this->styleSheets[$styleSheet . '_default'] = $themeStyleSheet;
              } elseif ($defaultTheme != $themeStyleSheet) {
                  $this->styleSheets[$styleSheet] = $themeStyleSheet;
              }
              return $themeStyleSheet;
          } catch (Exception $e) {
          	return '';
          }
      }
  
      private static function isAcceptGzipEncoding() {
          if (defined('GZIP_ENCODING_DISABLED')) {
              return false;
          }
          if(isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
              $encodings = explode(',', strtolower(preg_replace("/s+/", "", $_SERVER['HTTP_ACCEPT_ENCODING'])));
              return in_array('gzip', $encodings);
          }
          return false;
      }
  
      private static function isAcceptGzip() {
          return false;
          //return self::isAcceptGzipEncoding() && extension_loaded('zlib');
      }
  
      public static function startGzip() {
          if(self::isAcceptGzip()) {
              ob_start('ob_gzhandler');
          }
      }
      public static function flushGzip() {
          if(self::isAcceptGzip()) {
              ob_end_flush();
          }
      }
  
      protected function getModuleUrl() {
          if (!strlen($this->gwtModuleName)) {
              return false;
          }
          
          if(self::isAcceptGzipEncoding()) {
              try {
                  return Gpf_Paths::getInstance()->getGwtModuleUrl($this->gwtModuleName, true);
              } catch (Gpf_Exception $e) {
              }
          }
          return Gpf_Paths::getInstance()->getGwtModuleUrl($this->gwtModuleName);
      }
  
      protected function getTitle() {
          return $this->_("Application Title");
      }
      
      protected function getMetaDescription() {
          return $this->_('Application meta description');
      }
  
      protected function getMetaKeywords() {
          return $this->_('GwtPHP Framework Application, Gwt, PHP');
      }
      
      
      protected function onStart() {
          $this->initSession();
      }
  
      protected function initSession() {
          $sessionId = null;
          if (array_key_exists('S', $_REQUEST) && $_REQUEST['S'] != '') {
              $sessionId = $_REQUEST['S'];
          }
          Gpf_Session::create($this, $sessionId);
          $this->checkApplication();
      }
  
      protected function checkApplication() {
          if(Gpf_Application::getInstance()->isInMaintenanceMode()) {
              if(Gpf_Application::getInstance()->isInstalled()) {
                  throw new Gpf_Exception($this->_('Maintenance Mode: Update in progress. Please run installer to finish update.'));
              } else {
                  throw new Gpf_Exception($this->_('Maintenance Mode: Install in progress'));
              }
              
          }
      }
  
      protected function getFaviconUrl() {
          if (Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_FAVICON) != '') {
              return Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_FAVICON);
          } else {
              return Gpf_Paths::getInstance()->getImageUrl('favicon.ico');
          }
      }
  
      public function startAndGet() {
          return $this->getContent();
      }
  
      /**
       * Return body template
       *
       * @return Gpf_Templates_Template
       */
      protected function getBodyTemplate() {
          return new Gpf_Templates_Template('module_body.stpl');
      }
      
      final private function getContent() {
          $gwtModule = '';
          try {
              $this->onStart();
              $this->body = $this->getBodyTemplate()->getHTML();
              $this->initData();
              $this->initStyleSheets();
              $this->initJsResources();
              $gwtModule = $this->getModuleUrl();
          } catch (Exception $e) {
               $this->setError($e);
          }
  
          $template = new Gpf_Templates_Template($this->getMainDocumentTemplate());
          $this->assignTemplateVariables($template);
          $template->assign('gwtModuleName', $gwtModule);
  
          return $template->getHTML();
      }
  
      final public function start() {
          $this->checkRequirements();
          $content = $this->getContent();
          self::startGzip();
          echo $content;
          self::flushGzip();
      }
      
      private function checkRequirements() {
          // smarty requirement
          if (ini_get('magic_quotes_runtime') == '1' || ini_get('magic_quotes_runtime') == 'On') {
              die('magic_quotes_runtime has to be turned of in php.ini');
          }
          if(!class_exists('ReflectionClass', false)) {
              die('ReflectionClass not found. Please compile PHP with reflection enabled.');
          }
      }
      
      public function loadStaticPage($pageTemplate, $mainDocumentTemplate = '') {
          if($mainDocumentTemplate == '') {
              $mainDocumentTemplate = $this->getMainDocumentTemplate();
          }
           
          $content = $this->getStaticContent($pageTemplate, $mainDocumentTemplate);
          self::startGzip();
          echo $content;
          self::flushGzip();
      }
  
      protected function getMainDocumentTemplate() {
          return 'main_html_doc.tpl';
      }
  
      private function getStaticContent($pageTemplate, $mainDocumentTemplate) {
          try {
              $this->onStart();
              $this->initStyleSheets();
          } catch (Exception $e) {
              $this->setError($e);
          }
  
          $template = new Gpf_Templates_Template($mainDocumentTemplate);
          $this->assignTemplateVariables($template);
          $template->assign('staticPage', $this->getStaticPage($pageTemplate));
  
          return $template->getHTML();
      }
  
      protected function getStaticPage($pageTemplate) {
          if($pageTemplate == '') {
              return '';
          }
           
          $template = new Gpf_Templates_Template($pageTemplate);
          $this->assignTemplateVariables($template);
          return $template->getHTML();
      }
  
      /**
       * Get array of included css files
       *
       * @return unknown
       */
      public function getStyleSheets() {
          return $this->styleSheets;
      }
  
      protected function assignTemplateVariables(Gpf_Templates_Template $template) {
          $template->assign('title', $this->getTitle());
          $template->assign('metaDescription', $this->getMetaDescription());
          $template->assign('metaKeywords', $this->getMetaKeywords());
          $template->assign('cachedData', Gpf_Rpc_CachedResponse::render());
          $template->assign('stylesheets', $this->styleSheets);
          $template->assign('jsResources', Gpf_Contexts_Module::getContextInstance()->getJsResources());
          $template->assign('jsScripts', Gpf_Contexts_Module::getContextInstance()->getJsScripts());
          $template->assign('body', $this->body);
          $template->assign('faviconUrl', $this->getFaviconUrl());
      }
  
      protected function authenticate() {
          $loginRequest = new Gpf_Auth_Service();
          $loginRequest->authenticateNoRpc();
          $loginRequest->registerLogin();
          Gpf_Session::refreshAuthUser();
      }
  
      public function assignModuleAttributes(Gpf_Templates_Template $template) {
          $template->assign('qualityUnitBaseLink', Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_TEXT_BASE_LINK));
          $template->assign('qualityUnit', Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_QUALITY_UNIT));
          $template->assign('qualityUnitCompanyLink', Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_QUALITYUNIT_COMPANY_LINK));
          $template->assign('qualityUnitPrivacyPolicyLink', Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_QUALITYUNIT_PRIVACY_POLICY_LINK));
          $template->assign('qualityUnitContactUsLink', Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_QUALITYUNIT_CONTACT_US_LINK));
          $template->assign('supportEmail', Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_QUALITYUNIT_SUPPORT_EMAIL));
          $template->assign('qualityUnitAddonsLink', Gpf_Settings::get(Gpf_Settings_Gpf::BRANDING_QUALITYUNIT_ADDONS_LINK));
          Gpf_Plugins_Engine::extensionPoint('Core.assignModuleAttributes', $template);
      }
  
      /*************************************************************/
      /***************** Post request processing *******************/
      /*************************************************************/
  
      protected function isPostRequest() {
          return (is_array($_POST) && count($_POST) > 0);
      }
  
      protected function processPostRequest() {
          $this->debug("Processing form by POST called");
          $this->debug($this->convertPostValuesToString());
  
          $handler = new Gpf_Rpc_FormHandler();
          $params = new Gpf_Rpc_Params($handler->decode($_POST));
  
          try {
              $response = $this->executePostRequest($params);
          } catch (Gpf_Exception $e) {
              return null;
          }
  
          $url = '';
          if ($response->isSuccessful() && $this->getPostVariable(self::SUCCESS_URL) != "") {
              $url = $this->getPostVariable(self::SUCCESS_URL);
          } else if($this->getPostVariable(self::ERROR_URL) != "") {
              $url = $this->getPostVariable(self::ERROR_URL);
          }
  
          if($url != '') {
              echo $this->postResponseTo($url, $response);
              $this->debug("Finished signup form, posting response to URL: $url");
              exit;
          }
  
          $this->debug("Finished signup form processing, displaying after signup page");
          return $response;
      }
  
      /**
       * @param Gpf_Rpc_Params $params
       * @return Gpf_Rpc_Form
       */
      protected function executePostRequest(Gpf_Rpc_Params $params) {
          throw new Gpf_Exception("executePostRequest is not implemented in this module");
      }
  
      private function convertPostValuesToString() {
          $txt = 'Post parameters: ';
          foreach($_POST as $k =>$v) {
              $txt .= "$k = $v,";
          }
  
          return $txt;
      }
  
      private function getPostVariable($name) {
          if (!array_key_exists($name, $_POST)) {
              return "";
          }
          return $_POST[$name];
      }
  
      private function postResponseTo($url, $response) {
          $template = new Gpf_Templates_Template("post_response.stpl");
          $cumulativeErrorMessage = "";
          $fields = array();
          foreach ($response as $field) {
              $error = $field->get(Gpf_Rpc_Form::FIELD_ERROR);
              $fields[] = array("name" => $field->get(Gpf_Rpc_Form::FIELD_NAME),
                                "value" => $field->get(Gpf_Rpc_Form::FIELD_VALUE),
                                "error" => $error);
              if ($error != "") {
                  $cumulativeErrorMessage .= $error . "<br>";
              }
          }
          if ($response->getErrorMessage() != '') {
              $cumulativeErrorMessage .= $response->getErrorMessage() . "<br>";
          }
          $template->assign('fields', $fields);
          $template->assign('errorMessage', $response->getErrorMessage());
          $template->assign('cumulativeErrorMessage', $cumulativeErrorMessage);
          $template->assign('successMessage', $response->getInfoMessage());
          $template->assign('url', $url);
          return $template->getHTML();
      }
  
      protected function debug($msg) {
          Gpf_Log::debug($msg);
      }
  
      protected function initDefaultTheme($theme) {
          $this->defaultTheme = $theme;
      }
  
      public function isThemeValid($theme) {
          if(strpos($theme, '_') === 0) {
              return false;
          }
          $themePath = Gpf_Paths::getInstance()->getTopTemplatePath() .
          $this->getPanelName() . '/' .
          $theme . '/';
          if (!Gpf_Io_File::isFileExists($themePath)
          || !Gpf_Io_File::isFileExists($themePath . Gpf_Desktop_Theme::CONFIG_FILE_NAME)) {
              return false;
          }
          
          $themeObj = new Gpf_Desktop_Theme($theme, $this->getPanelName());
          return $themeObj->isEnabled();
      }
  
      public function getDefaultTheme() {
          if($this->isThemeValid($this->defaultTheme)) {
              return $this->defaultTheme;
          }
          return $this->getFirstAvailableTheme();
      }
  
      private function getFirstAvailableTheme() {
          $themeManager = new Gpf_Desktop_ThemeManager();
          return $themeManager->getFirstTheme($this->panelName);
      }
  
      /**
       * Overwrite this function in specific module.
       * Function should return default demo username.
       *
       * @return string
       */
      public function getDemoUsername() {
          return '';
      }
  
      /**
       * Overwrite this function in specific module.
       * Function should return default demo password.
       *
       * @return string
       */
      public function getDemoPassword() {
          return '';
      }
  }

} //end Gpf_ModuleBase

if (!class_exists('Gpf_LoginModule', false)) {
  class Gpf_LoginModule extends Gpf_ModuleBase {
      private $loginResponse;
      protected $panelUrl = 'index.php';
  
      protected function tryToLogin() {
          if (!$this->isAuthUserLogged()) {
              $this->authenticate();
          }
  
          if ($this->isAuthUserLogged()) {
              $this->redirectToPanel();
          }
      }
      
      protected function redirectToPanel() {
      	Gpf_Http::setHeader(Gpf_Net_Server_Http_Response::LOCATION, $this->panelUrl);
          exit;
      }
  
      protected function initJsResources() {
      }
  
      protected function onStart() {
          parent::onStart();
          if ($this->isPostRequest()) {
              $this->loginResponse = $this->processPostRequest();
          }
          $this->tryToLogin();
      }
  
      protected function initCachedData() {
          parent::initCachedData();
          $this->renderLoginRequest();
          $this->renderLanguagesRequest();
          $this->renderLoginFormLoadRequest();
      }
  
      protected function renderLoginRequest() {
          if ($this->loginResponse != null) {
              Gpf_Rpc_CachedResponse::add($this->loginResponse, "Gpf_Auth_Service", "authenticate", "loginRequest");
          }
      }
  
      protected function renderLoginFormLoadRequest() {
          $service = new Gpf_Auth_Service();
          Gpf_Rpc_CachedResponse::add($service->loadNoRpc(), "Gpf_Auth_Service", "load");
      }
  
      protected function renderLanguagesRequest() {
          $languages = Gpf_Lang_Languages::getInstance();
          Gpf_Rpc_CachedResponse::add($languages->getActiveLanguagesNoRpc(), "Gpf_Lang_Languages", "getActiveLanguages");
      }
  
      protected function executePostRequest(Gpf_Rpc_Params $params) {
          $authService = new Gpf_Auth_Service();
          return $authService->authenticate($params);
      }
  
      protected function getCachedTemplateNames() {
          return array_merge(parent::getCachedTemplateNames(), array('login_main', 'window', 'window_left', 'window_header',
          'window_bottom_left', 'window_empty_content','icon_button','login_form',
          'tooltip_popup','form_field', 'button', 'link_button','listbox',
          'listbox_popup','grid_pager'));
      }
  }

} //end Gpf_LoginModule

if (!class_exists('Pap_AffiliateLogin', false)) {
  class Pap_AffiliateLogin extends Gpf_LoginModule {
      protected $panelUrl = 'panel.php';
  
      public function __construct() {
          parent::__construct('com.qualityunit.pap.AffiliateLoginModule', 'affiliates', Pap_Application::ROLETYPE_AFFILIATE);
      }
  
      protected function getMainDocumentTemplate() {
          return 'main_aff_html_doc.stpl';
      }
  
      protected function getTitle() {
          return Gpf_Settings::get(Pap_Settings::BRANDING_TEXT_POST_AFFILIATE_PRO);
      }
  
      protected function initStyleSheets() {
          parent::initStyleSheets();
          $this->addStyleSheets(Pap_Module::getStyleSheets());
      }
  
      protected function setSessionInfo(Gpf_Rpc_Data $sessionInfo) {
          Pap_Module::setSessionInfo($sessionInfo);
      }
  
      protected function initCachedData() {
          parent::initCachedData();
          $this->renderApplicationSettingsRequest();
      }
  
      protected function getCachedTemplateNames() {
          return array('notification_window', 'icon_button', 'link_button',
          'listbox','listbox_popup', 'button', 'loading_screen', 'window','window_left',
          'window_header','window_bottom_left', 'grid_pager',
          'window_empty_content', 'login_main', 'login_form', 'tooltip_popup', 'form_field', 'select_account_form');
      }
  
      public function getDefaultTheme() {
          $this->initDefaultTheme(Gpf_Settings::get(Pap_Settings::DEFAULT_AFFILIATE_PANEL_THEME));
          return parent::getDefaultTheme();
  
      }
  
      public function assignModuleAttributes(Gpf_Templates_Template $template) {
          parent::assignModuleAttributes($template);
          Pap_Module::assignTemplateVariables($template);
          $template->assign(Pap_Settings::PROGRAM_NAME, $this->_localize(Gpf_Settings::get(Pap_Settings::PROGRAM_NAME)));
          $template->assign(Pap_Settings::PROGRAM_LOGO, Gpf_Settings::get(Pap_Settings::PROGRAM_LOGO));
      }
       
      protected function renderApplicationSettingsRequest() {
          $settings = new Pap_LoginApplicationSettings();
          Gpf_Rpc_CachedResponse::add($settings->getSettingsNoRpc(), "Pap_LoginApplicationSettings", "getSettings");
      }
  
      protected function assignTemplateVariables($template) {
          parent::assignTemplateVariables($template);
          Pap_Module::assignTemplateVariables($template);
      }
  
      /**
       * Overwrite this function in specific module.
       * Function should return default demo username.
       *
       * @return string
       */
      public function getDemoUsername() {
          return Pap_Branding::DEMO_AFFILIATE_USERNAME;
      }
  
      /**
       * Overwrite this function in specific module.
       * Function should return default demo password.
       *
       * @return string
       */
      public function getDemoPassword() {
          return Pap_Branding::DEMO_PASSWORD;
      }
  }

} //end Pap_AffiliateLogin

if (!class_exists('Gpf_Session', false)) {
  class Gpf_Session extends Gpf_Object {
      const AUTH_USER = 'AuthUser';
      const MODULE = 'Module';
      const TIME_OFFSET = 'timeOffset';
      
      private $name;
      protected $started = false;
      /**
       * @var Gpf_Auth_User
       */
      protected $authUser;
      /**
       * @var Gpf_Session
       */
      protected static $instance = null;
  
      public static function refreshAuthUser() {
          self::$instance->createAuthUser();
      }
      
      /**
       *
       * @return Gpf_Session
       */
      public static function getInstance() {
          if(self::$instance === null) {
              throw new Gpf_Exception('Session not initialized.');
          }
          return self::$instance;
      }
      
      public static function getRoleType() {
          return self::getModule()->getRoleType();
      }
      
      public static function create(Gpf_ModuleBase $module = null, $sessionId = null, $start = true) {
          if($module === null) {
              $module = new Gpf_System_Module();
          }
          if (self::$instance != null) {
              return;
          }
          self::$instance = new Gpf_Session(self::getSessionName($module->getRoleType()));
          if ($sessionId !== null) {
              self::$instance->setId($sessionId);
          }
          if ($start) {
              self::$instance->start();
          }
          self::$instance->setVarRaw(self::MODULE, $module);
          self::$instance->createAuthUser();
      }
      
      /**
       * Load session and compute if session is not expired
       *
       * @param string $sessionId
       */
      public static function load($sessionId) {
          if (self::$instance != null) {
              return;
          }
          self::$instance = new Gpf_Session(self::getSessionName('RPC'));
          if ($sessionId !== null) {
              self::$instance->setId($sessionId);
          }
          self::$instance->start();
          
          if (!self::$instance->existsVar(self::AUTH_USER)) {
              throw new Gpf_Rpc_SessionExpiredException();
          }
          
          self::$instance->createAuthUser();
      }
      
      /**
       * @return Gpf_Auth_User
       */
      public static function getAuthUser() {
          if(self::getInstance()->authUser === null) {
              throw new Gpf_Exception('AuthUser not created yet');
          }
          return self::getInstance()->authUser;
      }
      
      /**
       * @return Gpf_ModuleBase
       */
      public static function getModule() {
          if(self::getInstance()->getVar(self::MODULE) === null) {
              throw new Gpf_Exception('Module not set yet');
          }
          return self::getInstance()->getVar(self::MODULE);
      }
      
      public static function set(Gpf_Session $session) {
          self::$instance = $session;
      }
      
      public function save(Gpf_Auth_User $authUser) {
          $this->setVarRaw(self::AUTH_USER, $authUser);
          $this->authUser = $authUser;
      }
      
      public function getId() {
          return session_id();
      }
      
      public static function getSessionName($panel) {
          return $panel . '_' . Gpf_Application::getInstance()->getCode() . "_sid";
      }
      
      public function getName() {
          return $this->name;
      }
      
      public function setVar($var, $value) {
          if($var == self::AUTH_USER || $var == self::MODULE || $var == self::TIME_OFFSET) {
              throw new Gpf_Exception("Reserved session variable");
          }
          $this->setVarRaw($var, $value);
      }
      
      public function getVar($var) {
          if($this->existsVar($var)) {
              return $_SESSION[$var];
          }
          return false;
      }
  
      public function destroy() {
          $this->authUser = null;
          if($this->isStarted()) {
              session_unset();
              session_destroy();
          }
          $this->started = false;
          if (isset($_COOKIE[$this->name])) {
              setcookie($this->name, '', time()-42000, '/');
          }
      }
      
      protected function __construct($name = null) {
          if($name !== null) {
              $this->name = $name;
          }
      }
      
      protected function setId($id) {
          session_id($id);
      }
      
      public function existsVar($var) {
          return isset($_SESSION) && isset($_SESSION[$var]);
      }
      
      protected function start() {
          if(!$this->isStarted()) {
              if(strlen($this->name)) {
                  session_name($this->name);
              }
              if (!session_id()) {
                  @session_start();
                  @session_regenerate_id();
              } else {
                  @session_start();
              }
              $this->started = true;
          }
      }
      
      protected function createAuthUser() {
          if (!$this->existsVar(self::AUTH_USER)) {
              $authUser = Gpf::newObj(Gpf_Application::getInstance()->getAuthClass());
              $this->authUser = $authUser->createAnonym();
              $this->save($this->authUser);
          } else {
              $this->authUser = $this->getVar(self::AUTH_USER);
          }
          $this->authUser->init();
      }
      
      private function isStarted() {
          return $this->started;
      }
      
      protected function setVarRaw($var, $value) {
          $_SESSION[$var] = $value;
      }
  
      /**
       * Set time difference between client and server in seconds
       * Offset is computed as clientTime - serverTime
       *      *
       * @param integer $offset Time difference between client and server in seconds
       */
      public function setTimeOffset($offset) {
          $this->setVarRaw(self::TIME_OFFSET, $offset);
      }
      
      /**
       * Get time offset between client and server in seconds.
       * Offset is computed as clientTime - serverTime = offset
       *
       * @return integer time difference between client and server in seconds
       */
      public function getTimeOffset() {
      	if ($this->getVar(self::TIME_OFFSET) !== false) {
      		return $this->getVar(self::TIME_OFFSET);
      	}
          return 0;
      }
  }

} //end Gpf_Session

if (!class_exists('Gpf_Auth_User', false)) {
  abstract class Gpf_Auth_User extends Gpf_Object implements Gpf_Templates_HasAttributes {
      protected $accountUserId;
      protected $authId;
      protected $firstname;
      protected $lastname;
      protected $theme;
      protected $language;
      protected $authtoken;
      protected $username;
      protected $accountid;
      protected $roletypeid;
      protected $roleid;
      protected $ip;
      protected $privileges = array();
  
      const THEME_ATTRIBUTE_NAME = 'theme';
      const LANGUAGE_ATTRIBUTE_NAME = 'language';
  
      const REQUEST_AUTH_TOKEN = 'authToken';
  
      public function __construct() {
      }
  
      public function getTheme() {
          return $this->theme;
      }
  
      public function setTheme($theme) {
          if (!Gpf_Session::getModule()->isThemeValid($theme)) {
              $theme = Gpf_Session::getModule()->getDefaultTheme();
          }
          $this->theme = $theme;
          Gpf_Db_Table_UserAttributes::getInstance()->setSetting(self::THEME_ATTRIBUTE_NAME, $theme, $this->getAccountUserId());
      }
  
      public function setLanguage($language) {
          $this->language = $language;
      }
  
      public function createAnonym() {
          return new Gpf_Auth_Anonym();
      }
  
      public function createPrivilegedUser() {
          return new Gpf_Tasks_PrivilegedUser();
      }
  
      public function getLanguage() {
          return $this->language;
      }
  
      /**
       * Init language attribute
       * If not set, load default value
       * If set url parameter l, overwite existing setting
       *
       */
      private function initLanguage() {
          if (!strlen($this->language)) {
              $this->language = Gpf_Lang_Dictionary::getDefaultLanguage();
          } else if (isset($_REQUEST[Gpf_Lang_Dictionary::LANGUAGE_REQUEST_PARAMETER])
          && Gpf_Lang_Dictionary::isLanguageSupported($_REQUEST[Gpf_Lang_Dictionary::LANGUAGE_REQUEST_PARAMETER]) ) {
              //overwrite existing language setting, because it is defined in URL parameter, which is stronger as other settings
              $this->language = $_REQUEST[Gpf_Lang_Dictionary::LANGUAGE_REQUEST_PARAMETER];
          }
      }
  
      public function init() {
          $this->initLanguage();
  
          try {
              $this->setLanguage(Gpf_Lang_Dictionary::getInstance()->load($this->getLanguage()));
          } catch (Exception $e) {
          }
          $this->loadTheme();
          if (!Gpf_Session::getModule()->isThemeValid($this->theme)) {
              $this->setTheme(Gpf_Session::getModule()->getDefaultTheme());
          }
      }
  
      protected function loadTheme() {
          try {
              $attributes = Gpf_Db_Table_UserAttributes::getInstance();
              $attributes->loadAttributes($this->accountUserId);
              $this->theme = $attributes->getAttribute(self::THEME_ATTRIBUTE_NAME);
          } catch (Exception $e) {
          }
      }
  
      public function __wakeup() {
      }
  
      public function getAccountUserId() {
          return $this->accountUserId;
      }
  
      public function setAccountUserId($accountUserId) {
          $this->accountUserId = $accountUserId;
      }
  
      public function getAttributes() {
          $ret = array();
          $ret['isLogged'] = $this->isLogged();
          if ($this->isLogged()) {
              $ret['username'] = $this->getUsername();
              $ret['firstname'] = $this->getFirstName();
              $ret['lastname'] = $this->getLastName();
              $ret['ip'] = $this->getIp();
          }
          return $ret;
      }
  
      public function getAccountId() {
          return $this->accountid;
      }
  
      public function setAccountId($accountId) {
          $this->accountid = $accountId;
      }
  
      public function isLogged() {
          return true;
      }
      
      public function isExists() {
      	$select = new Gpf_SqlBuilder_SelectBuilder();
          $select->select->add('au.'.Gpf_Db_Table_AuthUsers::ID, Gpf_Db_Table_AuthUsers::ID);
          $select->from->add(Gpf_Db_Table_Users::getName(), 'u');
          $select->from->addInnerJoin(Gpf_Db_Table_AuthUsers::getName(), 'au',
              'u.'.Gpf_Db_Table_Users::AUTHID.'=au.'.Gpf_Db_Table_AuthUsers::ID);
          $select->from->addInnerJoin(Gpf_Db_Table_Roles::getName(), 'r',
              'u.'.Gpf_Db_Table_Users::ROLEID.'=r.'.Gpf_Db_Table_Roles::ID);
          $select->where->add('au.'.Gpf_Db_Table_AuthUsers::ID, '=', $this->authId);
          $select->where->add('u.'.Gpf_Db_Table_Roles::ID, '=', $this->roleid);
          $select->where->add('u.'.Gpf_Db_Table_Users::ACCOUNTID, '=', $this->accountid);
          try {
          	$select->getOneRow();
          	return true;
          } catch (Gpf_Exception $e) {
          }
          return false;
      }
  
      protected function getRememberMeCookieName() {
          return Gpf_Session::getRoleType() . "_auth";
      }
  
      public function getRemeberMeToken() {
          if (!array_key_exists($this->getRememberMeCookieName(), $_COOKIE)) {
              throw new Gpf_Exception('Auth token is empty');
          }
          $authToken = $_COOKIE[$this->getRememberMeCookieName()];
          if (strlen($authToken) != Gpf_Db_AuthUser::AUTH_TOKEN_LENGTH) {
              throw new Gpf_Exception('Wrong Auth token');
          }
          return $authToken;
      }
  
      public function saveRememberMeCookie() {
          Gpf_Http::setCookie($this->getRememberMeCookieName(), $this->authtoken, time()+60*60*24*356, '/');
      }
  
      public function clearRememberMeCookie() {
          Gpf_Http::setCookie($this->getRememberMeCookieName(), false, time() - 72000, '/');
      }
  
      /**
       * @param Gpf_Auth_Info $authInfo
       * @return Gpf_SqlBuilder_SelectBuilder
       */
      protected function createAuthSelect(Gpf_Auth_Info $authInfo) {
          $select = new Gpf_SqlBuilder_SelectBuilder();
  
          $select->select->add('u.accountuserid', 'accountuserid');
          $select->select->add('r.roletype', 'roletypeid');
          $select->select->add('r.roleid', 'roleid');
          $select->select->add('au.authid', 'authid');
          $select->select->add('au.authtoken', 'authtoken');
          $select->select->add('au.username', 'username');
          $select->select->add('au.firstname', 'firstname');
          $select->select->add('au.lastname', 'lastname');
          $select->select->add('au.ip', 'ip');
          $select->select->add('u.accountid', 'accountid');
          $select->select->add('u.rstatus', 'rstatus');
  
          $select->from->add(Gpf_Db_Table_Users::getName(), 'u');
          $select->from->addInnerJoin('qu_g_authusers', 'au', 'u.authid=au.authid');
          $select->from->addInnerJoin('qu_g_roles', 'r', 'u.roleid=r.roleid');
  
          $authInfo->addWhere($select);
  
          return $select;
      }
  
      protected function loadAuthData(Gpf_Data_Record $data) {
          $this->username = $data->get("username");
          $this->accountUserId = $data->get("accountuserid");
          $this->authtoken = $data->get("authtoken");
          $this->accountid = $data->get("accountid");
          $this->roletypeid = $data->get("roletypeid");
          $this->roleid = $data->get('roleid');
          $this->authId = $data->get('authid');
          $this->firstname = $data->get('firstname');
          $this->lastname = $data->get('lastname');
          $this->ip = $data->get('ip');
  
          $attributes = Gpf_Db_Table_UserAttributes::getInstance();
          $attributes->loadAttributes($this->accountUserId);
          $this->setLanguage($attributes->getAttributeWithDefaultValue(self::LANGUAGE_ATTRIBUTE_NAME,
          Gpf_Lang_Dictionary::getDefaultLanguage()));
      }
  
      /**
       * @param string $object
       * @param string $privilege
       * @return boolean
       */
      public function hasPrivilege($object, $privilege) {
          if (array_key_exists($object, $this->privileges)) {
              $objectPrivileges = $this->privileges[$object];
              if (array_key_exists($privilege, $objectPrivileges) ||
              array_key_exists(Gpf_Privileges::P_ALL, $objectPrivileges)) {
                  return true;
              }
          }
          return false;
      }
      
      /**
       * @return Gpf_Data_RecordSet
       */
      public function getPrivileges() {
          $privileges = new Gpf_Data_RecordSet();
          $privileges->setHeader(array('name'));
          foreach ($this->privileges as $object => $objectPrivileges) {
              foreach ($objectPrivileges as $privilege => $value) {
                  $privileges->add(array($object.'|'.$privilege));
              }
          }
          return $privileges;
      }
  
      /**
       * @param Gpf_Auth_Info $authInfo
       * @return Gpf_Auth_User
       */
      public function load(Gpf_Auth_Info $authInfo) {
          Gpf_Session::getAuthUser()->setAccountId($authInfo->getAccountId());
          $authData = $this->createAuthSelect($authInfo)->getOneRow();
          if ($authData->get('rstatus') != Gpf_Db_User::APPROVED) {
              throw new Gpf_Auth_Exception($this->_("User account not approved yet"));
          }
          $authUser = $this->createUser($authData);
          $authUser->loadAfterAuthentication($authData);
          return $authUser;
      }
  
      final public function loadAfterAuthentication(Gpf_Data_Record $authData) {
          $this->loadAuthData($authData);
          $this->privileges = Gpf_Privileges::loadPrivileges($this->roleid);
          $this->init();
      }
  
      /**
       *
       * @param Gpf_Auth_Info $authInfo
       * @return Gpf_Auth_User
       */
      protected function createUser(Gpf_Data_Record $authInfo) {
          return $this;
      }
  
      public function getUsername() {
          return $this->username;
      }
  
      public function getFirstName() {
          return $this->firstname;
      }
  
      public function getLastName() {
          return $this->lastname;
      }
  
      public function getIp() {
          return $this->ip;
      }
  
      /**
       * Update last known IP address of logged in user
       *
       * @param $ip New ip address
       */
      public function updateIp($ip) {
          $this->ip = $ip;
          try {
              //update last known ip address of user in auth user row
              $dbAuthUser = new Gpf_Db_AuthUser();
              $dbAuthUser->setId($this->getAuthUserId());
              $dbAuthUser->updateIp($ip);
          } catch (Gpf_Exception $e) {
          }
      }
  
      public function getUserId() {
          return $this->accountUserId;
      }
  
      public function getAuthUserId() {
          return $this->authId;
      }
  
      public function getRoleTypeId() {
          return $this->roletypeid;
      }
  
      public function getAuthToken() {
          return $this->authtoken;
      }
  
      public function logout() {
          Gpf_Log::info($this->_sys('Logged out user %s', $this->getUsername()));
          Gpf_Db_LoginHistory::logLogout();
  
          $this->clearRememberMeCookie();
          Gpf_Session::getInstance()->destroy();
  
          Gpf_Plugins_Engine::extensionPoint('Gpf_Auth_User.logout', $this);
      }
  }

} //end Gpf_Auth_User

if (!class_exists('Gpf_Auth_Anonym', false)) {
  class Gpf_Auth_Anonym extends Gpf_Auth_User {
  
      public function isLogged() {
          return false;
      }
  
      public function getAccountId() {
          if ($this->accountid === null) {
              throw new Gpf_Exception("No accountId defined for Anonym user");
          }
          return parent::getAccountId();
      }
  
      public function init() {
          $this->theme = '';
  
          parent::init();
      }
  
      public function setTheme($themeId) {
          $this->theme = $themeId;
      }
  
      public function getUserId() {
          throw new Gpf_Exception("No userId defined for Anonymous user");
      }
      
      public function isExists() {
      	return true;
      }
  }
  

} //end Gpf_Auth_Anonym

if (!class_exists('Pap_AnonymUser', false)) {
  class Pap_AnonymUser extends Gpf_Auth_Anonym {
  
      function __construct() {
          $this->accountid = $this->resolveAccountId();
      }
  
      public function isLogged() {
          return false;
      }
  
      private function resolveAccountId() {
          return Gpf_Db_Account::DEFAULT_ACCOUNT_ID;
      }
  
      public function getPapUserId() {
          throw new Gpf_Exception("No userId defined for Anonymous user");
      }
      
      public function isMerchant() {
          return false;
      }
  
      public function isAffiliate() {
          return false;
      }
  
      public function isMasterMerchant() {
          return false;
      }
      
      public function isDefaultAccount() {
          return $this->getAccountId() === Gpf_Db_Account::DEFAULT_ACCOUNT_ID;
      }
      
      public function isNetworkMerchant() {
          return false;
      }
  }

} //end Pap_AnonymUser

if (!class_exists('Gpf_Lang_Languages', false)) {
  class Gpf_Lang_Languages extends Gpf_Object {
  
      protected function __construct() {
      }
  
      /**
       * @return Gpf_Lang_Languages
       */
      public static function getInstance($ignoreInstall = false) {
          if (Gpf_Paths::getInstance()->isInstallModeActive() && !$ignoreInstall) {
              return new Gpf_Lang_InstallLanguages();
          }
          return new Gpf_Lang_Languages();
      }
  
      /**
       * Get recordset of active languages in this account
       *
       * @return Gpf_Data_IndexedRecordSet
       */
      public function getActiveLanguagesNoRpc() {
          $sql = new Gpf_SqlBuilder_SelectBuilder();
          $sql->select->add(Gpf_Db_Table_Languages::CODE);
          $sql->select->add(Gpf_Db_Table_Languages::ENGLISH_NAME);
          $sql->select->add(Gpf_Db_Table_Languages::NAME);
          $sql->select->add(Gpf_Db_Table_Languages::IS_DEFAULT);
          $sql->from->add(Gpf_Db_Table_Languages::getName());
          $sql->where->add(Gpf_Db_Table_Accounts::ID, '=', Gpf_Application::getInstance()->getAccountId());
          $sql->where->add(Gpf_Db_Table_Languages::ACTIVE, '=', Gpf::YES);
          $sql->orderBy->add(Gpf_Db_Table_Languages::NAME);
          return $sql->getAllRowsIndexedBy(Gpf_Db_Table_Languages::CODE);
      }
  }

} //end Gpf_Lang_Languages

if (!class_exists('Gpf_Db_Table_Languages', false)) {
  class Gpf_Db_Table_Languages extends Gpf_DbEngine_Table {
      const ID = 'languageid';
      const CODE = 'code';
      const NAME = 'name';
      const ENGLISH_NAME = 'eng_name';
      const ACTIVE = 'active';
      const AUTHOR = 'author';
      const VERSION = 'version';
      const IMPORTED = 'imported';
      const DATE_FORMAT = 'dateformat';
      const TIME_FORMAT = 'timeformat';
      const THOUSANDS_SEPARATOR = 'thousandsseparator';
      const DECIMAL_SEPARATOR = 'decimalseparator';
      const TRANSLATED_PERCENTAGE = 'translated';
      const IS_DEFAULT = 'is_default';
      const ACCOUNTID = 'accountid';
  
      private static $instance;
  
      /**
       * Default language object
       *
       * @var Gpf_Db_Language
       */
      private $defaultLanguage = null;
  
      /**
       * @return Gpf_Db_Table_Languages
       */
      public static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new self;
          }
          return self::$instance;
      }
  
      protected function initName() {
          $this->setName('g_languages');
      }
  
      public static function getName() {
          return self::getInstance()->name();
      }
  
      protected function initColumns() {
          $this->createPrimaryColumn(self::ID, 'char', 40, false);
          $this->createColumn(self::CODE, 'char', 5);
          $this->createColumn(self::NAME, 'varchar', 64);
          $this->createColumn(self::ENGLISH_NAME, 'varchar', 64);
          $this->createColumn(self::ACTIVE, 'char', 1);
          $this->createColumn(self::AUTHOR, 'varchar', 255);
          $this->createColumn(self::VERSION, 'varchar', 40);
          $this->createColumn(self::IMPORTED, 'datetime');
          $this->createColumn(self::ACCOUNTID, 'char', 8);
          $this->createColumn(self::DATE_FORMAT, 'varchar', 64);
          $this->createColumn(self::TIME_FORMAT, 'varchar', 64);
          $this->createColumn(self::THOUSANDS_SEPARATOR, 'varchar', 1);
          $this->createColumn(self::DECIMAL_SEPARATOR, 'varchar', 1);
          $this->createColumn(self::TRANSLATED_PERCENTAGE, 'int');
          $this->createColumn(self::IS_DEFAULT, 'char', 1);
      }
  
      /**
       * Unset on all languages status default language
       */
      public function unsetDefaultLanguage($defaultLanguageId) {
          $sql = new Gpf_SqlBuilder_UpdateBuilder();
          $sql->from->add(self::getName());
          $sql->where->add(Gpf_Db_Table_Accounts::ID, '=', Gpf_Application::getInstance()->getAccountId());
          $sql->where->add(Gpf_Db_Table_Languages::ID, '<>', $defaultLanguageId);
          $sql->set->add(self::IS_DEFAULT, Gpf::NO);
          $sql->execute();
      }
  
      /**
       * Load default language for this account
       *
       * @return Gpf_Db_Language
       */
      public function getDefaultLanguage() {
          if ($this->defaultLanguage == null) {
              $this->defaultLanguage = new Gpf_Db_Language();
              $this->defaultLanguage->setIsDefault(true);
              $this->defaultLanguage->setAccountId(Gpf_Session::getInstance()->getAuthUser()->getAccountId());
              $this->defaultLanguage->loadFromData(array(Gpf_Db_Table_Accounts::ID, self::IS_DEFAULT));
          }
          return $this->defaultLanguage;
      }
  
      public function recomputeTranslationPercentage($languageId) {
          $lang = new Gpf_Db_Language();
          $lang->setId($languageId);
          $lang->load();
  
          $fileName = Gpf_Lang_CsvLanguage::getAccountCsvFileName($language->getCode());
          $csvLanguage = new Gpf_Lang_CsvLanguage();
          $csvLanguage->loadFromCsvFile(new Gpf_Io_Csv_Reader($fileName));
  
          $lang->setTranslatedPercentage($csvLanguage->getTranslationPercentage());
          $lang->update(array(self::TRANSLATED_PERCENTAGE));
      }
  }

} //end Gpf_Db_Table_Languages

if (!class_exists('Gpf_Db_Table_Accounts', false)) {
  class Gpf_Db_Table_Accounts extends Gpf_DbEngine_Table {
      const ID = 'accountid';
      const NAME = 'name';
      const STATUS = 'rstatus';
      const EMAIL = 'email';
      const APPLICATION = 'application';
      const DATEINSERTED = 'dateinserted';
      const AGREEMENT = 'agreement';
      const ACCOUNT_NOTE = 'accountnote';
      const SYSTEM_NOTE = 'systemnote';
  
      private static $instance;
  
      public static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new self;
          }
          return self::$instance;
      }
  
      protected function initName() {
          $this->setName('g_accounts');
      }
  
      public static function getName() {
          return self::getInstance()->name();
      }
  
      public static function getDataColumnName($i) {
          return 'data'.$i;
      }
  
      protected function initColumns() {
          $this->createPrimaryColumn(self::ID, self::CHAR, 8, true);
          $this->createColumn(self::NAME, self::CHAR, 80);
          $this->createColumn(self::STATUS, self::CHAR, 1);
          $this->createColumn(self::APPLICATION, self::CHAR, 20);
          $this->createColumn(self::EMAIL, self::CHAR, 255);
          $this->createColumn(self::DATEINSERTED, self::DATETIME);
          $this->createColumn(self::AGREEMENT, self::CHAR);
          $this->createColumn(self::ACCOUNT_NOTE, self::CHAR);
          $this->createColumn(self::SYSTEM_NOTE, self::CHAR);
          for ($i = 1; $i <= 25; $i++) {
              $this->createColumn(self::getDataColumnName($i), self::CHAR, 255);
          }
      }
  
      protected function initConstraints() {
      	$this->addConstraint(new Gpf_DbEngine_Row_UniqueConstraint(array(self::EMAIL), $this->_('Email must be unique!')));
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_Currencies::ACCOUNTID, new Gpf_Db_Currency());
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_Exports::ACCOUNT_ID, new Gpf_Db_Export());
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_FieldGroups::ACCOUNTID, new Gpf_Db_FieldGroup());
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_FormFields::ACCOUNTID, new Gpf_Db_FormField());
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_ImportExports::ACCOUNT_ID, new Gpf_Db_ImportExport());
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_Languages::ACCOUNTID, new Gpf_Db_Language());
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_MailAccounts::ACCOUNT_ID, new Gpf_Db_MailAccount());
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_MailTemplates::ACCOUNT_ID, new Gpf_Db_MailTemplate());
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_Settings::ACCOUNTID, new Gpf_Db_Setting());
          $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_Users::ACCOUNTID, new Gpf_Db_User());
          Gpf_Plugins_Engine::extensionPoint('Accounts.initConstraints', $this);
      }
  
      /**
       *
       * @param Gpf_Auth_Info $authInfo
       * @return Gpf_Data_RecordSet
       */
      public static function getAccounts(Gpf_Auth_Info $authInfo) {
          $select = new Gpf_SqlBuilder_SelectBuilder();
  
          $select->select->add('a.accountid', 'accountid');
          $select->select->add('a.name', 'name');
  
          $select->from->add(Gpf_Db_Table_AuthUsers::getName(), 'au');
          $select->from->addInnerJoin(Gpf_Db_Table_Users::getName(), 'u', 'au.authid=u.authid');
          $select->from->addInnerJoin(self::getName(), 'a', 'a.accountid=u.accountid');
          $select->from->addInnerJoin(Gpf_Db_Table_Roles::getName(), 'r', 'u.roleid=r.roleid');
  
          $authInfo->addWhere($select);
          $select->where->add('a.rstatus', 'IN', array(Gpf_Db_Account::APPROVED, Gpf_Db_Account::SUSPENDED));
  
          return $select->getAllRows();
      }
  }
  

} //end Gpf_Db_Table_Accounts

if (!class_exists('Gpf_SqlBuilder_OrderByColumn', false)) {
  class Gpf_SqlBuilder_OrderByColumn extends Gpf_Object {
      private $name;
      private $asc;
      private $tableName;
  
      public function Gpf_SqlBuilder_OrderByColumn($name, $asc = true, $tableName = '') {
          $this->name = $name;
          $this->asc = $asc;
          $this->tableName = $tableName;
      }
      
      public function getName() {
          return $this->name;
      }
  
      public function toString() {
          $out = '';
          if(!empty($this->tableName)) {
              $out = $this->tableName . '.';
          }
          $out .= $this->name;
          if($this->asc) {
              $out .= ' ASC';
          } else {
              $out .= ' DESC';
          }
          return $out;
      }
  
  }
  

} //end Gpf_SqlBuilder_OrderByColumn

if (!class_exists('Gpf_Data_RecordSet', false)) {
  class Gpf_Data_RecordSet extends Gpf_Object implements IteratorAggregate, Gpf_Rpc_Serializable {
  
      const SORT_ASC = 'ASC';
      const SORT_DESC = 'DESC';
  
      protected $_array;
      /**
       * @var Gpf_Data_RecordHeader
       */
      private $_header;
  
      function __construct() {
          $this->init();
      }
  
      public function loadFromArray($rows) {
          $this->setHeader($rows[0]);
  
          for ($i = 1; $i < count($rows); $i++) {
              $this->add($rows[$i]);
          }
      }
  
      public function setHeader($header) {
          if($header instanceof Gpf_Data_RecordHeader) {
              $this->_header = $header;
              return;
          }
          $this->_header = new Gpf_Data_RecordHeader($header);
      }
  
      /**
       * @return Gpf_Data_RecordHeader
       */
      public function getHeader() {
          return $this->_header;
      }
  
      public function addRecord(Gpf_Data_Record $record) {
          $this->_array[] = $record;
      }
  
      /**
       * Adds new row to RecordSet
       *
       * @param array $record array of data for all columns in record
       */
      public function add($record) {
          $this->addRecord($this->getRecordObject($record));
      }
  
      /**
       * @return Gpf_Data_Record
       */
      public function createRecord() {
          return new Gpf_Data_Record($this->_header);
      }
  
      public function toObject() {
          $response = array();
          $response[] = $this->_header->toObject();
          foreach ($this->_array as $record) {
              $response[] = $record->toObject();
          }
          return $response;
      }
  
      public function loadFromObject($array) {
          if($array === null) {
              throw new Gpf_Exception('Array must be not NULL');
          }
          $this->_header = new Gpf_Data_RecordHeader($array[0]);
          for($i = 1; $i < count($array);$i++) {
              $record = new Gpf_Data_Record($this->_header);
              $record->loadFromObject($array[$i]);
              $this->loadRecordFromObject($record);
          }
      }
  
      public function sort($column, $sortType = 'ASC') {
          if (!$this->_header->contains($column)) {
              throw new Gpf_Exception('Undefined column');
          }
          $sorter = new Gpf_Data_RecordSet_Sorter($column, $sortType);
          $this->_array = $sorter->sort($this->_array);
      }
  
      protected function loadRecordFromObject(Gpf_Data_Record $record) {
          $this->_array[] = $record;
      }
  
      public function toArray() {
          $response = array();
          foreach ($this->_array as $record) {
              $response[] = $record->getAttributes();
          }
          return $response;
      }
  
      public function toText() {
          $text = '';
          foreach ($this->_array as $record) {
              $text .= $record->toText() . "<br>\n";
          }
          return $text;
      }
  
      /**
       * Return number of rows in recordset
       *
       * @return integer
       */
      public function getSize() {
          return count($this->_array);
      }
  
      /**
       * @return Gpf_Data_Record
       */
      public function get($i) {
          return $this->_array[$i];
      }
  
      /**
       * @param array/Gpf_Data_Record $record
       * @return Gpf_Data_Record
       */
      private function getRecordObject($record) {
          if(!($record instanceof Gpf_Data_Record)) {
              $record = new Gpf_Data_Record($this->_header->toArray(), $record);
          }
          return $record;
      }
  
      private function init() {
          $this->_array = array();
          $this->_header = new Gpf_Data_RecordHeader();
      }
  
      public function clear() {
          $this->init();
      }
  
      public function load(Gpf_SqlBuilder_SelectBuilder $select) {
          $this->init();
  
          foreach ($select->select->getColumns() as $column) {
              $this->_header->add($column->getAlias());
          }
          $statement = $this->createDatabase()->execute($select->toString());
          while($rowArray = $statement->fetchRow()) {
              $this->add($rowArray);
          }
      }
  
      /**
       *
       * @return ArrayIterator
       */
      public function getIterator() {
          return new ArrayIterator($this->_array);
      }
  
      public function getRecord($keyValue = null) {
          if(!array_key_exists($keyValue, $this->_array)) {
              return $this->createRecord();
          }
          return $this->_array[$keyValue];
      }
  
      public function addColumn($id, $defaultValue = "") {
          $this->_header->add($id);
          foreach ($this->_array as $record) {
              $record->add($id, $defaultValue);
          }
      }
  
      /**
       * Creates shalow copy of recordset containing only headers
       *
       * @return Gpf_Data_RecordSet
       */
      public function toShalowRecordSet() {
         $copy = new Gpf_Data_RecordSet();
         $copy->setHeader($this->_header->toArray());
         return $copy;
      }
  }
  
  class Gpf_Data_RecordSet_Sorter {
  
      private $sortColumn;
      private $sortType;
  
      function __construct($column, $sortType) {
          $this->sortColumn = $column;
          $this->sortType = $sortType;
      }
  
      public function sort(array $sortedArray) {
          usort($sortedArray, array($this, 'compareRecords'));
          return $sortedArray;
      }
  
      private function compareRecords($record1, $record2) {
          if ($record1->get($this->sortColumn) == $record2->get($this->sortColumn)) {
              return 0;
          }
          return $this->compare($record1->get($this->sortColumn), $record2->get($this->sortColumn));
      }
  
      private function compare($value1, $value2) {
          if ($this->sortType == Gpf_Data_RecordSet::SORT_ASC) {
              return ($value1 < $value2) ? -1 : 1;
          }
          return ($value1 < $value2) ? 1 : -1;
      }
  }

} //end Gpf_Data_RecordSet

if (!class_exists('Gpf_Data_IndexedRecordSet', false)) {
  class Gpf_Data_IndexedRecordSet extends Gpf_Data_RecordSet {
      private $key;
  
      /**
       *
       * @param int $keyIndex specifies which column should be used as a key
       */
      function __construct($key) {
          parent::__construct();
          $this->key = $key;
      }
      
      public function addRecord(Gpf_Data_Record $record) {
          $this->_array[$record->get($this->key)] = $record;
      }
      
      /**
       * @param String $keyValue
       * @return Gpf_Data_Record
       */
      public function createRecord($keyValue = null) {
          if($keyValue === null) {
              return parent::createRecord();
          }
          if(!array_key_exists($keyValue, $this->_array)) {
              $record = $this->createRecord();
              $record->set($this->key, $keyValue);
              $this->addRecord($record);
          }
          return $this->_array[$keyValue];
      }
      
      protected function loadRecordFromObject(Gpf_Data_Record $record) {    
          $this->_array[$record->get($this->key)] = $record; 
      }                
          
      /**
       * @param String $keyValue
       * @return Gpf_Data_Record
       */
      public function getRecord($keyValue = null) {
          if (!isset($this->_array[$keyValue])) {
              throw new Gpf_Data_RecordSetNoRowException($keyValue);
          }
          return $this->_array[$keyValue];
      }
      
      /**
       * @param String $keyValue
       * @return boolean
       */
      public function existsRecord($keyValue) {
          return isset($this->_array[$keyValue]);
      }
      
      /**
       * @param String $sortOptions (SORT_ASC, SORT_DESC, SORT_REGULAR, SORT_NUMERIC, SORT_STRING)
       * @return boolean
       */
      public function sortByKeyValue($sortOptions) {
          return array_multisort($this->_array, $sortOptions);
      }
  }
  

} //end Gpf_Data_IndexedRecordSet

if (!class_exists('Gpf_Data_RecordHeader', false)) {
  class Gpf_Data_RecordHeader extends Gpf_Object {
      private $ids = array();
      
      /**
       * Create Record header object
       *
       * @param array $headerArray
       */
      public function __construct($headerArray = null) {
          if($headerArray === null) {
              return;
          }
          
          foreach ($headerArray as $id) {
              $this->add($id);
          }
      }
      
      public function contains($id) {
          return array_key_exists($id, $this->ids);
      }
  
      public function add($id) {
          if($this->contains($id)) {
              return;
          }
  
          $this->ids[$id] = count($this->ids);
      }
  
      public function getIds() {
          return array_keys($this->ids);
      }
  
      public function getIndex($id) {
          if(!$this->contains($id)) {
              throw new Gpf_Exception("Unknown column '" . $id ."'");
          }
          return $this->ids[$id];
      }
      
      public function getSize() {
          return count($this->ids);
      }
  
      public function toArray() {
          $response = array();
          foreach ($this->ids as $columnId => $columnIndex) {
              $response[] = $columnId;
          }
          return $response;
      }
          
      public function toObject() {
          $result = array();
          foreach ($this->ids as $columnId => $columnIndex) {
              $result[] = $columnId;
          }
          return $result;
      }
  }
  

} //end Gpf_Data_RecordHeader

if (!class_exists('Gpf_Data_Record', false)) {
  class Gpf_Data_Record extends Gpf_Object implements Iterator, Gpf_Rpc_Serializable,
      Gpf_Templates_HasAttributes, Gpf_Data_Row {
      private $record;
      /**
       *
       * @var Gpf_Data_RecordHeader
       */
      private $header;
      private $position;
  
      /**
       * Create record
       *
       * @param array $header
       * @param array $array values of record from array
       */
      public function __construct($header, $array = array()) {
          if (is_array($header)) {
              $header = new Gpf_Data_RecordHeader($header);
          }
          $this->header = $header;
          $this->record = array_values($array);
          while(count($this->record) < $this->header->getSize()) {
              $this->record[] = null;
          }
      }
      
      function getAttributes() {
          $ret = array();
          foreach ($this as $name => $value) {
              $ret[$name] = $value;
          }
          return $ret;
      }
      
      /**
       * @return Gpf_Data_RecordHeader
       */
      public function getHeader() {
          return $this->header;
      }
      
      public function contains($id) {
          return $this->header->contains($id);
      }
      
      public function get($id) {
          $index = $this->header->getIndex($id);
          return $this->record[$index];
      }
  
      public function set($id, $value) {
          $index = $this->header->getIndex($id);
          $this->record[$index] = $value;
      }
      
      public function add($id, $value) {
          $this->header->add($id);
          $this->set($id, $value);
      }
      
      public function toObject() {
          return $this->record;
      }
      
      public function loadFromObject(array $array) {
          $this->record = $array;
      }
      
      public function toText() {
          return implode('-', $this->record);
      }
  
      public function current() {
          if(!isset($this->record[$this->position])) {
              return null;
          }
          return $this->record[$this->position];
      }
  
      public function key() {
          $ids = $this->header->getIds();
          return $ids[$this->position];
      }
  
      public function next() {
          $this->position++;
      }
  
      public function rewind() {
          $this->position = 0;
      }
  
      public function valid() {
          return $this->position < $this->header->getSize();
      }
  }
  

} //end Gpf_Data_Record

if (!class_exists('Gpf_Lang_Language', false)) {
  class Gpf_Lang_Language extends Gpf_Object {
      private $code;
      private $name;
      private $englishName;
      private $author;
      private $version;
      private $dateFormat;
      private $timeFormat;
      private $thousandsSeparator;
      private $decimalSeparator;
      private $dictionary = array();
      
      public function __construct($code) {
          $this->code = $code;
      }
      
      public function getCode() {
          return $this->code;
      }
      
      public function setName($name) {
          $this->name = $name;
      }
      
      public function getName() {
          return $this->name;
      }
     
      public function setEnglishName($name) {
          $this->englishName = $name;
      }
      
      public function getEnglishName() {
          return $this->englishName;
      }
      
      public function setVersion($version) {
          $this->version = $version;
      }
      
      public function setAuthor($author) {
          $this->author = $author;
      }
      
      public function setDictionary(array $dictionary) {
          $this->dictionary = $dictionary;
      }
      
      public function setDateFormat($dateFormat) {
          $this->dateFormat = $dateFormat;
      }
      
      public function getDateFormat() {
          return $this->dateFormat;
      }
      
      public function setTimeFormat($timeFormat) {
        $this->timeFormat = $timeFormat;
      }
      
      public function getTimeFormat() {
          return $this->timeFormat;
      }
      
      public function setThousandsSeparator($thousandsSeparator) {
        $this->thousandsSeparator = $thousandsSeparator;
      }
      
      public function getThousandsSeparator() {
          return $this->thousandsSeparator;
      }
      
      public function setDecimalSeparator($decimalSeparator) {
        $this->decimalSeparator = $decimalSeparator;
      }
      
      public function getDecimalSeparator() {
          return $this->decimalSeparator;
      }
      
      public function getClientMessages() {
          $langDirectory = Gpf_Paths::getInstance()->getAccountDirectoryPath() .
              Gpf_Paths::CACHE_DIRECTORY . Gpf_Lang_Dictionary::LANGUAGE_DIRECTORY;
          
          $file = new Gpf_Lang_CachedLanguageFile($langDirectory, $this->code);
          return $file->loadClientMessages();
      }
      
      public function load() {
          $langDirectory = Gpf_Paths::getInstance()->getAccountDirectoryPath() .
              Gpf_Paths::CACHE_DIRECTORY . Gpf_Lang_Dictionary::LANGUAGE_DIRECTORY;
          
          $file = new Gpf_Lang_CachedLanguageFile($langDirectory, $this->code);
          $file->load($this);
      }
      
      public function localize($mesage) {
          if(!isset($this->dictionary[$mesage])) {
              return $mesage;
          }
          return $this->dictionary[$mesage];
      }
  }

} //end Gpf_Lang_Language

if (!class_exists('Gpf_Lang_CachedLanguageFile', false)) {
  class Gpf_Lang_CachedLanguageFile extends Gpf_Object {
      private $directory;
      private $languageCode;
  
      public function __construct($directory, $languageCode) {
          $this->directory = $directory;
          $this->languageCode = $languageCode;
      }
  
      private function getFilename($server = true) {
          $ext = 's';
          if(!$server) {
              $ext = 'c';
          }
          $application = Gpf_Application::getInstance()->getCode();
          return $this->directory . $application . '_' . $this->languageCode . ".$ext.php";
      }
  
      public function regenerateLanguageCacheFiles() {
          //load langage
          $dbLang = new Gpf_Db_Language();
          $dbLang->setAccountId(Gpf_Session::getInstance()->getAuthUser()->getAccountId());
          $dbLang->setCode($this->languageCode);
          $dbLang->setId($dbLang->generateId());
          $dbLang->load();
          $lang = new Gpf_Lang_CsvLanguage();
          $lang->loadFromCsvFile(new Gpf_Io_Csv_Reader(Gpf_Lang_CsvLanguage::getAccountCsvFileName($this->languageCode)));
          $lang->exportAccountCache();
      }
  
      public function loadClientMessages() {
          $file = new Gpf_Io_File($this->getFilename(false));
          try {
              $file->open('r');
          } catch (Exception $e) {
              try {
                  $this->regenerateLanguageCacheFiles();
                  $file->open('r');
              } catch (Exception $e2) {
                  throw new Gpf_Exception($this->_('Could not open language file %s', $e2->getMessage()));
              }
          }
  
          @include($file->getFileName());
          return $_dict;
      }
  
      public function load(Gpf_Lang_Language $language) {
          $file = new Gpf_Io_File($this->getFilename());
          try {
              $file->open('r');
          } catch (Exception $e) {
              try {
                  $this->regenerateLanguageCacheFiles();
                  $file->open('r');
              } catch (Exception $e2) {
                  throw new Gpf_Exception($this->_('Could not open language file %s', $e2->getMessage()));
              }
          }
  
          $_name = '';
          $_engName = '';
          $_author = '';
          $_version = '';
          $_dict = '';
          $_dateFormat = '';
          $_timeFormat = '';
          $_thousandsSeparator = '';
          $_decimalSeparator = '';
  
          if (@eval(str_replace(array('<?php', '?>'), '',$file->getContents())) === false) {
              throw new Gpf_Exception($this->_('Corrupted language file %s', $file->getFileName()));
          }
  
          @$language->setName($_name);
          @$language->setEnglishName($_engName);
          @$language->setAuthor($_author);
          @$language->setVersion($_version);
          @$language->setDictionary($_dict);
          @$language->setDateFormat($_dateFormat);
          @$language->setTimeFormat($_timeFormat);
          @$language->setThousandsSeparator($_thousandsSeparator);
          @$language->setDecimalSeparator($_decimalSeparator);
      }
  }

} //end Gpf_Lang_CachedLanguageFile

if (!class_exists('Gpf_Db_Table_UserAttributes', false)) {
  class Gpf_Db_Table_UserAttributes extends Gpf_DbEngine_Table {
  
      const ID = 'attributeid';
      const ACCOUNT_USER_ID = "accountuserid";
      const NAME = "name";
      const VALUE = "value";
      
      /**
       * @var Gpf_Data_IndexedRecordSet
       */
      protected $attributes = null;
  
      protected static $instance;
          
      /**
       * @return Gpf_Db_Table_UserAttributes
       */
      public static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new self;
          }
          return self::$instance;
      }
          
      protected function initName() {
          $this->setName('g_userattributes');
      }
      
      public static function getName() {
          return self::getInstance()->name();
      }
      
      protected function initColumns() {
          $this->createPrimaryColumn(self::ID, 'char', 8, true);
          $this->createColumn(self::ACCOUNT_USER_ID, 'char', 8);
          $this->createColumn(self::NAME, 'char', 40);
          $this->createColumn(self::VALUE, 'char');
      }
  
      public function loadAttributes($userId) {
          $select = new Gpf_SqlBuilder_SelectBuilder();
  
          $select->select->add(self::NAME);
          $select->select->add(self::VALUE);
  
          $select->from->add(self::getName(), 'ua');
  
          $select->where->add(self::ACCOUNT_USER_ID, '=', $userId);
  
          $this->attributes = $select->getAllRowsIndexedBy('name');
      }
  
      /**
       * @param string $name
       * @return Gpf_Data_Record
       */
      private function get($name) {
          if ($this->attributes == null) {
              throw new Gpf_Exception("Attributes not loaded");
          }
          return $this->attributes->getRecord($name);
      }
      
      public function getAttributeWithDefaultValue($name, $defaultValue = "") {
          try {
              return $this->get($name)->get(self::VALUE);
          } catch (Gpf_Data_RecordSetNoRowException $e) {
              return $defaultValue;
          }
      }
      
      public function getAttribute($name) {
          return $this->get($name)->get(self::VALUE);
      }
      
      /**
       * @throws Gpf_DbEngine_NoRowException
       */
      public static function getSetting($name, $accounUsertId = null) {
          return self::getInstance()->getInstanceSetting($name, $accounUsertId);
      }
      
      public static function setSetting($name, $value, $accountUserId = null) {
          self::getInstance()->setInstanceSetting($name, $value, $accountUserId);
      }
  
      protected function setInstanceSetting($name, $value, $accountUserId = null) {
          if ($accountUserId == null) {
              $accountUserId = Gpf_Session::getAuthUser()->getAccountUserId();
          }
          
          $attribute = new Gpf_Db_UserAttribute();
          $attribute->setName($name);
          $attribute->set(self::VALUE, $value);
          $attribute->setAccountUserId($accountUserId);
          $attribute->save();
      }
      
      protected function getInstanceSetting($name, $accounUsertId = null) {
          $attribute = new Gpf_Db_UserAttribute();
          return $attribute->getSetting($name, $accounUsertId);
      }
  }
  

} //end Gpf_Db_Table_UserAttributes

if (!class_exists('Gpf_Data_RecordSetNoRowException', false)) {
  class Gpf_Data_RecordSetNoRowException extends Gpf_Exception {
      public function __construct($keyValue) {
          parent::__construct("'Row $keyValue does not exist");
      }
      
      protected function logException() {
      }
  }

} //end Gpf_Data_RecordSetNoRowException

if (!class_exists('Gpf_Desktop_Theme', false)) {
  class Gpf_Desktop_Theme extends Gpf_Object {
  
      const CONFIG_FILE_NAME = 'theme.php';
  
      const ID = 'id';
      const NAME = 'name';
      const AUTHOR = 'author';
      const URL = 'url';
      const DESCRIPTION = 'description';
      const THUMBNAIL = 'thumbnail';
      const DESKTOP_MODE = 'mode';
      const DEFAULT_WALLPAPER = 'defaultWallpaper';
      const DEFAULT_WALLPAPER_POSITION = 'defaultWallpaperPosition';
      const DEFAULT_BACKGROUND_COLOR = 'defaultBackgroundColor';
      const ENABLED = 'enabled';
      const BUILT_IN = 'built_in';
  
      const DESKTOP_MODE_WINDOW = "W";
      const DESKTOP_MODE_SINGLE = "S";
  
  
      /**
       * @var string
       */
      private $themeId;
      /**
       * @var string
       */
      private $panelName;
      /**
       * @var Gpf_File_Config
       */
      private $configFile;
  
      public function __construct($themeId = '', $panelName = '') {
          $this->themeId = $themeId;
          $this->panelName = $panelName;
          if ($this->themeId == '') {
              $this->themeId = Gpf_Session::getAuthUser()->getTheme();
          }
          if ($this->panelName == '') {
              $this->panelName = Gpf_Session::getModule()->getPanelName();
          }
  
          $this->initThemeConfig();
      }
  
      public function getDesktopMode() {
          if (strtolower($this->configFile->getSetting(self::DESKTOP_MODE)) == "w" ||
          strtolower($this->configFile->getSetting(self::DESKTOP_MODE)) == "window") {
              return self::DESKTOP_MODE_WINDOW;
          }
          return self::DESKTOP_MODE_SINGLE;
      }
  
      /**
       * Get default wallpaper of selected theme
       *
       * @return string wallpaper file name
       */
      public function getDefaultWallpaper() {
          return $this->configFile->getSetting(self::DEFAULT_WALLPAPER);
      }
  
      /**
       * Get default wallpaper position of selected theme
       *
       * @return string wallpaper position
       */
      public function getDefaultWallpaperPosition() {
          try {
              return $this->configFile->getSetting(self::DEFAULT_WALLPAPER_POSITION);
          } catch (Gpf_Settings_UnknownSettingException $e) {
              return 'S';
          }
      }
  
      /**
       * Get default background color of selected theme
       *
       * @return string background color code
       */
      public function getDefaultBackgroundColor() {
          try {
              return $this->configFile->getSetting(self::DEFAULT_BACKGROUND_COLOR);
          } catch (Gpf_Settings_UnknownSettingException $e) {
              return '#000000';
          }
      }
  
      /**
       * @param Gpf_Data_RecordSet $recordset
       * @return Gpf_Data_Record
       */
      public function toRecord(Gpf_Data_RecordSet $recordset) {
          $record = $recordset->createRecord();
          $record->set(self::ID, $this->themeId);
          $this->addImageUrlToRecord($record, self::THUMBNAIL);
          $this->addValueToRecord($record, self::NAME);
          $this->addValueToRecord($record, self::AUTHOR);
          $this->addValueToRecord($record, self::URL);
          $this->addValueToRecord($record, self::DESCRIPTION);
          $this->addValueToRecord($record, self::DESKTOP_MODE);
          $record->set(self::BUILT_IN, $this->isBuiltIn());
          $record->set(self::ENABLED, $this->isEnabled());
          return $record;
      }
  
      private function addImageUrlToRecord(Gpf_Data_Record $record, $name) {
          try {
              $paths = Gpf_Paths::getInstance()->clonePaths($this->themeId);
              $record->set($name, $paths->getImageUrl($this->configFile->getSetting($name)));
          } catch (Gpf_Exception $e) {
              return "";
          }
      }
  
      private function addValueToRecord(Gpf_Data_Record $record, $name) {
          $record->set($name, $this->configFile->getSetting($name));
      }
  
      /**
       * @return Gpf_Io_File
       */
      public function getThemePath(){
          return new Gpf_Io_File(Gpf_Paths::getInstance()->getTopTemplatePath() .
          $this->panelName . '/' .
          $this->themeId . '/');
      }
  
      private function initThemeConfig() {
          $this->configFile = new Gpf_File_Config($this->getThemePath()->__toString() . self::CONFIG_FILE_NAME);
          if (!$this->configFile->isExists()) {
              throw new Gpf_Exception($this->_("Theme file (theme.php) does not exist for theme %s in directory %s", $this->themeId, $this->getThemePath()->__toString()));
          }
      }
  
      public function load(){
          $this->configFile->getAll();
      }
  
      public function setEnabled($enabled){
          $this->configFile->setSetting(self::ENABLED, $enabled ? 'Y' : 'N');
      }
  
      public function isEnabled(){
          if($this->configFile->hasSetting(self::ENABLED)) {
              return $this->configFile->getSetting(self::ENABLED) == 'Y';
          }
          return true;
      }
  
      private function isBuiltIn(){
          if($this->configFile->hasSetting(self::BUILT_IN)) {
              return $this->configFile->getSetting(self::BUILT_IN) == 'Y';
          }
          return false;
      }
  
      public function setBuiltIn($builtIn){
          $this->configFile->setSetting(self::BUILT_IN, $builtIn ? 'Y' : 'N');
      }
  
      public function setName($value){
          $this->configFile->setSetting(self::NAME, $value, false);
      }
  
      public function setAuthor($value){
          $this->configFile->setSetting(self::AUTHOR, $value, false);
      }
  
      public function setUrl($value){
          $this->configFile->setSetting(self::URL, $value, false);
      }
  
      public function setDescription($value){
          $this->configFile->setSetting(self::DESCRIPTION, $value, false);
      }
  
      public function save(){
          $this->configFile->saveAll();
      }
      
      public function setSettingsFile($path) {
          $this->configFile->setSettingsFile($path);
      }
  
  }
  

} //end Gpf_Desktop_Theme

if (!class_exists('Gpf_Db_Table_Versions', false)) {
  class Gpf_Db_Table_Versions extends Gpf_DbEngine_Table {
      const ID = 'versionid';
      const NAME = 'name';
      const APPLICATION = 'application';
      const DONE_DATE = 'done';
      
      /**
       *
       * @var Gpf_Db_Table_Versions
       */
      private static $instance;
          
      /**
       *
       * @return Gpf_Db_Table_Versions
       */
      public static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new self;
          }
          return self::$instance;
      }
          
      protected function initName() {
          $this->setName('g_versions');
      }
      
      public static function getName() {
          return self::getInstance()->name();
      }
      
      public function getLatestVersion($application) {
          if(!is_array($application)) {
              $application = array($application);
          }
          
          $sql = new Gpf_SqlBuilder_SelectBuilder();
          $sql->select->add('name');
          $sql->from->add(self::getName(), 'v');
          $sql->where->add('application', 'IN', $application);
          $sql->where->add('done', '!=', null);
          $sql->orderBy->add('done', false);
          $sql->orderBy->add('versionid', false);
          $sql->limit->set(0, 1);;
          $rows = $sql->getAllRows();
          if($rows->getSize() == 0) {
              return false;
          }
          return $rows->getRecord(0)->get('name');
      }
      
      public function isExists() {
          try {
              $this->getLatestVersion('');
              return true;
          } catch (Exception $e) {
          }
          return false;
      }
      
      protected function initColumns() {
          $this->createPrimaryColumn(self::ID, 'int', 0, true);
          $this->createColumn(self::NAME, 'char', 40);
          $this->createColumn(self::APPLICATION, 'char', 40);
          $this->createColumn(self::DONE_DATE, 'datetime');
      }
  }
  

} //end Gpf_Db_Table_Versions

if (!class_exists('Gpf_Auth_Service', false)) {
  class Gpf_Auth_Service extends Gpf_Object {
      const USERNAME = 'username';
      const PASSWORD = 'password';
      const AUTHTOKEN = 'authToken';
      const ACCOUNTID = 'accountid';
      const REMEMBER_ME = 'rememberMe';
      const USER_ID = 'userid';
      const LANGUAGE = 'language';
      const ROLETYPE = 'roleType';
      const COOKIE_LANGUAGE = 'gpf_language';
      const TIME_OFFSET = 'time_offset';
  
      /**
       *
       * @service
       * @anonym
       * @param $username
       * @param $password
       * @param $accountId
       * @param $rememberMe
       * @param $language
       * @return Gpf_Rpc_Serializable
       */
      public function authenticate(Gpf_Rpc_Params $params) {
          $loginForm = new Gpf_Rpc_Form($params);
  
          Gpf_Plugins_Engine::extensionPoint('Gpf_Auth_Service.authenticateBefore', $loginForm);
  
          $username = $this->getFieldValue($loginForm, self::USERNAME);
          $password = $this->getFieldValue($loginForm, self::PASSWORD);
          $authToken = $this->getFieldValue($loginForm, self::AUTHTOKEN);
  
          $accountId = $this->getFieldValue($loginForm, self::ACCOUNTID);
          $rememberMe = $this->getFieldValue($loginForm, self::REMEMBER_ME, Gpf::NO);
          $language = $this->getFieldValue($loginForm, self::LANGUAGE, Gpf_Lang_Dictionary::getDefaultLanguage());
          $roleType = $this->getFieldValue($loginForm, self::ROLETYPE);
  
          $result = $this->authenticateNoRpc($username, $password, $accountId, $rememberMe, $language, $roleType, $authToken);
          $this->registerLogin($loginForm);
          return $result;
      }
  
      public function registerLogin(Gpf_Rpc_Form $loginForm = null) {
          $isFromApi = Gpf::NO;
          if($loginForm != null){
              try{
                  $isFromApi = $loginForm->getFieldValue('isFromApi');
              } catch(Gpf_Exception $e) {
              }
          }
          if($isFromApi == Gpf::YES) {
              return;
          }
          $log = new Gpf_Db_LoginHistory();
          $loginDbTime = $log->createDatabase()->getDateString();
  
          $log->setLoginTime($loginDbTime);
          $log->setIp(Gpf_Http::getRemoteIp());
          $log->setLastRequestTime($loginDbTime);
          $log->setAccountUserId(Gpf_Session::getInstance()->getAuthUser()->getAccountUserId());
          $log->insert();
  
          Gpf_Session::getInstance()->setVar(Gpf_Db_Table_LoginsHistory::ID, $log->getId());
          Gpf_Session::getInstance()->setVar(Gpf_Db_Table_LoginsHistory::LAST_REQUEST, time());
      }
  
      private function getFieldValue(Gpf_Rpc_Form $form, $name, $defaultValue = '') {
          try {
              return $form->getFieldValue($name);
          } catch (Gpf_Exception $e) {
              return $defaultValue;
          }
      }
  
      /**
       *
       * @param $username
       * @param $password
       * @param $accountId
       * @param $rememberMe
       * @return Gpf_Rpc_Form
       */
      public function authenticateNoRpc($username = '', $password = '', $accountId = '', $rememberMe = Gpf::NO, $language = '', $roleType = '', $authToken = '') {
          if ($language == '') {
              $language = Gpf_Lang_Dictionary::getDefaultLanguage();
          }
          $loginForm = $this->createResponseForm();
          $loginForm->setField(self::USERNAME, $username);
          $loginForm->setField(self::PASSWORD, $password);
          $loginForm->setField(self::ACCOUNTID, $accountId);
          $loginForm->setField(self::REMEMBER_ME, $rememberMe);
          $loginForm->setField(self::LANGUAGE, $language);
  
          try {
              $authInfo = Gpf_Auth_Info::create($loginForm->getFieldValue(self::USERNAME),
                                                $loginForm->getFieldValue(self::PASSWORD),
                                                $accountId,
                                                $roleType,
                                                $authToken);
              if ($authInfo->hasAccount()) {
                  return $this->authenticateUser($loginForm, $authInfo);
              }
  
              $accounts = Gpf_Db_Table_Accounts::getAccounts($authInfo);
              if ($accounts->getSize() == 0) {
                  Gpf_Log::info($this->_sys("Wrong username/password (Username: %s)", $username));
                  $loginForm->setErrorMessage($this->_("Wrong username/password"));
              } else if ($accounts->getSize() == 1) {
                  $authInfo->setAccount($accounts->getRecord(0)->get('accountid'));
                  return $this->authenticateUser($loginForm, $authInfo);
              } else if ($accounts->getSize() > 1) {
                  $loginForm->setField(self::ACCOUNTID, "select_account", $accounts->toObject());
                  $loginForm->setInfoMessage($this->_("Select account"));
              }
          } catch (Gpf_Auth_Exception $e) {
              $loginForm->setErrorMessage($e->getMessage());
          } catch (Gpf_DbEngine_NoRowException $e) {
              Gpf_Log::info($this->_sys("Wrong username/password (Username: %s)", $username));
              $loginForm->setErrorMessage($this->_("Wrong username/password"));
          } catch (Exception $e) {
              if (strlen($username)) {
                  Gpf_Log::info($this->_sys("Authentication failed for user %s", $username));
              }
              $loginForm->setErrorMessage($this->_("Authentication failed"));
          }
          return $loginForm;
      }
  
      /**
       * @return Gpf_Rpc_Form
       */
      protected function createResponseForm() {
          return new Gpf_Rpc_Form();
      }
  
      /**
       *
       * @param Gpf_Rpc_Form $loginForm
       * @param Gpf_Auth_Info $authInfo
       * @return Gpf_Rpc_Serializable
       */
      protected function authenticateUser(Gpf_Rpc_Form $loginForm, Gpf_Auth_Info $authInfo) {
          $authUser = $this->loadUser($authInfo);
          Gpf_Session::getInstance()->save($authUser);
          $this->setAuthenticationSucesful($loginForm, $authUser);
          Gpf_Session::getInstance()->getAuthUser()->updateIp(Gpf_Http::getRemoteIp());
          return $loginForm;
      }
  
      /**
       *
       * @return Gpf_Auth_User
       */
      protected function loadUser(Gpf_Auth_Info $authInfo) {
          $authUser = Gpf::newObj(Gpf_Application::getInstance()->getAuthClass());
          return $authUser->load($authInfo);
      }
  
      protected function setAuthenticationSucesful(Gpf_Rpc_Form $loginForm, Gpf_Auth_User $authUser) {
          try {
              if($loginForm->getFieldValue(self::REMEMBER_ME) == Gpf::YES) {
                  $authUser->saveRememberMeCookie();
              }
          } catch (Gpf_Exception $e) {
          }
  
          $authUser->setLanguage($loginForm->getFieldValue(self::LANGUAGE));
          Gpf_Db_UserAttribute::saveAttribute(self::LANGUAGE, $loginForm->getFieldValue(self::LANGUAGE), $authUser->getAccountUserId());
          Gpf_Db_UserAttribute::saveAttribute(self::TIME_OFFSET, Gpf_Session::getInstance()->getTimeOffset(), $authUser->getAccountUserId());
  
          Gpf_Http::setCookie(self::COOKIE_LANGUAGE,
          $authUser->getLanguage(), time() + 20000000, '/');
  
          $loginForm->addField("S", Gpf_Session::getInstance()->getId());
  
          $loginForm->setInfoMessage($this->_("User authenticated. Logging in."));
          Gpf_Log::info($this->_sys("User %s authenticated. Logging in.", $authUser->getUsername()));
      }
  
      private function getCookiePrefix() {
          return Gpf_Application::getInstance()->getCode() . '_' .
          Gpf_Session::getRoleType() . '_';
      }
  
      /**
       * Load default username and password in login form
       *
       * @service
       * @anonym
       *
       * @param Gpf_Rpc_Params $params
       * @return Gpf_Rpc_Form
       */
      public function load(Gpf_Rpc_Params $params) {
          return $this->loadNoRpc();
      }
  
      /**
       * Load default username and password in login form
       *
       * @return Gpf_Rpc_Form
       */
      public function loadNoRpc() {
          $form = new Gpf_Rpc_Form(new Gpf_Rpc_Params());
          $form->setField(self::REMEMBER_ME, Gpf::YES);
  
          if (Gpf_Application::isDemo()) {
              $form->setField(self::USERNAME, Gpf_Session::getInstance()->getModule()->getDemoUsername());
              $form->setField(self::PASSWORD, Gpf_Session::getInstance()->getModule()->getDemoPassword());
          }
  
          $langage = Gpf_Http::getCookie(self::COOKIE_LANGUAGE);
  
          $form->setField(self::LANGUAGE, $langage, $this->setDefaultLanguage(Gpf_Lang_Languages::getInstance()->getActiveLanguagesNoRpc())->toObject());
          return $form;
      }
      
      /**
       * @param Gpf_Data_IndexedRecordSet $languageRecordSet
       * @return Gpf_Data_IndexedRecordSet
       */
      private function setDefaultLanguage(Gpf_Data_IndexedRecordSet $languageRecordSet) {
          $defaultLanguageCode = Gpf_Lang_Dictionary::getInstance()->getLanguage()->getCode();
          
          foreach ($languageRecordSet as $languageRecord) {
              if ($languageRecord->get(Gpf_Db_Table_Languages::CODE) == $defaultLanguageCode) {
                  $languageRecord->set(Gpf_Db_Table_Languages::IS_DEFAULT, Gpf::YES);
              } else {
                  $languageRecord->set(Gpf_Db_Table_Languages::IS_DEFAULT, Gpf::NO);
              }
          }
          return $languageRecordSet;
      }
  
      /**
       *
       * @service authentication logout
       * @return Gpf_Rpc_Action
       */
      public function logout(Gpf_Rpc_Params $params) {
          $action = new Gpf_Rpc_Action($params);
          try {
              Gpf_Session::getAuthUser()->logout();
              $action->setInfoMessage($this->_('Logout successful'));
          } catch (Exception $e) {
              $action->setErrorMessage($this->_('Logout error'));
          }
          return $action;
      }
  
      /**
       *
       * @service authentication logout
       * @return Gpf_Rpc_Action
       */
      public function logoutByURL(Gpf_Rpc_Params $params) {
          try {
              $panelName = Gpf_Session::getModule()->getPanelName();
              Gpf_Session::getAuthUser()->logout();
              Gpf_Http::setHeader('Location', Gpf_Paths::getInstance()->getTopPath() . $panelName . '/login.php');
  
          } catch (Exception $e) {
              echo $this->_('Logout was not successful');
          }
      }
  
      /**
       * Request new password for specified username
       * @service
       * @anonym
       *
       * @param Gpf_Rpc_Params $params
       * @return Gpf_Rpc_Form
       */
      public function requestNewPassword(Gpf_Rpc_Params $params) {
          $response = new Gpf_Rpc_Form($params);
          if (!Gpf_Captcha::isValid('lost_pw_captcha', $response->getFieldValue('lost_pw_captcha'))) {
              $response->setFieldError('lost_pw_captcha', $this->_("You have entered invalid security code"));
              return $response;
          }
          try {
              $user = new Gpf_Db_AuthUser();
              $user->setUsername($response->getFieldValue('username'));
              $user->loadFromData(array(Gpf_Db_Table_AuthUsers::USERNAME));
          } catch (Gpf_Exception $e) {
              $response->setFieldError('username', $this->_("You entered invalid username"));
              return $response;
          }
  
          $mail = new Gpf_Auth_RequestNewPasswordMail();
          $mail->setUser($user);
          $mail->setUrl($response->getFieldValue('applicationUrl'));
          $mail->addRecipient($user->getEmail());
          $mail->sendNow();
  
          $response->setInfoMessage($this->_("Within a few minutes you will receive instruction on how to reset you password."));
          return $response;
      }
  
      /**
       * Load username from PasswordRequest
       *
       * @service
       * @anonym
       * @param Gfp_Rpc_Params $params
       * @return Gpf_Rpc_Form
       */
      public function loadAuthUserName(Gpf_Rpc_Params $params) {
          $response = new Gpf_Rpc_Form($params);
  
          $user = $this->getAuthUserFromRequest($params);
  
          if(is_null($user)) {
              $response->setErrorMessage($this->_('Load auth user from PasswordRequest failed.'));
              return $response;
          }
  
          $response->setField('username', $user->getUsername());
          return $response;
      }
  
      private function getAuthUserFromRequest($params) {
          if ($params->exists('requestid') && $params->get('requestid') != null && $params->get('requestid') != '') {
              $requestid = $params->get('requestid');
          } else {
              return null;
          }
          
          $errorMessage = $this->getInvalidPasswordRequestErrorMessage();
  
          $passwordRequest = new Gpf_Db_PasswordRequest();
          $passwordRequest->setId($requestid);
          try{
              $passwordRequest->load();
          } catch (Gpf_Exception $e) {
              return null;
          }
  
          $user = new Gpf_Db_AuthUser();
          $user->setId($passwordRequest->getAuthUser());
          try {
              $user->load();
          } catch (Gpf_Exception $e) {
              return null;
          }
          return $user;
      }
  
      /**
       * Set new password for user, which requested new password
       *
       * @service
       * @anonym
       * @param Gfp_Rpc_Params $params
       * @return Gpf_Rpc_Form
       */
      public function setNewPassword(Gpf_Rpc_Params $params) {
          $response = new Gpf_Rpc_Form($params);
          if (!Gpf_Captcha::isValid('set_pw_captcha', $response->getFieldValue('set_pw_captcha'))) {
              $response->setFieldError('set_pw_captcha', $this->_("You entered invalid security code"));
              return $response;
          }
  
          Gpf_Db_Table_PasswordRequests::expireOldRequest();
  
          $errorMessageInvalidUsername = $this->_('You entered invalid username');
  
          $user = new Gpf_Db_AuthUser();
          $user->setUsername($response->getFieldValue('username'));
          try {
              $user->loadFromData(array(Gpf_Db_Table_AuthUsers::USERNAME));
          } catch (Gpf_Exception $e) {
              $response->setFieldError('username', $errorMessageInvalidUsername);
              return $response;
          }
  
          $errorMessage = $this->getInvalidPasswordRequestErrorMessage();
  
          $passwordRequest = new Gpf_Db_PasswordRequest();
          $passwordRequest->setId($response->getFieldValue('requestid'));
          try{
              $passwordRequest->load();
          } catch (Gpf_Exception $e) {
              $response->setErrorMessage($errorMessage);
              return $response;
          }
  
          if ($user->getId() != $passwordRequest->getAuthUser()) {
              $response->setFieldError('username', $errorMessageInvalidUsername);
              return $response;
          }
  
          if ($passwordRequest->getStatus() != Gpf_Db_Table_PasswordRequests::STATUS_PENDING ||
          $user->getUsername() != $response->getFieldValue('username')) {
              $response->setErrorMessage($errorMessage);
              return $response;
          }
  
          $user->setPassword($response->getFieldValue('password'));
          try {
              $user->update(array(Gpf_Db_Table_AuthUsers::PASSWORD));
          } catch (Gpf_DbEngine_Row_ConstraintException $e) {
              $response->setErrorMessage($e->getMessage());
              return $response;
          }
  
          $passwordRequest->setStatus(Gpf_Db_Table_PasswordRequests::STATUS_APPLIED);
          $passwordRequest->update(array(Gpf_Db_Table_PasswordRequests::STATUS));
  
          $response->setInfoMessage($this->_("Your password was changed. Go back to login dialog and login."));
          return $response;
      }
  
      private function getInvalidPasswordRequestErrorMessage() {
          return $this->_('Invalid password request. You have to request again new password before trying to set password.');
      }
  }

} //end Gpf_Auth_Service

if (!class_exists('Gpf_Rpc_Form', false)) {
  class Gpf_Rpc_Form extends Gpf_Object implements Gpf_Rpc_Serializable, IteratorAggregate {
      const FIELD_NAME  = "name";
      const FIELD_VALUE = "value";
      const FIELD_ERROR = "error";
      const FIELD_VALUES = "values";
  
      private $isError = false;
      private $errorMessage = "";
      private $infoMessage = "";
      private $status;
      /**
       * @var Gpf_Data_IndexedRecordSet
       */
      private $fields;
      /**
       * @var Gpf_Rpc_Form_Validator_FormValidatorCollection
       */
      private $validators;
  
      public function __construct(Gpf_Rpc_Params $params = null) {
          $this->fields = new Gpf_Data_IndexedRecordSet(self::FIELD_NAME);
  
          $header = new Gpf_Data_RecordHeader();
          $header->add(self::FIELD_NAME);
          $header->add(self::FIELD_VALUE);
          $header->add(self::FIELD_VALUES);
          $header->add(self::FIELD_ERROR);
          $this->fields->setHeader($header);
          
          $this->validator = new Gpf_Rpc_Form_Validator_FormValidatorCollection($this);
          
          if($params) {
              $this->loadFieldsFromArray($params->get("fields"));
          }
      }
  
      /**
       * @param $validator
       * @param $fieldName
       * @param $fieldLabel
       */
      public function addValidator(Gpf_Rpc_Form_Validator_Validator $validator, $fieldName, $fieldLabel = null) {
          $this->validator->addValidator($validator, $fieldName, $fieldLabel);
      }
      
      /**
       * @return boolean
       */
      public function validate() {
          return $this->validator->validate();
      }
      
      public function loadFieldsFromArray($fields) {
          for ($i = 1; $i < count($fields); $i++) {
              $field = $fields[$i];
              $this->fields->add($field);
          }
      }
      
      /**
       *
       * @return ArrayIterator
       */
      public function getIterator() {
          return $this->fields->getIterator();
      }
      
      public function addField($name, $value) {
          $record = $this->fields->createRecord($name);
          $record->set(self::FIELD_VALUE, $value);
      }
      
      public function setField($name, $value, $values = null, $error = "") {
          $record = $this->fields->createRecord($name);
          $record->set(self::FIELD_VALUE, $value);
          $record->set(self::FIELD_VALUES, $values);
          $record->set(self::FIELD_ERROR, $error);
      }
      
      public function setFieldError($name, $error) {
          $this->isError = true;
          $record = $this->fields->getRecord($name);
          $record->set(self::FIELD_ERROR, $error);
      }
      
      public function getFieldValue($name) {
          $record = $this->fields->getRecord($name);
          return $record->get(self::FIELD_VALUE);
      }
      
      public function getFieldError($name) {
          $record = $this->fields->getRecord($name);
          return $record->get(self::FIELD_ERROR);
      }
      
      public function existsField($name) {
          return $this->fields->existsRecord($name);
      }
       
      public function load(Gpf_Data_Row $row) {
          foreach($row as $columnName => $columnValue) {
              $this->setField($columnName, $row->get($columnName));
          }
      }
  
      /**
       * @return Gpf_Data_IndexedRecordSet
       */
      public function getFields() {
          return $this->fields;
      }
      
      public function fill(Gpf_Data_Row $row) {
          foreach ($this->fields as $field) {
              try {
                  $row->set($field->get(self::FIELD_NAME), $field->get(self::FIELD_VALUE));
              } catch (Exception $e) {
              }
          }
      }
      
      public function toObject() {
          $response = new stdClass();
          $response->fields = $this->fields->toObject();
          if ($this->isSuccessful()) {
              $response->success = Gpf::YES;
              $response->message = $this->infoMessage;
          } else {
              $response->success = "N";
              $response->message = $this->errorMessage;
          }
          return $response;
      }
      
      public function loadFromObject(stdClass $object) {
          if ($object->success == Gpf::YES) {
          	$this->setInfoMessage($object->message);
          } else {
          	$this->setErrorMessage($object->message);
          }
          
          $this->fields = new Gpf_Data_IndexedRecordSet(self::FIELD_NAME);
          $this->fields->loadFromObject($object->fields);
      }
      
      public function toText() {
          return var_dump($this->toObject());
      }
  
      public function setErrorMessage($message) {
          $this->isError = true;
          $this->errorMessage = $message;
      }
      
      public function getErrorMessage() {
          if ($this->isError) {
              return $this->errorMessage;
          }
          return "";
      }
      
      public function setInfoMessage($message) {
          $this->infoMessage = $message;
      }
      
      public function setSuccessful() {
          $this->isError = false;
      }
      
      public function getInfoMessage() {
          if ($this->isError) {
              return "";
          }
          return $this->infoMessage;
      }
      
      
      /**
       * @return boolean
       */
      public function isSuccessful() {
          return !$this->isError;
      }
      
      /**
       * @return boolean
       */
      public function isError() {
          return $this->isError;
      }
  }
  

} //end Gpf_Rpc_Form

if (!class_exists('Gpf_Rpc_Form_Validator_FormValidatorCollection', false)) {
  class Gpf_Rpc_Form_Validator_FormValidatorCollection extends Gpf_Object {
      
      /**
       * @var array<Gpf_Rpc_Form_Validator_FieldValidator>
       */
      private $validators;
      /**
       * @var Gpf_Rpc_Form
       */
      private $form;
      
      public function __construct(Gpf_Rpc_Form $form) {
          $this->form = $form;
          $this->validators = array();
      }
      
      /**
       * @param $fieldName
       * @param $validator
       */
      public function addValidator(Gpf_Rpc_Form_Validator_Validator $validator, $fieldName, $fieldLabel = null) {
          if (!array_key_exists($fieldName, $this->validators)) {
              $this->validators[$fieldName] = new Gpf_Rpc_Form_Validator_FieldValidator(($fieldLabel === null ? $fieldName : $fieldLabel));
          }
          $this->validators[$fieldName]->addValidator($validator);
      }
      
      /**
       * @return boolean
       */
      public function validate() {
          $errorMsg = false;
          foreach ($this->validators as $fieldName => $fieldValidator) {
              if (!$fieldValidator->validate($this->form->getFieldValue($fieldName))) {
                  $errorMsg = true;
                  $this->form->setFieldError($fieldName, $fieldValidator->getMessage());
              }
          }
          if ($errorMsg) {
              $this->form->setErrorMessage($this->_('There were errors, please check highlighted fields'));
          }
          return !$errorMsg;
      }
  }

} //end Gpf_Rpc_Form_Validator_FormValidatorCollection

if (!class_exists('Gpf_Auth_Info', false)) {
  abstract class Gpf_Auth_Info extends Gpf_Object {
      private $accountid;
      private $roleType;
      
      public function __construct($accountid, $roleType = '') {
          $this->accountid = $accountid;
          $this->roleType = $roleType;
      }
      
      /**
       * @return Gpf_Auth_Info
       */
      public static function create($username, $password, $accountid, $roleType = '', $authToken = '') {
          if ($username != '' && $password != '') {
              return new Gpf_Auth_InfoUsernamePassword($username, $password, $accountid, $roleType);
          }
          return new Gpf_Auth_InfoAuthToken($accountid, $authToken, $roleType);
      }
      
      public function addWhere(Gpf_SqlBuilder_SelectBuilder $builder) {
          if ($this->accountid != '') {
              $builder->where->add('u.accountid', '=', $this->accountid);
          }
          $builder->where->add('r.roletype', '=', $this->getRoleType());
      }
      
      public function getRoleType() {
          if ($this->roleType == '') {
              return Gpf_Session::getModule()->getRoleType();
          }
          return $this->roleType;
      }
      
      public function hasAccount() {
          return $this->accountid != '';
      }
      
      public function setAccount($accountid) {
          $this->accountid = $accountid;
      }
      
      public function getAccountId() {
          return $this->accountid;
      }
  }

} //end Gpf_Auth_Info

if (!class_exists('Gpf_Auth_InfoAuthToken', false)) {
  class Gpf_Auth_InfoAuthToken extends Gpf_Auth_Info {
      private $authToken;
         
      public function __construct($accountId, $authToken = '', $roleType = '') {
          parent::__construct($accountId, $roleType);
          $this->authToken = $authToken;
          if($authToken == '') {
              $this->authToken = Gpf_Session::getAuthUser()->getRemeberMeToken();        
          }
      }
      
      public function addWhere(Gpf_SqlBuilder_SelectBuilder $builder) {
          parent::addWhere($builder);
          $builder->where->add('au.authtoken', '=', $this->authToken);
      }
  }

} //end Gpf_Auth_InfoAuthToken

if (!class_exists('Gpf_Db_Language', false)) {
  class Gpf_Db_Language extends Gpf_DbEngine_Row {
  
      public function __construct(){
          parent::__construct();
      }
  
      protected function init() {
          $this->setTable(Gpf_Db_Table_Languages::getInstance());
          parent::init();
      }
  
      public function insert() {
          $this->set('imported', Gpf_DbEngine_Database::getDateString());
          $this->setId($this->generateId());
          $this->checkIsDefaultStatus();
          return parent::insert();
      }
  
      public function delete() {
          $this->load();
          if ($this->isDefault()) {
              throw new Gpf_Exception($this->_("Default language can't be deleted"));
          }
  
          $returnValue = parent::delete();
  
          $this->deleteLanguageFilesFromAccount();
  
          return $returnValue;
      }
  
      /**
       * Delete csv file from account directory
       */
      private function deleteLanguageFilesFromAccount() {
          //delete csv file from account
          $fileName = Gpf_Lang_CsvLanguage::getAccountCsvFileName($this->getCode());
          $file = new Gpf_Io_File($fileName);
          if ($file->isExists()) {
              $file->delete();
          }
  
          //TODO delete also cache language files from account
      }
  
      private function checkIsDefaultStatus() {
          if (!$this->isActive() && $this->isDefault()) {
              throw new Gpf_Exception($this->_('Default language has to be active !'));
          }
  
          try {
              $defLang = Gpf_Db_Table_Languages::getInstance()->getDefaultLanguage();
              if (($this->getCode() == $defLang->getCode() || !strlen($defLang->getCode())) && $this->isDefault() === false) {
                  $this->setIsDefault(true);
              }
          } catch (Gpf_DbEngine_NoRowException $e) {
              $this->setIsDefault(true);
          }
  
          if ($this->isDefault()) {
              Gpf_Db_Table_Languages::getInstance()->unsetDefaultLanguage($this->getId());
          }
      }
  
      public function update($updateColumns = array()) {
          $this->checkIsDefaultStatus();
          parent::update($updateColumns);
      }
  
      public function generateId() {
          return $this->getAccountId() . '_' . $this->getCode();
      }
  
      public function getId() {
          return $this->get(Gpf_Db_Table_Languages::ID);
      }
  
      public function setId($id) {
          $this->set(Gpf_Db_Table_Languages::ID, $id);
      }
  
      public function getCode() {
          return $this->get(Gpf_Db_Table_Languages::CODE);
      }
  
      public function setCode($code) {
          $this->set(Gpf_Db_Table_Languages::CODE, $code);
      }
  
      public function getName() {
          return $this->get(Gpf_Db_Table_Languages::NAME);
      }
  
      public function setName($name) {
          $this->set(Gpf_Db_Table_Languages::NAME, $name);
      }
  
      public function getEnglishName() {
          return $this->get(Gpf_Db_Table_Languages::ENGLISH_NAME);
      }
  
      public function setEnglishName($name) {
          $this->set(Gpf_Db_Table_Languages::ENGLISH_NAME, $name);
      }
  
      public function isActive() {
          return $this->get(Gpf_Db_Table_Languages::ACTIVE) == Gpf::YES;
      }
  
      public function setActive($isActive) {
          if ($isActive == Gpf::YES || $isActive === true) {
              $this->set(Gpf_Db_Table_Languages::ACTIVE, Gpf::YES);
          } else {
              $this->set(Gpf_Db_Table_Languages::ACTIVE, Gpf::NO);
          }
      }
  
      public function getAuthor() {
          return $this->get(Gpf_Db_Table_Languages::AUTHOR);
      }
  
      public function setAuthor($author) {
          $this->set(Gpf_Db_Table_Languages::AUTHOR, $author);
      }
  
      public function getVersion() {
          return $this->get(Gpf_Db_Table_Languages::VERSION);
      }
  
      public function setVersion($version) {
          $this->set(Gpf_Db_Table_Languages::VERSION, $version);
      }
  
      public function getImported() {
          return $this->get(Gpf_Db_Table_Languages::IMPORTED);
      }
  
      public function setImported($imported) {
          $this->set(Gpf_Db_Table_Languages::IMPORTED, $imported);
      }
  
      public function getAccountId() {
          return $this->get(Gpf_Db_Table_Accounts::ID);
      }
  
      public function setAccountId($accountId) {
          $this->set(Gpf_Db_Table_Accounts::ID, $accountId);
      }
  
      public function getDateFormat() {
          return $this->get(Gpf_Db_Table_Languages::DATE_FORMAT);
      }
  
      public function setDateFormat($format) {
          $this->set(Gpf_Db_Table_Languages::DATE_FORMAT, $format);
      }
  
      public function getTimeFormat() {
          return $this->get(Gpf_Db_Table_Languages::TIME_FORMAT);
      }
  
      public function setTimeFormat($format) {
          $this->set(Gpf_Db_Table_Languages::TIME_FORMAT, $format);
      }
      
  	public function getThousandsSeparator() {
          return $this->get(Gpf_Db_Table_Languages::THOUSANDS_SEPARATOR);
      }
      
  	public function setThousandsSeparator($separator) {
          $this->set(Gpf_Db_Table_Languages::THOUSANDS_SEPARATOR, $separator);
      }
      
  	public function getDecimalSeparator() {
          return $this->get(Gpf_Db_Table_Languages::DECIMAL_SEPARATOR);
      }
  
  	public function setDecimalSeparator($separator) {
          $this->set(Gpf_Db_Table_Languages::DECIMAL_SEPARATOR, $separator);
      }
      
      public function getTranslatedPercentage() {
          return $this->get(Gpf_Db_Table_Languages::TRANSLATED_PERCENTAGE);
      }
  
      public function setTranslatedPercentage($percent) {
          $this->set(Gpf_Db_Table_Languages::TRANSLATED_PERCENTAGE, $percent);
      }
  
      public function isDefault() {
          return $this->get(Gpf_Db_Table_Languages::IS_DEFAULT) == Gpf::YES;
      }
  
      public function setIsDefault($isDefault) {
          if ($isDefault == Gpf::YES || $isDefault === true) {
              $this->set(Gpf_Db_Table_Languages::IS_DEFAULT, Gpf::YES);
          } else {
              $this->set(Gpf_Db_Table_Languages::IS_DEFAULT, Gpf::NO);
          }
      }
  }
} //end Gpf_Db_Language

if (!class_exists('Gpf_DbEngine_Row_NumberConstraint', false)) {
  class Gpf_DbEngine_Row_NumberConstraint extends Gpf_Object implements Gpf_DbEngine_Row_Constraint {
      
      private $columnName;
      private $length;
      
      /**
       * @param string $columnNames
       */
      public function __construct($columnName) {
          $this->columnName = $columnName;
      }
      
      /**
       * Validate Db_Row
       *
       * @param Gpf_DbEngine_Row $row
       * @throws Gpf_DbEngine_Row_ConstraintException
       */
      public function validate(Gpf_DbEngine_Row $row) {
          $value = $row->get($this->columnName);
          if ($value === null || $value == '') {
              return;
          }
          if (!is_numeric($value)) {
              throw new Gpf_DbEngine_Row_ConstraintException($this->columnName,
                  $this->_("Column %s must be number (%s given)",
                           $this->columnName, $value));
          }
      }
  }
} //end Gpf_DbEngine_Row_NumberConstraint

if (!interface_exists('Gpf_HttpResponse', false)) {
  interface Gpf_HttpResponse {
      public function setCookieValue($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null);
      
      public function setHeaderValue($name, $value, $replace = true, $httpResponseCode = null);
  }

} //end Gpf_HttpResponse

if (!class_exists('Gpf_Http', false)) {
  class Gpf_Http extends Gpf_Object implements Gpf_HttpResponse {
      /**
       *
       * @var Gpf_HttpResponse
       */
      private static $instance = null;
      
      /**
       * @return Gpf_Http
       */
      private static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new Gpf_Http();
          }
          return self::$instance;
      }
      
      public static function setInstance(Gpf_HttpResponse $instance) {
          self::$instance = $instance;
      }
      
      public static function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null) {
          self::getInstance()->setCookieValue($name, $value, $expire, $path, $domain, $secure, $httpOnly);
      }
      
      public static function setHeader($name, $value, $httpResponseCode = null) {
          self::getInstance()->setHeaderValue($name, $value, true, $httpResponseCode);
      }
      
      public function setHeaderValue($name, $value, $replace = true, $httpResponseCode = null) {
          $fileName = '';
          $line = '';
          if(headers_sent($fileName, $line)) {
              throw new Gpf_Exception("Headers already sent in $fileName line $line while setting header $name: $value");
          }
          header($name . ': ' . $value, $replace, $httpResponseCode);
      }
      
      public function setCookieValue($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null) {
          setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
      }
      
      public static function getCookie($name) {
          if (!array_key_exists($name, $_COOKIE)) {
              return null;
          }
          return $_COOKIE[$name];
      }
      
      public static function getUserAgent() {
          if (isset($_SERVER['HTTP_USER_AGENT'])) {
              return $_SERVER['HTTP_USER_AGENT'];
          }
          return null;
      }
      
      public static function getRemoteIp() {
          $ip = '';
          if (isset($_SERVER['REMOTE_ADDR'])) {
              $ip = $_SERVER['REMOTE_ADDR'];
          }
          if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
              $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
              $ipAddresses = explode(',', $ip);   //HTTP_X_FORWARDED_FOR returns multiple IP addresses
              $ip = trim($ipAddresses[0]);
              foreach ($ipAddresses as $ipAddress) {
                  $ipAddress = trim($ipAddress);
                  if (self::isValidIp($ipAddress)) {
                      $ip = $ipAddress;
                      break;
                  }
              }
          }
          return $ip;
      }
  
      private static function isValidIp($ip) {
          if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
              return true;
          }
          return false;
      }
  }

} //end Gpf_Http

if (!class_exists('Gpf_Templates_Template', false)) {
  class Gpf_Templates_Template extends Gpf_Object {
  
      /**
       * @var Gpf_Paths
       */
      private $paths;
      /**
       * @var Gpf_Templates_Smarty
       */
      protected $smarty;
      protected $name;
      protected $theme;
      protected $panel;
      protected $HTML;
      protected $basePath;
  
      const FETCH_FILE = "F";
      const FETCH_TEXT = "T";
  
      /**
       *
       * @param string $templateSource if $fetchType is FETCH_FILE,
       *                                  then $templateSource is template file name
       *                               if $fetchType is FETCH_TEXT,
       *                                  then $templateSource is template source as a string
       * @param string $panelName optional
       * @param string $fetchType FETCH_FILE (default) or FETCH_TEXT
       */
      public function __construct($templateSource, $panelName='', $fetchType=self::FETCH_FILE, $theme='') {
          if ($theme == '') {
              $this->theme = Gpf_Session::getAuthUser()->getTheme();
              $this->paths = Gpf_Paths::getInstance();
          } else {
              $this->theme = $theme;
              $this->paths = Gpf_Paths::getInstance()->clonePaths($theme);
          }
          if ($panelName == '') {
              $this->panel = Gpf_Session::getModule()->getPanelName();
          } else {
              $this->panel = $panelName;
          }
          $this->basePath = $this->paths->getTopPath();
  
          if ($fetchType == self::FETCH_FILE) {
              $this->initFetchFromFile($templateSource);
          } else {
              $this->initFetchFromText($templateSource);
          }
  
          $this->addPluginsDirectories();
  
          $this->setAndCheckCompileDir();
          $this->smarty->register_prefilter(array(&$this,'preProcess'));
  
          $this->assign('basePath', $this->paths->getBaseServerUrl());
          $this->assign('imgPath', $this->getImgUrl());
          $this->assign('logoutUrl', $this->getLogoutUrl());
  
          Gpf_Session::getModule()->assignModuleAttributes($this);
      }
  
      protected function addPluginsDirectories() {
          $paths = $this->paths->getSmartyPluginsPaths();
          foreach ($paths as $path) {
              $this->smarty->plugins_dir[] = $path;
          }
      }
  
      /**
       * @return boolean
       */
      public function isValid() {
          try {
              $this->getHTML();
              return true;
          } catch (Gpf_Templates_SmartySyntaxException $e) {
          }
          return false;
      }
  
      public function assignByRef($tpl_var, &$value) {
          $this->smarty->assign_by_ref($tpl_var, $value);
      }
  
      private function initFetchFromFile($templateName) {
          $this->name = $templateName;
          $this->smarty = new Gpf_Templates_Smarty($templateName, $this->panel);
          $this->smarty->template_dir = $this->getTemplateDir($templateName);
      }
  
      private function initFetchFromText($templateText) {
          $this->name = md5($templateText);
          $this->smarty = new Gpf_Templates_Smarty("text://" . $this->name, $this->panel);
          $this->smarty->setTemplateSource($templateText);
      }
  
      public function getName() {
          return $this->name;
      }
  
      public function setDelimiter($left, $right) {
          $this->smarty->setDelimiter($left, $right);
      }
  
      /**
       * @return Gpf_Io_File
       * @throws Gpf_Exception
       */
      public function getTemplateFile() {
          $file = new Gpf_Io_File($this->paths->getTemplatePath($this->name, $this->panel));
          if (!$file->isExists()) {
              throw new Gpf_Exception('Template '.$this->name.' does not exist');
          }
          return $file;
      }
  
      /**
       * @throws Gpf_Exception
       */
      public function getTemplateSource() {
          return $this->getTemplateFile()->getContents();
      }
  
      public function getTimestamp() {
          return $this->getTemplateFile()->getInodeChangeTime();
      }
  
      public function saveTemplateToFile($templateContent) {
          $file = new Gpf_Io_File($this->paths->getTemplatePath($this->name, $this->panel));
          $file->setFileMode("w");
          $file->write($templateContent);
          $this->deleteCacheFile();
      }
  
      public function deleteCacheFile(){
          $file = new Gpf_Io_File(Gpf_Paths::getInstance()->getCacheAccountDirectory()
          . Gpf_Templates_Smarty::COMPILED_TEMPLATES_DIR
          . $this->panel . '/' . $this->theme . '/' . basename($this->name));
  
          if ($this->theme != rtrim(Gpf_Paths::DEFAULT_THEME, '/')) {
              $this->deleteCacheFileFromDirectory($file->getParent(), $file->getName());
              return;
          }
  
          foreach (new Gpf_Io_DirectoryIterator($file->getParent()->getParent(), '', false, true) as $fullName => $name) {
              $this->deleteCacheFileFromDirectory($fullName, $file->getName());
          }
      }
  
      private function deleteCacheFileFromDirectory($directory, $fileName) {
          foreach (new Gpf_Io_DirectoryIterator($directory, '', false, false) as $fullName => $name) {
              if(strripos($name, '%%'.$this->encodeFileName($fileName))){
                  $fileToDelete = new Gpf_Io_File($fullName);
                  $fileToDelete->delete();
                  break;
              }
          }
      }
      
      protected function encodeFileName($fileName) {
          return str_replace('=', '%', $this->encodeQP($fileName));
      }
  
      private function encodeQP($str) {
          $search  = array('=',   '+',   '?',   ' ', '!', '@', '#', '$', '%', '^', '&');
          $replace = array('=3D', '=2B', '=3F', '_', '=21', '=40', '=23', '=24', '=25', '=5E', '=26');
          $str = str_replace($search, $replace, $str);
          // Replace all extended characters (\x80-xFF) with their ASCII values.
          return preg_replace_callback(
              '/([\x80-\xFF])/', array('Gpf_Templates_Template', '_qpReplaceCallback'), $str
          );
      }
  
      private function _qpReplaceCallback($matches) {
          return sprintf('=%02X', ord($matches[1]));
      }
  
      /**
       *
       * @return Gpf_Rpc_Data
       */
      public function getDataResponse() {
          $template = new Gpf_Rpc_Data(new Gpf_Rpc_Params());
          $template->setValue("html", $this->getHTML());
          return $template;
  
      }
  
      public function assignAttributes(Gpf_Templates_HasAttributes $data) {
          foreach ($data->getAttributes() as $id => $value) {
              $this->smarty->assign($id, $value);
          }
      }
  
      public function assignNameAttributes($name, Gpf_Templates_HasAttributes $data) {
          $array = array();
          foreach ($data->getAttributes() as $id => $value) {
              $array[$id] = $value;
          }
          $this->smarty->assign($name, $array);
      }
  
      public function assign($varName, $value = null) {
          if($value instanceof Gpf_Data_RecordSet) {
              $this->smarty->assign($varName, $value->toArray());
              return;
          }
          $this->smarty->assign($varName, $value);
      }
  
      public function register_object($name, $object, $methods){
          $this->smarty->register_object($name, $object, $methods);
      }
  
      public function register_function($name, $impl){
          $this->smarty->register_function($name, $impl);
      }
  
      public function preProcess($source, &$smarty) {
          preg_match_all('/##(.+?)##/ms', $source, $attributes, PREG_OFFSET_CAPTURE);
          foreach ($attributes[1] as $index => $attribute) {
              $source = str_replace($attributes[0][$index][0], '{localize str=\'' . addcslashes($attribute[0], "'"). '\'}', $source);
          }
          return $source;
      }
  
      /**
       * @throws Gpf_Templates_SmartySyntaxException
       */
      public function getHTML() {
          return $this->smarty->getText();
      }
  
      public function check() {
          return $this->smarty->checkSyntax();
      }
  
      private function setAndCheckCompileDir() {
          $this->checkCompilePanelDirectory();
          $baseCompileDir = Gpf_Templates_Smarty::getCompileDir();
          $dir = new Gpf_Io_File($baseCompileDir . $this->panel . '/' . $this->theme . '/');
          if(!$dir->isExists()) {
              $dir->mkdir(true);
          }
  
          $this->smarty->compile_dir = $dir->getFileName();
      }
  
      private function checkCompilePanelDirectory() {
          $baseCompileDir = Gpf_Templates_Smarty::getCompileDir();
          $panelDir = new Gpf_Io_File($baseCompileDir . $this->panel . '/');
          if(!$panelDir->isExists()) {
              $panelDir->mkdir(true);
          }
      }
  
      private function getImgUrl() {
          return $this->paths->getTopTemplateUrl() . $this->panel . '/' .  $this->theme . '/img/';
      }
  
      private function getTemplateDir($templateName) {
          return dirname($this->paths->getTemplatePath($templateName, $this->panel));
      }
  
      private function getLogoutUrl() {
      	return Gpf_Paths::getInstance()->getFullScriptsUrl(). 'server.php?C=Gpf_Auth_Service&M=logoutByURL&S=' . Gpf_Session::getInstance()->getId() . '&FormRequest=Y&FormResponse=Y';    	
      }
  }

} //end Gpf_Templates_Template

if (!class_exists('Pap_Module', false)) {
  class Pap_Module extends Gpf_Object {
      
      public static function assignTemplateVariables(Gpf_Templates_Template $template) {
          if (Gpf_Session::getAuthUser()->isLogged()) {
              $template->assign('isLogged', '1');
          } else {
              $template->assign('isLogged', '0');
          }
          $template->assign('papCopyrightText', Gpf_Settings::get(Pap_Settings::BRANDING_TEXT));
      	$template->assign('papVersionText', 'version ' . Gpf_Application::getInstance()->getVersion());
      	$template->assign('postAffiliatePro', Gpf_Settings::get(Pap_Settings::BRANDING_TEXT_POST_AFFILIATE_PRO));
      	$template->assign('qualityUnitPostaffiliateproLink', Gpf_Settings::get(Pap_Settings::BRANDING_QUALITYUNIT_POSTAFFILIATEPRO_LINK));
      	$template->assign('postAffiliateProHelp', Gpf_Settings::get(Pap_Settings::BRANDING_POST_AFFILIATE_PRO_HELP_LINK));
      	$template->assign('qualityUnitChangeLog', Gpf_Settings::get(Pap_Settings::BRANDING_QUALITYUNIT_CHANGELOG_LINK));
      	$template->assign('knowledgebaseUrl', Gpf_Settings::get(Pap_Settings::BRANDING_KNOWLEDGEBASE_LINK));
      	$template->assign('PAP', Gpf_Settings::get(Pap_Settings::BRANDING_QUALITYUNIT_PAP));
      	$template->assign('tutorialVideosBaseLink', Gpf_Settings::get(Pap_Settings::BRANDING_TUTORIAL_VIDEOS_BASE_LINK));
      }
      
      public static function setSessionInfo(Gpf_Rpc_Data $sessionInfo) {
          $sessionInfo->setValue('extra1', Pap_Db_Table_Users::getAffiliateCount());
      }
      
      /**
       *
       * @return array
       */
      public static function getStyleSheets() {
          return array('pap4.css', 'custom.css');
      }
  }

} //end Pap_Module

if (!class_exists('Gpf_Rpc_CachedResponse', false)) {
  class Gpf_Rpc_CachedResponse extends Gpf_Object {
      private $responses = array();
      private $encodedResponses = array();
      /**
       * @var Gpf_Rpc_CachedResponse
       */
      private static $instance = null;
      
      private function __construct() {
          
      }
      
      /**
       * @return Gpf_Rpc_CachedResponse
       */
      public static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new Gpf_Rpc_CachedResponse();
          }
          return self::$instance;
      }
      
      public static function reset() {
          $instance = self::getInstance();
          $instance->clearResponse();
      }
      
      public static function add(Gpf_Rpc_Serializable $response, $className, $methodName, $id = '') {
          $instance = self::getInstance();
          $instance->addResponse($response, $className, $methodName, $id);
      }
      
      public static function addById(Gpf_Rpc_Serializable $response, $id) {
          $instance = self::getInstance();
          $instance->addResponse($response, '', '', $id);
      }
      
      public static function addEncodedById($encodedValue, $id) {
          $instance = self::getInstance();
          $instance->addEncodedResponse($encodedValue, '', '', $id);
      }
      
      private function addEncodedResponse($encodedValue, $className, $methodName, $id = '') {
          $this->encodedResponses[$this->getKey($className, $methodName, $id)] = trim($encodedValue);
      }
      
      private function addResponse(Gpf_Rpc_Serializable $response, $className, $methodName, $id = '') {
          $this->responses[$this->getKey($className, $methodName, $id)] = $response;
      }
      
      private function getKey($className, $methodName, $id) {
          return md5(strtolower($className) . '|' . $methodName . '|' . $id);
      }
      
      private function clearResponse() {
          $this->responses = array();
      }
      
      public static function render() {
          $encoder = new Gpf_Rpc_Json();
          $out = '';
          $response = self::getInstance();
          
          foreach ($response->encodedResponses as $id => $value) {
              $out .= 'window["' . $id . '"]="' . $value . '";';                
          }
          
          foreach ($response->responses as $id => $value) {
              $out .= 'window["' . $id . '"]="' . addcslashes($encoder->encodeResponse($value), '"\\') . '";';                
          }
          return '<script type="text/javascript">' . $out . '</script>';
      }
  }

} //end Gpf_Rpc_CachedResponse

if (!class_exists('Gpf_Templates_Templates', false)) {
  class Gpf_Templates_Templates extends Gpf_Object {
      private $templates = array();
      
      /**
       * Gets template names for template name suggestion oracle
       *
       * @service template read
       * @param $search
       */
      public function getTemplateNames(Gpf_Rpc_Params $params) {
          $searchString = $params->get('search');
          $this->loadTemplates();
  
          $result = new Gpf_Data_RecordSet();
          $result->setHeader(array('id', 'name'));
  
          foreach ($this->templates as $templateName) {
              if ($searchString == "" || strstr($templateName, $searchString) !== false) {
                  $result->add(array($templateName, $templateName . '.tpl'));
              }
          }
          return $result;
      }
  
      public function getAllTemplateNames() {
          $this->loadTemplates();
          return $this->templates;
      }
      
      private function loadTemplates() {
          if(count($this->templates)) {
              return;
          }
          foreach (Gpf_Paths::getInstance()->getTemplateSearchPaths() as $templateDir) {
              $this->loadTemplatesFromDirectory($templateDir);
          }
      }
  
      private function loadTemplatesFromDirectory($dirname) {
          foreach (new Gpf_Io_DirectoryIterator($dirname, '.tpl') as $fullFileName => $fileName) {
              $name = substr($fileName, 0, strrpos($fileName, '.'));
              $this->templates[$name] = $name;
          }
      }
  
      public function addToCache($allowedTemplates) {
          $service = new Gpf_Templates_TemplateService();
          foreach ($allowedTemplates as $templateName) {        	
              if ($this->existTemplate($templateName)) {
                  Gpf_Rpc_CachedResponse::add($service->getTemplateNoRpc($templateName),
                  'Gpf_Templates_TemplateService', 'getTemplate', $templateName);
              }
          }
      }
      
      private function existTemplate($templateName) {
          foreach (Gpf_Paths::getInstance()->getTemplateSearchPaths() as $templateDir) {
          	if (Gpf_Io_File::isFileExists($templateDir . $templateName . ".tpl")) {
          		return true;
          	}
          }
          return false;
      }
  }

} //end Gpf_Templates_Templates

if (!class_exists('Gpf_Templates_TemplateService', false)) {
  class Gpf_Templates_TemplateService extends Gpf_Object {
  
      /**
       * Returns template
       *
       * @service
       * @anonym
       * @param $templateName
       * @return Gpf_Rpc_Serializable
       */
      public function getTemplate(Gpf_Rpc_Params $params) {
          $templateName = $params->get('templateName');
          return $this->getTemplateNoRpc($templateName);
      }
  
      /**
       * Returns missing templates
       *
       * @service
       * @anonym
       * @param $loadedTemplates String of templates names loaded already in client (separated by comma)
       * @return Gpf_Rpc_Serializable
       */
      public function getAllMissingTemplates(Gpf_Rpc_Params $params) {
          $loadedTemplates = explode(',', trim($params->get('loadedTemplates'), ','));
          $recordSet = new Gpf_Data_RecordSet();
          $recordSet->setHeader(array('templateName', 'templateHtml'));
  
          $service = new Gpf_Templates_Templates();
          $allNames = $service->getAllTemplateNames();
          $count = $params->get('templatesCount');
          foreach ($allNames as $templateName) {
              if (!in_array($templateName, $loadedTemplates)) {
                  $template = new Gpf_Templates_Template($templateName.'.tpl');
                  $recordSet->add(array($templateName, $template->getHTML()));
                  $count --;
                  if ($count == 0) break;
              }
          }
          return $recordSet;
      }
  
  
      /**
       * Returns raw template
       *
       * @service
       * @anonym
       * @param $templateName
       * @return Gpf_Data_RecordSet
       */
      public function getRawTemplate(Gpf_Rpc_Params $params) {
          $templateName = $params->get('templateName');
          return $this->createTemplateRecordSet($templateName, $this->getRawTemplateNoRpc($templateName));
      }
  
      /**
       * Returns default raw template
       * (template is not loaded from database)
       *
       * @service
       * @anonym
       * @param $templateName
       * @return Gpf_Data_RecordSet
       */
      public function getDefaultRawTemplate(Gpf_Rpc_Params $params) {
          $templateName = $params->get('templateName');
          return $this->createTemplateRecordSet($templateName, $this->getRawTemplateNoRpc($templateName, false));
      }
  
      public function getRawTemplateNoRpc($templateName, $loadCustomTemplates = true) {
          $template = new Gpf_Templates_Template($templateName.'.tpl', true, $loadCustomTemplates);
          return $template->getTemplateSource();
      }
  
  
      public function getTemplateNoRpc($templateName) {
          $template = new Gpf_Templates_Template($templateName.'.tpl');
          return $this->createTemplateRecordSet($templateName, $template->getHTML());
      }
  
      private function createTemplateRecordSet($templateName, $templateContent) {
          $recordSet = new Gpf_Data_RecordSet();
          $recordSet->setHeader(array('templateName', 'templateHtml'));
          $recordSet->add(array($templateName, $templateContent));
          return $recordSet;
      }
  }
  

} //end Gpf_Templates_TemplateService

if (!class_exists('Gpf_Rpc_Params', false)) {
  class Gpf_Rpc_Params extends Gpf_Object implements Gpf_Rpc_Serializable {
      private $params;
      const CLASS_NAME = 'C';
      const METHOD_NAME = 'M';
      const SESSION_ID = 'S';
      const ACCOUNT_ID = 'aid';
  
      function __construct($params = null) {
          if($params === null) {
              $this->params = new stdClass();
              return;
          }
          $this->params = $params;
      }
  
      public static function createGetRequest($className, $methodName = 'execute', $formRequest = false, $formResponse = false) {
          $requestData = array();
          $requestData[self::CLASS_NAME] = $className;
          $requestData[self::METHOD_NAME] = $methodName;
          $requestData[Gpf_Rpc_Server::FORM_REQUEST] = $formRequest ? Gpf::YES : '';
          $requestData[Gpf_Rpc_Server::FORM_RESPONSE] = $formResponse ? Gpf::YES : '';
          return $requestData;
      }
  
      /**
       *
       * @param unknown_type $className
       * @param unknown_type $methodName
       * @param unknown_type $formRequest
       * @param unknown_type $formResponse
       * @return Gpf_Rpc_Params
       */
      public static function create($className, $methodName = 'execute', $formRequest = false, $formResponse = false) {
          $params = new Gpf_Rpc_Params();
          $obj = new stdClass();
          foreach (self::createGetRequest($className, $methodName, $formRequest, $formResponse) as $name => $value) {
              $params->add($name,$value);
          }
          return $params;
      }
  
      public function setArrayParams(array $params) {
          foreach ($params as $name => $value) {
              $this->add($name, $value);
          }
      }
  
      public function exists($name) {
          if(!is_object($this->params) || !array_key_exists($name, $this->params)) {
              return false;
          }
          return true;
      }
  
      /**
       *
       * @param unknown_type $name
       * @return mixed Return null if $name does not exist.
       */
      public function get($name) {
          if(!$this->exists($name)) {
              return null;
          }
          return $this->params->{$name};
      }
  
      public function set($name, $value) {
          if(!$this->exists($name)) {
              return;
          }
          $this->params->{$name} = $value;
      }
  
      public function add($name, $value) {
          $this->params->{$name} = $value;
      }
  
      public function getClass() {
          return $this->get(self::CLASS_NAME);
      }
  
      public function getMethod() {
          return $this->get(self::METHOD_NAME);
      }
  
      public function getSessionId() {
          $sessionId = $this->get(self::SESSION_ID);
          if ($sessionId === null || strlen(trim($sessionId)) == 0) {
              Gpf_Session::create(new Gpf_ApiModule());
          }
          return $sessionId;
      }
      
      public function clearSessionId() {
          $this->set(self::SESSION_ID, null);
      }
  
      public function getAccountId() {
          return $this->get(self::ACCOUNT_ID);
      }
  
      public function toObject() {
          return $this->params;
      }
  
      public function toText() {
          throw new Gpf_Exception("Unimplemented");
      }
  }
  

} //end Gpf_Rpc_Params

if (!class_exists('Gpf_ApplicationSettings', false)) {
  abstract class Gpf_ApplicationSettings extends Gpf_Object {
      
      /**
       * @var Gpf_Data_RecordSet
       */
      private $recordSet;
      
      const CODE = "code";
      const VALUE = "value";
      
      protected function loadSetting() {
          $this->addValue("theme", Gpf_Session::getAuthUser()->getTheme());
          $this->addValue("date_time_format", 'MM/d/yyyy HH:mm:ss');
          $this->addValue("programVersion", Gpf_Application::getInstance()->getVersion());
          $this->addValue(Gpf_Settings_Gpf::NOT_FORCE_EMAIL_USERNAMES, Gpf_Settings::get(Gpf_Settings_Gpf::NOT_FORCE_EMAIL_USERNAMES));
          
          $quickLaunchSettings = new Gpf_Desktop_QuickLaunch();
      	$this->addValue(Gpf_Desktop_QuickLaunch::SHOW_QUICK_LAUNCH, $quickLaunchSettings->getShowQuickLaunch());
          
          $this->addValue(Gpf_Settings_Gpf::REGIONAL_SETTINGS_THOUSANDS_SEPARATOR, Gpf_Settings_Regional::getInstance()->getThousandsSeparator());
          $this->addValue(Gpf_Settings_Gpf::REGIONAL_SETTINGS_DECIMAL_SEPARATOR, Gpf_Settings_Regional::getInstance()->getDecimalSeparator());
          $this->addValue(Gpf_Settings_Gpf::REGIONAL_SETTINGS_DATE_FORMAT, Gpf_Settings_Regional::getInstance()->getDateFormat());
          $this->addValue(Gpf_Settings_Gpf::REGIONAL_SETTINGS_TIME_FORMAT, Gpf_Settings_Regional::getInstance()->getTimeFormat());
          
          Gpf_Plugins_Engine::extensionPoint('Core.loadSetting', $this);
      }
      
      /**
       * @anonym
       * @service
       */
      public function getSettings(Gpf_Rpc_Params $params) {
          return $this->getSettingsNoRpc();
      }
      
      /**
       *
       * @return Gpf_Data_RecordSet
       */
      public function getSettingsNoRpc() {
          $this->recordSet = new Gpf_Data_RecordSet();
          $this->recordSet->setHeader(new Gpf_Data_RecordHeader(array(self::CODE, self::VALUE)));
          $this->loadSetting();
          return $this->recordSet;
      }
      
      public function addValue($code, $value) {
          $record = $this->recordSet->createRecord();
          $record->set(self::CODE, $code);
          $record->set(self::VALUE, $value);
          $this->recordSet->addRecord($record);
      }
      
      protected function addSetting($code) {
          $this->addValue($code, Gpf_Settings::get($code));
      }
  }

} //end Gpf_ApplicationSettings

if (!class_exists('Pap_LoginApplicationSettings', false)) {
  class Pap_LoginApplicationSettings extends Gpf_ApplicationSettings {
  
      protected function loadSetting() {
          parent::loadSetting();
          $this->addValue("DEMO_MODE", Gpf_Application::isDemo() ? "Y" : "N");
          $this->addValue(Pap_Settings::BRANDING_TEXT_POST_AFFILIATE_PRO, Gpf_Settings::get(Pap_Settings::BRANDING_TEXT_POST_AFFILIATE_PRO));
      }
  }

} //end Pap_LoginApplicationSettings

if (!class_exists('Gpf_Desktop_QuickLaunch', false)) {
  class Gpf_Desktop_QuickLaunch extends Gpf_Object {
  
  	const SHOW_QUICK_LAUNCH = 'showQuickLaunch';
  	const SHOW_QUICK_LAUNCH_DEFAULT_VALUE = 'Y';
  
  	/**
  	 * @service quicklaunch read
  	 * @param Gpf_Rpc_Params $params
  	 */
  	public function load(Gpf_Rpc_Params $params) {
  		$form = new Gpf_Rpc_Form();
  		$form->addField(self::SHOW_QUICK_LAUNCH, $this->getShowQuickLaunch());
  		return $form;
  	}
  
  	/**
  	 * @service quicklaunch write
  	 * @param Gpf_Rpc_Params $params
  	 */
  	public function save(Gpf_Rpc_Params $params) {
  		$form = new Gpf_Rpc_Form($params);
  
  		try {
  			Gpf_Db_UserAttribute::saveAttribute(self::SHOW_QUICK_LAUNCH, $form->getFieldValue(self::SHOW_QUICK_LAUNCH));
  		} catch (Gpf_Exception $e) {
  			$form->setErrorMessage($this->_('Failed to save quick launch settings with error %s', $e->getMessage()));
  			return $form;
  		}
  
  		$form->setInfoMessage($this->_('Quick launch saved.'));
  
  		return $form;
  	}
  
  	/**
  	 * @return String
  	 */
  	public function getShowQuickLaunch() {
  		try {
  			$attributes = $this->getAttributes();
  			if (isset($attributes[self::SHOW_QUICK_LAUNCH])) {
  				return $attributes[self::SHOW_QUICK_LAUNCH];
  			}
  		} catch (Gpf_Exception $e) {
  		}
  		return self::SHOW_QUICK_LAUNCH_DEFAULT_VALUE;
  	}
  
  	/**
  	 * @return array
  	 */
  	private function getAttributes() {
  		$attributes = Gpf_Db_UserAttribute::getSettingsForGroupOfUsers(array(self::SHOW_QUICK_LAUNCH),
  		array($this->getAccountUserId()));
  
  		if (isset($attributes[$this->getAccountUserId()])) {
  			return $attributes[$this->getAccountUserId()];
  		}
  		throw new Gpf_Exception($this->_('Settings not exists, load default settings.'));
  	}
  
  	/**
  	 * @return String
  	 */
  	private function getAccountUserId() {
  		return Gpf_Session::getInstance()->getAuthUser()->getAccountUserId();
  	}
  }

} //end Gpf_Desktop_QuickLaunch

if (!class_exists('Gpf_Db_UserAttribute', false)) {
  class Gpf_Db_UserAttribute extends Gpf_DbEngine_Row {
  
      function init() {
          $this->setTable(Gpf_Db_Table_UserAttributes::getInstance());
          parent::init();
      }
  
      public function setName($name) {
          $this->set(Gpf_Db_Table_UserAttributes::NAME, $name);
      }
  
      public function setAccountUserId($accountUserId) {
          $this->set(Gpf_Db_Table_UserAttributes::ACCOUNT_USER_ID, $accountUserId);
      }
  
      public function getValue() {
          return $this->get(Gpf_Db_Table_UserAttributes::VALUE);
      }
  
      public function setValue($value) {
          $this->set(Gpf_Db_Table_UserAttributes::VALUE, $value);
      }
  
      public function getName() {
          return $this->get(Gpf_Db_Table_UserAttributes::NAME);
      }
  
      public function getAccountUserId() {
          return $this->get(Gpf_Db_Table_UserAttributes::ACCOUNT_USER_ID);
      }
  
      public function getSetting($name, $accounUsertId = null) {
          if ($accounUsertId == null) {
              $accounUsertId = Gpf_Session::getAuthUser()->getAccountUserId();
          }
          $this->setName($name);
          $this->setAccountUserId($accounUsertId);
          $this->loadFromData(array(Gpf_Db_Table_UserAttributes::NAME, Gpf_Db_Table_UserAttributes::ACCOUNT_USER_ID));
          return $this->getValue();
      }
  
      public function save() {
          try {
              $attribute = new Gpf_Db_UserAttribute();
              $attribute->getSetting($this->getName(), $this->getAccountUserId());
              $this->setPrimaryKeyValue($attribute->getPrimaryKeyValue());
              $this->update();
          } catch (Gpf_DbEngine_NoRowException $e) {
              $this->insert();
          }
      }
  
      /**
       * returns recordset of given attribute values for given array of users
       *
       * @param unknown_type $settingsNames
       * @param unknown_type $accountUserIds
       */
      public static function getSettingsForGroupOfUsers($settingsNames, $accountUserIds) {
          $select = new Gpf_SqlBuilder_SelectBuilder();
          $select->select->add(Gpf_Db_Table_UserAttributes::ACCOUNT_USER_ID, "accountuserid");
          $select->select->add(Gpf_Db_Table_UserAttributes::NAME, "name");
          $select->select->add(Gpf_Db_Table_UserAttributes::VALUE, "value");
  
          $select->from->add(Gpf_Db_Table_UserAttributes::getName());
  
          $select->where->add(Gpf_Db_Table_UserAttributes::NAME, "IN", $settingsNames);
          $select->where->add(Gpf_Db_Table_UserAttributes::ACCOUNT_USER_ID, "IN", $accountUserIds);
  
          $recordSet = $select->getAllRows();
  
          $results = array();
          foreach($recordSet as $record) {
              $results[$record->get('accountuserid')][$record->get('name')] = $record->get('value');
          }
  
          return $results;
      }
  
      public static function saveAttribute($name, $value, $accountUserId = null) {
          $attribute = new Gpf_Db_UserAttribute();
          $attribute->setName($name);
          $attribute->setValue($value);
          if ($accountUserId == null) {
              $attribute->setAccountUserId(Gpf_Session::getInstance()->getAuthUser()->getAccountUserId());
          } else {
              $attribute->setAccountUserId($accountUserId);
          }
          return $attribute->save();
      }
  }
  

} //end Gpf_Db_UserAttribute

if (!class_exists('Gpf_Settings_Regional', false)) {
  class Gpf_Settings_Regional {
  
      /**
       * @var Gpf_Settings_Regional
       */
      private static $instance;
  
      private $thousandsSeparator, $decimalSeparator, $dateFormat, $timeFormat;
  
      /**
       * @return Gpf_Settings_Regional
       */
      public function getInstance() {
          if (self::$instance == null) {
              self::$instance = new Gpf_Settings_Regional();
          }
          return self::$instance;
      }
  
      private function __construct() {
          $this->loadSettings();
      }
  
      public function getThousandsSeparator() {
          return $this->thousandsSeparator;
      }
  
      public function getDecimalSeparator() {
          return $this->decimalSeparator;
      }
  
      public function getDateFormat() {
          return $this->dateFormat;
      }
      
      public function setDateFormat($format) {
          $this->dateFormat = $format;
      }
  
      public function getTimeFormat() {
          return $this->timeFormat;
      }
  
      private function loadSettings() {
          if (Gpf_Settings::get(Gpf_Settings_Gpf::REGIONAL_SETTINGS_IS_DEFAULT) == Gpf::YES) {
              try {
                  $this->loadSettingsFromLanguage();
                  return;
              } catch (Gpf_Exception $e) {
              }
          }
          $this->thousandsSeparator = Gpf_Settings::get(Gpf_Settings_Gpf::REGIONAL_SETTINGS_THOUSANDS_SEPARATOR);
          $this->decimalSeparator = Gpf_Settings::get(Gpf_Settings_Gpf::REGIONAL_SETTINGS_DECIMAL_SEPARATOR);
          $this->dateFormat = Gpf_Settings::get(Gpf_Settings_Gpf::REGIONAL_SETTINGS_DATE_FORMAT);
          $this->timeFormat = Gpf_Settings::get(Gpf_Settings_Gpf::REGIONAL_SETTINGS_TIME_FORMAT);
      }
  
      /**
       * @throws Gpf_Exception
       */
      private function loadSettingsFromLanguage() {
          $lang = Gpf_Lang_Dictionary::getInstance()->getLanguage();
          if ($lang == null) {
              throw new Gpf_Exception('No language loaded');
          }
          $this->thousandsSeparator = $lang->getThousandsSeparator();
          $this->decimalSeparator = $lang->getDecimalSeparator();
          $this->dateFormat = $lang->getDateFormat();
          $this->timeFormat = $lang->getTimeFormat();
      }
  
  }

} //end Gpf_Settings_Regional

if (!class_exists('Gpf_Rpc_Data', false)) {
  class Gpf_Rpc_Data extends Gpf_Object implements Gpf_Rpc_Serializable {
  	const NAME  = "name";
      const VALUE = "value";
      const DATA = "data";
      const ID = "id";
      
  	/**
  	 * @var Gpf_Data_IndexedRecordSet
  	 */
      private $params;
      
      /**
       * @var string
       */
      private $id;
      
      
      /**
       * @var Gpf_Rpc_FilterCollection
       */
      private $filters;
      
      /**
       * @var Gpf_Data_IndexedRecordSet
       */
      private $response;
      
      /**
       *
       * @return Gpf_Data_IndexedRecordSet
       */
      public function getParams() {
          return $this->params;
      }
      
      /**
       * Create instance to handle DataRequest
       *
       * @param Gpf_Rpc_Params $params
       */
      public function __construct(Gpf_Rpc_Params $params = null) {
      	if($params === null) {
      	    $params = new Gpf_Rpc_Params();
      	}
          
      	$this->filters = new Gpf_Rpc_FilterCollection($params);
          
      	$this->params = new Gpf_Data_IndexedRecordSet(self::NAME);
      	$this->params->setHeader(array(self::NAME, self::VALUE));
          
          if ($params->exists(self::DATA) !== null) {
              $this->loadParamsFromArray($params->get(self::DATA));
          }
          
          $this->id = $params->get(self::ID);
          
          $this->response = new Gpf_Data_IndexedRecordSet(self::NAME);
          $this->response->setHeader(array(self::NAME, self::VALUE));
      }
      
     /**
       * Return id
       *
       * @return string
       */
      public function getId() {
          return $this->id;
      }
      
      /**
       * Return parameter value
       *
       * @param String $name
       * @return unknown
       */
      public function getParam($name) {
          try {
             return $this->params->getRecord($name)->get(self::VALUE);
          } catch (Gpf_Data_RecordSetNoRowException $e) {
             return null;
          }
      }
      
      public function setParam($name, $value) {
          self::setValueToRecordset($this->params, $name, $value);
      }
      
      public function loadFromObject(array $object) {
          $this->response->loadFromObject($object);
          $this->params->loadFromObject($object);
      }
          
      /**
       * @return Gpf_Rpc_FilterCollection
       */
      public function getFilters() {
      	return $this->filters;
      }
  
      private static function setValueToRecordset(Gpf_Data_IndexedRecordSet $recordset, $name, $value) {
          try {
             $record = $recordset->getRecord($name);
          } catch (Gpf_Data_RecordSetNoRowException $e) {
             $record = $recordset->createRecord();
             $record->set(self::NAME, $name);
             $recordset->addRecord($record);
          }
          $record->set(self::VALUE, $value);
      }
      
      public function setValue($name, $value) {
          self::setValueToRecordset($this->response, $name, $value);
      }
      
      public function getSize() {
          return $this->response->getSize();
      }
      
      public function getValue($name) {
          try {
              return $this->response->getRecord($name)->get(self::VALUE);
          } catch (Gpf_Data_RecordSetNoRowException $e) {
          }
          return null;
      }
      
      public function toObject() {
      	return $this->response->toObject();
      }
  
      public function toText() {
      	return $this->response->toText();
      }
  
      private function loadParamsFromArray($data) {
          for ($i = 1; $i < count($data); $i++) {
              $this->params->add($data[$i]);
          }
      }
  }

} //end Gpf_Rpc_Data

if (!class_exists('Gpf_Rpc_FilterCollection', false)) {
  class Gpf_Rpc_FilterCollection extends Gpf_Object implements IteratorAggregate {
  
      /**
       * @var array of Gpf_SqlBuilder_Filter
       */
      private $filters;
  
      public function __construct(Gpf_Rpc_Params $params = null) {
          $this->filters = array();
          if ($params != null) {
              $this->init($params);
          }
      }
      
      public function add(array $filterArray) {
      	$this->filters[] = new Gpf_SqlBuilder_Filter($filterArray);
      }
  
      private function init(Gpf_Rpc_Params $params) {
          $filtersArray = $params->get("filters");
          if (!is_array($filtersArray)) {
              return;
          }
          foreach ($filtersArray as $filterArray) {
              $this->add($filterArray);
          }
      }
  
      /**
       *
       * @return ArrayIterator
       */
      public function getIterator() {
          return new ArrayIterator($this->filters);
      }
  
      public function addTo(Gpf_SqlBuilder_WhereClause $whereClause) {
          foreach ($this->filters as $filter) {
              $filter->addTo($whereClause);
          }
      }
  
      /**
       * Returns first filter with specified code.
       * If filter with specified code does not exists null is returned.
       *
       * @param string $code
       * @return array<Gpf_SqlBuilder_Filter>
       */
      public function getFilter($code) {
      	$filters = array();
          foreach ($this->filters as $filter) {
              if ($filter->getCode() == $code) {
                  $filters[] = $filter;
              }
          }
          return $filters;
      }
      
      public function isFilter($code) {
          foreach ($this->filters as $filter) {
              if ($filter->getCode() == $code) {
                  return true;
              }
          }
          return false;
      }
      
      public function getFilterValue($code) {
          $filters = $this->getFilter($code);
          if (count($filters) == 1) {
              return $filters[0]->getValue();
          }
          return "";
      }
  
      public function matches(Gpf_Data_Record $row) {
          foreach ($this->filters as $filter) {
              if (!$filter->matches($row)) {
                  return false;
              }
          }
          return true;
      }
  
      public function getSize() {
          return count($this->filters);
      }
  }

} //end Gpf_Rpc_FilterCollection

if (!class_exists('Pap_Db_User', false)) {
  class Pap_Db_User extends Gpf_DbEngine_Row {
  
      function __construct(){
          parent::__construct();
      }
  
      function init() {
          $this->setTable(Pap_Db_Table_Users::getInstance());
          parent::init();
      }
  
      public function getType() {
          return $this->get(Pap_Db_Table_Users::TYPE);
      }
  
      public function getId() {
          return $this->get(Pap_Db_Table_Users::ID);
      }
  
      public function getParentUserId() {
          return $this->get(Pap_Db_Table_Users::PARENTUSERID);
      }
      
      public function getOriginalParentUserId() {
          return $this->get(Pap_Db_Table_Users::ORIGINAL_PARENT_USERID);
      }
  
      public function setParentUserId($userid) {
          $this->set(Pap_Db_Table_Users::PARENTUSERID, $userid);
      }
      
      public function setType($type) {
          $this->set(Pap_Db_Table_Users::TYPE, $type);
      }
  
      public function setOriginalParentUserId($userid) {
          $this->set(Pap_Db_Table_Users::ORIGINAL_PARENT_USERID, $userid);
      }
  
      public function setId($userid) {
          $this->set(Pap_Db_Table_Users::ID, $userid);
      }
  
      public function insert() {
          parent::insert();
      }
      
      protected function generatePrimaryKey() {
          parent::generatePrimaryKey();
          Gpf_Plugins_Engine::extensionPoint('PostAffiliate.User.generatePrimaryKey', $this);
      }
  
      public function update($updateColumns = array()) {
          Gpf_Plugins_Engine::extensionPoint('PostAffiliate.User.onUpdate', $this);
          parent::update($updateColumns);
      }
  
      public function setDeleted($deleted) {
          $this->set(Pap_Db_Table_Users::DELETED, $deleted);
      }
  
      public function setMinimumPayout($minimupPayout) {
          $this->set(Pap_Db_Table_Users::MINIMUM_PAYOUT, $minimupPayout);
      }
  
      public function getRefId() {
          return $this->get(Pap_Db_Table_Users::REFID);
      }
  
      public function setRefId($refid) {
          $this->set(Pap_Db_Table_Users::REFID, $refid);
      }
  
      public function getAccountUserId() {
          return $this->get(Pap_Db_Table_Users::ACCOUNTUSERID);
      }
      
      public function setAccountUserId($accountUserId) {
          $this->set(Pap_Db_Table_Users::ACCOUNTUSERID, $accountUserId);
      }
  
      public function getPayoutOptionId() {
          return $this->get(Pap_Db_Table_Users::PAYOUTOPTION_ID);
      }
  
      public function getMinimumPayout() {
          return $this->get(Pap_Db_Table_Users::MINIMUM_PAYOUT);
      }
      
      public function getDeleted(){
          return $this->get(Pap_Db_Table_Users::DELETED);
      }
  }
  

} //end Pap_Db_User

if (!class_exists('Pap_Db_Table_Users', false)) {
  class Pap_Db_Table_Users extends Gpf_DbEngine_Table {
      const ID = 'userid';
      const REFID = 'refid';
      const NUMBERUSERID = 'numberuserid';
      const TYPE = 'rtype';
      const DATEINSERTED = 'dateinserted';
      const DATEAPPROVED = 'dateapproved';
      const DELETED = 'deleted';
      const ACCOUNTUSERID = 'accountuserid';
      const PARENTUSERID = 'parentuserid';
      const PAYOUTOPTION_ID = "payoutoptionid";
      const MINIMUM_PAYOUT = "minimumpayout";
      const NOTE = "note";
      const PHOTO = "photo";
      const ORIGINAL_PARENT_USERID = 'originalparentuserid';
  
      private static $instance;
  
      public static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new self;
          }
          return self::$instance;
      }
  
      protected function initName() {
          $this->setName('pap_users');
      }
  
      public static function getName() {
          return self::getInstance()->name();
      }
  
      public static function getDataColumnName($i) {
          return 'data'.$i;
      }
  
      protected function initColumns() {
          $this->createPrimaryColumn(self::ID, 'char', 8, true);
          $this->createColumn(self::REFID, 'char', 128, true);
          $this->createColumn(self::NUMBERUSERID, self::INT);
          $this->createColumn(self::TYPE, 'char', 1);
          $this->createColumn(self::DATEINSERTED, 'datetime', 0);
          $this->createColumn(self::DATEAPPROVED, 'datetime', 0);
          $this->createColumn(self::DELETED, 'char', 1);
          $this->createColumn(self::ACCOUNTUSERID, 'char', 20);
          $this->createColumn(self::PARENTUSERID, 'char', 20);
          $this->createColumn(self::MINIMUM_PAYOUT, 'char', 20);
          $this->createColumn(self::PAYOUTOPTION_ID, 'char', 8);
          $this->createColumn(self::NOTE, 'char');
          $this->createColumn(self::PHOTO, 'char', 255);
          $this->createColumn(self::ORIGINAL_PARENT_USERID, self::CHAR, 20);
          for ($i = 1; $i <= 25; $i++) {
              $this->createColumn(self::getDataColumnName($i), self::CHAR, 255);
          }
      }
  
      public static function getAffiliateCount() {
          $select = new Gpf_SqlBuilder_SelectBuilder();
          $select->select->add('COUNT(*)', 'count');
          $select->from->add(self::getName(), 'pu');
          $select->from->addInnerJoin(Gpf_Db_Table_Users::getName(), 'u', 'u.accountuserid=pu.accountuserid');
          $select->where->add('pu.' . self::TYPE, '=', Pap_Application::ROLETYPE_AFFILIATE);
          $select->where->add('u.'.Gpf_Db_Table_Users::STATUS, '=', Gpf_Db_User::APPROVED);
          $select->where->add('pu.'.Pap_Db_Table_Users::DELETED, '=', Gpf::NO);
          return $select->getOneRow()->get('count');
      }
  
      /**
       * Pap alert application handle, do not modifi this source!
       *
       * @return Gpf_Data_Record
       */
      public static function getAffiliatesCount($date) {
          $select = new Gpf_SqlBuilder_SelectBuilder();
          $select->select->add("SUM(IF(gu.".Gpf_Db_Table_Users::STATUS."= 'A', 1, 0))", 'affiliates_approved');
          $select->select->add("SUM(IF(gu.".Gpf_Db_Table_Users::STATUS."= 'P', 1, 0))", 'affiliates_pending');
          $select->select->add("SUM(IF(gu.".Gpf_Db_Table_Users::STATUS."= 'D', 1, 0))", 'affiliates_declined');
          $select->from->add(Gpf_Db_Table_Users::getName(), 'gu');
          $select->from->addInnerJoin(Pap_Db_Table_Users::getName(), 'pu',
              'gu.'.Gpf_Db_Table_Users::ID.'=pu.'.Pap_Db_Table_Users::ACCOUNTUSERID);
          $select->from->addInnerJoin(Gpf_Db_Table_AuthUsers::getName(), 'au',
              'gu.'.Gpf_Db_Table_Users::AUTHID.'=au.'.Gpf_Db_Table_AuthUsers::ID);
          $select->where->add('gu.'.Gpf_Db_Table_Users::ROLEID, "=", Pap_Application::DEFAULT_ROLE_AFFILIATE);
          $select->where->add('pu.'.Pap_Db_Table_Users::DATEINSERTED, ">=", $date);
          $row = $select->getOneRow();
  
          return $row;
      }
  
      protected function initConstraints() {
          $this->addConstraint(new Gpf_DbEngine_Row_RegExpConstraint(self::REFID,
                                      "/^[a-zA-Z0-9_\-]*$/",
          $this->_('Referral ID can contain only [a-zA-Z0-9_-] characters. %s given')));
  
          $this->addConstraint(new Gpf_DbEngine_Row_ColumnsNotEqualConstraint(self::REFID, array(self::ID, self::REFID),
          $this->_("Referral ID is already used")));
  
          $this->addConstraint(new Gpf_DbEngine_Row_RelationConstraint(
          array(self::PARENTUSERID => self::ID),
          new Pap_Db_User_SpecialInit($this),
          false,
          $this->_('Selected parent affiliate does not exist')));
  
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_CachedBanners::USERID, new Pap_Db_CachedBanner());
          $this->addCascadeDeleteConstraint(self::REFID, Pap_Db_Table_CachedBanners::USERID, new Pap_Db_CachedBanner());
  
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_RawClicks::USERID, new Pap_Db_RawClick());
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_Clicks::USERID, new Pap_Db_Click());
  
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_Impressions::USERID, new Pap_Db_Impression());
  
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_CpmCommissions::USERID, new Pap_Db_CpmCommission());
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_Channels::USER_ID, new Pap_Db_Channel());
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_DirectLinkUrls::USER_ID, new Pap_Db_DirectLinkUrl());
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_LifetimeCommissions::USER_ID, new Pap_Db_LifetimeCommission());
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_Payouts::USER_ID, new Pap_Db_Payout());
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_Transactions::USER_ID, new Pap_Db_Transaction());
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_UserInCommissionGroup::USER_ID, new Pap_Db_UserInCommissionGroup());
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_UserPayoutOptions::USERID, new Pap_Db_UserPayoutOption());
          $this->addCascadeDeleteConstraint(self::ID, Pap_Db_Table_VisitorAffiliates::USERID, new Pap_Db_VisitorAffiliate());
  
          Gpf_Plugins_Engine::extensionPoint('PostAffiliate.UsersTable.constraints', $this);
      }
  }
  
  class Pap_Db_User_SpecialInit extends Pap_Db_User {
  
      private $table;
  
      function __construct(Gpf_DbEngine_Table $table){
          $this->table = $table;
          parent::__construct();
      }
  
      function init() {
          $this->setTable($this->table);
          Gpf_DbEngine_Row::init();
      }
  }
  

} //end Pap_Db_Table_Users

if (!class_exists('Gpf_DbEngine_DeleteConstraint', false)) {
  abstract class Gpf_DbEngine_DeleteConstraint extends Gpf_Object {
  
      /**
       * @var array of string
       */
      protected $selfColumns;
      /**
       * @var array of string
       */
      protected $foreignColumns;
      /**
       * @var Gpf_DbEngine_Row
       */
      protected $foreignDbRow;
      
      function __construct($selfColumns, $foreignColumns, Gpf_DbEngine_Row $foreignDbRow) {
          if (is_array($selfColumns)) {
              $this->selfColumns = $selfColumns;            
          } else {
              $this->selfColumns = array($selfColumns);
          }
          if (is_array($foreignColumns)) {
              $this->foreignColumns = $foreignColumns;            
          } else {
              $this->foreignColumns = array($foreignColumns);
          }
          if (count($this->selfColumns) != count($this->foreignColumns)) {
              throw new Gpf_Exception("selfColumns count and foreignColumnsCount must be equal when creating DeleteConstraint");
          }
          $this->foreignDbRow = $foreignDbRow;
      }
      
      abstract public function execute(Gpf_DbEngine_Row $dbRow);
      
  }
} //end Gpf_DbEngine_DeleteConstraint

if (!class_exists('Gpf_DbEngine_CascadeDeleteConstraint', false)) {
  class Gpf_DbEngine_CascadeDeleteConstraint extends Gpf_DbEngine_DeleteConstraint {
     
      public function execute(Gpf_DbEngine_Row $dbRow) {
          if (count($this->foreignDbRow->getTable()->getDeleteConstraints()) == 0) {
              $deleteBuilder = new Gpf_SqlBuilder_DeleteBuilder();
              $deleteBuilder->from->add($this->foreignDbRow->getTable()->name());
              for ($i=0; $i<count($this->selfColumns); $i++) {
                  $deleteBuilder->where->add($this->foreignColumns[$i], "=",
                                             $dbRow->get($this->selfColumns[$i]));
              }
              $deleteBuilder->execute(); 
              return;
          }
          for ($i=0; $i<count($this->selfColumns); $i++) {
              $this->foreignDbRow->set($this->foreignColumns[$i],
                                       $dbRow->get($this->selfColumns[$i]));
          }
          $rowCollection = $this->foreignDbRow->loadCollection($this->foreignColumns);
          foreach ($rowCollection as $row) {
              $row->delete();
          }
      }
      
  }

} //end Gpf_DbEngine_CascadeDeleteConstraint

if (!class_exists('Gpf_Db_Table_Users', false)) {
  class Gpf_Db_Table_Users extends Gpf_DbEngine_Table {
  
      /**
       * @deprecated use const ID instead
       */
      public static $ID = 'accountuserid';
      /**
       * @deprecated use const ACCOUNTID instead
       */
      public static $ACCOUNTID = 'accountid';
      
      const ID = 'accountuserid';
      const AUTHID = 'authid';
      const ACCOUNTID = 'accountid';
      const ROLEID = 'roleid';
      const STATUS = 'rstatus';
      
      private static $instance;
          
      public static function getInstance() {
          if(self::$instance === null) {
              self::$instance = new self;
          }
          return self::$instance;
      }
          
      protected function initName() {
          $this->setName('g_users');
      }
      
      public static function getName() {
          return self::getInstance()->name();
      }
      
      protected function initColumns() {
          $this->createPrimaryColumn(self::ID, 'char', 8, true);
          $this->createColumn(self::AUTHID, 'char', 100);
          $this->createColumn(self::ACCOUNTID, 'char', 20);
          $this->createColumn(self::ROLEID, 'char', 20);
          $this->createColumn(self::STATUS, 'char', 1);
      }
      
      protected function initConstraints() {
         $this->addCascadeDeleteConstraint(self::ID, Gpf_Db_Table_UserAttributes::ACCOUNT_USER_ID, new Gpf_Db_UserAttribute());
         $this->addDeleteConstraint(new Gpf_Db_Table_Users_AuthUsersDeleteConstraint(self::AUTHID, Gpf_Db_Table_AuthUsers::ID, new Gpf_Db_AuthUser()));
         
         $this->addConstraint(new Gpf_Db_Table_Constraints_UsersUniqueConstraint());
      }
  }
  
  class Gpf_Db_Table_Users_AuthUsersDeleteConstraint extends Gpf_DbEngine_CascadeDeleteConstraint {
     
      public function execute(Gpf_DbEngine_Row $dbRow) {
          if (!$this->isLastUserWithAuthID($dbRow->get(Gpf_Db_Table_Users::AUTHID))) {
              return;
          }
          parent::execute($dbRow);
      } 
         
      /**
       * @param $authId
       * @return boolean
       */
      private function isLastUserWithAuthID($authId) {
          $guser = new Gpf_Db_User();
          $guser->setAuthId($authId);
          try {
              $guser->loadFromData(array(Gpf_Db_Table_Users::AUTHID));
          } catch (Gpf_Exception $e) {
              return false;
          } 
          return true;
      }
  }

} //end Gpf_Db_Table_Users

if (!class_exists('Gpf_SqlBuilder_JoinTable', false)) {
  class Gpf_SqlBuilder_JoinTable extends Gpf_Object implements Gpf_SqlBuilder_FromClauseTable {
      private $type;
      private $name;
      private $alias;
      private $onCondition;
  
      function __construct($type, $name, $alias, $onCondition) {
          $this->type = $type;
          $this->name = $name;
          $this->alias = $alias;
          $this->onCondition = $onCondition;
      }
  
      public function getAlias() {
          return $this->alias;
      }
  
      public function getName() {
          return $this->name;
      }
  
      public function toString() {
          $out = " ".$this->type." JOIN ".$this->name;
          if(!empty($this->alias)) {
              $out .= ' ' . $this->alias;
          }
          $out .= " ON ".$this->onCondition;
          return $out;
      }
  
      public function isJoin() {
          return true;
      }
      
      public function getRequiredPreffixes() {
          $requiredPreffixes = array();
          $matches = array();
          if (preg_match_all("/([a-zA-Z]+)\./", $this->onCondition, $matches) > 0) {
              foreach ($matches[1] as $preffix) {
                  $requiredPreffixes[$preffix] = $preffix;
              }
          }
          return $requiredPreffixes;
      }
  }
  

} //end Gpf_SqlBuilder_JoinTable

if (!class_exists('Gpf_Db_User', false)) {
  class Gpf_Db_User extends Gpf_DbEngine_Row {
      const APPROVED = 'A';
      const PENDING  = 'P';
      const DECLINED = 'D';
  
      function __construct(){
          parent::__construct();
      }
  
      function init() {
          $this->setTable(Gpf_Db_Table_Users::getInstance());
          parent::init();
      }
      
      public function getId() {
          return $this->get(Gpf_Db_Table_Users::ID);
      }
      
      public function getAuthId() {
          return $this->get(Gpf_Db_Table_Users::AUTHID);
      }
      
      public function setAccountId($accountId) {
          $this->set(Gpf_Db_Table_Users::ACCOUNTID, $accountId);
      }
      
      public function getAccountId() {
          return $this->get(Gpf_Db_Table_Accounts::ID);
      }
      
      public function setId($id) {
          $this->set(Gpf_Db_Table_Users::ID, $id);
      }
      
      public function setRoleId($roleId) {
          $this->set(Gpf_Db_Table_Users::ROLEID, $roleId);
      }
      
      public function getRoleId() {
          return $this->get(Gpf_Db_Table_Users::ROLEID);
      }
      
      public function setStatus($newStatus) {
          $this->set(Gpf_Db_Table_Users::STATUS, $newStatus);
      }
      
      public function getStatus() {
          return $this->get(Gpf_Db_Table_Users::STATUS);
      }
      
      public function setAuthId($authId) {
          $this->set(Gpf_Db_Table_Users::AUTHID, $authId);
      }
      
      public function loadByRoleType($roleType, $application) {
          $query = new Gpf_SqlBuilder_SelectBuilder();
          $query->select->addAll(Gpf_Db_Table_Users::getInstance(), 'u');
          $query->from->add(Gpf_Db_Table_Users::getName(), "u");
          $query->from->addInnerJoin(Gpf_Db_Table_Roles::getName(), "r", "r.roleid = u.roleid");
          $query->from->addInnerJoin(Gpf_Db_Table_Accounts::getName(), "a", "u.accountid = a.accountid");
          $query->where->add('u.authid', '=', $this->getAuthId());
          $query->where->add('u.accountid', '=', $this->getAccountId());
          $query->where->add('a.application', '=', $application);
          $query->where->add('r.roletype', '=', $roleType);
          $record = $query->getOneRow();
          $this->fillFromRecord($record);
      }
      
      public function isStatusValid() {
          $status = $this->getStatus();
          return in_array($status, array(self::APPROVED, self::PENDING, self::DECLINED));
      }
      
      protected function beforeSaveCheck() {
      	parent::beforeSaveCheck();
          if(!$this->isStatusValid()) {
              throw new Gpf_Exception('User status is invalid.');
          }
      }
  }
  

} //end Gpf_Db_User

if (!class_exists('Gpf_ResourceNotFoundException', false)) {
  class Gpf_ResourceNotFoundException extends Gpf_Exception {
      public function __construct($resouceName, $panelName) { 
          parent::__construct("Resource '$resouceName' not found in panel '$panelName'");
      }
  }

} //end Gpf_ResourceNotFoundException

if (!interface_exists('Gpf_Rpc_DataEncoder', false)) {
  interface Gpf_Rpc_DataEncoder {
      function encodeResponse(Gpf_Rpc_Serializable $response);
  }
  
  

} //end Gpf_Rpc_DataEncoder

if (!interface_exists('Gpf_Rpc_DataDecoder', false)) {
  interface Gpf_Rpc_DataDecoder {
      /**
       * @param string $str
       * @return StdClass
       */
      function decode($str);
  }
  
  

} //end Gpf_Rpc_DataDecoder

if (!class_exists('Gpf_Rpc_Json', false)) {
  class Gpf_Rpc_Json implements Gpf_Rpc_DataEncoder, Gpf_Rpc_DataDecoder {
      /**
       * Marker constant for Services_JSON::decode(), used to flag stack state
       */
      const SERVICES_JSON_SLICE = 1;
  
      /**
       * Marker constant for Services_JSON::decode(), used to flag stack state
       */
      const SERVICES_JSON_IN_STR = 2;
  
      /**
       * Marker constant for Services_JSON::decode(), used to flag stack state
       */
      const SERVICES_JSON_IN_ARR = 3;
  
      /**
       * Marker constant for Services_JSON::decode(), used to flag stack state
       */
      const SERVICES_JSON_IN_OBJ = 4;
  
      /**
       * Marker constant for Services_JSON::decode(), used to flag stack state
       */
      const SERVICES_JSON_IN_CMT = 5;
  
      /**
       * Behavior switch for Services_JSON::decode()
       */
      const SERVICES_JSON_LOOSE_TYPE = 16;
  
      /**
       * Behavior switch for Services_JSON::decode()
       */
      const SERVICES_JSON_SUPPRESS_ERRORS = 32;
  
      /**
       * constructs a new JSON instance
       *
       * @param    int     $use    object behavior flags; combine with boolean-OR
       *
       *                           possible values:
       *                           - SERVICES_JSON_LOOSE_TYPE:  loose typing.
       *                                   "{...}" syntax creates associative arrays
       *                                   instead of objects in decode().
       *                           - SERVICES_JSON_SUPPRESS_ERRORS:  error suppression.
       *                                   Values which can't be encoded (e.g. resources)
       *                                   appear as NULL instead of throwing errors.
       *                                   By default, a deeply-nested resource will
       *                                   bubble up with an error, so all return values
       *                                   from encode() should be checked with isError()
       */
      function __construct($use = 0)
      {
          $this->use = $use;
      }
  
      /**
       * convert a string from one UTF-16 char to one UTF-8 char
       *
       * Normally should be handled by mb_convert_encoding, but
       * provides a slower PHP-only method for installations
       * that lack the multibye string extension.
       *
       * @param    string  $utf16  UTF-16 character
       * @return   string  UTF-8 character
       * @access   private
       */
      function utf162utf8($utf16)
      {
          // oh please oh please oh please oh please oh please
          if(Gpf_Php::isFunctionEnabled('mb_convert_encoding')) {
              return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
          }
  
          $bytes = (ord($utf16{0}) << 8) | ord($utf16{1});
  
          switch(true) {
              case ((0x7F & $bytes) == $bytes):
                  // this case should never be reached, because we are in ASCII range
                  // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                  return chr(0x7F & $bytes);
  
              case (0x07FF & $bytes) == $bytes:
                  // return a 2-byte UTF-8 character
                  // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                  return chr(0xC0 | (($bytes >> 6) & 0x1F))
                  . chr(0x80 | ($bytes & 0x3F));
  
              case (0xFFFF & $bytes) == $bytes:
                  // return a 3-byte UTF-8 character
                  // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                  return chr(0xE0 | (($bytes >> 12) & 0x0F))
                  . chr(0x80 | (($bytes >> 6) & 0x3F))
                  . chr(0x80 | ($bytes & 0x3F));
          }
  
          // ignoring UTF-32 for now, sorry
          return '';
      }
  
      /**
       * convert a string from one UTF-8 char to one UTF-16 char
       *
       * Normally should be handled by mb_convert_encoding, but
       * provides a slower PHP-only method for installations
       * that lack the multibye string extension.
       *
       * @param    string  $utf8   UTF-8 character
       * @return   string  UTF-16 character
       * @access   private
       */
      function utf82utf16($utf8)
      {
          // oh please oh please oh please oh please oh please
          if(Gpf_Php::isFunctionEnabled('mb_convert_encoding')) {
              return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
          }
  
          switch(strlen($utf8)) {
              case 1:
                  // this case should never be reached, because we are in ASCII range
                  // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                  return $utf8;
  
              case 2:
                  // return a UTF-16 character from a 2-byte UTF-8 char
                  // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                  return chr(0x07 & (ord($utf8{0}) >> 2))
                  . chr((0xC0 & (ord($utf8{0}) << 6))
                  | (0x3F & ord($utf8{1})));
  
              case 3:
                  // return a UTF-16 character from a 3-byte UTF-8 char
                  // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                  return chr((0xF0 & (ord($utf8{0}) << 4))
                  | (0x0F & (ord($utf8{1}) >> 2)))
                  . chr((0xC0 & (ord($utf8{1}) << 6))
                  | (0x7F & ord($utf8{2})));
          }
  
          // ignoring UTF-32 for now, sorry
          return '';
      }
  
      public function encodeResponse(Gpf_Rpc_Serializable $response) {
          return $this->encode($response->toObject());
      }
  
      /**
       * encodes an arbitrary variable into JSON format
       *
       * @param    mixed   $var    any number, boolean, string, array, or object to be encoded.
       *                           see argument 1 to Services_JSON() above for array-parsing behavior.
       *                           if var is a strng, note that encode() always expects it
       *                           to be in ASCII or UTF-8 format!
       *
       * @return   mixed   JSON string representation of input var or an error if a problem occurs
       * @access   public
       */
      public function encode($var) {
          if ($this->isJsonEncodeEnabled()) {
              return @json_encode($var);
          }
          switch (gettype($var)) {
              case 'boolean':
                  return $var ? 'true' : 'false';
  
              case 'NULL':
                  return 'null';
  
              case 'integer':
                  return (int) $var;
  
              case 'double':
              case 'float':
                  return (float) $var;
  
              case 'string':
                  // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
                  $ascii = '';
                  $strlen_var = strlen($var);
  
                  /*
                   * Iterate over every character in the string,
                   * escaping with a slash or encoding to UTF-8 where necessary
                   */
                  for ($c = 0; $c < $strlen_var; ++$c) {
  
                      $ord_var_c = ord($var{$c});
  
                      switch (true) {
                          case $ord_var_c == 0x08:
                              $ascii .= '\b';
                              break;
                          case $ord_var_c == 0x09:
                              $ascii .= '\t';
                              break;
                          case $ord_var_c == 0x0A:
                              $ascii .= '\n';
                              break;
                          case $ord_var_c == 0x0C:
                              $ascii .= '\f';
                              break;
                          case $ord_var_c == 0x0D:
                              $ascii .= '\r';
                              break;
  
                          case $ord_var_c == 0x22:
                          case $ord_var_c == 0x2F:
                          case $ord_var_c == 0x5C:
                              // double quote, slash, slosh
                              $ascii .= '\\'.$var{$c};
                              break;
  
                          case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                              // characters U-00000000 - U-0000007F (same as ASCII)
                              $ascii .= $var{$c};
                              break;
  
                          case (($ord_var_c & 0xE0) == 0xC0):
                              // characters U-00000080 - U-000007FF, mask 1 1 0 X X X X X
                              // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                              $char = pack('C*', $ord_var_c, ord($var{$c + 1}));
                              $c += 1;
                              $utf16 = $this->utf82utf16($char);
                              $ascii .= sprintf('\u%04s', bin2hex($utf16));
                              break;
  
                          case (($ord_var_c & 0xF0) == 0xE0):
                              // characters U-00000800 - U-0000FFFF, mask 1 1 1 0 X X X X
                              // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                              $char = pack('C*', $ord_var_c,
                              ord($var{$c + 1}),
                              ord($var{$c + 2}));
                              $c += 2;
                              $utf16 = $this->utf82utf16($char);
                              $ascii .= sprintf('\u%04s', bin2hex($utf16));
                              break;
  
                          case (($ord_var_c & 0xF8) == 0xF0):
                              // characters U-00010000 - U-001FFFFF, mask 1 1 1 1 0 X X X
                              // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                              $char = pack('C*', $ord_var_c,
                              ord($var{$c + 1}),
                              ord($var{$c + 2}),
                              ord($var{$c + 3}));
                              $c += 3;
                              $utf16 = $this->utf82utf16($char);
                              $ascii .= sprintf('\u%04s', bin2hex($utf16));
                              break;
  
                          case (($ord_var_c & 0xFC) == 0xF8):
                              // characters U-00200000 - U-03FFFFFF, mask 111110XX
                              // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                              $char = pack('C*', $ord_var_c,
                              ord($var{$c + 1}),
                              ord($var{$c + 2}),
                              ord($var{$c + 3}),
                              ord($var{$c + 4}));
                              $c += 4;
                              $utf16 = $this->utf82utf16($char);
                              $ascii .= sprintf('\u%04s', bin2hex($utf16));
                              break;
  
                          case (($ord_var_c & 0xFE) == 0xFC):
                              // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                              // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                              $char = pack('C*', $ord_var_c,
                              ord($var{$c + 1}),
                              ord($var{$c + 2}),
                              ord($var{$c + 3}),
                              ord($var{$c + 4}),
                              ord($var{$c + 5}));
                              $c += 5;
                              $utf16 = $this->utf82utf16($char);
                              $ascii .= sprintf('\u%04s', bin2hex($utf16));
                              break;
                      }
                  }
  
                  return '"'.$ascii.'"';
  
                          case 'array':
                              /*
                               * As per JSON spec if any array key is not an integer
                               * we must treat the the whole array as an object. We
                               * also try to catch a sparsely populated associative
                               * array with numeric keys here because some JS engines
                               * will create an array with empty indexes up to
                               * max_index which can cause memory issues and because
                               * the keys, which may be relevant, will be remapped
                               * otherwise.
                               *
                               * As per the ECMA and JSON specification an object may
                               * have any string as a property. Unfortunately due to
                               * a hole in the ECMA specification if the key is a
                               * ECMA reserved word or starts with a digit the
                               * parameter is only accessible using ECMAScript's
                               * bracket notation.
                               */
  
                              // treat as a JSON object
                              if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
                                  $properties = array_map(array($this, 'name_value'), array_keys($var), array_values($var));
  
                                  foreach($properties as $property) {
                                      if(Gpf_Rpc_Json::isError($property)) {
                                          return $property;
                                      }
                                  }
  
                                  return '{' . join(',', $properties) . '}';
                              }
  
                              // treat it like a regular array
                              $elements = array_map(array($this, 'encode'), $var);
  
                              foreach($elements as $element) {
                                  if(Gpf_Rpc_Json::isError($element)) {
                                      return $element;
                                  }
                              }
  
                              return '[' . join(',', $elements) . ']';
  
                          case 'object':
                              $vars = get_object_vars($var);
  
                              $properties = array_map(array($this, 'name_value'),
                              array_keys($vars),
                              array_values($vars));
  
                              foreach($properties as $property) {
                                  if(Gpf_Rpc_Json::isError($property)) {
                                      return $property;
                                  }
                              }
  
                              return '{' . join(',', $properties) . '}';
  
                          default:
                              if ($this->use & self::SERVICES_JSON_SUPPRESS_ERRORS) {
                                  return 'null';
                              }
                              return new Gpf_Rpc_Json_Error(gettype($var)." can not be encoded as JSON string");
          }
      }
  
      /**
       * array-walking function for use in generating JSON-formatted name-value pairs
       *
       * @param    string  $name   name of key to use
       * @param    mixed   $value  reference to an array element to be encoded
       *
       * @return   string  JSON-formatted name-value pair, like '"name":value'
       * @access   private
       */
      function name_value($name, $value)
      {
          $encoded_value = $this->encode($value);
  
          if(Gpf_Rpc_Json::isError($encoded_value)) {
              return $encoded_value;
          }
  
          return $this->encode(strval($name)) . ':' . $encoded_value;
      }
  
      /**
       * reduce a string by removing leading and trailing comments and whitespace
       *
       * @param    $str    string      string value to strip of comments and whitespace
       *
       * @return   string  string value stripped of comments and whitespace
       * @access   private
       */
      function reduce_string($str)
      {
          $str = preg_replace(array(
  
          // eliminate single line comments in '// ...' form
                  '#^\s*//(.+)$#m',
  
          // eliminate multi-line comments in '/* ... */' form, at start of string
                  '#^\s*/\*(.+)\*/#Us',
  
          // eliminate multi-line comments in '/* ... */' form, at end of string
                  '#/\*(.+)\*/\s*$#Us'
  
                  ), '', $str);
  
                  // eliminate extraneous space
                  return trim($str);
      }
  
      /**
       * decodes a JSON string into appropriate variable
       *
       * @param    string  $str    JSON-formatted string
       *
       * @return   mixed   number, boolean, string, array, or object
       *                   corresponding to given JSON input string.
       *                   See argument 1 to Services_JSON() above for object-output behavior.
       *                   Note that decode() always returns strings
       *                   in ASCII or UTF-8 format!
       * @access   public
       */
      function decode($str)
      {
          if ($this->isJsonDecodeEnabled()) {
              return json_decode($str);
          }
  
          $str = $this->reduce_string($str);
  
          switch (strtolower($str)) {
              case 'true':
                  return true;
  
              case 'false':
                  return false;
  
              case 'null':
                  return null;
  
              default:
                  $m = array();
  
                  if (is_numeric($str)) {
                      // Lookie-loo, it's a number
  
                      // This would work on its own, but I'm trying to be
                      // good about returning integers where appropriate:
                      // return (float)$str;
  
                      // Return float or int, as appropriate
                      return ((float)$str == (integer)$str)
                      ? (integer)$str
                      : (float)$str;
  
                  } elseif (preg_match('/^("|\').*(\1)$/s', $str, $m) && $m[1] == $m[2]) {
                      // STRINGS RETURNED IN UTF-8 FORMAT
                      $delim = substr($str, 0, 1);
                      $chrs = substr($str, 1, -1);
                      $utf8 = '';
                      $strlen_chrs = strlen($chrs);
  
                      for ($c = 0; $c < $strlen_chrs; ++$c) {
  
                          $substr_chrs_c_2 = substr($chrs, $c, 2);
                          $ord_chrs_c = ord($chrs{$c});
  
                          switch (true) {
                              case $substr_chrs_c_2 == '\b':
                                  $utf8 .= chr(0x08);
                                  ++$c;
                                  break;
                              case $substr_chrs_c_2 == '\t':
                                  $utf8 .= chr(0x09);
                                  ++$c;
                                  break;
                              case $substr_chrs_c_2 == '\n':
                                  $utf8 .= chr(0x0A);
                                  ++$c;
                                  break;
                              case $substr_chrs_c_2 == '\f':
                                  $utf8 .= chr(0x0C);
                                  ++$c;
                                  break;
                              case $substr_chrs_c_2 == '\r':
                                  $utf8 .= chr(0x0D);
                                  ++$c;
                                  break;
  
                              case $substr_chrs_c_2 == '\\"':
                              case $substr_chrs_c_2 == '\\\'':
                              case $substr_chrs_c_2 == '\\\\':
                              case $substr_chrs_c_2 == '\\/':
                                  if (($delim == '"' && $substr_chrs_c_2 != '\\\'') ||
                                  ($delim == "'" && $substr_chrs_c_2 != '\\"')) {
                                      $utf8 .= $chrs{++$c};
                                  }
                                  break;
  
                              case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)):
                                  // single, escaped unicode character
                                  $utf16 = chr(hexdec(substr($chrs, ($c + 2), 2)))
                                  . chr(hexdec(substr($chrs, ($c + 4), 2)));
                                  $utf8 .= $this->utf162utf8($utf16);
                                  $c += 5;
                                  break;
  
                              case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
                                  $utf8 .= $chrs{$c};
                                  break;
  
                              case ($ord_chrs_c & 0xE0) == 0xC0:
                                  // characters U-00000080 - U-000007FF, mask 1 1 0 X X X X X
                                  //see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                  $utf8 .= substr($chrs, $c, 2);
                                  ++$c;
                                  break;
  
                              case ($ord_chrs_c & 0xF0) == 0xE0:
                                  // characters U-00000800 - U-0000FFFF, mask 1 1 1 0 X X X X
                                  // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                  $utf8 .= substr($chrs, $c, 3);
                                  $c += 2;
                                  break;
  
                              case ($ord_chrs_c & 0xF8) == 0xF0:
                                  // characters U-00010000 - U-001FFFFF, mask 1 1 1 1 0 X X X
                                  // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                  $utf8 .= substr($chrs, $c, 4);
                                  $c += 3;
                                  break;
  
                              case ($ord_chrs_c & 0xFC) == 0xF8:
                                  // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                  // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                  $utf8 .= substr($chrs, $c, 5);
                                  $c += 4;
                                  break;
  
                              case ($ord_chrs_c & 0xFE) == 0xFC:
                                  // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                  // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                  $utf8 .= substr($chrs, $c, 6);
                                  $c += 5;
                                  break;
  
                          }
  
                      }
  
                      return $utf8;
  
                  } elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) {
                      // array, or object notation
  
                      if ($str{0} == '[') {
                          $stk = array(self::SERVICES_JSON_IN_ARR);
                          $arr = array();
                      } else {
                          if ($this->use & self::SERVICES_JSON_LOOSE_TYPE) {
                              $stk = array(self::SERVICES_JSON_IN_OBJ);
                              $obj = array();
                          } else {
                              $stk = array(self::SERVICES_JSON_IN_OBJ);
                              $obj = new stdClass();
                          }
                      }
  
                      array_push($stk, array('what'  => self::SERVICES_JSON_SLICE,
                                             'where' => 0,
                                             'delim' => false));
  
                      $chrs = substr($str, 1, -1);
                      $chrs = $this->reduce_string($chrs);
  
                      if ($chrs == '') {
                          if (reset($stk) == self::SERVICES_JSON_IN_ARR) {
                              return $arr;
  
                          } else {
                              return $obj;
  
                          }
                      }
  
                      //print("\nparsing {$chrs}\n");
  
                      $strlen_chrs = strlen($chrs);
  
                      for ($c = 0; $c <= $strlen_chrs; ++$c) {
  
                          $top = end($stk);
                          $substr_chrs_c_2 = substr($chrs, $c, 2);
  
                          if (($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == self::SERVICES_JSON_SLICE))) {
                              // found a comma that is not inside a string, array, etc.,
                              // OR we've reached the end of the character list
                              $slice = substr($chrs, $top['where'], ($c - $top['where']));
                              array_push($stk, array('what' => self::SERVICES_JSON_SLICE, 'where' => ($c + 1), 'delim' => false));
                              //print("Found split at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
  
                              if (reset($stk) == self::SERVICES_JSON_IN_ARR) {
                                  // we are in an array, so just push an element onto the stack
                                  array_push($arr, $this->decode($slice));
  
                              } elseif (reset($stk) == self::SERVICES_JSON_IN_OBJ) {
                                  // we are in an object, so figure
                                  // out the property name and set an
                                  // element in an associative array,
                                  // for now
                                  $parts = array();
  
                                  if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                      // "name":value pair
                                      $key = $this->decode($parts[1]);
                                      $val = $this->decode($parts[2]);
  
                                      if ($this->use & self::SERVICES_JSON_LOOSE_TYPE) {
                                          $obj[$key] = $val;
                                      } else {
                                          $obj->$key = $val;
                                      }
                                  } elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                      // name:value pair, where name is unquoted
                                      $key = $parts[1];
                                      $val = $this->decode($parts[2]);
  
                                      if ($this->use & self::SERVICES_JSON_LOOSE_TYPE) {
                                          $obj[$key] = $val;
                                      } else {
                                          $obj->$key = $val;
                                      }
                                  }
  
                              }
  
                          } elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != self::SERVICES_JSON_IN_STR)) {
                              // found a quote, and we are not inside a string
                              array_push($stk, array('what' => self::SERVICES_JSON_IN_STR, 'where' => $c, 'delim' => $chrs{$c}));
                              //print("Found start of string at {$c}\n");
  
                          } elseif (($chrs{$c} == $top['delim']) &&
                          ($top['what'] == self::SERVICES_JSON_IN_STR) &&
                          (($chrs{$c - 1} != '\\') ||
                          ($chrs{$c - 1} == '\\' && $chrs{$c - 2} == '\\'))) {
                              // found a quote, we're in a string, and it's not escaped
                              array_pop($stk);
                              //print("Found end of string at {$c}: ".substr($chrs, $top['where'], (1 + 1 + $c - $top['where']))."\n");
  
                          } elseif (($chrs{$c} == '[') &&
                          in_array($top['what'], array(self::SERVICES_JSON_SLICE, self::SERVICES_JSON_IN_ARR, self::SERVICES_JSON_IN_OBJ))) {
                              // found a left-bracket, and we are in an array, object, or slice
                              array_push($stk, array('what' => self::SERVICES_JSON_IN_ARR, 'where' => $c, 'delim' => false));
                              //print("Found start of array at {$c}\n");
  
                          } elseif (($chrs{$c} == ']') && ($top['what'] == self::SERVICES_JSON_IN_ARR)) {
                              // found a right-bracket, and we're in an array
                              array_pop($stk);
                              //print("Found end of array at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
  
                          } elseif (($chrs{$c} == '{') &&
                          in_array($top['what'], array(self::SERVICES_JSON_SLICE, self::SERVICES_JSON_IN_ARR, self::SERVICES_JSON_IN_OBJ))) {
                              // found a left-brace, and we are in an array, object, or slice
                              array_push($stk, array('what' => self::SERVICES_JSON_IN_OBJ, 'where' => $c, 'delim' => false));
                              //print("Found start of object at {$c}\n");
  
                          } elseif (($chrs{$c} == '}') && ($top['what'] == self::SERVICES_JSON_IN_OBJ)) {
                              // found a right-brace, and we're in an object
                              array_pop($stk);
                              //print("Found end of object at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
  
                          } elseif (($substr_chrs_c_2 == '/*') &&
                          in_array($top['what'], array(self::SERVICES_JSON_SLICE, self::SERVICES_JSON_IN_ARR, self::SERVICES_JSON_IN_OBJ))) {
                              // found a comment start, and we are in an array, object, or slice
                              array_push($stk, array('what' => self::SERVICES_JSON_IN_CMT, 'where' => $c, 'delim' => false));
                              $c++;
                              //print("Found start of comment at {$c}\n");
  
                          } elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == self::SERVICES_JSON_IN_CMT)) {
                              // found a comment end, and we're in one now
                              array_pop($stk);
                              $c++;
  
                              for ($i = $top['where']; $i <= $c; ++$i)
                              $chrs = substr_replace($chrs, ' ', $i, 1);
  
                              //print("Found end of comment at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
  
                          }
  
                      }
  
                      if (reset($stk) == self::SERVICES_JSON_IN_ARR) {
                          return $arr;
  
                      } elseif (reset($stk) == self::SERVICES_JSON_IN_OBJ) {
                          return $obj;
  
                      }
  
                  }
          }
      }
      
      protected function isJsonEncodeEnabled() {
          return Gpf_Php::isFunctionEnabled('json_encode');
      }
      
      protected function isJsonDecodeEnabled() {
          return Gpf_Php::isFunctionEnabled('json_decode');
      }
      
  
      /**
       * @todo Ultimately, this should just call PEAR::isError()
       */
      function isError($data, $code = null)
      {
          if (is_object($data) &&
              (get_class($data) == 'Gpf_Rpc_Json_Error' || is_subclass_of($data, 'Gpf_Rpc_Json_Error'))) {
                  return true;
          }
          return false;
      }
  }
  
  class Gpf_Rpc_Json_Error {
      private $message;
      
      public function __construct($message) {
          $this->message = $message;
      }
  }
  

} //end Gpf_Rpc_Json

if (!class_exists('Gpf_Plugins_Context', false)) {
  class Gpf_Plugins_Context {
  	
  	/**
  	 * array of context parameters
  	 *
  	 * @var unknown_type
  	 */
  	private $_parameters = array();
      
      /**
       * @var Gpf_Log_Logger
       */
      private $_logger = null;
  
      protected function __construct() {
      }
  
      /**
       * @return Gpf_Log_Logger
       */
      public function getLogger() {
          return $this->_logger;
      }
      
      public function getLoggerGroupId() {
          if ($this->_logger != null) {
              return $this->_logger->getGroup();
          }
          return null;
      }
  
      public function setLogger($logger) {
          $this->_logger = $logger;
      }
  
      /**
       * returns array with all keys (parameter names) that are currently
       * stored in the context
       *
       * @return unknown
       */
      public function getAllKeys() {
          $keys = array();
          foreach($this->_parameters as $key) {
              $keys[] = $key;
          }
  
          return $keys;
      }
  
      public function set($key, $value) {
          $this->_parameters[$key] = $value;
      }
  
      public function get($key) {
          if(!isset($this->_parameters[$key])) {
              return null;
          }
          return $this->_parameters[$key];
      }
  
      public function log($logLevel, $message, $logGroup = null) {
          if($this->_logger !== null) {
              $this->_logger->log($message, $logLevel, $logGroup);
          }
      }
  
      public function critical($msg) {
          $this->log(Gpf_Log::CRITICAL, $msg);
      }
  
      public function debug($msg) {
          $this->log(Gpf_Log::DEBUG, $msg);
      }
  
      public function info($msg) {
          $this->log(Gpf_Log::INFO, $msg);
      }
  
      public function error($msg) {
          $this->log(Gpf_Log::ERROR, $msg);
      }
  
      public function warning($msg) {
          $this->log(Gpf_Log::WARNING, $msg);
      }
  }

} //end Gpf_Plugins_Context

if (!class_exists('Gpf_Contexts_Module', false)) {
  class Gpf_Contexts_Module extends Gpf_Plugins_Context {
  
      /**
       * @var array of resource urls
       */
      private $jsResources = array();
  
      /**
       * @var array of js scripts
       */
      private $jsScripts = array();
  
      /**
       * @var Gpf_Contexts_Module
       */
      private static $instance = null;
  
      protected function __construct() {
      }
  
      /**
       * @return Gpf_Contexts_Module
       */
      public function getContextInstance() {
          if (self::$instance == null) {
              self::$instance = new Gpf_Contexts_Module();
          }
          return self::$instance;
      }
  
      /**
       * Add javascript resource url
       *
       * @param string $resource
       */
      public function addJsResource($resource, $id = null) {
          $this->jsResources[$resource] = array('resource' => $resource, 'id' => $id);
      }
  
      /**
       * Get javascript resources array
       *
       * @return array
       */
      public function getJsResources() {
          return $this->jsResources;
      }
  
      /**
       * Get javascripts array
       *
       * @return array
       */
      public function getJsScripts() {
          return $this->jsScripts;
      }
  
      /**
       * Add javascript code
       *
       * @param string $sourceCode javascript source code, which should be added to main page header
       */
      public function addJsScript($sourceCode) {
          $this->jsScripts[] = $sourceCode;
      }
  
  }

} //end Gpf_Contexts_Module
/*
VERSION
fc3e34c90d565a6c29aefb708e527c95
*/
?>
