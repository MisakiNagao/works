<?php
use function PHPSTORM_META\elementType;
require_once(dirname(__FILE__) . '/functions.php');

try {
  // 1.ログイン状態をチェック
  session_start();

  if (!isset($_SESSION['USER'])) {
    // ログインされていない場合はログイン画面へ
    redirect('/login.php');
  }

  // ログインユーザーの情報をセッションから取得
  $session_user = $_SESSION['USER'];

  $pdo = connect_db();

  $err = array();
  // モーダルの自動表示判定
  $modal_view_flg = TRUE;
  $target_date = date('y-m-d');

  // 対象日のデータがあるかどうかチェック
  $sql = "SELECT id, start_time, end_time, break_time, comment FROM work WHERE user_id = :user_id AND date = :date LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':user_id', (int)$session_user['id'], PDO::PARAM_INT);
  $stmt->bindValue(':date', date('Y-m-d'), PDO::PARAM_STR);
  $stmt->execute();
  $today_work = $stmt->fetch();

  if ($today_work) {
    $modal_start_time = $today_work['start_time'];
    $modal_end_time = $today_work['end_time'];
    $modal_break_time = $today_work['break_time'];
    $modal_comment = $today_work['comment'];
  } else {
    $modal_start_time = '';
    $modal_end_time = '';
    $modal_break_time = '01:00';
    $modal_comment = '';
  }

  // 2.ユーザーの業務日報データを取得
  if (isset($_GET['m'])) {
    $yyyymm = $_GET['m'];
    $day_count = date('t', strtotime($yyyymm));

    if (count(explode('-', $yyyymm)) != 2) {
      throw new Exception('日付の指定が不正', 500);
    }

    // 今月～過去12カ月の範囲内かどうか
    $check_date = new DateTime($yyyymm.'-01');
    $start_date = new DateTime('first day of -11 month 00:00');
    $end_date = new DateTime('first day of this month 00:00');

    if($check_date < $start_date || $end_date < $check_date){
      throw new Exception('日付の範囲が不正', 500);
    }

    if($check_date != $end_date){
      // 表示している画面が当月でなければモーダルを出さない
      $modal_view_flg = FALSE;
    }

  } else {
    $yyyymm = date('Y-m');
    $day_count = date('t');
  }

  $sql = "SELECT date, id, start_time, end_time, break_time, comment FROM work WHERE user_id = :user_id AND DATE_FORMAT(date,'%Y-%m') = :date";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':user_id', (int)$session_user['id'], PDO::PARAM_INT);
  $stmt->bindValue(':date', $yyyymm, PDO::PARAM_STR);
  $stmt->execute();
  $work_list = $stmt->fetchAll(PDO::FETCH_UNIQUE);

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 日報登録処理

    // 入力値をPOSTパラメータから取得
    $target_date = $_POST["target_date"];
    $modal_start_time = $_POST["Modal_start_time"];
    $modal_end_time = $_POST["Modal_end_time"];
    $modal_break_time = $_POST["Modal_break_time"];
    $modal_comment = $_POST["Modal_comment"];

    // バリデーションチェック
    // 出勤時間の必須・形式チェッ ク
    if (!$modal_start_time) {
      $err['modal_start_time'] = '出勤時間を入力してください。';
    } elseif (!check_time_format($modal_start_time)) {
      $modal_start_time = "";
      $err['modal_start_time'] = '出勤時間を正しく入力してください。';
    }
    // 退勤時間の形式チェック
    if (!check_time_format($modal_end_time)) {
      $modal_end_time = "";
      $err['modal_end_time'] = '退勤時間を正しく入力してください。';
    }
    // 休憩時間の形式チェック
    if (!check_time_format($modal_break_time)) {
      $modal_break_time = "";
      $err['modal_break_time'] = '休憩時間を正しく入力してください。';
    }
    // 業務内容の最大値チェック
    if (strlen($modal_comment) > 2000) {
      $err['modal_comment'] = '業務内容が長すぎます。';
    }

    // バリデーションチェックOKの場合
    if (empty($err)) {
      // 対象日のデータがあるかどうかチェック
      $sql = "SELECT id FROM work WHERE user_id = :user_id AND date = :date LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(':user_id', (int)$session_user['id'], PDO::PARAM_INT);
      $stmt->bindValue(':date', $target_date, PDO::PARAM_STR);
      $stmt->execute();
      $work = $stmt->fetch();

      if ($work) {
        // 対象日のデータがあればupdate
        $sql = "UPDATE work SET start_time =:start_time, end_time =:end_time, break_time =:break_time, comment =:comment WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$work['id'], PDO::PARAM_INT);
        $stmt->bindValue(':start_time', $modal_start_time, PDO::PARAM_STR);
        $stmt->bindValue(':end_time', $modal_end_time, PDO::PARAM_STR);
        $stmt->bindValue(':break_time', $modal_break_time, PDO::PARAM_STR);
        $stmt->bindValue(':comment', $modal_comment, PDO::PARAM_STR);
        $stmt->execute();
      } else {
        // 対象日のデータがなければinsert
        $sql = "INSERT INTO work (user_id, date, start_time, end_time, break_time, comment) VALUES(:user_id, :date, :start_time, :end_time, :break_time, :comment)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', (int)$session_user['id'], PDO::PARAM_INT);
        $stmt->bindValue(':date', $target_date, PDO::PARAM_STR);
        $stmt->bindValue(':start_time', $modal_start_time, PDO::PARAM_STR);
        $stmt->bindValue(':end_time', $modal_end_time, PDO::PARAM_STR);
        $stmt->bindValue(':break_time', $modal_break_time, PDO::PARAM_STR);
        $stmt->bindValue(':comment', $modal_comment, PDO::PARAM_STR);
        $stmt->execute();
      }
      $modal_view_flg = FALSE;

      $sql = "SELECT date, id, start_time, end_time, break_time, comment FROM work WHERE user_id = :user_id AND DATE_FORMAT(date,'%Y-%m') = :date";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(':user_id', (int)$session_user['id'], PDO::PARAM_INT);
      $stmt->bindValue(':date', $yyyymm, PDO::PARAM_STR);
      $stmt->execute();
      $work_list = $stmt->fetchAll(PDO::FETCH_UNIQUE);
    }
  }
  $page_title = "日報登録";
} catch (Exception $e) {
  // エラー時の処理
  redirect('/error.php');
}
?>

<!doctype html>
<html lang="ja">

<?php include('templates/head_tag.php') ?>

<body class="text-center bg-light">

  <?php include('templates/header.php') ?>

  <form class="border rounded bg-white form-time-table" action="index.php">
    <h1 class="h3 my-3">月別リスト</h1>

    <select class="form-control rounded-pill mb-3" name="m" onchange="submit(this.form)">
      <option value="<?= date('Y-m') ?>"><?= date('Y/m') ?></option>
      <?php for ($i = 1; $i < 12; $i++) : ?>
        <?php $target_yyyymm = strtotime("- {$i}months"); ?>
        <option value="<?= date('Y-m', $target_yyyymm) ?>" <?php if ($yyyymm == date('Y-m', $target_yyyymm)) echo 'selected' ?>>
          <?= date('Y/m', $target_yyyymm) ?></option>
      <?php endfor; ?>
    </select>

    <table class="table table-bordered">
      <thead>
        <tr class="bg-light">
          <th class="fix_col" style="width:5%">日</th>
          <th class="fix_col" style="width:10%">出勤</th>
          <th class="fix_col" style="width:10%">退勤</th>
          <th class="fix_col" style="width:10%">休憩</th>
          <th style="width:60%">業務内容</th>
          <th class="fix_col" style="width:5%"></th>
        </tr>
      </thead>
      <tbody>
        <?php for ($i = 1; $i <= $day_count; $i++) : ?>
          <?php
          $start_time = '';
          $end_time = '';
          $break_time = '';
          $comment = '';

          if (isset($work_list[date('Y-m-d', strtotime($yyyymm . '-' . $i))])) {

            $work = $work_list[date('Y-m-d', strtotime($yyyymm . '-' . $i))];

            if ($work['start_time']) {
              $start_time = $work['start_time'];
            }

            if ($work['end_time']) {
              $end_time = $work['end_time'];
            }

            if ($work['break_time']) {
              $break_time = $work['break_time'];
            }

            if ($work['comment']) {
              $comment = $work['comment'];
            }
          }
          ?>
          <tr>
            <th scope="row"><?= time_format_dw($yyyymm . '-' . $i) ?></th>
            <td><?= substr($start_time, 0, 5) ?></td>
            <td><?= substr($end_time, 0, 5) ?></td>
            <td><?= substr($break_time, 0, 5) ?></td>
            <td><?=  h($comment)  ?></td>
            <td><button type="button" class="btn btn-default h-auto py-0" data-toggle="modal" data-target="#inputModal" data-day="<?= $yyyymm . '-' . sprintf('%02d', $i) ?>"><i class="fas fa-pencil-alt"></i></button></td>
          </tr>
        <?php endfor; ?>
      </tbody>
    </table>
  </form>

  <!-- Modal -->
  <form method="POST">
    <div class="modal fade" id="inputModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <p></p>
            <h5 class="modal-title" id="exampleModalLabel">日報登録</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="container">
              <div class="alert alert-primary" role="alert">
                <?= date('n', strtotime($target_date)) ?>/<span id="Modal_day"><?= time_format_dw($target_date) ?></span>
              </div>
              <div class="row">
                <div class="col-sm">
                  <div class="input-group">
                    <input type="text" class="form-control <?php if (isset($err['modal_start_time'])) echo 'is-invalid'; ?>" placeholder="出勤" id="Modal_start_time" name="Modal_start_time" value="<?= format_date($modal_start_time) ?>" required>
                    <div class="input-group-prepend">
                      <button type="button" class="input-group-text" id="start_btn">打刻</button>
                    </div>
                    <div class="invalid-feedback"><?= $err['modal_start_time'] ?></div>
                  </div>
                </div>
                <div class="col-sm">
                  <div class="input-group">
                    <input type="text" class="form-control <?php if (isset($err['modal_end_time'])) echo 'is-invalid'; ?>" placeholder="退勤" id="Modal_end_time" name="Modal_end_time" value="<?= format_date($modal_end_time) ?>">
                    <div class="input-group-prepend">
                      <button type="button" class="input-group-text" id="end_btn">打刻</button>
                    </div>
                    <div class="invalid-feedback"><?= $err['modal_end_time'] ?></div>
                  </div>
                </div>
                <div class="col-sm">
                  <div class="input-group">
                    <input type="text" class="form-control <?php if (isset($err['modal_break_time'])) echo 'is-invalid'; ?>" placeholder="休憩" id="Modal_break_time" name="Modal_break_time" value="<?= format_date($modal_break_time) ?>">
                    <div class="invalid-feedback"><?= $err['modal_break_time'] ?></div>
                  </div>
                </div>
              </div>
              <div class="form-group pt-3">
                <textarea class="form-control <?php if (isset($err['modal_comment'])) echo 'is-invalid'; ?>" id="Modal_comment" name="Modal_comment" rows="5" placeholder="業務内容"><?= $modal_comment ?></textarea>
                <div class="invalid-feedback"><?= $err['modal_comment'] ?></div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary text-white rounded-pill px-5">登録</button>
          </div>
        </div>
      </div>
    </div>
    <input type="hidden" id="target_date" name="target_date" value="<?= date('Y-m-d') ?>">
  </form>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

  <script>
    // モーダルを自動表示
    <?php if ($modal_view_flg) : ?>
      var inputModal = new bootstrap.Modal(document.getElementById('inputModal'));
      inputModal.toggle();
    <?php endif; ?>

    $('#start_btn').click(function() {
      const now = new Date();
      const hour = now.getHours().toString().padStart(2, '0');
      const minute = now.getMinutes().toString().padStart(2, '0');
      $('#Modal_start_time').val(hour + ':' + minute);
    })  

    $('#end_btn').click(function() {
      const now = new Date();
      const hour = now.getHours().toString().padStart(2, '0');
      const minute = now.getMinutes().toString().padStart(2, '0');
      $('#Modal_end_time').val(hour + ':' + minute);
    })

    // モーダル初期化
    $('#inputModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget)
      var target_day = button.data('day')
      console.log(target_day)

      // 編集ボタンが押された日の表データを取得
      var day = button.closest('tr').children('th')[0].innerText
      var start_time = button.closest('tr').children('td')[0].innerText
      var end_time = button.closest('tr').children('td')[1].innerText
      var break_time = button.closest('tr').children('td')[2].innerText
      var comment = button.closest('tr').children('td')[3].innerText

      // 取得したデータをモーダルの各欄に設定
      $('#Modal_day').text(day)
      $('#Modal_start_time').val(start_time)
      $('#Modal_end_time').val(end_time)
      $('#Modal_break_time').val(break_time)
      $('#Modal_comment').val(comment)
      $('#target_date').val(target_day)

      // エラー表示をクリア
      $('#Modal_start_time').removeClass(' is-invalid')
      $('#Modal_end_time').removeClass(' is-invalid')
      $('#Modal_break_time').removeClass(' is-invalid')
      $('#Modal_comment').removeClass(' is-invalid')
    })
  </script>
</body>
</html>
