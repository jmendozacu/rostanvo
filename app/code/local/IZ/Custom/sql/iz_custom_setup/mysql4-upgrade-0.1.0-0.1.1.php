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
        Huge dynamicly ranged A string, delivering the largest forte unlike any
        you have heard before, as well as the quietest pianissimo without
        losing the sound's clarity.<br />
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
        An immensely easy G string to play at all volumes. Similar to our D, it
        is extremely responsive even during fast passages maintaining the
        clearest sound possible.<br />
        <a href="{{block type="core/template" key="block3" template="iz/link_to_product.phtml"}}">Read more</a>
    </div>
	{{block type="core/template" key="block3" template="iz/custom_buy_form.phtml"}}
</div>

<div class="home-string cstring">
    <a href="{{block type="core/template" key="block4" template="iz/link_to_product.phtml"}}"><img height=
    "180" src="{{skin url='images/home-C.png'}}" width=
    "220" /></a>

    <div class="stext">
        Our powerful C String, with rich and deep tones. A new standard for
        clarity and responsiveness. If you want big, open cello sounds, this is
        the string you need.<br />
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


