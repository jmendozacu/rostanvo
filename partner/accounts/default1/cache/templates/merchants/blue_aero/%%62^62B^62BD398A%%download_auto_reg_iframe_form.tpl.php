<?php /* Smarty version 2.6.18, created on 2012-05-29 04:01:26
         compiled from download_auto_reg_iframe_form.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'download_auto_reg_iframe_form.tpl', 4, false),)), $this); ?>
<!-- download_auto_reg_iframe_form -->

<div class="TabDescription">
<h3><?php echo smarty_function_localize(array('str' => 'Iframe link/banner code form'), $this);?>
</h3>
<?php echo smarty_function_localize(array('str' => 'If you cannot use PHP code on your HTML pages, you can use iframe codes. They are more simply, but you cannot customize CSS styles.'), $this);?>

<br/>
<?php echo smarty_function_localize(array('str' => 'NOTE: You need to insert correct banner ID into banner code form as url parameter bannerid instead of \'11110001\'.'), $this);?>

</div>

<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Get your iframe link/banner code form'), $this);?>
</legend>
<?php echo smarty_function_localize(array('str' => 'Copy and paste the code below to your web page'), $this);?>

<?php echo "<div id=\"formSource\" class=\"FormSource\"></div>"; ?>
</fieldset>

<br/>
<?php echo smarty_function_localize(array('str' => 'Preview'), $this);?>

<hr>
<?php echo "<div id=\"formPreview\"></div>"; ?>