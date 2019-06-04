<?php
session_start();
require_once '../../include/conf/const.php';
require_once '../../include/model/EC_function.php';

$data = array();

if (isset($_SESSION['id']) === TRUE && $_SESSION['id'] === '1') {
    $username = $_SESSION['username'];
} else {
    header('Location: ./login.php');
    exit();
}

$link = get_db_connect();

// admin以外のユーザーデータを取得
$sql = 'SELECT user_name, create_date 
        FROM EC_user_table 
        WHERE id <> 1';
if ($result = mysqli_query($link, $sql)){
    $data = get_assoc_html($result);
}

close_db_connect($link);

include_once '../../include/view/EC/admin_user.php';
?>