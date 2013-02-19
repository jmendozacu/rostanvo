<?php /* Smarty version 2.6.18, created on 2012-07-11 05:35:04
         compiled from banner_parameters_site_preview.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'banner_parameters_site_preview.tpl', 3, false),)), $this); ?>
<!-- banner_parameters_site_preview -->
<fieldset class="BannerSite">
<legend><?php echo smarty_function_localize(array('str' => 'Replicated site preview'), $this);?>
</legend>
    <div class="FormFieldLabel"><div class="Inliner"><?php echo smarty_function_localize(array('str' => 'Preview for'), $this);?>
</div></div>
    <div class="FormFieldInputContainer">
        <div class="FormFieldInput AffiliateInput"><?php echo "<div id=\"affiliate\"></div>"; ?></div>
        <div class="Inline"><?php echo "<div id=\"previewLink\"></div>"; ?></div>
    </div>
    <div class="clear" style="height: 10px;"></div>
</fieldset>