<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:33
         compiled from text://da6f1af08fbe811ddfa154d033e5e6b2 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://da6f1af08fbe811ddfa154d033e5e6b2', 1, false),array('modifier', 'currency', 'text://da6f1af08fbe811ddfa154d033e5e6b2', 6, false),)), $this); ?>
<?php echo smarty_function_localize(array('str' => 'Dear merchant'), $this);?>
,<br>
<br>
<?php echo smarty_function_localize(array('str' => 'Today is pay day.'), $this);?>
<br>
<?php echo smarty_function_localize(array('str' => 'Now is'), $this);?>
 <?php echo $this->_tpl_vars['date']; ?>
 <?php echo $this->_tpl_vars['time']; ?>
<br>
<br>
<?php echo smarty_function_localize(array('str' => 'Payout amount:'), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['amounttopay'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
<br><?php echo smarty_function_localize(array('str' => 'Approved commissions:'), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['approvedcommissions'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
<br>
<?php echo smarty_function_localize(array('str' => 'Pending commissions:'), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['pendingcommissions'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
<br>
<?php echo smarty_function_localize(array('str' => 'Declined commissions:'), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['declinedcommissions'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>