<?php /* Smarty version 2.6.18, created on 2012-07-11 05:37:01
         compiled from theme_settings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'theme_settings.tpl', 3, false),)), $this); ?>
<!-- theme_settings.tpl -->
<fieldset>
	<legend><?php echo smarty_function_localize(array('str' => 'Selected theme'), $this);?>
</legend>
	<?php echo "<div id=\"selectedTheme\"></div>"; ?>
	<div class="ClearBoth"></div>
</fieldset>

<fieldset>
	<legend><?php echo smarty_function_localize(array('str' => 'Other themes'), $this);?>
</legend>
	<?php echo "<div id=\"otherThemes\"></div>"; ?>
	<div class="ClearBoth"></div>
</fieldset>