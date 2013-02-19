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
class Pap_Features_BannersCategories_Tree extends Gpf_Tree_Base {
    public function __construct($withRoot = true, $onlyWithStatuses = array(Gpf::YES, Gpf::NO)) {
        parent::__construct(Pap_Features_BannersCategories_Main::BANNERS_CATEGORIES_HIERARCHICAL_DATE_TYPE, $withRoot, $onlyWithStatuses);
    }

    private function deleteUnusedCategories($activeList) {
        //TODO: do this with delte constraints
        $delete = new Gpf_SqlBuilder_DeleteBuilder();
        $delete->from->add(Pap_Db_Table_BannersCategories::getName());
        $delete->where->add(Pap_Db_Table_BannersCategories::CATEGORYID, 'not in', $activeList);
        $delete->execute();

        $delete = new Gpf_SqlBuilder_DeleteBuilder();
        $delete->from->add(Pap_Db_Table_BannersCategories::getName());
        $delete->where->add(Pap_Db_Table_BannersCategories::CATEGORYID, 'not in', $activeList);
        $delete->execute();
    }

    public function save($JSONString) {
        $activeIds = parent::save($JSONString);
        $this->deleteUnusedCategories($activeIds);
    }
}
?>
