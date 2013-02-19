<html>
<body>
<form action="isecure.php?PDebug=Y" method=post>
User (cookie): <input id="pap_dx8vc2s5" type="text" name="xxxVar1" value=""><br />
Amount: <input type="text" name="xxxAmount" value="120.50"><br />
Transaction ID: <input type="text" name=SalesOrderNumber value="AB_12345"><br />

Customer name: <input type="text" name=xxxName value="Firstname Lastname"><br />
Customer email: <input type="text" name=xxxEmail value="user@name.com"><br />
Customer city: <input type="text" name=xxxCity value="User City"><br />
Customer address: <input type="text" name=xxxAddress value="User Address"><br />
Tax: <input type="text" name="xxxTotalTax" value=""><br />

<input type="hidden" name="DoubleColonProducts" value="Price::Qty::Code::Description::Flags::|120.50::1::test::test prod::asdf::">
<input type="hidden" name="NiceVerbage" value="Approved">
<input type="hidden" name="mc_currency" value="0">

<input type="hidden" name="PDebug" value="Y">
<input type="submit" value="Test sale with additional parameters">
<script id="pap_x2s6df8d" src="../../scripts/notifysale.php" type="text/javascript">
</script>
</form>

<form action="isecure.php?PDebug=Y" method=post>
<?php
$req = "MerchantNumber=11530; CustomerName=Russ Hunter; xxxName=Russ Hunter; CustomerCompany=; xxxCompany=; CustomerAddress=109 John St. E.; xxxAddress=109 John St. E.; CustomerCity=Waterloo; xxxCity=Waterloo; CustomerProvince=ON; xxxProvince=ON; CustomerCountry=CA; xxxCountry=CA; CustomerPostalCode=N2J 1G2; xxxPostal=N2J 1G2; CustomerEmail=russ@russhunter.com; xxxEmail=russ@russhunter.com; CustomerPhone=519-745-2379; xxxPhone=519-745-2379; CardHolder=Russ Hunter; xxxCard_Name=Russ Hunter; TimeStamp=01/09/2012 10:55:53; Live=1; Currency=0; receiptnumber=1350471083.66B1; SalesOrderNumber=11473; ReturnURL=http://www.realestateword.com/completesignup.php?type=fullplus; Language=EN; UserAgent=Mozilla/5.0 (Windows NT 6.0;WOW64;rv:8.0.1) Gecko/20100101 Firefox/8.0.1; GUID=25ee2a37-e705-6ca6-849d-8514e601b006; ECI=7; ApprovalCode=484412; PageNumber=90000; Page=90000; Verbage=AP/AP/Approved; NiceVerbage=AP/AP/Approved; xxxAmount=98.31; Amount=98.31; xxxCCType=Visa; CardType=VI; xxxCardType=VI; DoubleColonProducts=50.00::1::005::Initial Set-up Fee::{GST}{HST}|37.00::1::020::RealEstateWord.com Deluxe Edition::{GST}{HST}{RB AMOUNT%3D37 STARTMONTH%3D+1 FREQUENCY%3DMONTHLY DURATION%3D0 EMAIL%3D2}|11.31::1::HST::Canadian HST Charged::{TAX}{CALCULATED}; Products=005,1|020,1; UnixTimeStamp=1326124553; xxxProcessorTime=120109105721000; Date=2012/01/09 10:55:53; RefererURL=http://www.realestateword.com/signup.php; ip_address=99.235.243.97; xxxTemp=1; xxxTransType=00; xxxCVV2ResponseCode=P; xxxRecurringTrackingNumber=1350471439.9627; xxxCard_Number=************8015; xxxCustomerIP=99.235.243.97; xxxPS2000=N082009574415037MC D; xxxALP=D; xxxVar1=default1bd6cabc6c33350edc754924cd00KnOKA; xxxShippingEmail=; xxxShippingProvince=; xxxShippingPostal=; CardHolderInfo=1; xxxShippingCompany=; xxxShippingCountry=; xxxShippingPhone=; xxxShippingName=; AVSResponseCode=Y; EntryTimeStamp=01/09/2012 10:55:53 AM; ISOCurrency=124; CVV2Result=P; xxxShippingCity=; xxxShippingAddress=";

$post = explode("; ",$req);
foreach($post as $key) {
  $row = explode("=",$key);
  echo '<input type="hidden" name="'.$row['0'].'" value="'.$row['1'].'" />'."\n";
}
?>
<input type="submit" value="Test sale with real parameters" />
</form>
 
</body>
</html>
