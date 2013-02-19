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
class ISecure_Main extends Gpf_Plugins_Handler {

    /**
     * @return ISecure_Main
     */
    public static function getHandlerInstance() {
        return new ISecure_Main();
    }

    public function initSettings($context) {
        $context->addDbSetting(ISecure_Config::CUSTOM_FIELD_ID, '');
        $context->addDbSetting(ISecure_Config::DISCOUNT_TAX, '');
        $context->addDbSetting(ISecure_Config::REGISTER_AFFILIATE, '');
        $context->addDbSetting(ISecure_Config::TEST_MODE, '');
        $context->addDbSetting(ISecure_Config::APPROVE_AFFILIATE, '');
        $context->addDbSetting(ISecure_Config::PROCESS_WHOLE_CART_AS_ONE_TRANSACTION, Gpf::YES);
    }
}
?>
