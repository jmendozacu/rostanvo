<?php /* Smarty version 2.6.18, created on 2012-07-14 15:51:14
         compiled from text://124978b61cc1ef173c9398c0fce3fe74 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://124978b61cc1ef173c9398c0fce3fe74', 2, false),array('modifier', 'currency', 'text://124978b61cc1ef173c9398c0fce3fe74', 4, false),)), $this); ?>
<font size="2">
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,</span><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'new sale / lead was registered by our affiliate program with status:'), $this);?>
 <?php echo $this->_tpl_vars['status']; ?>
.</span><br><br><font size="4"><strong style="font-family: Arial;"><?php if ($this->_tpl_vars['rawtype'] == 'U'): ?><?php echo smarty_function_localize(array('str' => 'Recurring sale'), $this);?>
<?php else: ?><?php echo smarty_function_localize(array('str' => 'Sale'), $this);?>
<?php endif; ?> <?php echo smarty_function_localize(array('str' => 'details'), $this);?>
:</strong></font><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Total cost'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo ((is_array($_tmp=$this->_tpl_vars['totalcost'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
</strong><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Commission from this sale'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo ((is_array($_tmp=$this->_tpl_vars['commission'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
</strong><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Order ID'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['orderid']; ?>
</strong><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Product ID'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['productid']; ?>
</strong><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'IP address'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['ip']; ?>
</strong><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Referrer Url'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['refererurl']; ?>
</strong><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely'), $this);?>
,</span><br><br>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your Partner manager'), $this);?>
</span>
</font>