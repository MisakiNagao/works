<?php
require_once(dirname(__FILE__) . '/functions.php');

try {
  $err = array();
  session_start();

  if (isset($_SESSION['USER'])) {
    // ログイン済の場合はHOME画面へ
    header('Location: /');
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // POST処理時
    
    check_token();

    // 1.入力値を取得
    $user_no = $_POST['user_no'];
    $password = $_POST['password'];

    // 2.バリデーションチェック
    if (!$user_no) {
      $err['user_no'] = '社員番号を入力してください';
    } elseif (!preg_match('/^[0-9]+$/', $user_no)) {
      $err['user_no'] = '社員番号を正しく入力してください';
    } elseif (strlen($user_no) > 20) {
      $err['user_no'] = '社員番号が長すぎます';
    }

    if (!$password) {
      $err['password'] = 'パスワードを入力してください';
    }
    if (empty($err)) {

      $pdo = connect_db();
      $sql = "SELECT * FROM user WHERE user_no = :user_no LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(':user_no', $user_no, PDO::PARAM_STR);
      $stmt->execute();
      $user = $stmt->fetch();

      // var_dump($user);
      // exit;
      if ($user && password_verify($password,$user['password'])) {  
        // 4.ログイン処理（セッションに保存）
        $_SESSION['USER'] = $user;

        // 5.HOME画面へ遷移
        header('Location: /');
        exit;
      } else {
        $err['password'] = '認証に失敗しました。';
      }
    };
  } else {
    // 画面初回アクセス時
    $user_no = "";
    $password = "";

    set_token();
  };
  
  $page_title = "ログイン";

} catch (Exception $e) {
  // エラー時の処理
  header('Location: /error.php');
  exit;
}
?>

<!doctype html>
<html lang="ja">

<?php include('templates/head_tag.php') ?>

<body class="text-center bg-light">

  <?php include('templates/header.php') ?>
  
  <form class="border rounded bg-white form-login" method="post">
    <h1 class="h3 my-3">Login</h1>
    <div class="form-group pt-3">
      <input type="text" class="form-control rounded-pill <?php if (!is_null($err) && isset($err['user_no'])) echo 'is-invalid'; ?>" name="user_no" value="<?= $user_no ?>" placeholder="社員番号" required>
      <div class="invalid-feedback"><?= $err['user_no'] ?></div>
    </div>
    <div class="form-group">
      <input type="password" class="form-control rounded-pill <?php if (!is_null($err) && isset($err['password'])) echo 'is-invalid'; ?>" name="password" placeholder="パスワード">
      <div class="invalid-feedback"><?= $err['password'] ?></div>
    </div>
    <button type="submit" class="btn btn-primary rounded-pill">ログイン</button>
    <input type="hidden" name="CSRF_TOKEN" value="<?= $_SESSION['CSRF_TOKEN'] ?>"> 
  </form>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
