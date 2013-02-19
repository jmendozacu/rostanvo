<?php /* Smarty version 2.6.18, created on 2012-07-14 15:21:29
         compiled from text://66f457c5a49854b9a76cff75ad7b6736 */ ?>
<h1 style="font-family: Arial;"><font size="4">Congratulations</font></h1>
<p style="font-family: Arial;"><font size="2">Your mail account is configured correctly and your installation is capable to send mails.</font></p>
<div style="font-family: Arial;">
    <fieldset>
        <legend><font size="2">Mail account setup</font></legend>
        <table>
            <tbody><tr>
                <td style="font-weight: bold;"><font size="2">Mail Account Name</font></td>

                <td><font size="2"><?php echo $this->_tpl_vars['account_name']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2">From Name</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['from_name']; ?>
</font></td>
            </tr>
            <tr>

                <td style="font-weight: bold;"><font size="2">From Email</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['account_email']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2">Use SMTP protocol</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['use_smtp']; ?>
</font></td>
            </tr>

            <tr>
                <td style="font-weight: bold;"><font size="2">SMTP Server</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['smtp_server']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2">SMTP Port</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['smtp_port']; ?>
</font></td>

            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2">SMTP Authentication</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['smtp_auth']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2">Use SSL connection</font></td>

                <td><font size="2"><?php echo $this->_tpl_vars['smtp_ssl']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2">SMTP Username</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['smtp_username']; ?>
</font></td>
            </tr>
            <tr>

                <td style="font-weight: bold;"><font size="2">Is default mail account</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['is_default']; ?>
</font></td>
            </tr>
        </tbody></table>
    </fieldset>
</div>