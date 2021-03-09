<?php
class SpeechRecognizeResultClient {

    const PROJECT_ID = '1000'; //'YOUR_PROJECT_ID_GOES_HERE';
    const SECRET_KEY = 'd9e23d93053f49ade2f8fce185acedd4'; //'YOUR_SECRET_KEY_GOES_HERE';
    const REQ_URL = 'https://asr.ilivedata.com/api/v1/speech/recognize/result';
    const HOST = 'asr.ilivedata.com';
    const PATH = '/api/v1/speech/recognize/result';

    public function get_result($taskId) {
        // UTC Time
        $nowDate = gmdate('Y-m-d\TH:i:s\Z');

        // Prepare parameters
        $params = array(
            'taskId'      => $taskId,
        );
        $queryBody = json_encode($params);
        //echo $queryBody, PHP_EOL;

        // Prepare stringToSign
        $data = array(
            'POST',
            self::HOST,
            self::PATH,
            $this->sha256AndHexEncode($queryBody),
            'X-AppId:' . self::PROJECT_ID,
		    'X-TimeStamp:' . $nowDate,
        );
        $stringToSign = join("\n", $data);    
        echo $stringToSign, PHP_EOL;
        // Compute signature
        $signature = $this->signAndBase64Encode($stringToSign, self::SECRET_KEY);
        echo $signature, PHP_EOL;

        // Make request
        $res = $this->request($queryBody, $signature, $nowDate);
        print_r($res);
        return $res;
    }

    private function signAndBase64Encode($data, $secretKey) {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }

    private function sha256AndHexEncode($data) {
        return hash('sha256', $data);
    }

    private function request($body, $signature, $timeStamp) {
        $_header = array(
            'Connection: Keep-Alive',
            'Cache-Control: no-cache',
            "X-AppId: " . self::PROJECT_ID,
	        "X-TimeStamp: {$timeStamp}",
            "Authorization: {$signature}",
            "Content-Type: application/json",
        );
        //print_r($_header);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_URL, self::REQ_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'PHP HttpClient v1');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_header);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

        $response['result'] = curl_exec($curl);
        $response['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        unset($curl);
        return $response;
    }
}

$speech = new SpeechRecognizeResultClient();
$taskId = 'us_b5d0c173-96e8-47b1-879c-d9444512ea12_1615195987742';
$speech->get_result($taskId);


?>