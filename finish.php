<?php 
session_start();
$err_msg = array();
$message =array();
$file_dir = 'img/';

require_once '../../include/conf/const.php';
require_once '../../include/model/EC_function.php';
$file_dir = 'img/';

if (isset($_SESSION['id']) === TRUE) {
    $username = $_SESSION['username'];
    $id = $_SESSION['id'];
} else {
    header('Location: ./login.php');
    exit();
}

$link = get_db_connect();

mysqli_autocommit($link, false);


// cartのstatus関係データを取得
$sql = "SELECT item_id, status, name
        FROM EC_cart_table JOIN EC_item_table
        ON EC_cart_table.item_id = EC_item_table.id
        WHERE user_id = $id";
if ($result = mysqli_query($link, $sql)) {
    $row = get_assoc_html($result);
}

// statusチェック
foreach ($row as $value) {
    var_dump($value['status']);
    if ($value['status'] === '0') {
        $sql = "DELETE FROM EC_cart_table 
                WHERE item_id = '".$value['item_id']."'";
        if (mysqli_query($link, $sql)) {
            $message[] = '現在、非公開商品のため' . $value['name'] . 'は購入できませんでした';
        }
    }
}

// cartデータを取得
$sql = "SELECT item_id, status, img, name, price, amount
        FROM EC_cart_table JOIN EC_item_table
        ON EC_cart_table.item_id = EC_item_table.id
        WHERE user_id = $id";
if ($result = mysqli_query($link, $sql)) {
    $row = get_assoc_html($result);
}
$cnt = count($row);

// 合計金額を取得
$sql = "SELECT SUM(price * amount)
        FROM EC_cart_table JOIN EC_item_table
        ON EC_cart_table.item_id = EC_item_table.id
        WHERE user_id = $id";
if ($result = mysqli_query($link, $sql)) {
    $sum = get_assoc_html($result);
}
// 商品在庫数を減らす

foreach ($row as $value) {
    $sql = "UPDATE EC_stock_table 
            SET stock = stock - '".$value['amount']."' , update_date = now()
            WHERE item_id = '".$value['item_id']."'";
    if (mysqli_query($link, $sql) !== TRUE) {
            $err_msg[] = 'sql失敗' . $sql;
        }
}
// cartデータをデリート
$sql = "DELETE FROM EC_cart_table 
        WHERE user_id = $id";
if (mysqli_query($link, $sql) !== TRUE) {
    $err_msg[] = 'sql失敗' . $sql;
} 

if (count($err_msg) === 0) {
    mysqli_commit($link);
} else {
    mysqli_rollback($link);
}

close_db_connect($link);

include_once '../../include/view/EC/finish.php';
?>