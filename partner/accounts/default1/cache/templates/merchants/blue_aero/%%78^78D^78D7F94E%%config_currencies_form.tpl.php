<?php /* Smarty version 2.6.18, created on 2012-05-29 04:01:14
         compiled from config_currencies_form.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'config_currencies_form.tpl', 6, false),)), $this); ?>
<!-- config_currencies_form -->

<div class="DefaultCurrency">

<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Default currency'), $this);?>
</legend>
<?php echo "<div id=\"name\"></div>"; ?>
<?php echo "<div id=\"symbol\"></div>"; ?>
<?php echo "<div id=\"cprecision\" class=\"Precision\"></div>"; ?>
<?php echo "<div id=\"wheredisplay\" class=\"WhereDisplay\"></div>"; ?>
<?php echo "<div id=\"multiple_currencies\" class=\"Multiple\"></div>"; ?>
</fieldset>

</div>

<?php echo "<div id=\"multiple_currencies_panel\"></div>"; ?>