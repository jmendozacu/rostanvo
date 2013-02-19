<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://0977d0fbc4d2a80896fb7570b59a0c02 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://0977d0fbc4d2a80896fb7570b59a0c02', 2, false),array('modifier', 'currency', 'text://0977d0fbc4d2a80896fb7570b59a0c02', 5, false),)), $this); ?>
<font size="2">
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'I would like to let you know that we paid your commissions earned in our affiliate program.'), $this);?>
</span><br/><br/><strong style="font-family: Arial;">.:<?php echo smarty_function_localize(array('str' => 'Payout preview'), $this);?>
:.</strong><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Date'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['date']; ?>
</strong><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Amount paid'), $this);?>
: </span> <strong style="font-family: Arial;"><?php echo ((is_array($_tmp=$this->_tpl_vars['payment'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
</strong><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Payout method'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['payoutmethod']; ?>
</strong><br/><br/>
<br/><span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your Affiliate manager'), $this);?>
</span><br/>
</font>