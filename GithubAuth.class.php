<?php

class GithubAuth
{
    public $OAUTH2_CLIENT_ID = 'OAUTH2_CLIENT_ID';
    public $OAUTH2_CLIENT_SECRET = 'OAUTH2_CLIENT_SECRET';
    public $authorizeURL = 'https://github.com/login/oauth/authorize';
    public $tokenURL = 'https://github.com/login/oauth/access_token';
    public $apiURLBase = 'https://api.github.com/';
    public $redirect_uri = 'http://example.com/api/github';
    
    function __construct()
    {
        session_start();
    }
    
    public function apiRequest($url, $post = FALSE, $headers = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        $headers[] = 'Accept: application/json';
        $headers[] = 'User-Agent: browserling';

        if ($this->session('access_token'))
            $headers[] = 'Authorization: Bearer ' . $this->session('access_token');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        return json_decode($response);
    }
    
    public function code() 
    {
	    if (!$this->get('state') || $_SESSION['state'] != $this->get('state')) {
	        header('Location: ' . $_SERVER['PHP_SELF']);
	        die();
	    }           
	    $token = $this->apiRequest($this->tokenURL, array(
	        'client_id' => $this->OAUTH2_CLIENT_ID,
	        'client_secret' => $this->OAUTH2_CLIENT_SECRET,
	        'redirect_uri' => $this->redirect_uri,
	        'state' => $_SESSION['state'],
	        'code' => $this->get('code')
	    ));

	    $_SESSION['access_token'] = $token->access_token;
	    header('Location: ' .  $this->redirect_uri);    	
    }

    public function get($key, $default = NULL)
    {
        return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
    }
    
    public function session($key, $default = NULL)
    {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }
    
    public function login()
    {
        $_SESSION['state'] = hash('sha256', microtime(TRUE) . rand() . $_SERVER['REMOTE_ADDR']);
        unset($_SESSION['access_token']);
        
        $params = array(
            'client_id' => $this->OAUTH2_CLIENT_ID,
            'redirect_uri' => $this->redirect_uri,
            'scope' => 'user',
            'state' => $_SESSION['state']
        );
        
        header('Location: ' . $this->authorizeURL . '?' . http_build_query($params));
        die();
    }
}
?>