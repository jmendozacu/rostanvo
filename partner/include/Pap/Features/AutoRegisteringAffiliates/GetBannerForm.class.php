<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Michal Bebjak
 *   @since Version 1.0.0
 *   $Id: AffiliatesGrid.class.php 16622 2008-03-21 09:39:50Z aharsani $
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 *
 */

/**
 * @package PostAffiliatePro
 */
class Pap_Features_AutoRegisteringAffiliates_GetBannerForm extends Gpf_Object  {

    /**
     * @service affiliate_signup_form read
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Data
     */
    public function loadLink(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);

        $scriptDirUrl = Gpf_Paths::getInstance()->getFullBaseServerUrl().'include/Pap/Features/AutoRegisteringAffiliates/generatelink/';

        $linkFormHtml = '<html>
        <head>
        <style>
        .getaffiliatelink fieldset {
            border: 1px solid #CCCCCC;
            border-radius: 10px 10px 10px 10px;
            padding: 20px;
            width: 320px;
        }
        
        .getaffiliatelink legend {
            color: #333333;
            font-family: Arial,sans-serif;
            font-size: 1.3em;
            font-weight: bold;
        }
        
        #affiliatelink input[type="text"] {
            border-radius: 5px 5px 5px 5px;
            border: 1px solid #999999;
            height: 25px;
            width: 220px;
            color: #000000;
            font-family: Arial,Verdana,sans-serif;
            font-size: 0.8em;
            line-height: 140%;
            padding: 3px;
        }
        
        #affiliatelink .field {
            margin-bottom: 10px;
            margin-top: 8px;
        }
        
        #affiliatelink label {
            font-family: Arial,sans-serif;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        #affiliatelink  .message{
            font-family: Arial,Verdana,sans-serif;
            font-size: 0.8em;
        }
        
        .BannerCode {
            min-width:300px;
            min-height:50px;
            width:-moz-available;
            border-radius: 5px 5px 5px 5px;
            border: 1px solid #999999;
        }
        </style>
        
        </head>
        <body>
        
        <div class="getaffiliatelink">
            <fieldset><legend>Get your affiliate link</legend>
                <?php
                if (isset($_REQUEST["email"]) && $_REQUEST["email"] != "") {
                    $email = $_REQUEST["email"];
                    echo "Your affiliate link is:";

                    echo "<textarea class=\"BannerCode\" readonly=\"\" onclick=\"this.focus();this.select()\" onmouseover=\"this.focus();this.select()\" >";
                    readfile("' . $scriptDirUrl . 'getLink.php?email=$email");
                    echo "</textarea><br /><br />";
                    echo "<a href=\"javascript:history.go(-1)\">Back</a>";
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
        </html>';

        $linkFormPreviewHtml = '<html>
        <head>
        <style>
        .getaffiliatelink fieldset {border: 1px solid #CCCCCC;border-radius: 10px 10px 10px 10px;padding: 20px;width: 320px;}
        
        .getaffiliatelink legend {color: #333333;font-family: Arial,sans-serif;font-size: 1.3em;font-weight: bold;}
        
        #affiliatelink input[type="text"] {border-radius: 5px 5px 5px 5px;border: 1px solid #999999;height: 25px;width: 220px;color: #000000;font-family: Arial,Verdana,sans-serif;font-size: 0.8em;line-height: 140%;padding: 3px;}
        
        #affiliatelink .field {margin-bottom: 10px;margin-top: 8px;}
        
        #affiliatelink label {font-family: Arial,sans-serif;font-size: 0.8em;font-weight: bold;}
        
        #affiliatelink  .message{font-family: Arial,Verdana,sans-serif;font-size: 0.8em;}
        
        .BannerCode {min-width:300px;min-height:50px;width:-moz-available;border-radius: 5px 5px 5px 5px;border: 1px solid #999999;}
        </style>
        
        </head>
        <body>
        
        <div class="getaffiliatelink">
            <fieldset><legend>Get your affiliate link</legend>
                <form id="affiliatelink" onSubmit="return false;" action="" method="post">
                    <div class="field">
                        <label>Email Address:</label>
                        <br />
                        <input type="text" name="email">
                    </div>
                    <div class="field">
                        <input type="submit" value="Submit">
                    </div>
                </form>
            </fieldset>
        </div>
        
        </body>
        </html>';

        $data->setValue("formSource", $linkFormHtml);
        $data->setValue("formPreview", $linkFormPreviewHtml);
        
        return $data;
    }

    /**
     * @service affiliate_signup_form read
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Data
     */
    public function loadBanner(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);

        $scriptDirUrl = Gpf_Paths::getInstance()->getFullBaseServerUrl().'include/Pap/Features/AutoRegisteringAffiliates/generatelink/';

        $bannerFormHtml = '<html>
        <head>
        <style>
        .getaffiliatelink fieldset {
            border: 1px solid #CCCCCC;
            border-radius: 10px 10px 10px 10px;
            padding: 20px;
            width: 320px;
        }
        
        .getaffiliatelink legend {
            color: #333333;
            font-family: Arial,sans-serif;
            font-size: 1.3em;
            font-weight: bold;
        }
        
        #affiliatelink input[type="text"] {
            border-radius: 5px 5px 5px 5px;
            border: 1px solid #999999;
            height: 25px;
            width: 220px;
            color: #000000;
            font-family: Arial,Verdana,sans-serif;
            font-size: 0.8em;
            line-height: 140%;
            padding: 3px;
        }
        
        #affiliatelink .field {
            margin-bottom: 10px;
            margin-top: 8px;
        }
        
        #affiliatelink label {
            font-family: Arial,sans-serif;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        #affiliatelink  .message{
            font-family: Arial,Verdana,sans-serif;
            font-size: 0.8em;
        }
        
        .BannerCode {
            min-width:300px;
            min-height:50px;
            width:-moz-available;
            border-radius: 5px 5px 5px 5px;
            border: 1px solid #999999;
        }
        </style>
        
        </head>
        <body>
        
        <div class="getaffiliatelink">
            <fieldset><legend>Get your banner</legend>
                <?php
                if (isset($_REQUEST["bannerid"]) && $_REQUEST["bannerid"] != "") {
                    $bannerid = $_REQUEST["bannerid"];
                } else {
                    $bannerid = "11110001";
                }
                if (isset($_REQUEST["email"]) && $_REQUEST["email"] != "") {
                    $email = $_REQUEST["email"];
                
                    readfile("' . $scriptDirUrl . 'getBanner.php?email=$email&bannerid=$bannerid");
                    echo "<br/><br/>";
                
                    echo "Your banner code is:";
                    echo "<textarea class=\"BannerCode\" readonly=\"\" onclick=\"this.focus();this.select()\" onmouseover=\"this.focus();this.select()\" >";
                    readfile("' . $scriptDirUrl . 'getBannerCode.php?email=$email&bannerid=$bannerid");
                    echo "</textarea><br /><br />";
                    echo "<a href=\"javascript:history.go(-1)\">Back</a>";
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
        </html>';

        $bannerFormPreviewHtml = '<html>
        <head>
        <style>
        .getaffiliatelink fieldset {border: 1px solid #CCCCCC;border-radius: 10px 10px 10px 10px;padding: 20px;width: 320px;}
        
        .getaffiliatelink legend {color: #333333;font-family: Arial,sans-serif;font-size: 1.3em;font-weight: bold;}
        
        #affiliatelink input[type="text"] {border-radius: 5px 5px 5px 5px;border: 1px solid #999999;height: 25px;width: 220px;color: #000000;font-family: Arial,Verdana,sans-serif;font-size: 0.8em;line-height: 140%;padding: 3px;}
        
        #affiliatelink .field {margin-bottom: 10px;margin-top: 8px;}
        
        #affiliatelink label {font-family: Arial,sans-serif;font-size: 0.8em;font-weight: bold;}
        
        #affiliatelink  .message{font-family: Arial,Verdana,sans-serif;font-size: 0.8em;}
        
        .BannerCode {min-width:300px;min-height:50px;width:-moz-available;border-radius: 5px 5px 5px 5px;border: 1px solid #999999;}
        </style>
        
        </head>
        <body>
        
        <div class="getaffiliatelink">
            <fieldset><legend>Get your banner</legend>
                <form id="affiliatelink" onSubmit="return false;" action="" method="post">
                    <div class="field">
                        <label>Email Address:</label>
                        <br />
                        <input type="text" name="email">
                    </div>
                    <div class="field">
                        <input type="submit" value="Submit">
                    </div>
                </form>
            </fieldset>
        </div>
        
        </body>
        </html>';

        $data->setValue("formSource", $bannerFormHtml);
        $data->setValue("formPreview", $bannerFormPreviewHtml);

        return $data;
    }
    
    /**
     * @service affiliate_signup_form read
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Data
     */
    public function loadIframe(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);

        $scriptDirUrl = Gpf_Paths::getInstance()->getFullBaseServerUrl().'include/Pap/Features/AutoRegisteringAffiliates/generatelink/';

        $iframeFormHtml = '<!-- affiliate link form -->
        <iframe src="' . $scriptDirUrl . 'formlink.php" frameborder="0" width="500" height="200">
            <p>Your browser does not support iframes.</p>
        </iframe>

        <br />

        <!-- banner code form -->
        <iframe src="' . $scriptDirUrl . 'formbanner.php?bannerid=11110001" frameborder="0" width="500" height="300">
            <p>Your browser does not support iframes.</p>
        </iframe>';

        $data->setValue("formSource", $iframeFormHtml);
        $data->setValue("formPreview", $iframeFormHtml);

        return $data;
    }
}
?>
