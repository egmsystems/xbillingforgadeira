<?php
 /**
 * PayPal IPN Listener for ZPanel xBilling Module
 * Version : 100
 * @author Aderemi Adewale (modpluz @ ZPanel Forums)
 * Email : goremmy@gmail.com
 * @desc Waits for connection from PayPal and update user account / domains accordingly
*/

   require_once('config.php');
    require_once('functions/xbilling.php');
    
    $settings = getSettings();

    if(isset($_POST) && is_array($_POST)){
        //$fp = fopen('ipn_test.txt', 'a');
        //fwrite($fp, file_get_contents('php://input')."\n\n");

        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
          $keyval = explode ('=', $keyval);
          if (count($keyval) == 2)
             $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        if(function_exists('get_magic_quotes_gpc')) {
           $get_magic_quotes_exists = true;
        } 
        foreach ($myPost as $key => $value) {        
           if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
                $value = urlencode(stripslashes($value)); 
           } else {
                $value = urlencode($value);
           }
           $req .= "&$key=$value";
        }
        
         
         
        // STEP 2: Post IPN data back to paypal to validate         
        $ch = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
         
        // In wamp like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and 
        // set the directory path of the certificate as shown below.
        
        /* You probably want to replace this file once you go live */
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cert/cacert.pem');
        if( !($res = curl_exec($ch)) ) {
            //fwrite($fp, curl_error($ch)."\n\n");
            curl_close($ch);
            exit;
        }
        curl_close($ch);
         
         
        // STEP 3: Inspect IPN validation result and act accordingly         
        if (strcmp ($res, "VERIFIED") == 0) {
            // process payment            
            $payment_method_id = $_POST['custom'];
            $invoice_reference = $_POST['invoice'];
            $transaction_id = $_POST['txn_id'];
            $payment_status = $_POST['payment_status'];
            $payment_amount = $_POST['mc_gross'];
            $payment_currency = $_POST['mc_currency'];
            $receiver_email = $_POST['receiver_email'];
            $payment_date = date("Y-m-d", strtotime(urldecode($_POST['payment_date'])));
            
            
            //is this payment completed
            if($payment_status == 'Completed'){
                $invoice_info = fetchInvoice($invoice_reference);
                //fetch payment method fields
                $payment_fields = PaymentOptionFields($payment_method_id);
                //verify that receiver email matches business_email and system 
                //currency matches payment currency
				
				//fwrite($fp, "\n\n 1.".$payment_fields['business_email']. " 2.".$payment_method_id."\n\n");
				
                if($payment_fields['business_email'] == $receiver_email && $settings['currency'] == $payment_currency){
                    //verify that amount paid is the expected amount
                    if(strpos($invoice_info['result']['total_amount'], ".") === false){
                        $invoice_info['result']['total_amount'] .= '.00';
                    }
                    if($invoice_info['result']['total_amount'] == $payment_amount){
                        //make sure this invoice hasn't been processed
                        if(!$invoice_info['transaction_id']){
                            UpdateInvoice($invoice_reference, $transaction_id, $payment_method_id, $payment_date);
                        }
                        
                    }                    
                }
            
            }
        } else if (strcmp ($res, "INVALID") == 0) {
            // log for manual investigation
        }  
        
        //fclose($fp);  
    }
?>
