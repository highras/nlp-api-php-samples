<?php
class ProfanityClient {

    const PROJECT_ID = 'YOUR_PROJECT_ID_GOES_HERE';
    const SECRET_KEY = 'YOUR_SECRET_KEY_GOES_HERE';
    const REQ_URL = 'https://profanity.ilivedata.com/api/v2/profanity';
    const HOST = 'profanity.ilivedata.com';
    const PATH = '/api/v2/profanity';

    public function profanity($sentence, $classify, $userId, $userName) {
        // UTC Time
        $nowDate = gmdate('Y-m-d\TH:i:s\Z');

        // Prepare parameters
        $params = array(
            'q'         => $sentence,
            'classify'  => $classify,
            'userId'    => $userId,
            'userName'  => $userName,
            'timeStamp' => $nowDate,
            'appId'     => self::PROJECT_ID,
        );

        // Compute signature
        $signature = $this->signAndBase64Encode($this->stringToSign($params), self::SECRET_KEY);
        echo $signature, PHP_EOL;

        // Build query
        $data = http_build_query($params);
        echo $data, PHP_EOL;

        // Make request
        $res = $this->request($data, $signature);
        print_r($res);

        return $res;
    }

    private function stringToSign($params) {
        ksort($params);
        $data = array();
        foreach ($params as $key => $value) {
            $data[] = sprintf('%s=%s', rawurlencode($key), rawurlencode($value));
        }

        $data = array(
            'POST',
            self::HOST,
            self::PATH,
            join('&', $data),
        );
        return join("\n", $data);
    }

    private function signAndBase64Encode($data, $secretKey) {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }

    private function request($params, $signature) {
        $_header = array(
            'Connection: Keep-Alive',
            'Cache-Control: no-cache',
            "Authorization: {$signature}",
            "Content-Type: application/x-www-form-urlencoded;charset='utf-8'",
            "Expect:",
        );
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, self::REQ_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'PHP HttpClient v1');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_header);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

        $response['result'] = curl_exec($curl);
        $response['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_errno($curl)  . " : " . curl_error($curl);
        }

        curl_close($curl);
        unset($curl);
        return $response;
    }
}

$riskyContent = new ProfanityClient();
$riskyContent->profanity('加微13812123434', 1, '12345678', '李四');
$riskyContent->profanity('我日你', 0, '12345678', '李四');

?>
