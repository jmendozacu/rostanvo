<?php /* Smarty version 2.6.18, created on 2012-07-14 15:42:24
         compiled from text://669f1a31d4b24ee654b38be7a29b2e48 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://669f1a31d4b24ee654b38be7a29b2e48', 1, false),)), $this); ?>
<font size="2"><span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
</span><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Thank you for registration in our partner program.'), $this);?>
</span><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'You have been approved and you can login using the following link'), $this);?>
: </span>
<span style="font-weight: bold; font-family: Arial;"><?php echo $this->_tpl_vars['affiliateLoginLink']; ?>
</span><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your username'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['username']; ?>
</strong><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your password'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['password']; ?>
</strong><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
</span><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your Partner manager'), $this);?>
</span><br></font>