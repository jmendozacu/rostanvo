<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Matej Kendera
 *   @since Version 1.0.0
 *   $Id: TransactionsInfoData.class.php 16653 2008-03-25 10:42:12Z mjancovic $
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
class Pap_Features_BannersCategories_CategoryInfoData extends Gpf_Object {

    /**
     * Load category detail for transaction manager
     *
     * @service banners_categories read
     * @param $fields
     */
    public function categoryDetails(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);

        $ids = $data->getFilters()->getFilter("id");
        	
        if (sizeof($ids) == 1) {
            $id = $ids[0]->getValue();
        }

        $category = $this->getCategoryData($id);
        if($category == null) {
            return $data;
        }

        foreach ($category as $name => $value) {
            $data->setValue($name, $this->_localize($value));
        }

        return $data;
    }

    /**
     * @throws Gpf_DbEngine_TooManyRowsException
     * @throws Gpf_DbEngine_NoRowException
     * @param string $categoryId
     * @return Gpf_Data_Record
     */
    private function getCategoryData($categoryId) {
        $selectBuilder = new Gpf_SqlBuilder_SelectBuilder();

        $selectBuilder->select->add(Pap_Db_Table_BannersCategories::DESCRIPTION, Pap_Db_Table_BannersCategories::DESCRIPTION, 'c');
        $selectBuilder->select->add(Pap_Db_Table_BannersCategories::CATEGORYID, Pap_Db_Table_BannersCategories::CATEGORYID, 'c');
        $selectBuilder->select->add(Gpf_Db_Table_HierarchicalDataNodes::NAME, Gpf_Db_Table_HierarchicalDataNodes::NAME, 'h');
        $selectBuilder->select->add(Gpf_Db_Table_HierarchicalDataNodes::STATE, 'visible', 'h');

        $selectBuilder->from->add(Gpf_Db_Table_HierarchicalDataNodes::getName(), "h");
        $selectBuilder->from->addLeftJoin(Pap_Db_Table_BannersCategories::getName(), "c",
            "h.".Gpf_Db_Table_HierarchicalDataNodes::CODE." = c.".Pap_Db_Table_BannersCategories::CATEGORYID);

        $selectBuilder->where->add(Gpf_Db_Table_HierarchicalDataNodes::TYPE, '=', Pap_Features_BannersCategories_Main::BANNERS_CATEGORIES_HIERARCHICAL_DATE_TYPE);
        $selectBuilder->where->add(Pap_Db_Table_BannersCategories::CATEGORYID,'=',$categoryId);

        $row = $selectBuilder->getOneRow();
        	
        return $row;
    }
}

?>
