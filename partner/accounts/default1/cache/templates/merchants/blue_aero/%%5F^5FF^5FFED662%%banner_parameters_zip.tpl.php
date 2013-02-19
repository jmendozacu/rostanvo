<?php /* Smarty version 2.6.18, created on 2012-05-29 04:00:37
         compiled from banner_parameters_zip.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'banner_parameters_zip.tpl', 3, false),)), $this); ?>
<!-- banner_parameters_zip -->
<fieldset class="BannerSite">
<legend><?php echo smarty_function_localize(array('str' => 'Banner settings'), $this);?>
</legend>
    <?php echo "<div id=\"zipFile\"></div>"; ?>
    <?php echo "<div id=\"fileTypes\"></div>"; ?>
    <div class="clear" style="height: 10px;"></div>
</fieldset>
<?php echo "<div id=\"bannerPreview\"></div>"; ?>
<?php echo "<div id=\"files\"></div>"; ?>