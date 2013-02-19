<html>
<head>
<link rel="stylesheet" type="text/css" href="affiliatelink.css" />
</head>
<body>

<div class="getaffiliatelink">
    <fieldset><legend>Get your banner</legend>
        <?php
        if (isset($_REQUEST['bannerid']) && $_REQUEST['bannerid'] != '') {
            $bannerid = $_REQUEST['bannerid'];
        } else {
            $bannerid = '11110001';
        }

        if (isset($_REQUEST['email']) && $_REQUEST['email'] != '') {
            $email = $_REQUEST['email'];
        
            readfile("http://localhost/PostAffiliatePro/trunk/server/include/Pap/Features/AutoRegisteringAffiliates/generatelink/getBanner.php?email=$email&bannerid=$bannerid");
            echo '<br/><br/>';
        
            echo 'Your banner code is:';
            echo '<textarea class="BannerCode" readonly="" onclick="this.focus();this.select()" onmouseover="this.focus();this.select()" >';
            readfile("http://localhost/PostAffiliatePro/trunk/server/include/Pap/Features/AutoRegisteringAffiliates/generatelink/getBannerCode.php?email=$email&bannerid=$bannerid");
            echo '</textarea><br /><br />';
            echo '<a href="javascript:history.go(-1)">Back</a>';
        } else{
        ?>
    
        <form id="affiliatelink" action="" method="post">
            <div class="field">
                <label>Email Address:</label>
                <br />
                <input type="text" name="email"> <input type="hidden" name="bannerid" value="<?php echo $bannerid ?>">
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
