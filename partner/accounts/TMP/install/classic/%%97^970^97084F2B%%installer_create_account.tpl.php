<?php /* Smarty version 2.6.18, created on 2012-05-29 03:56:07
         compiled from installer_create_account.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'installer_create_account.tpl', 2, false),)), $this); ?>
<!-- installer_create_account -->
<?php echo smarty_function_localize(array('str' => 'Create your merchant (admin) account for login to the program.<br/>
All the fields below are mandatory. Please remember your username and password, you will need them when you\'ll want to log in to your merchant panel.'), $this);?>


<br/><br/>
<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Admin Account Info'), $this);?>
</legend>
<?php echo "<div id=\"Firstname\"></div>"; ?>
<?php echo "<div id=\"Lastname\"></div>"; ?>
<?php echo "<div id=\"Username\"></div>"; ?>
<?php echo "<div id=\"Password\"></div>"; ?>
<?php echo "<div id=\"RetypePassword\"></div>"; ?>
</fieldset>