<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://ee5d18da6c5e3af1aa6aaeb1176f7eb1 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://ee5d18da6c5e3af1aa6aaeb1176f7eb1', 1, false),)), $this); ?>
<font size="2"><span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'You have been approved in campaign'), $this);?>
 <?php echo $this->_tpl_vars['campaignname']; ?>
 </span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your Affiliate manager'), $this);?>
</span><br/></font>