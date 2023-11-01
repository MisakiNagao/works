<?php
require_once(dirname(__FILE__) . '/../functions.php');
require_once(dirname(__FILE__) . '/../lib/encrypt.php');

try {
  session_start();

  if (!isset($_SESSION['USER']) && $_SESSION['USER']['auth_type'] != 1) {
    // ログインされていない場合はログイン画面へ
    header('Location: /admin/login.php');
    exit;
  }

  $pdo = connect_db();

  $sql = "SELECT * FROM user";
  $stmt = $pdo->query($sql);
  $user_list = $stmt->fetchAll();

  $page_title = "ユーザ一覧";

} catch (Exception $e) {
  // エラー時の処理
  header('Location: /error.php');
  exit;
}
?>

<!doctype html>
<html lang="ja">

<?php include('../templates/head_tag.php') ?>

<body class="text-center bg-green">

  <?php include('../templates/header.php') ?>

  <form class="border rounded bg-white form-user-list" action="index.php">
    <h1 class="h3 my-3">社員一覧</h1>

    <table class="table table-bordered">
      <thead>
        <tr class="bg-light">
          <th scope="col">社員番号</th>
          <th scope="col">社員名</th>
          <th scope="col">権限</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($user_list as $user) : ?>
          <tr>
            <td scope="row"><?= $user['user_no'] ?></td>
            <td><a href="/admin/user_result.php?id=<?= $user['id'] ?>"><?= decrypt($user['name']) ?></a></td>
            <td scope="row"><?php if ($user['auth_type'] == 1) echo '管理者' ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>

  </form>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>

</html>