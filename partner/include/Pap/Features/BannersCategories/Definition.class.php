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

class Pap_Features_BannersCategories_Definition extends Gpf_Plugins_Definition  {
    public function __construct() {
        $this->codeName = 'BannersCategories';
        $this->name = $this->_('Banners categories');
        $this->description = $this->_('Banners categories allow you to categorize your banners to tree-like structure.') . '<br/><a href="'.Gpf_Application::getKnowledgeHelpUrl('441949-Banners-categories').'" target="_blank">' . $this->_('More help in our Knowledge Base') . '</a>';
        $this->version = '1.0.0';
        $this->pluginType = self::PLUGIN_TYPE_FEATURE;

        $this->addImplementation('PostAffiliate.merchant.menu',
                                 'Pap_Features_BannersCategories_Main', 'addToMenu');
        $this->addImplementation('BannersGrid.modifyWhere',
                                 'Pap_Features_BannersCategories_Main', 'addCategoryFilterToMerchantCategoryList');
    }

    public function onDeactivate() {
    }

    public function onActivate() {
    }
}
?>
