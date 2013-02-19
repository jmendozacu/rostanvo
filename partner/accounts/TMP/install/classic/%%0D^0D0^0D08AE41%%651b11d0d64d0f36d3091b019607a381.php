<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:31
         compiled from text://651b11d0d64d0f36d3091b019607a381 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://651b11d0d64d0f36d3091b019607a381', 3, false),)), $this); ?>
<p style="font-family: Arial;">
    <font size="2">
        <?php echo smarty_function_localize(array('str' => 'Hello'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,<br><br><?php echo smarty_function_localize(array('str' => 'we have received new password request for your account'), $this);?>
 <?php echo $this->_tpl_vars['username']; ?>
.
    </font>
</p>
<p style="font-family: Arial;">
    <font size="2">
        <?php echo smarty_function_localize(array('str' => 'If this request was initiated by you, please click'), $this);?>
 <span style="font-weight: bold;"><?php echo $this->_tpl_vars['newPasswordLink']; ?>
</span> <?php echo smarty_function_localize(array('str' => 'or copy URL'), $this);?>
 <span style="font-weight: bold;">http://<?php echo $this->_tpl_vars['newPasswordUrl']; ?>
</span> <?php echo smarty_function_localize(array('str' => 'to your browser and change your password.'), $this);?>
<a style="text-decoration: underline; color: rgb(0, 0, 255); font-weight: bold;" href="http://<?php echo $this->_tpl_vars['newPasswordUrl']; ?>
"></a><br>
    </font>
</p>
<p style="font-family: Arial;">
    <font size="2">
        <?php echo smarty_function_localize(array('str' => 'This request is valid until'), $this);?>
 <span style="font-weight: bold; color: rgb(221, 34, 71);"><?php echo $this->_tpl_vars['validUntil']; ?>
</span>
    </font>
</p>
<font size="2">
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Sincerely,'), $this);?>
</span><br/><br/>
    <span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Quality Unit Team'), $this);?>
</span>
</font>