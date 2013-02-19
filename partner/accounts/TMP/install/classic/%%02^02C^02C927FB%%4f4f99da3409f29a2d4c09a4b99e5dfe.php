<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://4f4f99da3409f29a2d4c09a4b99e5dfe */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://4f4f99da3409f29a2d4c09a4b99e5dfe', 2, false),)), $this); ?>
<font size="2">
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear merchant'), $this);?>
,</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Affiliate'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
 <?php echo smarty_function_localize(array('str' => 'added new directlink at'), $this);?>
 <?php echo $this->_tpl_vars['date']; ?>
.</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'URL'), $this);?>
: <span style="font-weight: bold;"><?php echo $this->_tpl_vars['directlink_url']; ?>
</span></span><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Note'), $this);?>
: <span style="font-weight: bold;"><?php echo $this->_tpl_vars['directlink_note']; ?>
</span></span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'If you like to APPROVE it click here'), $this);?>
: <a href="<?php echo $this->_tpl_vars['directlink_approve']; ?>
"><?php echo $this->_tpl_vars['directlink_approve']; ?>
</a></span><br/></font><font size="2"><br/><br/> 
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'If you like to DECLINE it click here'), $this);?>
: <a href="<?php echo $this->_tpl_vars['directlink_decline']; ?>
"><?php echo $this->_tpl_vars['directlink_decline']; ?>
</a></span><br/></font><font size="2"><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Regards'), $this);?>
,</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your'), $this);?>
 <?php echo $this->_tpl_vars['postAffiliatePro']; ?>
.</span>
</font>