<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://be432d3142dd7ff0c74a13d5854b986e */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://be432d3142dd7ff0c74a13d5854b986e', 2, false),array('modifier', 'currency', 'text://be432d3142dd7ff0c74a13d5854b986e', 7, false),)), $this); ?>
<font size="2">
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear merchant'), $this);?>
,</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'A new sale/lead was recorded at '), $this);?>
<?php echo $this->_tpl_vars['date']; ?>
 <?php echo $this->_tpl_vars['time']; ?>
</span><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'The sale was assigned to affiliate with user name'), $this);?>
 </span>
<span style="font-weight: bold; color: rgb(51, 0, 255); font-family: Arial;"><?php echo $this->_tpl_vars['username']; ?>
</span>
<span style="font-family: Arial;">.</span><br/><br/><font size="4"><strong style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sale details'), $this);?>
:</strong></font><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Total cost'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo ((is_array($_tmp=$this->_tpl_vars['totalcost'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
</strong><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Commission for affiliate'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo ((is_array($_tmp=$this->_tpl_vars['commission'])) ? $this->_run_mod_handler('currency', true, $_tmp) : smarty_modifier_currency($_tmp)); ?>
</strong><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Order ID'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['orderid']; ?>
</strong><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Product ID'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['productid']; ?>
</strong><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'IP address'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['ip']; ?>
</strong><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Referrer Url'), $this);?>
: </span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['refererurl']; ?>
</strong><br/><br/>

<?php if ($this->_tpl_vars['statuscode'] != 'A'): ?>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'If you want to APPROVE new sale click here'), $this);?>
: </span><br/>
<a href="<?php echo $this->_tpl_vars['sale_approve_link']; ?>
"><?php echo $this->_tpl_vars['sale_approve_link']; ?>
</a>
<br/><br/>
<?php endif; ?>

<?php if ($this->_tpl_vars['statuscode'] != 'D'): ?>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'If you want to DECLINE new sale click here'), $this);?>
: </span><br/>
<a href="<?php echo $this->_tpl_vars['sale_decline_link']; ?>
"><?php echo $this->_tpl_vars['sale_decline_link']; ?>
</a>
<br/><br/>
<?php endif; ?>

<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Regards,'), $this);?>
</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your'), $this);?>
 <?php echo $this->_tpl_vars['postAffiliatePro']; ?>
.</span><br/>
</font>