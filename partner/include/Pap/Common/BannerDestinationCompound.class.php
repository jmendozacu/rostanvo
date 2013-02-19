<?php

/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Matej Kendera
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
 * @package PostAffiliatePro
 */

class Pap_Common_BannerDestinationCompound {
    /**
     * @var Pap_Common_Banner
     */
    private $banner;
    private $destinationUrl;
    /**
     * @var Pap_Common_User
     */
    private $user;

    public function __construct(Pap_Common_Banner $banner, Pap_Common_User $user){
        $this->banner = $banner;
        $this->user = $user;
    }

    public function setDestinationUrl($destinationUrl) {
        $this->destinationUrl = $destinationUrl;
    }
    /**
     * @return Pap_Common_Banner
     */
    public function getBanner(){
        return $this->banner;
    }

    public function getDestinationUrl() {
        return $this->destinationUrl;
    }

    /**
     * @return Pap_Common_User
     */
    public function getUser() {
        return $this->user;
    }
}

?>
