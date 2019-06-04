<?php
session_start();

require_once '../../include/conf/const.php';
require_once '../../include/model/EC_function.php';

$err_msg = array();
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


// if(!empty($_POST)) {
// }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) === TRUE) {
        $user_name = $_POST['username'];
    }
    if (isset($_POST['passwd']) === TRUE) {
        $user_passwd = $_POST['passwd'];
    }
    
    // エラーチェック
    if ($user_name === '') {
    $err_msg[] = 'ユーザー名を入力してください';
    } else if(preg_match($regexp_box, $user_name) !== 1) {
        $err_msg[] = 'ユーザー名は６文字以上の半角英数字で入力してください';
    }
    if ($user_passwd === '') {
        $err_msg[] = 'パスワードを入力してください';
    } else if(preg_match($regexp_box, $user_passwd) !== 1) {
        $err_msg[] = 'パスワードは6文字以上の半角英数字で入力してください';
        }
    
    // 当てはまるデータあるか確認、エラーなければセッションにデータ代入
    if (count($err_msg) === 0) {
        $link = get_db_connect();
        $sql = "SELECT id, user_name, password, admin
                FROM EC_user_table 
                WHERE user_name = '".$user_name."'";
        if ($result = mysqli_query($link, $sql)) {
            $row_cnt = $result->num_rows;
            if ($row_cnt > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    foreach ($row as $key => $value) {
                        $row[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    }
                    
                    $user_id = $row['id'];
                    $user_name = $row['user_name'];
                    $hash = $row['password'];
                    $admin = $row['admin'];
                    

                    if(password_verify($user_passwd, $hash)){

                        if (count($err_msg) === 0) {
                            $_SESSION['id'] = $user_id;
                            $_SESSION['username'] = $user_name;
                            $_SESSION['admin'] = $admin;
                        }

                        // idが1なら管理ページ。それ以外はトップページ
                        if ($_SESSION['admin'] === '1') {
                            header ('Location: ./admin.php');
                            exit();
                        } else {
                            header ('Location: ./top.php');
                            exit();
                        }
                    } else {
                        $err_msg[] = 'パスワードが誤っています';
                    }
                }
            } else {
                $err_msg[] = 'ユーザーが見つかりません';
            }
            close_db_connect($link);
        }
    }
    
}

include_once '../../include/view/EC/login.php';
?>