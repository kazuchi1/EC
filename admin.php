<?php
session_start();
require_once '../../include/conf/const.php';
require_once '../../include/model/EC_function.php';


$data = array();
$file_dir = 'img/';
$err_msg = array();
$message = array();
$regexp_num = '/^[0-9]+$/';
$regexp_str = '/^(?!.*( |　)).*$/';

if (isset($_SESSION['id']) === TRUE && $_SESSION['id'] === '1') {
    $username = $_SESSION['username'];
} else {
    header('Location: ./login.php');
    exit();
}



// 商品追加を押した時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_kind']) === TRUE 
&& $_POST['sql_kind'] === 'insert') {
    if (isset($_POST['new_name']) === TRUE) {
        $new_name = $_POST['new_name'];
    }
    if (isset($_POST['new_price']) === TRUE) {
        $new_price = $_POST['new_price'];
    }
    if (isset($_POST['new_stock']) === TRUE) {
        $new_stock = $_POST['new_stock'];
    }
    if (isset($_POST['new_status']) === TRUE) {
        $new_status = $_POST['new_status'];
    }
    if ($new_name === '') {
        $err_msg[] = '商品名を入力してください';
    }
    if (preg_match($regexp_str,$new_name) !== 1) {
        $err_msg[] = 'スペースは商品名に使用できません';
    }
    if ($new_price === '') {
        $err_msg[] = '値段を入力してください';
    }
    if ($new_stock === '') {
        $err_msg[] = '在庫数を入力してください';
    } 
    if (isset($_FILES['new_img']) && ($_FILES['new_img']['error'] !== 0)) {
        $err_msg[] = '画像ファイルを選択してください';
    }
    if (preg_match($regexp_num, $new_stock) !== 1 || preg_match($regexp_num, $new_price) !== 1) {
        $err_msg[] = '0以上の整数を入力してください';
    }
    // 画像ファイルアップロード
    if (count($err_msg) === 0) {
        if(isset($_FILES['new_img']) && is_uploaded_file($_FILES['new_img']['tmp_name'])){
            $ext = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
            $a = basename($_FILES['new_img']['tmp_name']) . '.' . $ext;
            if ($ext === 'jpeg' || $ext === 'jpg' || $ext === 'png') {
                if(move_uploaded_file($_FILES['new_img']['tmp_name'], $file_dir . $a)){
                } else {
                    $err_msg[] = 'アップロードに失敗しました';
                }
            } else {
                $err_msg[] = 'ファイルの拡張子が違います';
            }
        }
    }
    
    if (count($err_msg) === 0) {
        if ($link = get_db_connect()) {
            
            mysqli_autocommit($link, false);
            
            $sql = "INSERT INTO EC_item_table(name, price, img, status, create_date, update_date)
                    VALUES ('$new_name', $new_price, '$a', $new_status , now(),now())";
            if(mysqli_query($link, $sql) !== TRUE) {
                $err_msg[] = 'sqlエラー' .$sql;
            } 
            // 前回のsqlのid
            $id = mysqli_insert_id($link);
            $sql = "INSERT INTO EC_stock_table(item_id, stock, create_date, update_date)
                    VALUES ($id, $new_stock, now(), now())";
            if (mysqli_query($link, $sql) !== TRUE) {
                $err_msg[] = 'sqlエラー' . $sql;
            }
            
            if (count($err_msg) === 0){
                mysqli_commit($link);
                $message[] = '商品の追加を完了しました';
            } else {
                mysqli_rollback($link);
            }
        }
        close_db_connect($link);
    }
    
    
}

// 在庫数変更
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_kind']) === TRUE
&& $_POST['sql_kind'] === 'update') {
    if (isset($_POST['update_stock']) === TRUE) {
        $update_stock = $_POST['update_stock'];
    }
    if (isset($_POST['item_id']) === TRUE) {
        $id = $_POST['item_id'];
    }
    
    $link = get_db_connect();
    $sql = "UPDATE EC_stock_table
            SET stock = $update_stock, update_date = now()
            WHERE item_id = $id";
    if (mysqli_query($link, $sql) === TRUE) {
        $message[] = '在庫数を変更しました';
    }
    close_db_connect($link);
}


// ステータス変更処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_kind']) === TRUE
&& $_POST['sql_kind'] === 'change') {
    if (isset($_POST['change_status']) === TRUE) {
        $change_status = $_POST['change_status'];
    }
    if (isset($_POST['item_id']) === TRUE) {
        $id = $_POST['item_id'];
    }
    if ($link = get_db_connect()) {
        
        $sql = "UPDATE EC_item_table
            SET status = $change_status, update_date = now()
            WHERE id = $id";
        if (mysqli_query($link, $sql) === TRUE) {
            $message[] = '公開ステータスを変更しました';
        }
        close_db_connect($link);
    }
}
// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_kind']) === TRUE
&& $_POST['sql_kind'] === 'delete') {
    if (isset($_POST['item_id']) === TRUE) {
        $id = $_POST['item_id'];
    }
    if ($link = get_db_connect()) {
        
        mysqli_autocommit($link, false);
        
        $sql = "DELETE FROM EC_item_table 
                WHERE id = '$id'";
        if (mysqli_query($link, $sql) !== TRUE) {
            $err_msg[] = 'sqlエラー' . $sql;
        }
        $sql = "DELETE FROM EC_stock_table 
                WHERE item_id = '$id'";
        if (mysqli_query($link, $sql) !== TRUE) {
            $err_msg[] = 'sqlエラー' . $sql;
        }
        
        if (count($err_msg) === 0){
            mysqli_commit($link);
            $message[] = '商品を削除しました';
        } else {
            mysqli_rollback($link);
        }
        close_db_connect($link);
    }
}


// 商品取得の処理
$link = get_db_connect();
mysqli_set_charset($link, 'utf8');
$sql = 'SELECT EC_item_table.id, img, name, price, stock, status 
        FROM EC_item_table JOIN EC_stock_table
        ON EC_item_table.id = EC_stock_table.item_id';
if ($result = mysqli_query($link, $sql)){
    $data = get_assoc_html($result);
}

close_db_connect($link);

include_once '../../include/view/EC/admin.php';
?>