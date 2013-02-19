<?php /* Smarty version 2.6.18, created on 2012-07-11 05:35:04
         compiled from banner_params_text_link.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'banner_params_text_link.tpl', 5, false),)), $this); ?>
<!-- banner_params_text_link -->

<div class="BannerParameterTextLink">
<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Text Link Preview'), $this);?>
</legend> 
<?php echo "<div id=\"preview\"></div>"; ?>
</fieldset>
<?php echo "<div id=\"wrapperid\"></div>"; ?>
<?php echo "<div id=\"data1\" class=\"TextLink\"></div>"; ?>
<?php echo "<div id=\"data2\" class=\"TextLink\"></div>"; ?>
<br />
<?php echo "<div id=\"target\"></div>"; ?>
</div>