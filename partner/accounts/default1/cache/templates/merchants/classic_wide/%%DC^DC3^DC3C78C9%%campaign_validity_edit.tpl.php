<?php /* Smarty version 2.6.18, created on 2012-07-11 05:35:16
         compiled from campaign_validity_edit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'campaign_validity_edit.tpl', 2, false),)), $this); ?>
<!-- campaign_validity_edit -->
<div class="submenu"><?php echo smarty_function_localize(array('str' => 'Campaign capping description'), $this);?>
</div>

<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Campaign Type'), $this);?>
</legend>
<?php echo "<div id=\"rtype\"></div>"; ?>
</fieldset>

<fieldset>
	<legend><?php echo smarty_function_localize(array('str' => 'Campaign Validity'), $this);?>
</legend>
	<?php echo "<div id=\"rstatus\"></div>"; ?>
	<?php echo "<div id=\"discontinueurl\"></div>"; ?>
</fieldset>

<fieldset>
  <legend><?php echo smarty_function_localize(array('str' => 'Allowed countries'), $this);?>
</legend>
  <?php echo smarty_function_localize(array('str' => 'Choose countries for this campaign'), $this);?>

  <?php echo "<div id=\"countries\"></div>"; ?>
  <?php echo smarty_function_localize(array('str' => 'Country capping behavior'), $this);?>

  <?php echo "<div id=\"geocampaigndisplay\"></div>"; ?>
  <?php echo "<div id=\"geobannersshow\"></div>"; ?>
  <?php echo "<div id=\"geotransregister\"></div>"; ?>
</fieldset>


<?php echo "<div id=\"FormMessage\"></div>"; ?><br/>
<?php echo "<div id=\"SaveButton\"></div>"; ?>
