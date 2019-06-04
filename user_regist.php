<?php
session_start();
require_once '../../include/conf/const.php';
require_once '../../include/model/EC_function.php';

$err_msg = array();
$message = array();
$regexp_box = '/^[a-zA-Z0-9]{6,}$/';


// ログイン状態なら各トップページへ
if (isset($_SESSION['id']) === TRUE) {
    if ($_SESSION['id'] === '1') {
        header('Location: ./admin.php');
        exit();
    } else {
        header('Location: ./top.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['btn']) === TRUE) {
        $btn = $_POST['btn'];
    }
    if (isset($_POST['username']) === TRUE) {
        $user_name = $_POST['username'];
    }
    if (isset($_POST['passwd']) === TRUE) {
        $user_passwd = $_POST['passwd'];
    }
    if ($user_name === '') {
        $err_msg[] = 'ユーザー名を入力してください';
    } else if(preg_match($regexp_box, $user_name) !== 1) {
        $err_msg[] = 'ユーザー名は6文字以上の半角英数字で入力してください';
    }
    if ($user_passwd === '') {
        $err_msg[] = 'パスワードを入力してください';
    } else if(preg_match($regexp_box, $user_passwd) !== 1) {
        $err_msg[] = 'パスワードは6文字以上の半角英数字で入力してください';
    }

    
    // ユーザー情報の重複チェック 直せる
    if (count($err_msg) === 0) {
        $link = get_db_connect();
        $sql = "SELECT count(id) 
                FROM EC_user_table 
                WHERE user_name = '".$user_name."'";
                
        if ($result = mysqli_query($link, $sql)) {
            $table = mysqli_fetch_assoc($result);
            if ($table['count(id)'] > 0) {
                $err_msg[] = '同じユーザー名が既に登録されています';
            }
        }
        
        $hash = password_hash($user_passwd, PASSWORD_BCRYPT);

            // データベースへユーザー情報を追加
        if (count($err_msg) === 0) {
            $sql = "INSERT INTO EC_user_table(user_name, password, admin, create_date, update_date) 
                    VALUES ('".$user_name."', '".$hash."', 0, now(), now())";
            if (mysqli_query($link,$sql) === TRUE) {
                $message[] = 'ユーザー登録が完了しました';
            }
        }
        close_db_connect($link);
    }

}
include_once '../../include/view/EC/user_regist.php';
?>