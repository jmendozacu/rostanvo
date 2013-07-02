<?php
try{
    $installer = $this;
    $installer->startSetup();

    // Create global top navigation menu
    $content =
<<<EOD
<li class = "first"><a href ="{{config path="web/unsecure/base_url"}}">Home</a></li>
<li class = ""><a href ="{{store url='about-us'}}">About</a></li>
<li class = "">
<a href = "{{store url='resource/history-of-the-cello-string'}}">Resource</a>
<ul>
	<li><a href = "{{store url='resource/history-of-the-cello-string'}}">History of the cello string</a></li>
	<li><a href = "{{store url='resource/how-to-choose-cello-strings'}}">How to choose cello strings</a></li>
	<li><a href = "{{store url='resource/best-strings'}}">Why the best strings are important</a></li>
	<li><a href = "{{store url='resource/how-to-string-a-cello'}}">How to string a cello</a></li>
</ul>
</li>
<li class = ""><a href ="{{config path='web/unsecure/base_url'}}en/news">Cello news</a></li>
<li class = ""><a href ="{{store url='events'}}">Events</a></li>
<li class = ""><a href ="{{store url='testimonials'}}">Testimonials</a></li>
EOD;

	$title = 'Top navigation';

	$identifier = 'top_nav';

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
