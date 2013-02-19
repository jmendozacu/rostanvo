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
class PagosOnline_Definition extends Gpf_Plugins_Definition  {
    public function __construct() {
        $this->codeName = 'PagosOnline';
        $this->name = $this->_('PagosOnline IPN handling');
        $this->description = $this->_('This plugin handles PagosOnline IPN notifications (integration of Post Affiliate with PagosOnline)');
        $this->version = '1.0.1';
        $this->configurationClassName = 'PagosOnline_Config';

        $this->addRequirement('PapCore', '4.0.4.6');

        $this->addImplementation('Core.defineSettings', 'PagosOnline_Main', 'initSettings');
    }
}
?>
