<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Milos Jancovic
 *   @since Version 1.0.0
 *   $Id: CampaignStatisticsData.class.php 16653 2008-03-25 10:42:12Z mjancovic $
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
class Pap_Merchants_BlogRss extends Gpf_Gadget_Rss {

    public function __construct() {
        parent::__construct();
        $protocol = substr(Gpf_Paths::getInstance()->getFullDomainUrl(), 0, strpos(Gpf_Paths::getInstance()->getFullDomainUrl(), ':'));
        $this->setUrl($protocol.'://www.qualityunit.com/blog/rssType/2.0/?no_cache=1&type=100&tx_t3blog_pi1[rss][feed_type]=post');
        $this->setName($this->_('Quality Unit News'));
    }
    
    protected function getTemplateName() {
        return "qualityunit_news.stpl";
    }

    /**
     * @anonym
     * @service
     * @param $data
     */
    public function load(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);
        $data->setValue('content', $this->toText());
        return $data;
    }
}

?>
