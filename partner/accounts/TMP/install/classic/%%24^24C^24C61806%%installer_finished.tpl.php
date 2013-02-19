<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:38
         compiled from installer_finished.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'installer_finished.tpl', 3, false),)), $this); ?>
<!-- installer_finished -->

<?php echo smarty_function_localize(array('str' => 'The installation finished successfully. Thank you for choosing our product.'), $this);?>

<br/><br/>
<a href="../index.html"><?php echo smarty_function_localize(array('str' => 'Click here to go to introduction screen'), $this);?>
</a>