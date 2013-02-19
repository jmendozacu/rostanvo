<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://acfc5e4c6aed3089e79ff4d2b2ca4c0d */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://acfc5e4c6aed3089e79ff4d2b2ca4c0d', 2, false),)), $this); ?>
<font size="2">
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear merchant,'), $this);?>
</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Affiliate'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
 <?php echo smarty_function_localize(array('str' => 'signed-up to your affiliate program at'), $this);?>
 <?php echo $this->_tpl_vars['date']; ?>
.</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Affiliate name'), $this);?>
: <span style="font-weight: bold;"><?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
</span></span><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Affiliate email'), $this);?>
: <span style="font-weight: bold;"><?php echo $this->_tpl_vars['username']; ?>
</span></span><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Affiliate ID'), $this);?>
: <span style="font-weight: bold;"><?php echo $this->_tpl_vars['userid']; ?>
</span></span><br/>
<br/>

<?php if ($this->_tpl_vars['new_user_signup_status'] != 'A'): ?>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'If you want to APPROVE new affiliate click here'), $this);?>
: </span><br/>
<span style="font-family: Arial;"><a href="<?php echo $this->_tpl_vars['new_user_signup_approve_link']; ?>
"><?php echo $this->_tpl_vars['new_user_signup_approve_link']; ?>
</a></span>
<br/><br/>
<?php endif; ?>

<?php if ($this->_tpl_vars['new_user_signup_status'] != 'D'): ?>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'If you want to DECLINE new affiliate click here'), $this);?>
: </span><br/>
<span style="font-family: Arial;"><a href="<?php echo $this->_tpl_vars['new_user_signup_decline_link']; ?>
"><?php echo $this->_tpl_vars['new_user_signup_decline_link']; ?>
</a></span>
<br/><br/>
<?php endif; ?>

<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Regards'), $this);?>
,</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your'), $this);?>
 <?php echo $this->_tpl_vars['postAffiliatePro']; ?>
.</span>
</font>