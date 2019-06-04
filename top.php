<?php
session_start();

require_once '../../include/conf/const.php';
require_once '../../include/model/EC_function.php';

$data = array();
$err_msg = array();
$message = array();
$file_dir = 'img/';

if (isset($_SESSION['id']) === TRUE) {
    $username = $_SESSION['username'];
    $id = $_SESSION['id'];
} else {
    header('Location: ./login.php');
    exit();
}

$link = get_db_connect();
// set_charsetしないと日本語が文字化けする
    

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['btn']) === TRUE){
        $item_id = $_POST['btn'];
    }
    
    mysqli_autocommit($link, false);
    
    // cartデータ取得し、既にあるidならupdate
    $sql = "SELECT count(*) 
            FROM EC_cart_table 
            WHERE item_id = '$item_id' AND user_id = '$id'";
    // うまくいかなかった書き方
    // if (mysqli_query($link, $sql) !== TRUE) {
    //     $err_msg[] = 'sql失敗' . $sql;
    // }
    if ($result = mysqli_query($link, $sql)) {
        $table = mysqli_fetch_assoc($result);
        if ($table['count(*)'] > 0) {
            $sql = "UPDATE EC_cart_table 
                    SET amount = amount + 1
                    WHERE item_id = $item_id AND user_id = $id";
            if (mysqli_query($link, $sql) === TRUE) {
                $message[] = '商品をカートに追加しました';
            }
        } else {
            // 新しくcartにinsert
            $sql = "INSERT INTO EC_cart_table(user_id, item_id, amount, create_date, update_date)
                    VALUES ($id, $item_id, 1, now(), now())";
            if (mysqli_query($link, $sql) === TRUE) {
                $message[] = '商品をカートに追加しました';
            }
        }
    }
        // トランザクション成否判定
        if (count($err_msg) === 0) {
           // 処理確定
           mysqli_commit($link);
        } else {
           // 処理取消
           mysqli_rollback($link);
        }
}

$sql = 'SELECT EC_item_table.id, name, price, img, stock 
        FROM EC_item_table 
        JOIN EC_stock_table 
            ON EC_item_table.id = EC_stock_table.item_id 
        WHERE status = 1';
if ($result = mysqli_query($link, $sql)) {
    $data = get_assoc_html($result);
}

close_db_connect($link);

include_once '../../include/view/EC/top.php';
?>