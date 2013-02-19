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
class Pap_Features_BannersCategories_TreePanel extends Gpf_Object {

    const BANNERS_CATEGORIES_TREE = 'bannersCategoriesTree';
    const BANNERS_CATEGORIES_TREE_MAXITEMCODE = 'bannersCategoriesTreeMaxItemCode';
     
    /**
     * @service banners_categories read
     * @param $fields
     */
    public function loadTree(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);

        $tree = new Pap_Features_BannersCategories_Tree(false);
        $tree->laod();
        $data->setValue(self::BANNERS_CATEGORIES_TREE, $tree->toJSON());
        $data->setValue(self::BANNERS_CATEGORIES_TREE_MAXITEMCODE, $tree->getMaxCode());

        return $data;
    }

    public function loadTreeNoRpc() {
        return $this->loadTree(new Gpf_Rpc_Params());
    }

    /**
     *
     * @service banners_categories write
     * @param $fields
     * @return Gpf_Rpc_Action
     */
    public function saveTree(Gpf_Rpc_Params $params) {
        $action = new Gpf_Rpc_Action($params);
        $action->setInfoMessage($this->_("Banner categories structure was saved"));

        try {
            $tree = new Pap_Features_BannersCategories_Tree(false);
            $tree->save($action->getParam(self::BANNERS_CATEGORIES_TREE));
            $action->addOk();
        } catch (Gpf_Exception $e) {
            $action->setErrorMessage($e);
            $action->addError();
            return $action;
        }
        return $action;
    }
}

?>
