<?php

namespace Service;

class GuzzleClientService {

	/*
	* Estas constantes pertenecen a la api no publica de Latch
	* se mantienen ocultas por motivos de privacidad y seguridad.
	*/
	const KEY_HEADER 	= '';
	const VALUE_HEADER 	= '';
	const VALUE_COOKIE 	= '';
	const VALUE_SESSION = '';
	const URL_AUTENTICATION = '';
	const URL_APPLICATIONS = '';
	const URL_TOKEN = '';
	const URL_SWITCH = '';

	private $client;

	function __construct() {
		$this->client = new \GuzzleHttp\Client();
	}

	public function autenticate($username, $password, $session){
		$url = str_replace('{{USERNAME}}', $username, self::URL_AUTENTICATION);
		$url = str_replace('{{PASSWORD}}', $password, $url);
		$response = $this->send('post', $url);
		$cookies = explode(';', $response->getHeader('Set-cookie',true));
		foreach ($cookies as $cookie) {
			if (strpos($cookie, self::VALUE_COOKIE) !== false) {
				$cookieSend = explode(self::VALUE_COOKIE.'=',str_replace('"', '', $cookie));;
				$session->set(self::VALUE_SESSION, $cookieSend[1]);	    
			} 
		}
		return $response;
	}

	public function applications($session){
		$response = $this->send('get', self::URL_APPLICATIONS, $session);
		return $response;  	
	}

	public function token($session){
		$response = $this->send('get', self::URL_TOKEN, $session);
		return $response; 	
	}

	public function switcher($operator, $mode, $session){
		$url = str_replace('{{OPERATOR}}', $operator, self::URL_SWITCH);
		$url = str_replace('{{MODE}}', $mode, $url);    	
		$response = $this->send('post', $url, $session);  	
	}

	public function send($method, $url, $cookie = null ){
		if (is_null($cookie)) {
			$response = $this->client->{$method}($url, [
			    'headers' => [self::KEY_HEADER => self::VALUE_HEADER]
			]);
		} else {
			$response = $this->client->{$method}($url, [
			    'headers' => [self::KEY_HEADER => self::VALUE_HEADER],
			    'cookies' => [self::VALUE_COOKIE => $cookie]
			]);				
		}
		return $response;
	}
	
}