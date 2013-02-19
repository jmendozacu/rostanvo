<?php /* Smarty version 2.6.18, created on 2012-05-29 04:01:14
         compiled from country_select_panel.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'country_select_panel.tpl', 3, false),)), $this); ?>
<!-- country_select_panel -->
<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Country settings'), $this);?>
</legend>
<?php echo "<div id=\"countryMultiSelect\"></div>"; ?>
</fieldset>