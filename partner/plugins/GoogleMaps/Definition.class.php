<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Viktor Zeman
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 *
 */

/**
 * @package GwtPhpFramework
 */
class GoogleMaps_Definition extends Gpf_Plugins_Definition {

    public function __construct() {
        $this->codeName =  'GoogleMaps';
        $this->name = $this->_('Google Maps');
        $this->description = $this->_('Plugin requires GeoIp Core module. Without it it will not work. If the module is activated together with the GeoIp module, when you click on any IP address, you get the IP details in a popup together with map.');
        $this->version = '1.0.0';
        $this->help = $this->_('Before you will start using Google Maps plugin, you will need to add Google Maps JavaScript API v2 key to your account settings.<hr>
Here you can find how to Obtaining an API Key for Google Maps JavaScript API v2 (you can generate it for your domain, domain has to be same as is domain name under which is loaded your installation): %s.', '<b><a href="http://code.google.com/apis/maps/signup.html" target="_blank">'.$this->_('Generate Google Maps API v2 key').'</a></b>');
        $this->configurationClassName = 'GoogleMaps_Config';

        $this->addRequirement('GeoIp', '1.0.0');

        $this->addImplementation('Core.initJsResources', 'GoogleMaps_Main', 'initJsResources');
        $this->addImplementation('Core.defineSettings', 'GoogleMaps_Main', 'initSettings');
        $this->addImplementation('Core.loadSetting', 'GoogleMaps_Main', 'loadSetting');
        $this->addImplementation('Core.initPrivileges', 'GoogleMaps_Main', 'initPrivileges');
    }
}

?>
