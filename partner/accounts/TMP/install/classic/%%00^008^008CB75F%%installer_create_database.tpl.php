<?php /* Smarty version 2.6.18, created on 2012-05-29 03:55:23
         compiled from installer_create_database.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'installer_create_database.tpl', 2, false),)), $this); ?>
<!-- installer_create_database -->
<?php echo smarty_function_localize(array('str' => 'Create database for your PAP installation first and then fill the database access information below.<br/>
The database can be usually created in your webhosting control panel. If you have problems with it, contact your webhosting support for the correct database information.'), $this);?>


<br/><br/>
<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Database Info'), $this);?>
</legend>
<?php echo "<div id=\"Hostname\"></div>"; ?>
<?php echo "<div id=\"Username\"></div>"; ?>
<?php echo "<div id=\"Password\"></div>"; ?>
<?php echo "<div id=\"Dbname\"></div>"; ?>
</fieldset>