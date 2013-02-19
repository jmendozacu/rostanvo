<?php /* Smarty version 2.6.18, created on 2012-10-01 12:57:56
         compiled from text://35dd3557dc32f98a37d7c84c4a4319af */ ?>
<p style="font-family: Arial;">
    <font size="2">
        Hello <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
,<br><br>we have received new password request for your account <?php echo $this->_tpl_vars['username']; ?>
.
    </font>
</p>
<p style="font-family: Arial;">
    <font size="2">
        If this request was initiated by you, please click <span style="font-weight: bold;"><?php echo $this->_tpl_vars['newPasswordLink']; ?>
</span> or copy URL <span style="font-weight: bold;">http://<?php echo $this->_tpl_vars['newPasswordUrl']; ?>
</span> to your browser and change your password.<a style="text-decoration: underline; color: rgb(0, 0, 255); font-weight: bold;" href="http://<?php echo $this->_tpl_vars['newPasswordUrl']; ?>
"></a><br>
    </font>
</p>
<p style="font-family: Arial;">
    <font size="2">
        This request is valid until <span style="font-weight: bold; color: rgb(221, 34, 71);"><?php echo $this->_tpl_vars['validUntil']; ?>
</span>
    </font>
</p>
<font size="2">
    <span style="font-family: Arial;">Sincerely,</span><br><br>
    <span style="font-family: Arial;">Rostanvo</span>
</font>