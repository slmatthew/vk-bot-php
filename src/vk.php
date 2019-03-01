<?php

/**
 * VK PHP Framework for bots
 * by slmatthew
 * follow me: vk.com/slmatthew
 * last update: 26.02.2019
 */

class VK {

	/**
	 * Необходимые переменные для работы фреймворка
	 */
	protected const CHAT_PEER_ID = 2000000000;

	protected $group_id;
	protected $token;
	protected $lang;
	protected $v;

	/**
	 * Переменные, необходимые для некоторых функций
	 */
	public $ph_dw_folder = "images"; // директория, где будут храниться фотографии, которые загружаются с другого сервера

	/**
	 * Указываем необходимые параметры
	 *
	 * @param int $group_id ID сообщества, в котором работает бот
	 * @param string $token access_token, полученный для бота (как минимум, нужны права доступа messages)
	 * @param string $language Язык возвращаемых данных
	 * @param string $v Версия API
	 * @return boolean true
	 */
	public function __construct(int $group_id, string $token, string $language = 'ru', string $v = '5.92') {
		$this->group_id = $group_id;
		$this->token = $token;
		$this->lang = $language;
		$this->v = (string)$v;

		return true;
	}

	/**
	 * Вызов любого метода API
	 *
	 * @param string $m Название метода
	 * @param array $p Параметры
	 * @return array json_decode($json, true)
	 */
	public function call(string $m, array $p = array()) {
		if(!isset($p['lang'])) $p['lang'] = $this->lang;
		if(!isset($p['access_token'])) $p['access_token'] = $this->token;
		if(!isset($p['v'])) $p['v'] = $this->v;

		$a = $p['offToken'] ? "VKAndroidApp/5.11.1-2316" : "VKBot/1.0";
		unset($p['offToken']);

		$ch = curl_init("https://api.vk.com/method/{$m}");
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $a,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $p
		));
		$json = curl_exec($ch);
		curl_close($ch);

		return json_decode($json, true);
	}

	/**
	 * Загрузка файла на сервер ВКонтакте
	 *
	 * @param string $url Адрес загрузки
	 * @param string $file Путь к файлу
	 * @return array json_decode($json, true)
	 */
	function upload($url, $file) {
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => array('file' => new CURLfile($file))
		));
		$json = curl_exec($ch);
		curl_close($ch);

		return json_decode($json, true);
	}

	/**
	 * Вызов execute
	 *
	 * @param string $code Код алгоритма в VKScript
	 * @return array
	 */
	public function execute(string $code) {
		return $this->call('execute', ['code' => $code]);
	}

	/**
	 * Отправка сообщения
	 *
	 * Для проверки ошибки нужно использовать ===
	 * Например: if(send(1, 'Привет!') !== true) {...}
	 *
	 * @param int $peer_id Идентификатор назначения
	 * @param string $message Текст сообщения
	 * @param string $attachment Вложения
	 * @param string $keyboard Клавиатура
	 * @param int $reply_to Идентификатор сообщения, на которое нужно ответить
	 * @param int $dont_parse_links Нужны ли сниппеты
	 * @return boolean
	 */
	function send(int $peer_id, string $message, string $attachment = '', string $keyboard = '{"one_time": false, "buttons": []}', int $reply_to = 0, int $dont_parse_links = 0) {
		$p = array();

		$p['peer_id'] = $peer_id; // куда отправляем
		$p['message'] = $message; // что отправляем
		$p['attachment'] = $attachment; // картиночки и всё такое
		$p['random_id'] = 0; // для версии API >5.90

		if($reply_to > 0) $p['reply_to'] = $reply_to; // для ответов на сообщения

		if($peer_id > CHAT_PEER_ID) $p['keyboard'] = '{"one_time": false, "buttons": []}'; // не отправляем клавиатуру в чаты
		elseif(!empty($keyboard)) $p['keyboard'] = $keyboard; // если клавиатура не пуста, то отправляем её
		else unset($p['keyboard']); // не отправляем клавиатуру

		$p['dont_parse_links'] = $dont_parse_links; // сниппеты

		$r = $this->call('messages.send', $p); // вызываем messages.send
		if(isset($response['error'])) return $response['error']; // если есть ошибка - выдаём false

		return true; // всё ок
	}

	/**
	 * Загрузка фотографий в сообщения
	 *
	 * @param string $filename Путь до файла (абсолютный/относительный)
	 * @return array $save
	 */
	public function uploadPhoto(string $filename) {
		$server = $this->call('photos.getMessagesUploadServer')['response']['upload_url']; // получаем адрес для загрузки фотографии в сообщения
		$upload = $this->upload($server, $filename); // загружаем фотографию на сервер ВКонтакте
		$save = $this->call('photos.saveMessagesPhoto', array('photo' => $upload['photo'], 'server' => $upload['server'], 'hash' => $upload['hash'])); // сохраняем фотографию
		return $save;
	}

	/**
	 * Загрузка фотографий в сообщения
	 *
	 * @param string $filename Путь до файла (абсолютный/относительный)
	 * @param string $type Тип документа, vk.com/dev/docs.getMessagesUploadServer
	 * @return array $save
	 */
	public function uploadDoc(string $filename, string $type = 'doc') {
		$server = $this->call('docs.getMessagesUploadServer')['response']['upload_url']; // получаем адрес для загрузки фотографии в сообщения
		$upload = $this->upload($server, $filename); // загружаем фотографию на сервер ВКонтакте
		$save = $this->call('docs.save', array('file' => $upload['file'])); // сохраняем фотографию
		return $save;
	}

	/**
	 * Проверяем, есть ли пользователь в чате
	 *
	 * @param int $peer_id peer_id
	 * @param int $member_id ID участника
	 * @return bool
	 */
	public function isUserChatMember(int $peer_id, int $member_id) {
		$api = $this->call('messages.getConversationMembers', array('peer_id' => $peer_id)); // получаем список участников беседы
		if(isset($api['error']) || empty($api['response']['items'])) return false; // если есть ошибка или список пуст - false

		foreach($api['response']['items'] as $p) { // перебираем всех участников беседы
			if($p['member_id'] != $member_id) continue; // ищем нужного
			return true; // нашли!
		}

		return false; // не нашли :(
	}

	/**
	 * Участвует ли бот в чате
	 *
	 * @param int $peer_id peer_id
	 * @return bool
	 */
	public function isBotChatMember(int $peer_id) {
		return (!isset(vkapi('messages.getConversationsById', array('peer_ids' => $peer_id))['error']));
	}

	/**
	 * Выборка текста в зависимости от пола пользователя
	 *
	 * @param int $user_id ID пользователя
	 * @param string $male Текст, если указан мужской пол
	 * @param string $female Текст, если указан женский пол
	 * @param int $sex Предопределённый пол, необязательный параметр
	 * @return string $male or $female
	 */
	public function getTextBySex(int $user_id, string $male, string $female, int $sex = -1) {
		if($sex == -1) {
			$user = $this->call('users.get', array('user_ids' => $user_id, 'fields' => 'sex'));
			if(isset($user['response']) && !empty($user['response'])) {
				return ($user['sex'] == 1 ? $female : $male);
			}
		} else {
			return ($sex == 1 ? $female : $male);
		}

		return $male;
	}

}

?>
