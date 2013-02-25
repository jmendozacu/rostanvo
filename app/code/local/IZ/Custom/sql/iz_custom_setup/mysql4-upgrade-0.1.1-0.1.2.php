<?php
try{
    $installer = $this;
    $installer->startSetup();

	/*ADD NEW HOME PAGE*/

    $content = <<<EOD
<!--add homepage-->
<!--end of page-->
EOD;

	$root_template = 'two_columns_left';

	$title = 'Water Filters Blog';

	$identifier = 'water-filters-blog.html';

	$layout_update = <<<EOD
<reference name="left">
<block type="cms/block" name="water.university.navigation">
    <action method="setBlockId"><block_id>water-university-navigation </block_id></action>
</block> 
</reference>
<reference name="head">
	<action method="addCss"><stylesheet>css/cmspage.css</stylesheet></action>
</reference>
EOD;

	$meta_keywords = <<<EOD
	Water Filters for Charity, Improving Water and the World, water charity,
    charity: water, corporate philanthropy, social entrepeneur, safe water,
    clean water, water education, servant leadership, water quality
EOD;

	$meta_description = <<<EOD
	Water Filters for Charity Improves Water and the World with a Ripple Effect
    of WaterFilters.NET serving customers and coworkers as well as people in
    need
EOD;

    $cmspage = array(
        'title' => $title,
        'identifier' => $identifier,
        'content' => $content,
        'sort_order' => 0,
		'stores' => array(0),
		'root_template' => $root_template,
		'layout_update_xml' => $layout_update,
		'meta_keywords' => $meta_keywords,
		'meta_description' => $meta_description
    );
	if (Mage::getModel('cms/page')->load($identifier)->getPageId())
	{
		Mage::getModel('cms/page')->load($identifier)->delete();
	}

	Mage::getModel('cms/page')->setData($cmspage)->save();
    $installer->endSetup();

}catch(Excpetion $e){
    Mage::logException($e);
    Mage::log("ERROR IN SETUP ".$e->getMessage());
}

