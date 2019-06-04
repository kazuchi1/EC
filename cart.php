<?php
session_start();
require_once '../../include/conf/const.php';
require_once '../../include/model/EC_function.php';
$file_dir = 'img/';
$message = array();
$err_msg = array();
$regexp_num = '/^[1-9][0-9]*$/';

if (isset($_SESSION['id']) === TRUE) {
    $username = $_SESSION['username'];
    $user_id = $_SESSION['id'];
} else {
    header('Location: ./login.php');
    exit();
}

// カート内の商品削除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_kind']) === TRUE
&& $_POST['sql_kind'] === 'delete_cart') {
    if (isset($_POST['item_id']) === TRUE) {
        $item_id = $_POST['item_id'];
    }
    $link = get_db_connect();
    
    $sql = "DELETE FROM EC_cart_table 
            WHERE user_id = $user_id AND item_id = $item_id";
    
    if (mysqli_query($link, $sql) === TRUE) {
        $message = 'カートの商品を削除しました';
    }
}

// 購入数変更
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_kind']) === TRUE
&& $_POST['sql_kind'] === 'change_cart') {
    if (isset($_POST['item_id']) === TRUE) {
        $item_id = $_POST['item_id'];
    }
    if (isset($_POST['select_amount']) === TRUE) {
        $amount = $_POST['select_amount'];
    }
    if (preg_match($regexp_num, $amount) !== 1) {
        $err_msg[] = '1以上の数値を入力してください';
    }
    // 在庫数と変更数の比較チェック
    if (count($err_msg) === 0) { 
        $link = get_db_connect();
        
        $sql = "SELECT stock 
                FROM EC_stock_table 
                WHERE item_id = $item_id";
        if ($result = mysqli_query($link, $sql)) {
            $row = get_assoc_html($result);
        }
        foreach ($row as $value) {
            if ($amount > $value['stock']) {
                $err_msg[] = '在庫数を上回っています変更できません';
            }
        }
        // 購入数変更処理
        if (count($err_msg) === 0) {
            $sql = "UPDATE EC_cart_table 
                    SET amount = $amount, update_date = now()
                    WHERE user_id = $user_id AND item_id = $item_id";
                    
            if (mysqli_query($link, $sql)) {
                $message[] = '数量を変更しました';
            }
        }
    }
}

// カート内データ取得
$link = get_db_connect();
$sql = "SELECT item_id, img, name, price, amount
        FROM EC_cart_table JOIN EC_item_table
        ON EC_cart_table.item_id = EC_item_table.id
        WHERE user_id = $user_id";

if ($result = mysqli_query($link, $sql)) {
    $row = get_assoc_html($result);
}
// 取得後のレコード数え方
$cnt = count($row);
if ($cnt = 0) {
    $message[] = '商品はありません';
}

// 合計金額を取得
$sql = "SELECT SUM(price * amount)
        FROM EC_cart_table JOIN EC_item_table
        ON EC_cart_table.item_id = EC_item_table.id
        WHERE user_id = $user_id";
if ($result = mysqli_query($link, $sql)) {
    $sum = get_assoc_html($result);
}

close_db_connect($link);

include_once '../../include/view/EC/cart.php';
?>