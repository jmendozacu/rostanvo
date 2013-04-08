<?php
try{
    $installer = $this;
    $installer->startSetup();

    // Create global top header stripe
    $content =
<<<EOD
<div class="header-stripe">
	<ul>
		<li class="stripe stripe1"><a href = "{{store url='#'}}">FREE SHIPPING WORLDWIDE</a></li>
		<li class="stripe stripe2"><a href = "{{store url='#'}}">100% GUARENTEED SATISFACTION</a></li>
		<li class="stripe stripe3"><a href = "{{store url='#'}}">NO ADDED IMPORT / SALES TAX</a></li>
	</ul>
</div>
EOD;

	$title = 'Header stripe';

	$identifier = 'header_stripe';

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

