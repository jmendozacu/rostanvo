<?php /* Smarty version 2.6.18, created on 2012-05-29 04:00:37
         compiled from banner_parameters_pdf.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'banner_parameters_pdf.tpl', 11, false),)), $this); ?>
<!--    banner_parameters_pdf   -->

<div class="BannerSimplePdf">
<?php echo "<div id=\"data1\"></div>"; ?>
<?php echo "<div id=\"data3\"></div>"; ?>
<?php echo "<div id=\"data2\"></div>"; ?>
</div>

<div class="Preview">
<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Preview'), $this);?>
</legend>
<?php echo "<div id=\"affiliate\"></div>"; ?>    
<?php echo "<div id=\"infoMessageLabel\"></div>"; ?>
<?php echo "<div id=\"showPreview\"></div>"; ?>
</fieldset>
</div>