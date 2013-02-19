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

/**
 * @package PostAffiliatePro plugins
 */
class Recurly_Definition extends Gpf_Plugins_Definition  {
    public function __construct() {
        $this->codeName = 'Recurly';
        $this->name = $this->_('Recurly postback handling');
        $this->description = $this->_('This plugin handles Recurly push notifications (integration of Post Affiliate with Recurly). The plugin only works with the custom code defined in the integration methods list.');
        $this->version = '1.1.1';
        $this->configurationClassName = 'Recurly_Config';

        $this->addRequirement('PapCore', '4.2.3.2');

        $this->addImplementation('Core.defineSettings', 'Recurly_Main', 'initSettings');
    }
}
?>
