<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Matej Kendera
 *   @since Version 1.0.0
 *   $Id: ExistingExportsGrid.class.php 17234 2008-04-11 14:23:06Z mbebjak $
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
class Pap_Features_BannersCategories_EditableCategoriesGrid extends Gpf_View_GridService {

    protected function initViewColumns() {
        $this->addViewColumn(Gpf_Db_Table_HierarchicalDataNodes::NAME, $this->_('Name'), true);
        $this->addViewColumn('selected', $this->_('In category'), true);
    }

    protected function initDataColumns() {
        $this->setKeyDataColumn('ni.'.Gpf_Db_Table_HierarchicalDataNodes::CODE);
        $this->addDataColumn(Gpf_Db_Table_HierarchicalDataNodes::CODE, 'ni.' . Gpf_Db_Table_HierarchicalDataNodes::CODE);
        $this->addDataColumn(Gpf_Db_Table_HierarchicalDataNodes::NAME, 'ni.'.Gpf_Db_Table_HierarchicalDataNodes::NAME);
        $this->addDataColumn('depth', '(COUNT(p.'.Gpf_Db_Table_HierarchicalDataNodes::NAME.') - 1)');
    }

    protected function initDefaultView() {
        $this->addDefaultViewColumn('selected', '');
        $this->addDefaultViewColumn(Gpf_Db_Table_HierarchicalDataNodes::NAME, '');
    }

    protected function buildFrom(){
        $this->_selectBuilder->from->add(Gpf_Db_Table_HierarchicalDataNodes::getName(), 'ni');
        $this->_selectBuilder->from->add(Gpf_Db_Table_HierarchicalDataNodes::getName(), 'p');
    }

    protected function buildWhere() {
        $this->_selectBuilder->where->add('ni.' . Gpf_Db_Table_HierarchicalDataNodes::LFT, 'BETWEEN', 'p.'.Gpf_Db_Table_HierarchicalDataNodes::LFT.' AND p.'.Gpf_Db_Table_HierarchicalDataNodes::RGT, 'AND', false);
        $this->_selectBuilder->where->add('ni.'.Gpf_Db_Table_HierarchicalDataNodes::TYPE,'=',Pap_Features_BannersCategories_Main::BANNERS_CATEGORIES_HIERARCHICAL_DATE_TYPE);
        $this->_selectBuilder->where->add('p.'.Gpf_Db_Table_HierarchicalDataNodes::TYPE,'=',Pap_Features_BannersCategories_Main::BANNERS_CATEGORIES_HIERARCHICAL_DATE_TYPE);

        parent::buildWhere();
    }

    protected function buildFilter() {
    }

    protected function buildGroupBy() {
        $this->_selectBuilder->groupBy->add('ni.' . Gpf_Db_Table_HierarchicalDataNodes::NAME);
        $this->_selectBuilder->groupBy->add('ni.' . Gpf_Db_Table_HierarchicalDataNodes::ID);
    }

    protected function buildOrder() {
        $this->_selectBuilder->orderBy->add('ni.'.Gpf_Db_Table_HierarchicalDataNodes::LFT);

    }

    private function getBannerCategories($bannerid) {
        $select = new Gpf_SqlBuilder_SelectBuilder();
        $select->select->add(Pap_Db_Table_BannersInCategory::CATEGORYID);
        $select->from->add(Pap_Db_Table_BannersInCategory::getName());
        $select->where->add(Pap_Db_Table_BannersInCategory::BANNERID,'=', $bannerid);
        $select->groupBy->add(Pap_Db_Table_BannersInCategory::CATEGORYID);

        $rows = $select->getAllRowsIterator();
        $categories = array();
        foreach ($rows as $row) {
            $categories[] = $row->get(Pap_Db_Table_BannersInCategory::CATEGORYID);
        }
        return $categories;
    }

    protected function afterExecute(Gpf_Data_RecordSet $inputResult) {
        $outputResult = new Gpf_Data_RecordSet();

        $bannerFilter = $this->filters->getFilter('bannerid');
        if (count($bannerFilter)>0) {
            $filterValue = $bannerFilter[0]->getValue();
            $categories = $this->getBannerCategories($filterValue);
        }

        foreach ($inputResult as $record) {
            if ($record->get('code')=='0') {
                continue;
            }
            $record->set('name', $this->_localize($record->get('name')));
            if (count($bannerFilter)>0) {
                $filterValue = $bannerFilter[0]->getValue();

                if (in_array($record->get('code'), $categories)) {
                    $record->add('selected', 'Y');
                } else {
                    $record->add('selected', 'N');
                }
            }
            $outputResult->add($record);
        }
        $outputResult->setHeader($inputResult->getHeader());
        $outputResult->getHeader()->add('selected');
        return $outputResult;
    }

    /**
     * @service banners_categories read
     *
     * @return Gpf_Rpc_Serializable
     */
    public function getRows(Gpf_Rpc_Params $params) {
        return parent::getRows($params);
    }

    /**
     * @service banner write
     * @return Gpf_Rpc_Serializable
     */
    public function saveCategories(Gpf_Rpc_Params $params) {
        $action = new Gpf_Rpc_Action($params);
        $bannerId = $action->getParam('bannerid');

        $fields = $action->getParam('fields');
        for ($i=1; $i<count($fields); $i++) {
            $field = $fields[$i];
            $catgoryId = $field[0];
            $selected = $field[2];
            if ($selected == Gpf::YES) {
                $bannerInCateogry = new Pap_Db_BannerInCategory();
                $bannerInCateogry->setBannerId($bannerId);
                $bannerInCateogry->setCategoryId($catgoryId);
                $bannerInCateogry->insert();
            } else if ($selected == Gpf::NO) {
                $delete = new Gpf_SqlBuilder_DeleteBuilder();
                $delete->from->add(Pap_Db_Table_BannersInCategory::getName());
                $delete->where->add(Pap_Db_Table_BannersInCategory::BANNERID, '=', $bannerId);
                $delete->where->add(Pap_Db_Table_BannersInCategory::CATEGORYID, '=', $catgoryId);
                $delete->execute();
            }
        }

        $action->setInfoMessage('Cateogries saved');
        return $action;
    }
}
?>
