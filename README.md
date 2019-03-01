# VK PHP Framework
## Установка
Для того, чтобы установить фреймворк, просто скачайте файл [vk.php](https://github.com/slmatthew/vk-php-bot/blob/master/src/vk.php) и поместите его в нужную директорию.
## Инициализация
1. В самом начале файла подключите фреймворк:
    ```php
    include './vk.php';
    ```
2. Инициализируйте и используйте фреймворк там, где это нужно:
    ```php
    $bot = new VK(168041404, '8devqmftwx5cw7yg5w5v95bpgg47z6kdmj4uacybc44vrdvbb5uab8ksdd8v4h4bjb5aqzk45tyakgzhy5sg7');
    $bot->send(1, 'Привет! Твой пол: '.$bot->getTextBySex(1, 'мужской', 'женский'));
    ```
## Загрузка фотографий и документов
### Формат ответа
* документ:
```json
{
  "response": {
    "type": "doc",
    "doc": {
      "id": 1,
      "owner_id": 1,
      "title": "Document",
      "size": 500,
      "ext": "jpg",
      "url": "https://vk.com/doc1_1",
      "date": 1551477311,
      "type": 4,
      "preview": []
    }
  }
}
```
* аудиосообщение:
```json
{
  "response": {
    "type": "audio_message",
    "audio_message": {
      "id": 1,
      "owner_id": 1,
      "duration": 1,
      "waveform": [0, 0, 0, 0, 0, 0, 0],
      "link_ogg": "https://psv4.userapi.com/server/file.ogg",
      "link_mp3": "https://psv4.userapi.com/server/file.mp3",
      "access_key": "key"
    }
  }
}
```

### Реализация
* Загрузка фотографий
    ```php
    $photo = $bot->uploadPhoto('nicecat.png');
    $bot->send(1, "Смотри, какой красивый котик!", "photo{$photo['response'][0]['owner_id']}_{$photo['response'][0]['id']}");
    ```

* Загрузка документов
    ```php
    $doc = $bot->uploadDoc('voice.mp3', 'audio_message');
    $bot->send(1, "Послушай, это моя старая запись.", "doc{$doc['response']['audio_message']['owner_id']}_{$doc['response']['audio_message']['id']}");
    ```
