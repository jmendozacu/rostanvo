<?php

class Mage_Pap_Block_GoogleCheckout_Link extends Mage_GoogleCheckout_Block_Link
{
    public function _toHtml()
    {
        $html = parent::_toHtml();
        
        $config = Mage::getSingleton('pap/config'); // we'll need this
        
        ob_start();
        ?>
        <script type="text/javascript">
            (function () {
                var papDomain = (("https:" == document.location.protocol) ? "https://":"http://");papDomain+="<?php echo preg_replace('~^(https?://)?~', '', $config->getRemotePath()); ?>";
                var papId = 'pap_x2s6df8d';
                // adjust the ID iff it would conflict with an existing element
                if ((function(elementId){var nodes=new Array();var tmpNode=document.getElementById(elementId);while(tmpNode){nodes.push(tmpNode);tmpNode.id="";tmpNode=document.getElementById(elementId);for(var x=0;x<nodes.length;x++){if(nodes[x]==tmpNode){tmpNode=false;}}}})('pap_x2s6df8d')) {papId += '_salestrack';}
                document.write(unescape("%3Cscript id='pap_x2s6df8d' src='" + papDomain + "/scripts/<?php echo $config->getTracksalescript(); ?>' type='text/javascript'%3E%3C/script%3E"));
            })();
        </script>
        <?php
        $script_block = ob_get_clean();
        
        // Append the script to make the affiliate tracking work
        $html .= $script_block;

        // Add a bit of script to add the additional hidden field to the form(s) with the link
        $html .= '<script type="text/javascript">';
        $html .= "var AnalyticsDataFields = document.getElementsByName('analyticsdata');";
        $html .= "for(var i = 0; i < AnalyticsDataFields.length; i++)";
        $html .= "{";
        $html .= "   var newinput = document.createElement('input');";
        $html .= "   newinput.setAttribute('type','hidden');";
        $html .= "   newinput.setAttribute('id','pap_ab78y5t4a_'+i);";
        $html .= "   newinput.setAttribute('name','pap-cookie-data');";
        $html .= "   AnalyticsDataFields[i].parentNode.insertBefore(newinput,AnalyticsDataFields[i].nextSibling);";
        // Write the tracking data to the form, rather than registering the sale immediately
        $html .= "   PostAffTracker.writeCookieToCustomField('pap_ab78y5t4a_'+i);";
        $html .= "}";
        $html .= '</script>';
        
        return $html;
    }
}