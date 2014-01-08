<?php
/**
 * xBilling configuration settings
 * Version : 100
 * @author Aderemi Adewale (modpluz @ ZPanel Forums)
 * Email : goremmy@gmail.com
 * @desc XWMS Functions
*/
if(!isset($dir)){
    $dir = '';
}

require_once($dir.'classes/xmwsclient.class.php');

function getSettings(){
    global $cfg;
    $settings = new xmwsclient();
    
    $settings->InitRequest($cfg['panel_url'], 'xbilling', 'GetSettings', $cfg['api_key']);
    $settings->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid>');

    $res = $settings->XMLDataToArray($settings->Request($settings->BuildRequest()));

    if ($res['xmws']['response'] != '1101') {
        die("API Error: " . $res['xmws']['content']);
    }
    return $res['xmws']['content']['settings'];
}

function getPackages(){
    global $cfg;
    $pkg = new xmwsclient();
    
    $pkg->InitRequest($cfg['panel_url'], 'xbilling', 'GetHostingPackages', $cfg['api_key']);
    $pkg->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid>');

    $res = $pkg->XMLDataToArray($pkg->Request($pkg->BuildRequest()));
    if ($res['xmws']['response'] != '1101') {
        die("API Error: " . $res['xmws']['content']);
    }
    if(isset($res['xmws']['content']['packages'])){
        return $res['xmws']['content']['packages'];
    }
}

function checkUserName($username){
    global $cfg;
    if($username){
        $xmws = new xmwsclient();
        
        $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'CheckUserName', $cfg['api_key']);
        $xmws->SetRequestData('<zpx_user>' .$username. '</zpx_user>');

        $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

        if ($res['xmws']['response'] != '1101') {
            die("API Error: " . $res['xmws']['content']);
        }
        return $res['xmws']['content']['result']['user_exists'];
    }
}

function getPackageName($pkg_id){
    global $cfg;
    if($pkg_id){
        $pkg = new xmwsclient();
        
        $pkg->InitRequest($cfg['panel_url'], 'xbilling', 'GetPackageName', $cfg['api_key']);
        //$pkg->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid>');
        $pkg->SetRequestData('<package_id>' .$pkg_id. '</package_id>');

        $res = $pkg->XMLDataToArray($pkg->Request($pkg->BuildRequest()));

        if ($res['xmws']['response'] != '1101') {
            die("API Error: " . $res['xmws']['content']);
        }
        return $res['xmws']['content']['package']['name'];
    }
}

function getPeriodInfo($pkg_id, $pid){
    global $cfg;
    if($pkg_id && $pid){
        $xmws = new xmwsclient();        
        $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'GetPeriodInfo', $cfg['api_key']);
        $xmws->SetRequestData('<package_id>' .$pkg_id. '</package_id><period_id>' .$pid. '</period_id>');

        $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

        if ($res['xmws']['response'] != '1101') {
            die("API Error: " . $res['xmws']['content']);
        }
        return $res['xmws']['content']['period'];
    }
}

function registerUser(){
    global $cfg;
    if(is_array($_POST)){
        $post_parms = '<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid>';
        $xmws = new xmwsclient();        
        $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'CreateNewAccount', $cfg['api_key']);
           
        
        foreach($_POST as $fld=>$value){
            if($fld != 'btn_submit' && $fld != 'section'){
                if(!$value){
                    $value = 'n/a';
                }
                $post_parms .= '<'.$fld.'>' .$value. '</'.$fld.'>';
            }
        }
        
        if($post_parms){
            $xmws->SetRequestData($post_parms);
            $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));
        }
        
        if ($res['xmws']['response'] != '1101') {
            die("API Error: " . $res['xmws']['content']);
        }
        
        $ret['error'] = 0;
        
        if(!isset($res['xmws']['content']['invoice']['reference']) && isset($res['xmws']['content']['invoice']['message'])){
            $ret['error'] = 1;
            $ret['result'] = $res['xmws']['content']['invoice']['message'];
        } elseif(isset($res['xmws']['content']['invoice']['reference']) && !isset($res['xmws']['content']['invoice']['message'])){
            $ret['result'] = $res['xmws']['content']['invoice']['reference'];
        }
        return $ret;
    }
}

function fetchInvoice($reference){
    global $cfg;
    if($reference){
        $xmws = new xmwsclient();        
        $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'GetInvoiceInfo', $cfg['api_key']);
        $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid><ref>' .$reference. '</ref>');

        $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

        if ($res['xmws']['response'] != '1101') {
            die("API Error: " . $res['xmws']['content']);
        }        
        
        $ret['error'] = 0;
        
        if($res['xmws']['content']['invoice']['error'] == 1){
            $ret['error'] = 1;
            $ret['message'] = $res['xmws']['content']['invoice']['message'];
        } else {
            $ret['result'] = $res['xmws']['content']['invoice'];
        }
        return $ret;
    }
}


function fetchUserInfo($user_id){
    global $cfg;
    if($user_id){
        $xmws = new xmwsclient();        
        $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'GetUserInfo', $cfg['api_key']);
        $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid><uid>' .$user_id. '</uid>');

        $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

        if ($res['xmws']['response'] != '1101') {
            die("API Error: " . $res['xmws']['content']);
        }        
        
        $ret['error'] = 0;
        
        if($res['xmws']['content']['user']['error'] == 1){
            $ret['error'] = 1;
            $ret['message'] = $res['xmws']['content']['user']['message'];
        } else {
            $ret['result'] = $res['xmws']['content']['user'];
        }

        return $ret;
    }
}

function fetchPaymentMethods(){
    global $cfg;

    $xmws = new xmwsclient();        
    $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'GetPaymentMethods', $cfg['api_key']);
    $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid>');
    $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

    if ($res['xmws']['response'] != '1101') {
        die("API Error: " . $res['xmws']['content']);
    }        
        
    $ret['error'] = 0;
        
    if(isset($res['xmws']['content']['methods']) && $res['xmws']['content']['methods']['error'] == 1){
        $ret['error'] = 1;
        $ret['message'] = $res['xmws']['content']['methods']['message'];
    } else {
        $ret['result'] = $res['xmws']['content']['options'];
    }
    //print_r($ret);
    return $ret;   
}

function PaymentOptionHTML($id, $html, $invoice_info){
    global $cfg, $settings;
    
    if($id && $html){
        $xmws = new xmwsclient();        
        $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'GetPaymentCustomFields', $cfg['api_key']);
        $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid><id>' .$id. '</id>');
        $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

        if ($res['xmws']['response'] != '1101') {
            die("API Error: " . $res['xmws']['content']);
        }        
            
        $ret['error'] = 0;
            
        if(isset($res['xmws']['content']['methods']) && $res['xmws']['content']['methods']['error'] == 1){
            $ret['error'] = 1;
            $ret['message'] = $res['xmws']['content']['fields']['message'];
        } else {
            if(is_array($res['xmws']['content']['fields'])){
               //replace system-wide tags
               if(is_array($invoice_info)){
                    $html = str_replace("{{country_code}}", $settings['country_code'], $html);
                    $html = str_replace("{{invoice_desc}}", $invoice_info['desc'], $html);
                    $html = str_replace("{{invoice_id}}", $invoice_info['reference'], $html);
                    $html = str_replace("{{invoice_amount}}", $invoice_info['total_amount'], $html);
                    $html = str_replace("{{currency}}", $settings['currency'], $html);
                    $html = str_replace("{{payment_method_id}}", $id, $html);
               }
               $fields = $res['xmws']['content']['fields'];
               //replace custom user tags
                foreach($fields as $html_field){
                    $field = json_decode($html_field,true);
                    if(is_array($field)){
                        if($field['name']){
                           $html = str_replace("{{".$field['name']."}}", $field['value'], $html);
                        }
                    }
                }
            }
           $ret = $html;
          //$ret['result'] = $res['xmws']['content']['fields'];
        }
        return $ret;    
    }
}

function PaymentOptionFields($id){
    global $cfg;
    
    $ret = array();
    if($id){
        $xmws = new xmwsclient();        
        $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'GetPaymentCustomFields', $cfg['api_key']);
        $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid><id>' .$id. '</id>');
        $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

        if(is_array($res['xmws']['content']['fields'])){
               $fields = $res['xmws']['content']['fields'];
                foreach($fields as $html_field){
                    $field = json_decode($html_field,true);
                    if(is_array($field)){
                        if($field['name']){
                            $ret[$field['name']] = $field['value'];
                        }
                    }
                }
        }
        return $ret;   
    }
}

function UpdateInvoice($invoice_reference, $transaction_id, $payment_method_id, $payment_date){
    global $cfg;

    if($invoice_reference && $transaction_id && $payment_method_id && $payment_date){
        $xmws = new xmwsclient();        
        $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'CompleteInvoice', $cfg['api_key']);
        $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid><ref>' .$invoice_reference. '</ref><transaction_id>' .$transaction_id. '</transaction_id><date>' .$payment_date. '</date><payment_method_id>' .$payment_method_id. '</payment_method_id>');
        $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

        if ($res['xmws']['response'] != '1101') {
            return "API Error: " . $res['xmws']['content'];
        }        
    }        
}

function InvoiceReminder(){
    global $cfg;
    
    $xmws = new xmwsclient();        
    $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'InvoiceReminder', $cfg['api_key']);
    $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid>');
     $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

     if ($res['xmws']['response'] != '1101') {
         return "API Error: " . $res['xmws']['content'];
     }
}

function RenewalReminders(){
    global $cfg;
    
    $xmws = new xmwsclient();        
    $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'RemindDomainExpiration', $cfg['api_key']);
    $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid>');
     $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

     if ($res['xmws']['response'] != '1101') {
         return "API Error: " . $res['xmws']['content'];
     }    
}

function DisableExpiredDomains(){
    global $cfg;
    
    $xmws = new xmwsclient();        
    $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'DisableExpiredDomains', $cfg['api_key']);
    $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid>');
     $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));

     if ($res['xmws']['response'] != '1101') {
         return "API Error: " . $res['xmws']['content'];
     }    
}

function DeleteExpiredDomains(){
    global $cfg;
    
    $xmws = new xmwsclient();        
    $xmws->InitRequest($cfg['panel_url'], 'xbilling', 'DomainExpireDelete', $cfg['api_key']);
    $xmws->SetRequestData('<zpx_uid>' .$cfg['zpx_uid']. '</zpx_uid>');
     $res = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()));
    var_dump($res);
     if ($res['xmws']['response'] != '1101') {
         return "API Error: " . $res['xmws']['content'];
     }        
    
}

function js_header(){
    global $cfg, $packages, $settings, $package_id, $period_id, $section;

    $js_str = "
    function getServicePeriods(pkg_id){
        var _packages = {};
        
        ";
            if(is_array($packages)){
                $js_str .= "var srvc_periods;\n";
                foreach($packages as $package){
                    if($package['service_periods']){
                        $srvc_periods = json_decode($package['service_periods'],true);

                        $js_str .= "srvc_periods = {};\n";
                        //echo("srvc_periods[".$package['id']."] = new Object();\n");
                        if(is_array($srvc_periods)){
                            foreach($srvc_periods as $idx=>$item){
                                //$itm_id = $package['id'].'_'.$item['id'];
                                $js_str .= "srvc_periods[".$item['id']."] = new Object();\n";
                                $js_str .= "srvc_periods[".$item['id']."]['id'] = '".$item['id']."';\n";
                                $js_str .= "srvc_periods[".$item['id']."]['duration'] = '".$item['duration']."';\n";
                                $js_str .= "srvc_periods[".$item['id']."]['amount'] = '".number_format($item['amount'],2)."';\n";
                            }
                        }
                    }
                    $js_str .= "_packages[".$package['id']."] = srvc_periods;\n";
                }
            }
        
        $js_str .= "return _packages[pkg_id];
    }";
    
    $js_str .= "
    function _select_pkg(pkg_id){
        if(pkg_id){
            var _selected_pid = $('#package_id').val();
            var _srvc_periods = getServicePeriods(pkg_id);
            var _selected_period = '';
    ";
     if(isset($period_id)){
            $js_str .= "_selected_period = '$period_id';";
     }
            
     $js_str .= "
            if(_selected_pid){
                $('#selected_pkg_'+_selected_pid).hide();
                $('#select_pkg_'+_selected_pid).show();
            }
            
            if(_srvc_periods){
                var _period_id;
                var _period_amt;
                var _period_duration;
                var _periods_html = '<div align=\"left\" style=\"margin-left: 50px;\"><p><br>';
                
                $.each(_srvc_periods, function(idx, item){
                    $.each(item, function(itm_idx, itm_val) {
                         if(itm_idx == 'id'){
                            _period_id = itm_val;
                         }
                         if(itm_idx == 'duration'){
                            _period_duration = itm_val;
                         }
                         if(itm_idx == 'amount'){
                            _period_amt = itm_val;
                         }
                    });
                    
                    if(_period_id && _period_amt && _period_duration){
                        if(_period_id > 0){
                            _periods_html += '<input type=\"radio\" name=\"package_period\" id=\"package_pid\" value=\"'+_period_id+'\" onclick=\"_set_period('+_period_id+');\"';
                            if(_selected_period == _period_id){
                                _periods_html += ' checked=\"checked\"';
                            }
                            _periods_html += '> '+_period_duration+ ' Mes';
                            if(_period_duration > 1){
                                _periods_html += 'es';
                            }
                            _periods_html += ' por '+_period_amt+' ".$settings['currency']."<br>';
                        } else {
                            _periods_html = 'Este plan es gratuito';
                            _set_period(_period_id);
                        }
                        /*_periods_html += '<input type=\"radio\" name=\"package_period\" id=\"package_pid\" value=\"'+_period_id+'\" /> '+_period_duration+ ' Month @ '+_period_amt+' ".$settings['currency']."<br />';*/
                    }
                });
				_periods_html += '<br></p></div>';
                $('#service_period').html(_periods_html);         
            }
            
            $('#package_id').val(pkg_id);
            $('#selected_pkg_'+pkg_id).show();
            $('#select_pkg_'+pkg_id).hide();
            
        }
    }
    
    function _set_period(_pid){
        if(_pid){
            $('#period_id').val(_pid);
        }
    }
    

	$(function(){
	";
    if(isset($package_id) && isset($period_id) && $section == 1){
        $js_str .= "
        _select_pkg($package_id);
        _set_period($period_id);";
    }
	$js_str .= "});";

    return $js_str;
}
?>
