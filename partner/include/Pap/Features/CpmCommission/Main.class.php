<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Maros Fric
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
 * @package PostAffiliate
 */
class Pap_Features_CpmCommission_Main extends Gpf_Plugins_Handler {
    const IMPRESSION_COUNT_PER_COMMISSION = 1000;

	public static function getHandlerInstance() {
 		return new Pap_Features_CpmCommission_Main();
	}

	public function insertDefaultCommissionType(Pap_Merchants_Campaign_CampaignForm $context) {
	    $context->insertDefaultCommissionType(Pap_Common_Constants::TYPE_CPM);
	}

	public function saveCommission(Pap_Contexts_Impression $context) {
	    $context->debug('    Impression: CPM commission starting');
	    $cpmCommission = new Pap_Db_CpmCommission();
	    $cpmCommission->setUserId($context->getUserObject()->getId());
	    $cpmCommission->setBannerId($context->getBannerObject()->getId());
	    try {
	        $context->debug('    Impression: load CPM impression count for user: '. $context->getUserObject()->getId() . ' banner: ' . $context->getBannerObject()->getId());
	        $cpmCommission->load();
	        $cpmCommission->setCount($cpmCommission->getCount()+$context->getCount());
	        $context->debug('    Impression: CPM actual impression count: ' . $cpmCommission->getCount());
	        if ($cpmCommission->getCount() < self::IMPRESSION_COUNT_PER_COMMISSION) {
	            $cpmCommission->save();
	            $context->debug('    Impression: CPM actual impression count saved.');
	        } else {
	            try {
	                while ($cpmCommission->getCount() >= self::IMPRESSION_COUNT_PER_COMMISSION) {
	                    $cpmCommission->setCount($cpmCommission->getCount()-self::IMPRESSION_COUNT_PER_COMMISSION);
                        $this->saveCpmTransaction($context);
                        $context->debug('    Impression: CPM commission created');
	                }
	            } catch (Pap_Tracking_Exception $e) {
	                $context->debug($this->_sys("CPM commissions not defined in campaign %s",
	                                               $context->getCampaignObject()->getName()));
                    $cpmCommission->setCount($cpmCommission->getCount() % self::IMPRESSION_COUNT_PER_COMMISSION);
	                $context->debug('    Impression: CPM commission not defined, CPM impression count set ' . $cpmCommission->getCount() % self::IMPRESSION_COUNT_PER_COMMISSION);
	            }

	            $cpmCommission->save();
	        }
	    } catch (Gpf_DbEngine_NoRowException $e) {
	        $cpmCommission->setCount($context->getCount());
	        $cpmCommission->save();
	        $context->debug('    Impression: CPM commission not impression count, count set: ' . $context->getCount());
	    }
	    $context->debug('    Impression: CPM commission ending');
	}

	private function saveCpmTransaction(Pap_Contexts_Impression $context) {
	    $campaign = new Pap_Common_Campaign();
	    $campaign->setId($context->getBannerObject()->getCampaignId());
	    $campaign->load();
	    $context->setCampaignObject($campaign);
	    $transaction = new Pap_Common_Transaction();
	    $transaction->setType(Pap_Common_Constants::TYPE_CPM);
	    $context->setTransactionObject($transaction);

	    $commissionType = new Pap_Tracking_Common_RecognizeCommType();
	    $commissionType->process($context);

	    $commissionGroup = new Pap_Tracking_Common_RecognizeCommGroup();
        $commissionGroup->recognize($context);

        $commissionSettings = new Pap_Tracking_Common_RecognizeCommSettings();
        $commissionSettings->recognize($context);

        $saveCommissions = new Pap_Tracking_Common_UpdateAllCommissions();
        $saveCommissions->process($context);
        $saveCommissions->saveChanges();
	}
}
?>
