<?php

class Mage_Pap_Block_GoogleCheckout_Redirect extends Mage_GoogleCheckout_Block_Redirect
{
    public function getMethod ()
    {
        return 'POST'; // we're stuffing arbitrary data through, so it'll have to be posted
    }
    
    protected function _toHtml()
    {
        $form = new Varien_Data_Form();
        $form->setAction($this->getTargetURL())
            ->setId($this->getFormId())
            ->setName($this->getFormId())
            ->setMethod($this->getMethod())
            ->setUseContainer(true);
        foreach ($this->_getFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        
        //*******************************************
        // BEGIN PAP TRACKING EDITS
        //*******************************************
        
        $config = Mage::getSingleton('pap/config'); // we'll need this
        
        // Add a special field to hold the affiliate cookie data
        $form->addField('pap_ab78y5t4a', 'hidden', array('name'=>'pap-cookie-data', 'id'=>'pap_ab78y5t4a', 'value'=>''));
        
        //*******************************************
        // END PAP TRACKING EDITS
        //*******************************************
        
        $html = $form->toHtml();
        
        //*******************************************
        // BEGIN PAP TRACKING EDITS
        //*******************************************
        
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
        $html.= '<script type="text/javascript">';

        // Write the tracking data to the form, rather than registering the sale immediately
        $html.= 'PostAffTracker.writeCookieToCustomField(\'pap_ab78y5t4a\');';
        $html.= '</script>';
        
        //*******************************************
        // END PAP TRACKING EDITS
        //*******************************************
        
        $html.= '<script type="text/javascript">document.getElementById("' . $this->getFormId() . '").submit();</script>';
        return $html;
    }
}