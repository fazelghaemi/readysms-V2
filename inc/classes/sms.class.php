<?php
/*
 * آموزش کدنویسی این پلاگین در دوره های وبسایت شکرینو
 * shokrino.com
 */
defined('ABSPATH') || exit;
class OTINO_Sms {
    public function __construct() {}

    public function listOfPanels() {
        $panels = apply_filter('smspanel_pnlt', array(
            'farazsms' => 'فراز اس ام اس',
            'modirpayamak' => 'مدیر پیامک',
            'maxsms' => 'مکس اس ام اس',
            'panelsmspro' => 'پنل اس ام اس پرو',
            'rangine' => 'رنگینه',
            'kavehnegar' => 'کاوه نگار',
            'farapayamak' => 'فرا پیامک',
            'mellipayamak' => 'ملی پیامک',
            'smsir' => 'sms.ir',
        ));
        return $panels;
    }
    public function connection() {
        $data = array(
            'panel' => otino_option('panel-sms'),
            'user' => otino_option('username_sms'),
            'pass' => otino_option('password_sms'),
            'sender' => otino_option('sender_sms'),
            'api' => otino_option('api_sms'),
            'secret' => otino_option('secret_sms'),
            'templateId' => otino_option('templateid_sms'),
            'pattern' => otino_option('pattern_sms'),
            'variable' => str_replace('%', '', otino_option('variable_sms')),
        );
        return $data;
    }
    public function sendSmsPattern($to) {
        $conn = $this->connection();
        $sms_panel = $conn['panel'];
        if($sms_panel == "farazsms" or $sms_panel == "maxsms" or $sms_panel == "modirpayamak" or $sms_panel == "panelsmspro" or $sms_panel == "rangine") {
            $return = $this->ippanel_pattern($conn['user'],$conn['pass'],$conn['sender'],$to,$conn['pattern'],$conn['variable']);
        } elseif($sms_panel == "kavehnegar") {
            $return = $this->kavehnegar_pattern($conn['api'],$to,$conn['pattern']);
        } elseif($sms_panel == "melipayamak" or $sms_panel == "farapayamak") {
            $return = $this->melipayamak_pattern($conn['user'],$conn['pass'],$to,$conn['pattern']);
        } elseif($sms_panel == "smsir") {
            $return = $this->smsir_pattern($conn['api'],$conn['secret'],$to,$conn['templateId'],$conn['pattern']);
        } else {
            $return = "پنل پیامکی انتخابی شما پشتیبانی نمیشود!";
        }
        return $return;
    }
    public function ippanel_pattern($user,$pass,$from,$to,$pattern,$variable) {
        try {
            if(class_exists('SoapClient')) {
                $client = new SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
                if (!is_array($to)) {
                    $to = array($to);
                }
                $random_code = (new OTINO_Ajax())->generatePattern();
                $input_data = array($variable => "$random_code");
                $response = $client->sendPatternSms($from, $to, $user, $pass, $pattern, $input_data);
                if (is_numeric($response)) {
                    return $random_code;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (SoapFault $e) {
            echo "SOAP Error: " . $e->getMessage();
            return false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    public function kavehnegar_pattern($api,$to,$pattern) {
        try {
            $random_code = (new OTINO_Ajax())->generatePattern();
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://api.kavenegar.com/v1/'.$api.'/verify/lookup.json/');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'charset: utf-8'
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array(
                "receptor" => $to,
                "token" => $random_code,
                "template" => $pattern
            )));

            $response = curl_exec($curl);
            curl_close($curl);

            $response_data = json_decode($response, true);
            if (isset($response_data['return']['status']) && $response_data['return']['status'] == 200) {
                return $random_code;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    public function melipayamak_pattern($user,$pass,$to,$pattern) {
        try {
            if(class_exists('SoapClient')) {
                $client = new SoapClient("http://api.payamak-panel.com/post/Send.asmx?wsdl");
                if (is_array($to)) {
                    $to = $to[0];
                }
                $random_code = (new OTINO_Ajax())->generatePattern();
                $input_data = array(
                    'username' => $user,
                    'password' => $pass,
                    'text' => array($random_code),
                    'to' => $to,
                    'bodyId' => $pattern
                );
                $response = $client->SendByBaseNumber($input_data)->SendByBaseNumberResult;
                if (is_numeric($response)) {
                    return $random_code;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (SoapFault $e) {
            echo "SOAP Error: " . $e->getMessage();
            return false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    public function get_token_smsir($api,$secret) {
        $postData = array(
            'UserApiKey' => $api,
            'SecretKey' => $secret,
            'System' => 'php_rest_v_2_0'
        );
        $postString = json_encode($postData);
        $APIURL = "https://ws.sms.ir/";
        $ch = curl_init($APIURL."api/Token");
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            )
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result);

        $resp = false;
        $IsSuccessful = '';
        $TokenKey = '';
        if (is_object($response)) {
            $IsSuccessful = $response->IsSuccessful;
            if ($IsSuccessful == true) {
                $TokenKey = $response->TokenKey;
                $resp = $TokenKey;
            } else {
                $resp = false;
            }
        }
        return $resp;
    }
    public function smsir_pattern($api,$secret,$to,$template_id,$pattern) {
        $random_code = (new OTINO_Ajax())->generatePattern();
        $params = json_encode([
            "mobile" => $to,
            "templateId" => $template_id,
            "parameters" => [['name' => $pattern, 'value' => (string) $random_code]],
        ]);
        $token = $this->get_token_smsir($api,$secret);
        $auth = array(
            'Content-Type: application/json',
            "X-API-KEY: {$token}"
        );
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.sms.ir/v1/send/verify',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => $auth,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($response,true);
            if($res['status'] == true){
                if (is_numeric($response)) {
                    return $random_code;
                } else {
                    return false;
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
new OTINO_Sms;