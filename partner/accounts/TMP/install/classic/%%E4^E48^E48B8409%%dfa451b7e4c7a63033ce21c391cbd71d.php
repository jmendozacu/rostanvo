<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:31
         compiled from text://dfa451b7e4c7a63033ce21c391cbd71d */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://dfa451b7e4c7a63033ce21c391cbd71d', 2, false),)), $this); ?>
<font size="2">
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,</span><br/><br/>
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Thank you for your registration in our affiliate program.'), $this);?>
</span><br/>
    <span style="font-family: Arial;"><br/><?php echo smarty_function_localize(array('str' => 'We review every application'), $this);?>
 <span style="font-weight: bold;"><?php echo smarty_function_localize(array('str' => 'manually'), $this);?>
</span>, <?php echo smarty_function_localize(array('str' => 'and your registration is waiting for manual approval.'), $this);?>
 <?php echo smarty_function_localize(array('str' => 'Please, be patient.'), $this);?>
</span><br/><br/>
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'After confirming your registration, you will receive one more email with all the necessary information.'), $this);?>
</span><br/><br/><span style="font-family: Arial;">--</span><br/>
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
</span><br/><br/>
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your Affiliate manager'), $this);?>
</span></font>