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
class Pap_Affiliates_Promo_RowCompoundFilter extends Gpf_Object {

    /**
     * @var Gpf_Data_Row
     */
    private $row;

    /**
     * @var Gpf_Rpc_FilterCollection
     */
    private $filters;

    public function __construct(Gpf_Data_Row $row, Gpf_Rpc_FilterCollection $filters = null) {
        $this->row = $row;
        $this->filters = $filters;
    }

    /**
     * @return Gpf_Data_Row
     */
    public function getRow() {
        return $this->row;
    }

    /**
     * @return Gpf_Rpc_FilterCollection
     */
    public function getFilters() {
        return $this->filters;
    }

}
?>
