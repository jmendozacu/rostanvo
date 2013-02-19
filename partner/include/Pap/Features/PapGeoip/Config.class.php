<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Matej Kendera
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
class Pap_Features_PapGeoip_Config extends Gpf_Plugins_Config {

    protected function initFields() {
        $this->addCheckBox($this->_('Disable GeoIp for impressions'), Pap_Settings::GEOIP_IMPRESSIONS_DISABLED, $this->_('If you have huge amount of incoming impressions, GeoIp for impressions can cause slowdown of impression processor and delay during loading statistics.'));
    }

    /**
     * @anonym
     * @service custom_separator write
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function save(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);

        Gpf_Settings::set(Pap_Settings::GEOIP_IMPRESSIONS_DISABLED,
        $form->getFieldValue(Pap_Settings::GEOIP_IMPRESSIONS_DISABLED));

        $form->setInfoMessage($this->_('GeoIp settings saved'));
        return $form;
    }

    /**
     * @anonym
     * @service custom_separator read
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function load(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        $form->addField(Pap_Settings::GEOIP_IMPRESSIONS_DISABLED, Gpf_Settings::get(Pap_Settings::GEOIP_IMPRESSIONS_DISABLED));
        return $form;
    }
}

?>
