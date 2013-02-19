<html>
<body>

<form action="moneybookers.php?PDebug=Y" method=post>
User (cookie): <input id="pap_dx8vc2s5" type="text" name="field1" value=""><br/>
Totalcost: <input type="text" name="amount" value="120.50"><br/>
Transaction ID: <input type="text" name=transaction_id value="489841647"><br/>

Customer email: <input type="text" name=pay_from_email value="user@name.com"><br/>
MD5 sign: <input type="text" name=md5sig value="13CBFAE7EF92114757129749D42F8881"><br/> <!-- for secret word: test -->
Data1: <input type="text" name=status value="2"><br/>
Data2: <input type="text" name=mb_amount value="0.01"><br/>
Data3: <input type="text" name=mb_currency value="GBP"><br/>
Data4: <input type="text" name=merchant_id value="24287121"><br/>

<input type="submit" value="Test normal sale">
<script id="pap_x2s6df8d" src="../../scripts/notifysale.php" type="text/javascript">
</script>
</form>  
 
</body>
</html>
