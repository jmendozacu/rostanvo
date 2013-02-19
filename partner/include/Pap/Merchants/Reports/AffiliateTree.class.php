<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Milos Jancovic
 *   @since Version 1.0.0
 *   $Id: TransactionsGrid.class.php 17234 2008-04-11 14:23:06Z mbebjak $
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
class Pap_Merchants_Reports_AffiliateTree extends Pap_Common_Reports_AffiliateTreeBase {

    private $onlyTopAffiliates = false;

    protected function addWhereCondition(Gpf_SqlBuilder_SelectBuilder $selectBuilder, $parentUserId) {
        $selectBuilder->where->add('u.deleted', '=', Gpf::NO);
        if (is_array($parentUserId)) {
            $selectBuilder->where->add('u.userid', 'IN', $parentUserId);
            if ($this->onlyTopAffiliates) {
                $selectBuilder->where->addCondition($this->getTopAfffiliatesWhereCondition());
            }
            return;
        }
        if($parentUserId == '') {
            $selectBuilder->where->addCondition($this->getTopAfffiliatesWhereCondition());
            return;
        }
        $selectBuilder->where->add('u.parentuserid', '=', $parentUserId);
    }

    protected function createFilterCollection(Gpf_Rpc_Params $params) {
        $this->filterCollection = new Gpf_Rpc_FilterCollection($params);
        if ($params->get('itemId') == '') {
            if ($this->filterCollection->isFilter('affiliate') && $this->filterCollection->getFilterValue('affiliate') != '') {
                $params->set('itemId', $this->getAffiliateIds($this->filterCollection->getFilterValue('affiliate')));
            }
            if ($this->filterCollection->isFilter('onlyTopAffiliates') && $this->filterCollection->getFilterValue('onlyTopAffiliates') == Gpf::YES) {
                $this->onlyTopAffiliates = true;
            }
        }
        $this->removeAffiliateFilter();
    }

    protected function loadItems($itemId, $lastTier = false)  {
        if (!$this->isItemIdCorrect($itemId)) {
            return new Gpf_Data_RecordSet();
        }
        return parent::loadItems($itemId, $lastTier);
    }

    private function isItemIdCorrect($itemId) {
        if (is_array($itemId) && count($itemId) == 0) {
            return false;
        }
        return true;
    }

    private function getTopAfffiliatesWhereCondition() {
        $condition = new Gpf_SqlBuilder_CompoundWhereCondition();
        $condition->add('u.parentuserid', '=', null, 'OR');
        $condition->add('u.parentuserid', '=', '', 'OR');
        return $condition;
    }

    private function getAffiliateIds($searchValue) {
        $affiliatesGrid = new Pap_Merchants_User_AffiliatesGrid();
        $params = new Gpf_Rpc_Params();
        $params->add('filters', array(array("search", "L", $searchValue)));
        $params->add('columns', array(array("id")));
        $affiliateRows = $affiliatesGrid->getRows($params);
        $affiliateIds = array();
        foreach ($affiliateRows->rows as $row) {
            if ($row[0] == 'id') {
                continue;
            }
            $affiliateIds[] = $row[0];
        }
        return $affiliateIds;
    }

    private function removeAffiliateFilter() {
        $tempFilterCollection = new Gpf_Rpc_FilterCollection();
        foreach ($this->filterCollection as $filter) {
            if ($filter->getCode() == 'affiliate' || $filter->getCode() == 'onlyTopAffiliates') {
                continue;
            }
            $tempFilterCollection->add(array($filter->getCode(), $filter->getRawOperator()->getCode(), $filter->getValue()));
        }
        $this->filterCollection = $tempFilterCollection;
    }
}
?>
