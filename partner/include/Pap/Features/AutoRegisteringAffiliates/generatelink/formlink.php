<html>
<head>
<link rel="stylesheet" type="text/css" href="affiliatelink.css" />
</head>
<body>

<div class="getaffiliatelink">
    <fieldset><legend>Get your affiliate link</legend>
        <?php
        if (isset($_REQUEST['email']) && $_REQUEST['email'] != '') {
            $email = $_REQUEST['email'];
            echo 'Your affiliate link is:';
            echo '<textarea class="BannerCode" readonly="" onclick="this.focus();this.select()" onmouseover="this.focus();this.select()" >';
            readfile("http://localhost/PostAffiliatePro/trunk/server/include/Pap/Features/AutoRegisteringAffiliates/generatelink/getLink.php?email=$email");
            echo '</textarea><br /><br />';
            echo '<a href="javascript:history.go(-1)">Back</a>';
        } else{
        ?>
    
        <form id="affiliatelink" action="" method="post">
            <div class="field">
                <label>Email Address:</label>
                <br />
                <input type="text" name="email">
            </div>
            <div class="field">
                <input type="submit" value="Submit">
            </div>
        </form>

        <?php } ?>
    </fieldset>
</div>

</body>
</html>
