<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Martin Pullmann
 *   @package PostAffiliatePro
 *   @since Version 1.0.0
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 *
 */

class ISecure_Definition extends Gpf_Plugins_Definition  {
    public function __construct() {
        $this->codeName = 'ISecure';
        $this->name = $this->_('Internet Secure notification handling');
        $this->description = $this->_('This plugin handles Internet Secure notifications (integration of Post Affiliate with Internet Secure)');
        $this->version = '1.0.2';
        $this->configurationClassName = 'ISecure_Config';

        $this->addRequirement('PapCore', '4.0.4.6');

        $this->addImplementation('Core.defineSettings', 'ISecure_Main', 'initSettings');
    }
}
?>
