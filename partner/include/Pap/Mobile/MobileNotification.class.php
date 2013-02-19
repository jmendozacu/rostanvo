<?php

/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Rene Dohan
 *   @package PostAffiliatePro
 *   @since Version 1.0.0
 *   $Id: UpdateManager.class.php 18026 2008-05-14 08:07:20Z mbebjak $
 *
 *   Licensed under the Quality Unit, s.r.o. Dual License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/gpf
 *
 */
class Pap_Mobile_MobileNotification {

	private $saleRow;
	private $isMerchant;

	public function __construct($isMerchant, Gpf_Data_Record $saleRow) {
		$this->saleRow = $saleRow;
		$this->isMerchant = $isMerchant;
	}

	public function getCommission() {
		return $this->toCurency($this->saleRow->get('commission'));
	}

	public function getCampaign() {
		return $this->saleRow->get('campaign_name');
	}

	public function getTotalCost() {
		return $this->toCurency($this->saleRow->get('total_cost'));
	}

	public function getAffiliateName() {
		return $this->saleRow->get('affname').' '.$this->saleRow->get('affsurname');
	}

	public function isAffiliate() {
		return !$this->isMerchant;
	}

	private function toCurency($value){
		return (string)Pap_Common_Utils_CurrencyUtils::toStandardCurrencyFormat($value);
	}
}

?>
