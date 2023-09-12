<?php
class SpeechRecognizeSubmitClient {

    const PROJECT_ID = 'YOUR_PROJECT_ID_GOES_HERE';
    const SECRET_KEY = 'YOUR_SECRET_KEY_GOES_HERE';
    const REQ_URL = 'https://asr.ilivedata.com/api/v1/speech/recognize/submit';
    const HOST = 'asr.ilivedata.com';
    const PATH = '/api/v1/speech/recognize/submit';

    public function recognize($audioUrl, $languageCode, $userId, $speakerDiarization) {
        // UTC Time
        $nowDate = gmdate('Y-m-d\TH:i:s\Z');

        $config = array(
            'codec'             => 'PCM',
            'sampleRateHertz'   => 16000
        );
        $diarizationConfig = array(
            'enableSpeakerDiarization'   => $speakerDiarization
        );
        // Prepare parameters
        $params = array(
            'languageCode'      => $languageCode,
            'diarizationConfig'   => $diarizationConfig,
            'config'            => $config,
            'uri'             => $audioUrl,
            'userId'            => $userId,
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

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_errno($curl)  . " : " . curl_error($curl);
        }

        curl_close($curl);
        unset($curl);
        return $response;
    }
}

$speech = new SpeechRecognizeSubmitClient();
$audioUrl = 'https://asafe-ap-beijing-1306922583.cos.ap-beijing.myqcloud.com/7days/rcs/audio/80800001/2023/09/12/nx_6e79f7ee-15ae-467b-a802-49d29088edff_1694483427844/0d11ee00a83840569ced9c023aeb21af?q-sign-algorithm=sha1&q-ak=AKIDpgSI43vd8QjwyxpUzPisjqyyxmsshKm2&q-sign-time=1694483429%3B1702259429&q-key-time=1694483429%3B1702259429&q-header-list=host&q-url-param-list=&q-signature=120c87948e6ad4630b0f39a043719afb7dbb337e';
$speech->recognize($audioUrl, 'zh-CN', '12345678', TRUE);
