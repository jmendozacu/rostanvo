<?php

/*********************************************************************************
 * Copyright 2009 Priacta, Inc.
 * 
 * This software is provided free of charge, but you may NOT distribute any
 * derivative works or publicly redistribute the software in any form, in whole
 * or in part, without the express permission of the copyright holder.
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *********************************************************************************/

class Mage_Pap_Block_Clicktracking extends Mage_Core_Block_Text
{
    protected function _toHtml()
    {
        $config = Mage::getSingleton('pap/config');
        if (!$config->getTrackClicks()) {
            return '';
        }

        ob_start();
        ?>
        <script type="text/javascript">
            (function () {
                var papDomain = (("https:" == document.location.protocol) ? "https://":"http://");papDomain+="<?php echo preg_replace('~^(https?://)?~', '', $config->getRemotePath()); ?>";
                var papId = 'pap_x2s6df8d';
                // adjust the ID iff it would conflict with an existing element
                if ((function(elementId){var nodes=new Array();var tmpNode=document.getElementById(elementId);while(tmpNode){nodes.push(tmpNode);tmpNode.id="";tmpNode=document.getElementById(elementId);for(var x=0;x<nodes.length;x++){if(nodes[x]==tmpNode){tmpNode=false;}}}})('pap_x2s6df8d')) {papId += '_clicktrack';}
                document.write(unescape("%3Cscript id='pap_x2s6df8d' src='" + papDomain + "/scripts/<?php echo $config->getTrackclickscript(); ?>' type='text/javascript'%3E%3C/script%3E"));
            })();
        </script>
        <?php
        $script_block = ob_get_clean();

        
        $this->addText('
          <!-- BEGIN AFFILIATE TRACKING CODE -->
          '.$script_block.'
          <script type="text/javascript">
          <!--
          papTrack();
          //-->
          </script>
          <!-- END AFFILIATE TRACKING CODE -->
        ');

/* Asynchronous version. We can't use this currently because the tracking script uses document.write, and
   that causes problems if the page is already fully loaded. Chrome also has security issues with asynchronous
   document.write.

   QualityUnit has been asked to update the script to allow for this.

        $this->addText('
<!-- BEGIN AFFILIATE TRACKING CODE -->
<script type="text/javascript">
  (function() {
    var pap_script = document.createElement("script"); pap_script.type = "text/javascript"; pap_script.async = true;
    pap_script.id = "'.$id.'";
    pap_script.src = "'.$config->getRemotePath().'/scripts/'.$config->getTrackclickscript().'";
    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(pap_script, s);
  })();

  var pap_script_init = function()
  {
    if(typeof papTrack == "function")
    {
      clearInterval(pap_script_init_interval);
      papTrack();
    }
  }
  pap_script_init_interval = setInterval("pap_script_init()", 100);
</script>
<!-- END AFFILIATE TRACKING CODE -->
        ');
*/
        
        return parent::_toHtml();
    }
}
