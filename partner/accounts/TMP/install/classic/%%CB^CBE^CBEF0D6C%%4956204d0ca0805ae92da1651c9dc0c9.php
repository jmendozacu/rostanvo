<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://4956204d0ca0805ae92da1651c9dc0c9 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://4956204d0ca0805ae92da1651c9dc0c9', 1, false),)), $this); ?>
<font size="2"><span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['parent_firstname']; ?>
 <?php echo $this->_tpl_vars['parent_lastname']; ?>
,</span><br>
<br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'You just signed up a new sub-affiliate for our Affiliate Program.'), $this);?>
</span><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'The name of the affiliate is'), $this);?>
 <span style="font-weight: bold;"><?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
</span>. </span><br>
<br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Keep up the good work!'), $this);?>
</span><br>
<br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'You can check your stats here:'), $this);?>
 <?php echo $this->_tpl_vars['affiliateLoginUrl']; ?>
</span><br>
<br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely'), $this);?>
,</span><br style="font-family: Arial;"><span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'The Quality Unit Affiliate Program team'), $this);?>
</span><br>
</font>