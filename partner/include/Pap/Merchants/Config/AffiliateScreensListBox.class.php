<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Matej Kendera
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
class Pap_Merchants_Config_AffiliateScreensListBox extends Gpf_Object implements Gpf_Rpc_TableData {
    
    const ID = 'id';
    const VALUE = 'value';

    /**
     * @service affiiliate_screen read
     * @return Gpf_Data_RecordSet
     */
    public function getRow(Gpf_Rpc_Params $params) {
        $recordset = $this->getRowFromDB($params->get(self::SEARCH));
        return $this->updateAffiliateScreensRecordSet($recordset);
    }

    /**
     * @service affiiliate_screen read
     * @return Gpf_Data_Table
     */
    public function getRows(Gpf_Rpc_Params $params) {
        $select = $this->createAffiliateScreensSelect();
        $affiliateScreens = $select->getAllRows();
        $affiliateScreens = $this->updateAffiliateScreensRecordSet($affiliateScreens);
        return $this->createResponse($params, $affiliateScreens);
    }

    /**
     * @param $params
     * @return Gpf_Data_Table
     */
    protected function createResponse(Gpf_Rpc_Params $params, Gpf_Data_RecordSet $affiliateScreens) {
        $data = new Gpf_Data_Table($params);
        $data->fill($affiliateScreens);
        return $data;
    }

    /**
     * @return Gpf_SqlBuilder_SelectBuilder
     */
    protected function createAffiliateScreensSelect() {
        $selectBuilder = new Gpf_SqlBuilder_SelectBuilder();
        foreach ($this->createHeaderArray() as $column) {
            $selectBuilder->select->add($column);
        }
        $selectBuilder->from->add(Pap_Db_Table_AffiliateScreens::getName());
        return $selectBuilder;
    }

    /**
     * @param $commTypeId
     * @param $campaignId
     * @return Gpf_Data_RecordSet
     */
    protected function getRowFromDB($codeParams) {
        if (strstr($codeParams, ';')) {
            $code = substr($codeParams, 0, strrpos($codeParams, ';'));
            $params = substr($codeParams, strrpos($codeParams, ';')+1);
        } else {
            $code = $codeParams;
            $params = null;
        }
        
        $select = $this->createAffiliateScreensSelect();
        $select->where->add(Pap_Db_Table_AffiliateScreens::CODE, '=', $code);
        if (!is_null($params)) {
            $select->where->add(Pap_Db_Table_AffiliateScreens::PARAMS, '=', $params);
        }
        $select->limit->set(0, 1);
        $affiliateScreen = $select->getAllRows();
        return $affiliateScreen;
    }

    /**
     * @return array
     */
    private function createHeaderArray() {
        return array(Pap_Db_Table_AffiliateScreens::CODE,
        Pap_Db_Table_AffiliateScreens::PARAMS,
        Pap_Db_Table_AffiliateScreens::TITLE);
    }
    
    /**
     * @param Gpf_Data_RecordSet $affiliateScreens
     * @return Gpf_Data_RecordSet
     */
    private function updateAffiliateScreensRecordSet(Gpf_Data_RecordSet $affiliateScreens) {
        $header = $affiliateScreens->getHeader();
        $header->add(self::ID);
        $header->add(self::VALUE);
        foreach ($affiliateScreens as $record) {
            if ($record->get(Pap_Db_Table_AffiliateScreens::PARAMS) != null) {
                $id = $record->get(Pap_Db_Table_AffiliateScreens::CODE) . ';' . $record->get(Pap_Db_Table_AffiliateScreens::PARAMS);
            } else {
                $id = $record->get(Pap_Db_Table_AffiliateScreens::CODE);
            }
            $record->add(self::ID, $id);
            $record->add(self::VALUE, $this->_localize($record->get(Pap_Db_Table_AffiliateScreens::TITLE)));
        }
        
        return $affiliateScreens;
    }
}
?>
