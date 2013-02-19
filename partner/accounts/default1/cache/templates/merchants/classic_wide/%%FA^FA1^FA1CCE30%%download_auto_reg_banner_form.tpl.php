<?php /* Smarty version 2.6.18, created on 2012-07-11 05:35:41
         compiled from download_auto_reg_banner_form.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'download_auto_reg_banner_form.tpl', 4, false),)), $this); ?>
<!-- download_auto_reg_banner_form -->

<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Get your banner code form'), $this);?>
</legend>
<?php echo smarty_function_localize(array('str' => 'Copy and paste the code below to your web page'), $this);?>

<?php echo "<div id=\"formSource\" class=\"FormSource\"></div>"; ?>
</fieldset>

<div class="TabDescription">
<?php echo smarty_function_localize(array('str' => 'NOTE: You need to insert correct banner ID into line: $bannerid = "11110001"; instead of \'11110001\'.'), $this);?>

</div>

<br/>
<?php echo smarty_function_localize(array('str' => 'Preview'), $this);?>

<hr>
<?php echo "<div id=\"formPreview\"></div>"; ?>