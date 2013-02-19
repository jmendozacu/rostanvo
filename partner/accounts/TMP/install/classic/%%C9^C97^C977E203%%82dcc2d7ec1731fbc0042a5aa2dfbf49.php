<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://82dcc2d7ec1731fbc0042a5aa2dfbf49 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://82dcc2d7ec1731fbc0042a5aa2dfbf49', 2, false),array('modifier', 'currency', 'text://82dcc2d7ec1731fbc0042a5aa2dfbf49', 4, false),)), $this); ?>
<font style="font-family: Arial;" size="2">
<?php echo smarty_function_localize(array('str' => 'Dear'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,<br/><br/>
<?php echo smarty_function_localize(array('str' => 'One of your sub-affiliates'), $this);?>
 </font><font style="font-family: Arial;" size="2"><?php echo smarty_function_localize(array('str' => 'made a sale/lead.'), $this);?>
<br/><br/>
<strong>.:<?php echo smarty_function_localize(array('str' => 'Sale/lead preview'), $this);?>
:.</strong><br/><?php echo smarty_function_localize(array('str' => 'Total cost'), $this);?>
: <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['totalcost'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
</strong><br/>
<?php echo smarty_function_localize(array('str' => 'Product ID'), $this);?>
: <strong><?php echo $this->_tpl_vars['productid']; ?>
</strong><br/><br/><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
<br/><br/>
<?php echo smarty_function_localize(array('str' => 'Your Affiliate manager'), $this);?>
<br/>
</font>