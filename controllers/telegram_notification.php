<?php

function telegram_post_notification($arr, $telegram_token, $telegram_chat_id) {
    $txt = '';
    if(is_array($arr)) {

        foreach($arr as $key => $value) {
        //	$txt .= "<b>".$key."</b> ".$value."%0A";
            $txt .= "<b>".$key."</b> ".$value." \n";
        };
    }
    else {
        $txt = $arr;
    }
    // отправка сообщения
    $enc = urlencode($txt);
    $url = "https://api.telegram.org/bot{$telegram_token}/sendMessage?chat_id={$telegram_chat_id}&parse_mode=html&text={$enc}";

    $headersTG = ['Content-Type: application/json']; // заголовки нашего запроса
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headersTG);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, false);
    $result = curl_exec($curl); // результат POST запроса
   // print_r($result);
    sleep(1);
}


?>