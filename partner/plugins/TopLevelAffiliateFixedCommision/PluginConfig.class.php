<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package PostAffiliatePro
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 */

/**
 * Configuration class for plugin
 * 
 * Name of this class should be always yourUniquePluginName_Config and extends from Gpf_Plugins_Config 
 */
class TopLevelAffiliateFixedCommision_PluginConfig extends Gpf_Plugins_Config {
    const USE_FIRST_TIER_COMMISSION = 'UseFirstTierCommission';
    
    protected function initFields() {
        $this->addCheckBox($this->_('Use original first tier commission'), self::USE_FIRST_TIER_COMMISSION, $this->_('If user has no parent (and should get first tier commission), use original first tier commission instead of fixed commission set up by this plugin.'));
    }
    
    /**
     * @anonym
     * @service
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function save(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);        
        Gpf_Settings::set(self::USE_FIRST_TIER_COMMISSION, $form->getFieldValue(self::USE_FIRST_TIER_COMMISSION));        
        $form->setInfoMessage($this->_('Settings saved'));
        return $form;
    }

    /**
     * @anonym
     * @service 
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function load(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);        
        $form->addField(self::USE_FIRST_TIER_COMMISSION, Gpf_Settings::get(self::USE_FIRST_TIER_COMMISSION));        
        return $form;
    }
}

?>
