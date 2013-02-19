<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Maros Fric
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
class Pap_Tracking_Action_RecognizeBanner extends Pap_Tracking_Common_RecognizeBanner implements Pap_Tracking_Common_Recognizer  {

    /**
     * @return Pap_Common_Banner
     */
    public function recognizeBanners(Pap_Contexts_Tracking $context) {
        if ($context->getBannerObject() != null) {
            $context->debug('Banner oject was set before banner recognizing.');
            return $context->getBannerObject();
        }

        try {
            $banner = $this->getBannerById($context, $context->getBannerIdFromRequest());
            $context->debug('Banner is recognized from request parameter.');
            return $banner;
        } catch (Exception $e) {
        }

        try {
            $banner = $this->getBannerById($context, $context->getVisitorAffiliate()->getBannerId());
            $context->debug('Banner is recognized from VisitorAffiliate.');
            return $banner;
        } catch (Exception $e) {
            $context->debug('Banner not recognized');
            return;
        }
    }

    protected function setParentBanner(Pap_Contexts_Tracking $context, Pap_Common_Banner $banner){
    }
}

?>
