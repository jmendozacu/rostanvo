<?php /* Smarty version 2.6.18, created on 2012-07-14 15:42:52
         compiled from text://641d98b0a56035b6abb0568f93fe9530 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://641d98b0a56035b6abb0568f93fe9530', 2, false),)), $this); ?>
<font size="2">
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,</span><br><br>
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Thank you for your registration in our affiliate program.'), $this);?>
</span><br>
    <span style="font-family: Arial;"><br><?php echo smarty_function_localize(array('str' => 'We review every application'), $this);?>
 <span style="font-weight: bold;"><?php echo smarty_function_localize(array('str' => 'manually'), $this);?>
</span>, <?php echo smarty_function_localize(array('str' => 'and your registration is waiting for manual approval.'), $this);?>
 <?php echo smarty_function_localize(array('str' => 'Please, be patient.'), $this);?>
</span><br><br>
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'After confirming your registration, you will receive one more email with all the necessary information.'), $this);?>
</span><br><br><span style="font-family: Arial;">--</span><br>
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
</span><br><br>
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your Affiliate manager'), $this);?>
</span></font>