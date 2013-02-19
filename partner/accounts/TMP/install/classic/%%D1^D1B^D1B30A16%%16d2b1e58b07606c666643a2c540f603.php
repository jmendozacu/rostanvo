<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:31
         compiled from text://16d2b1e58b07606c666643a2c540f603 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://16d2b1e58b07606c666643a2c540f603', 1, false),)), $this); ?>
<h1 style="font-family: Arial;"><font size="4"><?php echo smarty_function_localize(array('str' => 'Congratulations'), $this);?>
</font></h1>
<p style="font-family: Arial;"><font size="2"><?php echo smarty_function_localize(array('str' => 'Your mail account is configured correctly and your installation is capable to send mails.'), $this);?>
</font></p>
<div style="font-family: Arial;">
    <fieldset>
        <legend><font size="2"><?php echo smarty_function_localize(array('str' => 'Mail account setup'), $this);?>
</font></legend>
        <table>
            <tbody><tr>
                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'Mail Account Name'), $this);?>
</font></td>

                <td><font size="2"><?php echo $this->_tpl_vars['account_name']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'From Name'), $this);?>
</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['from_name']; ?>
</font></td>
            </tr>
            <tr>

                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'From Email'), $this);?>
</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['account_email']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'Use SMTP protocol'), $this);?>
</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['use_smtp']; ?>
</font></td>
            </tr>

            <tr>
                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'SMTP Server'), $this);?>
</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['smtp_server']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'SMTP Port'), $this);?>
</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['smtp_port']; ?>
</font></td>

            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'SMTP Authentication'), $this);?>
</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['smtp_auth']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'Use SSL connection'), $this);?>
</font></td>

                <td><font size="2"><?php echo $this->_tpl_vars['smtp_ssl']; ?>
</font></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'SMTP Username'), $this);?>
</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['smtp_username']; ?>
</font></td>
            </tr>
            <tr>

                <td style="font-weight: bold;"><font size="2"><?php echo smarty_function_localize(array('str' => 'Is default mail account'), $this);?>
</font></td>
                <td><font size="2"><?php echo $this->_tpl_vars['is_default']; ?>
</font></td>
            </tr>
        </tbody></table>
    </fieldset>
</div>