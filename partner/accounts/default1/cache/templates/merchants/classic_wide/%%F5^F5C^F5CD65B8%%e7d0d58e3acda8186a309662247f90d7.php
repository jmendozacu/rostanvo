<?php /* Smarty version 2.6.18, created on 2012-07-14 15:51:29
         compiled from text://e7d0d58e3acda8186a309662247f90d7 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://e7d0d58e3acda8186a309662247f90d7', 2, false),array('modifier', 'currency', 'text://e7d0d58e3acda8186a309662247f90d7', 4, false),)), $this); ?>
<font style="font-family: Arial;" size="2">
<?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,<br><br>
<?php echo smarty_function_localize(array('str' => 'One of your sub-affiliates'), $this);?>
 </font><font style="font-family: Arial;" size="2"><?php echo smarty_function_localize(array('str' => 'made a sale/lead.'), $this);?>
<br><br>
<strong>.:<?php echo smarty_function_localize(array('str' => 'Sale/lead preview'), $this);?>
:.</strong><br><?php echo smarty_function_localize(array('str' => 'Total cost'), $this);?>
: <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['totalcost'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
</strong><br>
<?php echo smarty_function_localize(array('str' => 'Product ID'), $this);?>
: <strong><?php echo $this->_tpl_vars['productid']; ?>
</strong><br><br><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
<br><br>
<?php echo smarty_function_localize(array('str' => 'Your Partner manager'), $this);?>
<br>
</font>