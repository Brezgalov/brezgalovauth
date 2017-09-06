<?php 
namespace Brau\oAuth;

class BasicAuthHelper {
	private $appSecret;
	private $appId;
	private $apiVersion;
	private $apiBase;

	public function __construct($appId, $appSecret, $apiBase) {
		$this->appSecret = $appSecret;
		$this->appId = $appId;
		$this->apiBase = $apiBase;
	}

	public function generateUrl($path, $args, $addId=false, $addSecret=false) {
		if ($addId)
			$args['client_id'] = $this->appId;
		if ($addSecret)
			$args['client_secret'] = $this->appSecret;

		$params = urldecode(http_build_query($args));
		return $this->apiBase . $path . '?' . $params;
	}

	public function get($path, $args, $addId=false, $addSecret=false) {
		if ($addId)
			$args['client_id'] = $this->appId;
		if ($addSecret)
			$args['client_secret'] = $this->appSecret;

		$url = $this->apiBase . $path . '?' . urldecode(http_build_query($args));

		// var_dump($url);echo "<br>";

		return json_decode(
			file_get_contents($url), 
			true
		);
	}

	public function post($path, $args, $addId=false, $addSecret=false) {
		if ($addId)
			$args['client_id'] = $this->appId;
		if ($addSecret)
			$args['client_secret'] = $this->appSecret;

		$curl = curl_init();
		// var_dump($this->apiBase . $path);die();
    	curl_setopt($curl, CURLOPT_URL, $this->apiBase . $path); // url, куда будет отправлен запрос
    	curl_setopt($curl, CURLOPT_POST, 1);
    	curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($args))); // передаём параметры
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    
	    $result = curl_exec($curl);
	    
	    curl_close($curl);

    	return json_decode($result, true);
	}
}