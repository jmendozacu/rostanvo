<?php /* Smarty version 2.6.18, created on 2012-07-14 15:41:55
         compiled from text://883547359106ce1a157d557a32aa1f7e */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://883547359106ce1a157d557a32aa1f7e', 1, false),)), $this); ?>
<font size="2"><span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
</span><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'You have been declined in partner program.'), $this);?>
</span><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
</span><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your Partner manager'), $this);?>
</span><br></font>