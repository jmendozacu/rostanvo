<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Matej Kendera
 *   @package PostAffiliatePro
 *   @since Version 1.0.0
 *   $Id: ActionParser.class.php 16620 2008-03-21 09:21:07Z aharsani $
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 *
 */

/**
 * @package PostAffiliatePro
 */
class Pap_Mail_AutoRegisteringAffiliateOnFirstSale extends Pap_Mail_UserMail {

    public function __construct() {
        parent::__construct();
        $this->mailTemplateFile = 'autoregisteringaffiliates_affiliate_on_first_sale.stpl';
        $this->isHtmlMail = true;
        $this->templateName = Gpf_Lang::_runtime('Auto Registering Affiliates - Affiliate - First Sale / Lead');
        $this->subject = Gpf_Lang::_runtime('Welcome to our affiliate program - First Sale / Lead');
    }

    protected function getTemplateFromFile() {
        $tmpl = new Gpf_Templates_Template(self::MAIL_TEMPLATE_DIR . $this->mailTemplateFile, 'install');
        return $tmpl->getTemplateSource();
    }
}
