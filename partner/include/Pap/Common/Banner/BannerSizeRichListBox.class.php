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
class Pap_Common_Banner_BannerSizeRichListBox extends Gpf_Ui_RichListBoxService {

    /**
     * @service banner read
     * @param $id, $search, $from, $rowsPerPage
     * @return Gpf_Rpc_Object
     */
    public function load(Gpf_Rpc_Params $params) {
        return parent::load($params);
    }

    /**
     * @return Gpf_SqlBuilder_SelectBuilder
     */
    protected function createSelectBuilder() {
        $selectBuilder = new Gpf_SqlBuilder_SelectBuilder();
        $selectBuilder->select->add('SUBSTRING('.Pap_Db_Table_Banners::SIZE.',2)', self::ID);
        $selectBuilder->select->add('SUBSTRING('.Pap_Db_Table_Banners::SIZE.',2)', self::VALUE);
        $selectBuilder->from->add(Pap_Db_Table_Banners::getName());
        $selectBuilder->where->add(Pap_Db_Table_Banners::SIZE, '!=', Pap_Db_Banner::SIZE_NOTAPPLICABLE);
        $selectBuilder->groupBy->add(self::VALUE);

        return $selectBuilder;
    }
     
    /**
     * @param $selectBuilder
     * @param $search
     */
    protected function addSearchCondition(Gpf_SqlBuilder_SelectBuilder $selectBuilder, $search) {
        $selectBuilder->where->add(Pap_Db_Table_Banners::SIZE, 'LIKE', '%'.$search.'%');
    }

    /**
     * @param $selectBuilder
     * @param $id
     */
    protected function addIdSearchCondition(Gpf_SqlBuilder_SelectBuilder $selectBuilder, $id) {
        $selectBuilder->where->add('SUBSTRING('.Pap_Db_Table_Banners::SIZE.',2)', '=', $id);
    }
}

?>
