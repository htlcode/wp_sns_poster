<?php
class Buffer {
	private $client_id;
	private $client_secret;
	private $code;
	private $access_token;
	
	private $callback_url;
	private $authorize_url = 'https://bufferapp.com/oauth2/authorize';
	private $access_token_url = 'https://api.bufferapp.com/1/oauth2/token.json';
	private $buffer_url = 'https://api.bufferapp.com/1';
	
	public $ok = false;
	
	private $endpoints = array(
		'/user' => 'get',
		
		'/profiles' => 'get',
		'/profiles/:id/schedules/update' => 'post',	// Array schedules [0][days][]=mon, [0][times][]=12:00
		'/profiles/:id/updates/reorder' => 'post',	// Array order, int offset, bool utc
		'/profiles/:id/updates/pending' => 'get',
		'/profiles/:id/updates/sent' => 'get',
		'/profiles/:id/schedules' => 'get',
		'/profiles/:id' => 'get',
		
		'/updates/:id/update' => 'post',						// String text, Bool now, Array media ['link'], ['description'], ['picture'], Bool utc
		'/updates/create' => 'post',								// String text, Array profile_ids, Aool shorten, Bool now, Array media ['link'], ['description'], ['picture']
		'/updates/:id/destroy' => 'post',
		'/updates/:id' => 'get',
		
		'/links/shares' => 'get',
	);
	
	function __construct($client_id = '', $client_secret = '', $callback_url = '', $access_token = '') {
		if ($client_id) $this->set_client_id($client_id);
		if ($client_secret) $this->set_client_secret($client_secret);
		if ($callback_url) $this->set_callback_url($callback_url);
		if ($access_token) $this->set_access_token($access_token);
	}
	
	function go($endpoint = '', $data = '') {
		if (in_array($endpoint, array_keys($this->endpoints))) {
			$done_endpoint = $endpoint;
		} else {
			$ok = false;
			
			foreach (array_keys($this->endpoints) as $done_endpoint) {
				if (preg_match('/' . preg_replace('/(\:\w+)/i', '(\w+)', str_replace('/', '\/', $done_endpoint)) . '/i', $endpoint, $match)) {
					$ok = true;
					break;
				}
			}
			
			if (!$ok) return false;
		}
		
		if (!$data || !is_array($data)) $data = array();

		$data['access_token'] = $this->access_token;
		
		$method = $this->endpoints[$done_endpoint]; //get() or post()
		return $this->$method($this->buffer_url . $endpoint . '.json', $data);
	}
	
	function req($url = '', $data = '', $post = true) {
		if (!$url) return false;
		if (!$data || !is_array($data)) $data = array();
					
		$options = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => false);
		
		if ($post) {
			$options += array(
				CURLOPT_POST => $post,
				CURLOPT_POSTFIELDS => http_build_query($data)
			);
		} else {
			$url .= '?' . http_build_query($data);
		}
		
		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		$response = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if ($code != 200) {
			$response_array = json_decode($response,true);
			echo $response_array['code'].':'.$response_array['error'];
			return false;
		}
		
		return json_decode($response);
	}
	
	function get($url = '', $data = '') {
		return $this->req($url, $data, false);
	}
	
	function post($url = '', $data = '') {
		return $this->req($url, $data, true);
	}
	
	function set_client_id($client_id) {
		$this->client_id = $client_id;
	}
	
	function set_client_secret($client_secret) {
		$this->client_secret = $client_secret;
	}

	function set_callback_url($callback_url) {
		$this->callback_url = $callback_url;
	}

	function set_access_token($access_token) {
		$this->access_token = $access_token;
	}
}
?>