<?php
try{
    $installer = $this;
    $installer->startSetup();

	/*ADD NEW HOME PAGE*/

    $content = <<<EOD
<!--add homepage-->
<p>{{block type="cms/block" block_id="header_banner" template="cms/content.phtml"}}</p>
<p>{{block type="cms/block" block_id="strings" template="cms/content.phtml"}}</p>
<!--end of page-->
EOD;

	$root_template = 'one_column';

	$title = 'Rostanvo - best cello strings to improve your tonal range, tuning and volume';

	$identifier = 'home';

	$layout_update = <<<EOD
<?php echo $this->getLayout()->createBlock('cms/block')->setBlockId('featured_links')->toHtml() ?>
<reference name="left">
  <!--<block type="cms/block" name="leaf-corner" before="-">
    <action method="setBlockId"><block_id>leaf_corner</block_id></action>
  </block>-->
 <!-- <block type="cms/block" name="featured-pages" before="-">
    <action method="setBlockId"><block_id>featured_links</block_id></action>
  </block>-->
<action method="unsetChild"><name>cart_sidebar</name></action>
<action method="unsetChild"><name>catalog.compare.sidebar</name></action>
</reference>
EOD;

	$meta_keywords = <<<EOD
cello strings, accessories, violoncello, tuning, range
EOD;

	$meta_description = <<<EOD
Ultimate handmade cello strings, designed to maximise tonal range, tuning stability and projection for all cellists such as solists, concert players, beginners and cello makers
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

