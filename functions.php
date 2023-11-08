<?php
try {
  // DBに接続する
  function connect_db()
  {
    $host = '3.27.110.115'; // AWS EC2上のMySQLサーバーのホスト名またはIPアドレス
    $port = '3306'; // MySQLのポート番号（通常は3306）
    $dbname = 'works'; // データベース名
    $username = 'root'; // MySQLのユーザー名
    $password = 'Iammisaki1218'; // MySQLのパスワード

    $param = "mysql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($param, $username, $password);
    $pdo->query('SET NAMES utf8;');
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;

    // $param = 'mysql:host=sample-shop_mysql_1;port=3306;dbname=works;';
    // $pdo = new PDO($param, "root", "password");
    // $pdo->query('SET NAMES utf8;');
    // $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // return $pdo;
  }

  // 日付を日(曜日)の形式に変換する
  function time_format_dw($date)
  {
    $format_date = NULL;
    $week = array('日', '月', '火', '水', '木', '金', '土');

    if ($date) {
      $format_date = date('j(' . $week[date('w', strtotime($date))] . ')', strtotime($date));
    }

    return $format_date;
  }

  // 時間を00:00の形式に変換する
  function format_date($time)
  {
    if ($time == null || $time == '00:00:00') {
      return null;
    } else {
      return date('H:i', strtotime($time));
    }
  }
  
  // htmlエスケープ処理（XSS対策）
  function h($original_str){
    return htmlspecialchars($original_str, ENT_QUOTES,'UTF-8');
  }
  
  // トークンを発行する処理
  function set_token(){
    $token = sha1(uniqid(mt_rand(),true));
    $_SESSION['CSRF_TOKEN'] = $token;
  }
  
  // トークンをチェックする処理
  function check_token(){
    if(empty($_SESSION['CSRF_TOKEN'])|| ($_SESSION['CSRF_TOKEN'] != $_POST['CSRF_TOKEN'])){
      unset($pdo);
      header('Location: /error.php');
      exit;
    }
  }

  // 時間の形式チェックを行う
  function check_time_format($time){
    if ($time != "" && !preg_match('/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/', $time)) {
      return FALSE;
    }else{
      return TRUE;
    }
  }

  // 指定されたPHPへリダイレクトする
  function redirect($path){
    // DBから切断する
    unset($pdo);
    // リダイレクト
    header('Location: '.$path);
  }

} catch (Exception $e) {
  // エラー時の処理
  header('Location: /error.php');
  exit;
}
