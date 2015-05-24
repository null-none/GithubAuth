# GithubAuth

$auth = new GithubAuth();

if($auth->get('action') == 'login') {

    $auth->login();

}


if($auth->get('code')) {

    $auth->code();

}


if($auth->session('access_token')) {

    $user = $auth->apiRequest($auth->apiURLBase . 'user');

}

