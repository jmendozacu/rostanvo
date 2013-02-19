<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:33
         compiled from text://68380e62b47919411713d8b204f53eb3 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://68380e62b47919411713d8b204f53eb3', 1, false),)), $this); ?>
<?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,

<br><br><?php echo smarty_function_localize(array('str' => 'You are invited to campaign'), $this);?>
 <?php echo $this->_tpl_vars['campaignname']; ?>
.
<br>
<?php echo smarty_function_localize(array('str' => 'Campaign description'), $this);?>
: <span style="font-weight: bold;"><?php echo $this->_tpl_vars['campaigndescription']; ?>
</span>

<br><br><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>


<br><?php echo smarty_function_localize(array('str' => 'Your Affiliate manager'), $this);?>
