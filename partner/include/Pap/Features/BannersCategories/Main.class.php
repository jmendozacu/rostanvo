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
 * @package PostAffiliatePro
 */
class Pap_Features_BannersCategories_Main extends Gpf_Plugins_Handler {

    const BANNERS_CATEGORIES_HIERARCHICAL_DATE_TYPE = 'bancat';

    public static function getHandlerInstance() {
        return new Pap_Features_BannersCategories_Main();
    }

    public function addToMenu(Gpf_Menu $menu) {
        $menu->getItem('Banners-Overview')->addItem('Banners-Categories', $this->_('Banners Categories'));
    }

    public function addCategoryFilterToMerchantCategoryList(Pap_Affiliates_Promo_SelectBuilderCompoundFilter $compound) {
        $selectBuilder = $compound->getSelectBuilder();
        $filters = $compound->getFilters();
        $categoryFilter = $filters->getFilter("categoryid");
        if (count($categoryFilter) > 0) {
            if ($categoryFilter[0]->getValue()=="FILTER_SELECT_ALL") {
                $selectBuilder->from->addLeftJoin(Pap_Db_Table_BannersInCategory::getName(), 'bic',
                    'b.'.Pap_Db_Table_Banners::ID.'=bic.'.Pap_Db_Table_BannersInCategory::BANNERID);
                $selectBuilder->where->add('bic.'.Pap_Db_Table_BannersInCategory::CATEGORYID,'!=',null);
                $selectBuilder->groupBy->add('b.'.Pap_Db_Table_Banners::ID);
            } else {
                $selectBuilder->from->addLeftJoin(Pap_Db_Table_BannersInCategory::getName(), 'bic',
                    'b.'.Pap_Db_Table_Banners::ID.'=bic.'.Pap_Db_Table_BannersInCategory::BANNERID.
                    ' AND bic.'.Pap_Db_Table_BannersInCategory::CATEGORYID.' IN ('.$categoryFilter[0]->getValue().')');
                $selectBuilder->where->add('bic.'.Pap_Db_Table_BannersInCategory::CATEGORYID,'in',preg_split('/,/',$categoryFilter[0]->getValue()));
                $selectBuilder->groupBy->add('b.'.Pap_Db_Table_Banners::ID);
            }
        }
    }
}
?>
