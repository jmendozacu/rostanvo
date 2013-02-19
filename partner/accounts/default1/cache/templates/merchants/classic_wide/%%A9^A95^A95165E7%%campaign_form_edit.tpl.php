<?php /* Smarty version 2.6.18, created on 2012-07-11 05:35:16
         compiled from campaign_form_edit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'campaign_form_edit.tpl', 5, false),)), $this); ?>
<!-- campaign_form_edit -->

<div class="Details">
    <fieldset>
        <legend><?php echo smarty_function_localize(array('str' => 'Details'), $this);?>
</legend>
        <?php echo "<div id=\"name\"></div>"; ?>
        <?php echo "<div id=\"logourl\" class=\"CampaignLogo\"></div>"; ?>
        <?php echo "<div id=\"description\"></div>"; ?>
        <?php echo "<div id=\"longdescription\"></div>"; ?>
    </fieldset>
</div>

<div class="CampaignStatus">
    <fieldset>
        <legend><?php echo smarty_function_localize(array('str' => 'Campaign status'), $this);?>
</legend>
        <div class="CampaignFormEdit_CampaignStatus"><?php echo "<div id=\"rstatus\"></div>"; ?></div>
    </fieldset>
</div>

<?php echo "<div id=\"accountid\"></div>"; ?>

<?php echo "<div id=\"rtype\"></div>"; ?>

<div class="Cookies">
    <fieldset>
        <legend><?php echo smarty_function_localize(array('str' => 'Cookies'), $this);?>
</legend>
        <?php echo "<div id=\"cookielifetime\"></div>"; ?>
        <div class="Line"></div>
        <?php echo "<div id=\"overwritecookie\" class=\"OCookies\"></div>"; ?>
    </fieldset>
</div>

<div class="LinkingMethod">
    <fieldset>
        <legend><?php echo smarty_function_localize(array('str' => 'Affiliate linking method'), $this);?>
</legend>
        <?php echo smarty_function_localize(array('str' => 'You can choose the style of URL links specially for this campaign.'), $this);?>

        <?php echo "<div id=\"linkingmethod\" class=\"LinkingMethod\"></div>"; ?>
    </fieldset>
</div>

<div class="ProductId">
    <fieldset>
        <legend><?php echo smarty_function_localize(array('str' => 'Product ID matching'), $this);?>
</legend>
        <?php echo "<div id=\"productid\" class=\"CampaignProductId\"></div>"; ?>
    </fieldset>
</div>

<?php echo "<div id=\"campaignDetailsAdditionalForm\"></div>"; ?>
<?php echo "<div id=\"campaignDetailsFeaturesPlaceholder\"></div>"; ?>

<?php echo "<div id=\"FormMessage\"></div>"; ?><br/>
<?php echo "<div id=\"SaveButton\"></div>"; ?> <?php echo "<div id=\"NextButton\"></div>"; ?>

<div class="clear"></div>