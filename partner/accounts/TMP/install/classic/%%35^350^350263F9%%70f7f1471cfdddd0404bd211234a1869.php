<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:33
         compiled from text://70f7f1471cfdddd0404bd211234a1869 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://70f7f1471cfdddd0404bd211234a1869', 1, false),array('modifier', 'currency', 'text://70f7f1471cfdddd0404bd211234a1869', 12, false),)), $this); ?>
<?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,<br>
<br>
<?php echo smarty_function_localize(array('str' => 'new weekly report was generated.'), $this);?>
<br>
<br>
<?php echo smarty_function_localize(array('str' => 'Now is'), $this);?>
 <?php echo $this->_tpl_vars['date']; ?>
 <?php echo $this->_tpl_vars['time']; ?>
<br>
<?php echo smarty_function_localize(array('str' => 'Weekly report is generated for:'), $this);?>
 <span style="font-weight: bold;"><?php echo $this->_tpl_vars['dateFrom']; ?>
 </span>- <span style="font-weight: bold;"><?php echo $this->_tpl_vars['dateTo']; ?>
</span><br>
<br>
<?php echo smarty_function_localize(array('str' => 'Impressions:'), $this);?>
 <?php echo $this->_tpl_vars['impressions']->count->all; ?>
<br>
<?php echo smarty_function_localize(array('str' => 'Clicks:'), $this);?>
 <?php echo $this->_tpl_vars['clicks']->count->all; ?>
<br>
<br>
<?php echo smarty_function_localize(array('str' => 'Number of Sales:'), $this);?>
 <?php echo $this->_tpl_vars['sales']->count->all; ?>
<br>
<?php echo smarty_function_localize(array('str' => 'Commissions per Sales:'), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['sales']->commission->all)) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
<br>
<?php echo smarty_function_localize(array('str' => 'Totalcost of Sales:'), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['sales']->totalCost->all)) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
<br>
<br>
<?php echo smarty_function_localize(array('str' => 'Number of Actions:'), $this);?>
 <?php echo $this->_tpl_vars['actions']->count->all; ?>
<br>
<?php echo smarty_function_localize(array('str' => 'Commissions per Actions:'), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['actions']->commission->all)) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
<br>
<?php echo smarty_function_localize(array('str' => 'Total cost of Actions:'), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['actions']->totalCost->all)) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
<br>
<br>
<?php echo smarty_function_localize(array('str' => 'All Commissions:'), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['transactions']->commission->all)) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
<br>
-----------------------------------<br>
<br>
<?php echo $this->_tpl_vars['commissionsList']->list; ?>