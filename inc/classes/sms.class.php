<?php
/**
 * ReadySMS SMS Handling Class using MessageWay (msgway.com) API
 * Customized for Ready Studio - readystudio.ir
 */
defined('ABSPATH') || exit;

class READYSMS_Sms {

    /**
     * The API endpoint for sending OTP messages with a custom code.
     */
    const MSGWAY_SEND_ENDPOINT = 'https://api.msgway.com/send';

    public function __construct() {
    }

    /**
     * Gathers the connection data from plugin options.
     * @return array
     */
    private function get_connection_data() {
        // Options needed for the MessageWay /send API.
        // The option names should be defined in 'config-optionino.php'.
        $data = [
            'apikey'     => readysms_option('api_key_sms'),
            'templateID' => readysms_option('template_id_sms'), // Using templateID now
        ];
        return $data;
    }

    /**
     * Sends the OTP SMS to the given number.
     * @param string $to The recipient's mobile number.
     * @return string|false The generated code on success, false on failure.
     */
    public function send_pattern_sms($to) {
        $conn = $this->get_connection_data();
        
        if (empty($conn['apikey']) || empty($conn['templateID'])) {
            // error_log('ReadySMS Error: MessageWay API Key or Template ID is not set.');
            return false;
        }
        
        // First, generate the random code ourself.
        $random_code = (new READYSMS_Ajax())->generatePattern();

        // Then, try to send it via the gateway.
        $is_sent = $this->msgway_send_otp($conn['apikey'], $to, $conn['templateID'], $random_code);

        // If it was sent successfully, return the code so it can be verified.
        if ($is_sent) {
            return $random_code;
        }

        return false;
    }

    /**
     * Handles sending the self-generated OTP code via MessageWay gateway.
     *
     * @param string $api_key      The gateway API key.
     * @param string $recipient    The recipient mobile number.
     * @param int    $template_id  The template ID from the gateway panel.
     * @param string $code         The random code to send.
     * @return bool True on success, false on failure.
     */
    private function msgway_send_otp($api_key, $recipient, $template_id, $code) {
        // Prepare the data payload as a JSON object, according to the new sample.
        $params = [
            "mobile"     => $recipient,
            "method"     => "sms",
            "templateID" => (int) $template_id,
            "code"       => (string) $code,
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => self::MSGWAY_SEND_ENDPOINT,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => json_encode($params),
            CURLOPT_HTTPHEADER     => [
                'apiKey: ' . $api_key,
                'Content-Type: application/json',
            ],
        ]);

        $response_body = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        // Check for a successful HTTP status code (e.g., 200)
        if ($http_code == 200) {
            $response_data = json_decode($response_body, true);
            // Check for the success status in the response.
            // Adjust 'status' and '1' if the actual success response is different.
            if (isset($response_data['status']) && $response_data['status'] == 1) {
                return true;
            }
        }
        
        // If the request fails, log the response for debugging.
        // error_log("ReadySMS MessageWay Error: HTTP Code: $http_code | Response: $response_body");
        return false;
    }
}

// Instantiate the class.
new READYSMS_Sms();