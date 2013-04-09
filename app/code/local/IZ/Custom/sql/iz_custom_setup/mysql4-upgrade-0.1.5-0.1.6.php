<?php
try{
    $installer = $this;
    $installer->startSetup();

    // Create links to strings on home page
    $content =
<<<EOD
<div class="home-string astring">
    <a href="{{block type="core/template" key="block1" template="iz/link_to_product.phtml"}}"><img height=
    "180" src="{{skin url='images/home-A.png'}}" width=
    "220" /></a>

    <div class="stext">
		Huge dynamic range, projection and immediate response, our A is the ultimate concert string.
		Benefit from easier intonation due to the A’s improved consistency against the D string.
        <br />
        <a href="{{block type="core/template" key="block1" template="iz/link_to_product.phtml"}}">Read more</a>
    </div>
	{{block type="core/template" key="block1" template="iz/custom_buy_form.phtml"}}
</div>

<div class="home-string dstring">
    <a href="{{block type="core/template" key="block2" template="iz/link_to_product.phtml"}}"><img height=
    "180" src="{{skin url='images/home-D.png'}}" width=
    "230" /></a>

    <div class="stext">
        Our groundbreaking D string is able to project like the A while
        producing rich, deeper tones normally associated with the G string on
        your cello.<br />
        <a href="{{block type="core/template" key="block2" template="iz/link_to_product.phtml"}}">Read more</a>
    </div>
	{{block type="core/template" key="block2" template="iz/custom_buy_form.phtml"}}
</div>

<div class="home-string gstring">
    <a href="{{block type="core/template" key="block3" template="iz/link_to_product.phtml"}}"><img height=
    "180" src="{{skin url='images/home-G.png'}}" width=
    "230" /></a>

    <div class="stext">
		Open and with a wide range of possible sounds and harmonics, our G string has
		quick response times giving you the acoustic freedom to play the way that suits any musical style.
        <br />
        <a href="{{block type="core/template" key="block3" template="iz/link_to_product.phtml"}}">Read more</a>
    </div>
	{{block type="core/template" key="block3" template="iz/custom_buy_form.phtml"}}
</div>

<div class="home-string cstring">
    <a href="{{block type="core/template" key="block4" template="iz/link_to_product.phtml"}}"><img height=
    "180" src="{{skin url='images/home-C.png'}}" width=
    "220" /></a>

    <div class="stext">
		Rich and deep, our C string offers more tonal colours than ever.
		Improved response at pianissimo volumes makes it an easy & enjoyable string to play
        <br/>
        <a href="{{block type="core/template" key="block4" template="iz/link_to_product.phtml"}}">Read more</a>
    </div>
	{{block type="core/template" key="block4" template="iz/custom_buy_form.phtml"}}
</div>
EOD;

	$title = 'Strings';

	$identifier = 'strings';

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


