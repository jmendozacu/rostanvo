<?php /* Smarty version 2.6.18, created on 2012-07-11 05:37:01
         compiled from template_form_variables.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'template_form_variables.tpl', 2, false),)), $this); ?>
<!-- template_form_variables -->
<h4><?php echo smarty_function_localize(array('str' => 'You can use following variables in template:'), $this);?>
</h4>
<?php echo smarty_function_localize(array('str' => 'Bold fields are mandatory and have to be included in teh template'), $this);?>

<br/>
<?php echo "<div id=\"Variables\"></div>"; ?>