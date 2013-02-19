<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://20a0b06dd332a161cf958f2d005a13ff */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://20a0b06dd332a161cf958f2d005a13ff', 2, false),)), $this); ?>
<font size="2">
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'You have been contacted by your affiliate'), $this);?>
</span>
<span style="font-family: Arial;"> <?php echo smarty_function_localize(array('str' => 'at'), $this);?>
 <?php echo $this->_tpl_vars['date']; ?>
.<br></span></font><font size="2">
<span style="font-family: Arial;"></span><strong style="font-family: Arial;"><?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
</strong></font><font size="2">
<span style="font-family: Arial;"> <span style="font-style: italic;">(</span></span><span style="font-family: Arial; font-style: italic;"><?php echo smarty_function_localize(array('str' => 'Referrer:'), $this);?>
 <?php echo $this->_tpl_vars['refid']; ?>
)</span></font> <font size="2">
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'filled "Contact us" form.'), $this);?>
</span><br/><br/>
<strong style="font-family: Arial;"></strong></font><hr style="font-family: Arial; height: 2px;"><font size="2">
<span style="font-family: Arial;"><font size="3"><span style="font-weight: bold;"><?php echo $this->_tpl_vars['emailsubject']; ?>
</span></font></span><br/>
<span style="font-family: Arial; color: rgb(42, 42, 42);"><?php echo $this->_tpl_vars['emailtext']; ?>
</span><br/></font>
<hr style="font-family: Arial; height: 2px;">
<font size="2"><br/><span style="font-family: Arial;">
--</span><br/><span style="font-family: Arial;">
<?php echo smarty_function_localize(array('str' => 'Regards,'), $this);?>
</span><br/><br/><span style="font-family: Arial;">
<?php echo smarty_function_localize(array('str' => 'Your'), $this);?>
 <?php echo $this->_tpl_vars['postAffiliatePro']; ?>
</span><br/></font>