<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:31
         compiled from text://2f5b84242493d90d58e7094453e299ad */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://2f5b84242493d90d58e7094453e299ad', 1, false),)), $this); ?>
<font size="2"><span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Thank you for registration in our affiliate program.'), $this);?>
</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'You have been approved and you can login using the following link'), $this);?>
: </span>
<span style="font-weight: bold; font-family: Arial;"><?php echo $this->_tpl_vars['affiliateLoginLink']; ?>
</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your username'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['username']; ?>
</strong><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your password'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['password']; ?>
</strong><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your Affiliate manager'), $this);?>
</span><br/></font>