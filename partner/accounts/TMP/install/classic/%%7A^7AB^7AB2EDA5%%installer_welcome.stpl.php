<?php /* Smarty version 2.6.18, created on 2012-05-29 03:55:03
         compiled from installer_welcome.stpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'installer_welcome.stpl', 3, false),array('function', 'server_widget', 'installer_welcome.stpl', 7, false),)), $this); ?>
<!-- update_check_requirements -->
<p>
<?php echo smarty_function_localize(array('str' => 'Thank you for choosing Post Affiliate Pro! We hope you have as much fun using this
program as we did creating it.'), $this);?>

</p>

<?php echo smarty_function_server_widget(array('class' => 'Pap_Install_Ui_CheckRequirements'), $this);?>

<?php echo smarty_function_server_widget(array('class' => 'Pap_Install_Ui_RecommendedSettings'), $this);?>


<?php echo "<div id=\"Check\"></div>"; ?>