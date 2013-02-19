<?php

/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Milos Jancovic
 *   @package PostAffiliatePro
 *   @since Version 1.0.0
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 *
 */

class Pap_Features_ReCaptcha_Definition extends Gpf_Plugins_Definition  {
    
    public function __construct() {
        
        $this->codeName = 'ReCaptcha';
        $this->name = $this->_('ReCaptcha');
        $this->description = $this->_('ReCaptcha lets you embed a CAPTCHA in your signup in order to protect it against spam and other types of automated abuse.').
            '<br><a href="' . Gpf_Application::getKnowledgeHelpUrl('396134-ReCaptcha') . '" target="_blank">'.$this->_('More help in our Knowledge Base').'</a>';
        $this->version = '1.0.0';
        $this->pluginType = self::PLUGIN_TYPE_FEATURE;
        
        $this->addImplementation('Pap_Signup_AffiliateForm.checkBeforeSaveNotApi', 'Pap_Features_ReCaptcha_Main','validateCaptcha');
        $this->addImplementation('Pap_Features_AffiliateNetwork_Signup_AccountSignupForm.checkBeforeSaveNotApi', 'Pap_Features_ReCaptcha_Main','validateCaptchaAccount');
        $this->addImplementation('PostAffiliate.AffiliateSignupForm.save', 'Pap_Features_ReCaptcha_Main', 'saveSettings');
        $this->addImplementation('PostAffiliate.AffiliateSignupForm.load', 'Pap_Features_ReCaptcha_Main', 'loadSettings');
        $this->addImplementation('Core.initJsResources', 'Pap_Features_ReCaptcha_Main', 'initJsResource');
        $this->addImplementation('PostAffiliate.ApplicationSettings.loadSetting', 'Pap_Features_ReCaptcha_Main', 'addApplicationSettings');
        $this->addImplementation('PostAffiliate.AccountSignupSettingsForm.save', 'Pap_Features_ReCaptcha_Main', 'saveAccountSettings');
        $this->addImplementation('PostAffiliate.AccountSignupSettingsForm.load', 'Pap_Features_ReCaptcha_Main', 'loadAccountSettings');
    }
}
?>
