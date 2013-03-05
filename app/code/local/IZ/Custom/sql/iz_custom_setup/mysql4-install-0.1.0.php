<?php
try{
    $installer = $this;
    $installer->startSetup();

    // Create global banner
    $content =
<<<EOD
<p>
	<a title="Buy Cello Strings" href="{{store url='shop.html'}}">
		<img src="{{skin url='images/headline-banner.jpg'}}" alt="Buy Cello Strings" width="919" height="256" border="0" />
	</a>
</p>
EOD;

	$title = 'Banner';

	$identifier = 'header_banner';

    $staticBlock = array(
        'title' => $title,
        'identifier' => $identifier,
        'content' => $content,
        'is_active' => 1,
        'stores' => array(0)
    );

	if (Mage::getModel('cms/block')->load($identifier)->getBlockId())
	{
		Mage::getModel('cms/block')->load($identifier)->delete();
	}
	Mage::getModel('cms/block')->setData($staticBlock)->save();

    $installer->endSetup();

}catch(Excpetion $e){
    Mage::logException($e);
    Mage::log("ERROR IN SETUP ".$e->getMessage());
}

