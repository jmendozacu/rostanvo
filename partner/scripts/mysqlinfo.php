<?php
@include('custom.php');
include('bootstrap.php');

if (@function_exists('mysql_get_server_info')) {
    $mysqlVersion = mysql_get_server_info();
} else {
    $mysqlVersion = 'unknown';
} 

echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html><head>
<style type="text/css"><!--
body {background-color: #ffffff; color: #000000;}
body, td, th, h1, h2 {font-family: sans-serif;}
pre {margin: 0px; font-family: monospace;}
a:link {color: #000099; text-decoration: none;}
a:hover {text-decoration: underline;}
table {border-collapse: collapse;}
.center {text-align: center;}
.center table { margin-left: auto; margin-right: auto; text-align: left;}
.center th { text-align: center; !important }
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccccff; font-weight: bold;}
.h {background-color: #9999cc; font-weight: bold;}
.v {background-color: #cccccc;}
i {color: #666666;}
img {float: right; border: 0px;}
hr {width: 600px; align: center; background-color: #cccccc; border: 0px; height: 1px;}
//--></style>
<title>mysqlinfo()</title><meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" /></head>
</head>
<body>
<div class="center">
<table border="0" cellpadding="3" width="600">
<tr class="h"><td>
<h1 class="p">MySQL '.$mysqlVersion.'</h1>
</td></tr>
</table><br />
';

$clientVersion = @function_exists('mysql_get_client_info')?mysql_get_client_info():'unknown';
$protoVersion = @function_exists('mysql_get_proto_info')?mysql_get_proto_info():'unknown';
$hostVersion = @function_exists('mysql_get_host_info')?mysql_get_host_info():'unknown';
//versions
echo '<h2>Versions</h2>';
echo '<table border="0" cellpadding="3" width="600">
<tr class="h"><th>Variable</th><th>Value</th></tr>';
echo '<tr><td class="e">Server version </td><td class="v">'.$mysqlVersion.' </td></tr>'; 
echo '<tr><td class="e">Client version </td><td class="v">'.$clientVersion.' </td></tr>';
echo '<tr><td class="e">Protocol version </td><td class="v">'.$protoVersion.' </td></tr>';
echo '<tr><td class="e">Client version </td><td class="v">'.$hostVersion.' </td></tr>';
echo '</table><br/>';

//variables section
echo '<h2>Server variables</h2>';
echo '<table border="0" cellpadding="3" width="600">
<tr class="h"><th>Variable</th><th>Value</th></tr>';
$result = Gpf_DbEngine_Database::getDatabase()->execute("show variables");
while ($row=$result->fetchArray()) {
    echo '<tr><td class="e">'.$row['Variable_name'].' </td><td class="v">'.$row['Value'].' </td></tr>'; 
}
echo '</table><br/>';

//server status
echo '<h2>Server status</h2>';
echo '<table border="0" cellpadding="3" width="600">
<tr class="h"><th>Variable</th><th>Value</th></tr>';
$result = Gpf_DbEngine_Database::getDatabase()->execute("SHOW STATUS");
while ($row=$result->fetchArray()) {
    echo '<tr><td class="e">'.$row['Variable_name'].' </td><td class="v">'.$row['Value'].' </td></tr>'; 
}
echo '</table><br/>';

//process list - NOT used
/*echo '<h2>Process list</h2>';
echo '<table border="0" cellpadding="3" width="600">
<tr class="h"><th>Id</th><th>User</th><th>Host</th><th>db</th><th>Command</th><th>Time</th><th>State</th><th>Info</th></tr>';
$result = Gpf_DbEngine_Database::getDatabase()->execute("SHOW PROCESSLIST");
while ($row=$result->fetchArray()) {
    echo '<tr><td class="e">'.$row['Id'].' </td><td class="v">'.$row['User'].' </td><td class="v">'.$row['Host'].' </td><td class="v">'.$row['db'].' </td><td class="v">'.$row['Command'].' </td><td class="v">'.$row['Time'].' </td><td class="v">'.$row['State'].' </td><td class="v">'.$row['Info'].' </td></tr>'; 
}
echo '</table><br/>';*/

echo '
</div>
</body>';
?>
