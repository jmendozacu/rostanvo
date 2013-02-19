<?php /* Smarty version 2.6.18, created on 2012-07-13 09:48:14
         compiled from advanced_screen.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'advanced_screen.tpl', 3, false),)), $this); ?>
<!-- advanced_screen -->
<fieldset>
	<legend><?php echo smarty_function_localize(array('str' => 'Advanced Functionality'), $this);?>
</legend>
	<?php echo "<div id=\"AffLinkProtector\"></div>"; ?>
	<?php echo "<div id=\"SignupSubaffiliates\"></div>"; ?>
	<?php echo "<div id=\"SubIdTracking\"></div>"; ?>
	<div class="clear"></div>
</fieldset>