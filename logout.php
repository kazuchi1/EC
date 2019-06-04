<?php
session_start();
$_SESSION = array();
if (isset($_COOKIE['PHPSESSID'])) {
    if (ini_set('session.use_cookies')) {
        $params = session_get_cookie_params();
    
        setcookie('PHPSESSID', '', time() - 3600,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
        );
    }
}
session_destroy();

header('Location: ./login.php');
exit;
?>