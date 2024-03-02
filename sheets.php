<?php
  ini_set('error_reporting', E_ALL);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  $code = "";
  $spreadsheetId = "";
  $data = json_decode(file_get_contents('php://input'), true);
  $code = $data['code_service_sheet'];
  $count_data = count($data['data']);
  require_once "./config/config.php";
  require_once "./controllers/telegram_notification.php"; 
  // Подключаем клиент Google таблиц
  require_once __DIR__ . '/vendor/autoload.php';
  //Данный КОД статичный и задается на js для блокировки спама
  if($code == '3252648628322435010') {
    if($count_data> 0) {
      // Наш ключ доступа к сервисному аккаунту
      $googleAccountKeyFilePath = __DIR__ . '/service_key.json';
      putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath);

      // Создаем новый клиент
      $client = new Google\Client();
      // Устанавливаем полномочия
      $client->useApplicationDefaultCredentials();

      // Добавляем область доступа к чтению, редактированию, созданию и удалению таблиц
      $client->addScope('https://www.googleapis.com/auth/spreadsheets');

      $service = new Google_Service_Sheets($client);

      // ID таблицы
      $spreadsheetId = $data['spreadsheetId'];
      $response = $service->spreadsheets->get($spreadsheetId);

      // Идентификатор таблицы
      //var_dump($response->spreadsheetId);

      // URL страницы
      //var_dump($response->spreadsheetUrl);

      // Получение свойств таблицы
      $spreadsheetProperties = $response->getProperties();

      // Имя таблицы
      //var_dump($spreadsheetProperties->title);

      // Получение данных из таблицы
      /*
      $range = 'Заявки';
      $response = $service->spreadsheets_values->get($spreadsheetId, $range);
      var_dump($response);

       $values = [
            [$date, $name , $email,  $phone, $utm_source, $utm_content, $utm_medium, $utm_campaign, $utm_term, $ip, $page, $yandex_client_id],
        ];
      */
      // Диапазон, в котором мы определяем заполненные данные. Например, если указать диапазон A1:A10
      // и если ячейка A2 ячейка будет пустая, то новое значение запишется в строку, начиная с A2.
      // Поэтому лучше перестраховаться и указать диапазон побольше:
      $range = 'Звонки!A1:Z';
      // Данные для добавления
      $date=date("d-m-y H:i"); // число.месяц.год
      $client_ip = $_SERVER['REMOTE_ADDR'];
      $name = "";
      $arrayForm = "";
      $data_array = $data['data'];
      $values = array();
      
      foreach($data_array as $call ) {
        $call_arr = [$call["type"], $call["status"], $call["time"], $call["idschema"], $call["schema"], $call["fromphone"], $call["tophone"], $call["whoanswer"], $call["durationall"], $call["durationtalking"], $call["newclient"], $call["mark"]];
        array_push($values, $call_arr);
     }
     
      // Объект - диапазон значений
      $ValueRange = new Google_Service_Sheets_ValueRange();
      // Устанавливаем наши данные
      $ValueRange->setValues($values);
      // Указываем в опциях обрабатывать пользовательские данные
      $options = ['valueInputOption' => 'USER_ENTERED'];
      // Добавляем наши значения в последнюю строку (где в диапазоне A1:Z все ячейки пустые)
      $service->spreadsheets_values->append($spreadsheetId, $range, $ValueRange, $options);
      
      sleep(1);
      telegram_post_notification("Добавлены новые данные в таблицу. Количество строк: ".$data['date_length'], $telegram_token, $telegram_chat_id);
    // sleep(1);
    }
    else {

    }
  }
  else {
      telegram_post_notification("Неверный ключ", $telegram_token, $telegram_chat_id);
      echo 'неверный секретный ключ';
  };

?>