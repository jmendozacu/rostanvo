<?php /* Smarty version 2.6.18, created on 2012-07-11 05:34:50
         compiled from banner_category_selector.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'banner_category_selector.tpl', 3, false),)), $this); ?>
<!-- banner_category_selector -->
<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Categories'), $this);?>
</legend>
<?php echo "<div id=\"grid\"></div>"; ?>
<?php echo "<div id=\"editButton\"></div>"; ?>
</fieldset>