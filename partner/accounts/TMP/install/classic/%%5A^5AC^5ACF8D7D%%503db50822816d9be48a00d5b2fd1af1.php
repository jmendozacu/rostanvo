<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://503db50822816d9be48a00d5b2fd1af1 */ ?>
<font size="2">
<span style="font-family: Arial;">Dear <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
</span> <br/><br/>

<?php if (! empty ( $this->_tpl_vars['directlinks_approved'] )): ?>
<span style="font-family: Arial;">These DirectLinks have been approved:</span><br/>
<?php $_from = $this->_tpl_vars['directlinks_approved']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['approvedLink']):
?>
    <span style="font-family: Arial;"><?php echo $this->_tpl_vars['approvedLink']; ?>
</span><br/>
<?php endforeach; endif; unset($_from); ?>
<br/><br/>
<?php endif; ?>

<?php if (! empty ( $this->_tpl_vars['directlinks_declined'] )): ?>
<span style="font-family: Arial;">These DirectLinks have been declined:</span><br/>
<?php $_from = $this->_tpl_vars['directlinks_declined']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['declinedLink']):
?>
    <span style="font-family: Arial;"><?php echo $this->_tpl_vars['declinedLink']; ?>
</span><br/>
<?php endforeach; endif; unset($_from); ?>
<br/><br/>
<?php endif; ?>

<?php if (! empty ( $this->_tpl_vars['directlinks_pending'] )): ?>
<span style="font-family: Arial;">These DirectLinks are pending:</span><br/>
<?php $_from = $this->_tpl_vars['directlinks_pending']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pendingLink']):
?>
    <span style="font-family: Arial;"><?php echo $this->_tpl_vars['pendingLink']; ?>
</span><br/>
<?php endforeach; endif; unset($_from); ?>
<br/><br/>
<?php endif; ?>

<?php if (! empty ( $this->_tpl_vars['directlinks_deleted'] )): ?>
<span style="font-family: Arial;">These DirectLinks have been deleted:</span><br/>
<?php $_from = $this->_tpl_vars['directlinks_deleted']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['deletedLink']):
?>
    <span style="font-family: Arial;"><?php echo $this->_tpl_vars['deletedLink']; ?>
</span><br/>
<?php endforeach; endif; unset($_from); ?>
<br/><br/>
<?php endif; ?>

<br />
<span style="font-family: Arial;">Sincerely,</span><br/><br/>
<span style="font-family: Arial;">Your Affiliate manager</span><br/>
</font>