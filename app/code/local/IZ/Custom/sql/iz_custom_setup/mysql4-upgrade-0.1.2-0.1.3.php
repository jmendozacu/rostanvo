<?php
try{
    $installer = $this;
    $installer->startSetup();

    // Create global banner
    $content =
<<<EOD
<div class="block">
	<div class="block-title"><strong>Resource Pages</strong></div>
	<div class="block-content">
		<ul style="font-size: 110%; line-height: 20px;">
			<li>- <a class="cmsurl" href="{{store url='resource/history-of-the-cello-string'}}">History of the Cello string</a></li>
			<li>- <a class="cmsurl" href="{{store url='resource/how-to-choose-cello-strings'}}">How to choose Cello strings</a></li>
			<li>- <a class="cmsurl" href="{{store url='resource/best-strings'}}">Why the best strings are important</a></li>
			<li>- <a class="cmsurl" href="{{store url='resource/how-to-string-a-cello'}}">How to string a Cello</a></li>
		</ul>
	</div>
</div>
<script type="text/javascript">
document.observe("dom:loaded", function() {
	$$('.cmsurl').each(function(link){
		if (link.readAttribute('href').indexOf(document.URL) >= 0) {
			link.addClassName('selected');
		}
		else
		{
			if (link.hasClassName('selected'));
			{
				link.removeClassName('selected');
			}
		}
	});
});
</script>
EOD;

	$title = 'Resource Links';

	$identifier = 'resource-links';

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

