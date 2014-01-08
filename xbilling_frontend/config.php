
<?php
  /**
   * API connection settings for xBilling
   * Version : 100
   * @author Aderemi Adewale (modpluz @ ZPanel Forums)
   * Email : goremmy@gmail.com
   * @desc This allows front-end billing package interact with the backend module
  */
                    
  // Config;
     $cfg = array();
     $cfg['api_key'] = '22105787fc4cd7525b4c5051ea69b06a';
     $cfg['panel_url'] = 'zpanel.gadeira.es';
     $cfg['zpx_uid'] = 1;
                    
     if(strpos($cfg['panel_url'], 'http') === false){
         $cfg['panel_url'] = 'http://'.$cfg['panel_url'];
     }                    
?>