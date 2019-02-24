<?php

/**
 * VK PHP Framework for bots
 * by slmatthew
 * follow me: vk.com/slmatthew
 * last update: 24.02.2019
 */

class VK {

	protected const CHAT_PEER_ID = 2000000000;

	protected $group_id;
	protected $token;
	protected $v;

	/**
	 * Указываем необходимые параметры
	 * 
	 * @param int $group_id ID сообщества, в котором работает бот
	 * @param string $token access_token, полученный для бота (как минимум, нужны права доступа messages)
	 * @param string $v Версия API
	 * @return boolean
	 */
	public function __construct(int $group_id, string $token, string $v = '5.92') {
		$this->group_id = $group_id;
		$this->token = $token;
		$this->v = (string)$v;

		return true;
	}

	/**
	 * Вызов любого метода API
	 * 
	 * @param string $m Название метода
	 * @param array $p Параметры
	 * @return array
	 */
	public function call(string $m, array $p = array()) {
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
	 * @return array
	 */
	function upload_vk($url, $file) {
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
	 * Отправка сообщения
	 * 
	 * @param int $peer_id Идентификатор назначения
	 * @param string $message Текст сообщения
	 * @param string $attachment Вложения
	 * @param string $keyboard Клавиатура
	 * @param int $reply_to Идентификатор сообщения, на которое нужно ответить
	 * @param int $dont_parse_links Нужны ли сниппеты
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
		if(isset($response['error'])) return false; // если есть ошибка - выдаём false

		return true; // всё ок
	}

	
}

?>