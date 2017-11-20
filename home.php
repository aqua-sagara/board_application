<?php
session_start();
include_once 'dbconnect.php';
// ログイン状態チェック
if (!isset($_SESSION["user"])) {
    header("Location: Logout.php");
    exit;
}
$errorMessage="";
$username = $_SESSION["user"];
//id取得
try {
  $dsn = sprintf('mysql: host=%s; dbname=%s; port=3333;charset=utf8', $db['host'], $db['dbname']);
  $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
  $sql='select id from account where name=:name';
  $prepare=$pdo->prepare($sql);
  $prepare->bindValue(':name',$username,PDO::PARAM_STR);
  $prepare->execute();
  $result2=$prepare->fetch(PDO::FETCH_ASSOC);
  $id=$result2['id'];
}

catch (PDOException $e) {
    $errorMessage = 'データベースエラー';
}

 ?>
<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>メイン</title>
        <!-- Bootstrap読み込み（スタイリングのため） -->
        <link rel="stylesheet" href="bootstrap-4.0.0-beta.2-dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="style2.css">
        <!-- jQuery読み込み -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <!-- BootstrapのJS読み込み -->
        <script src="bootstrap-4.0.0-beta.2-dist/js/bootstrap.min.js"></script>




    </head>
    <body>
      <nav class="navbar navbar-inverse bg-primary">
    <!-- Navbar content -->
    <h1><font  class="btn btn-danger">メイン画面</font></h1>
    ようこそ<u><?php echo htmlspecialchars($_SESSION["user"], ENT_QUOTES);
    ?></u>さん
     <!-- ユーザー名をechoで表示 -->
     <button type="button" class="btn btn-danger"><a href="Logout.php"><font color="#ffffff">ログアウト</a></button></font>
  </nav>
        <!-- ユーザーIDにHTMLタグが含まれても良いようにエスケープする -->
        <div class="container-fluid">
        <div class=".card-container.card">

            <div class="row">

                <div class="col-xl-3">


            <form enctype="multipart/form-data" method="post" action="">
                <fieldset>
                    <legend>画像ファイルを選択(GIF, JPEG, PNGのみ対応)</legend>
                    <input type="file" name="upfile" /><br />
          <div class=".card">
        title:
        <textarea name="title" rows="2" cols="35"></textarea><br></div>
        <div class=."card">
        text:
        <textarea name="text" rows="8" cols="35"></textarea><br>
        <input type="submit" value="投稿" name="text_sub">

      </div>
    </fieldset></form>
                    <?php if (!empty($msgs)): ?>
                        <fieldset>
                            <legend>メッセージ</legend>
                            <?php foreach ($msgs as $msg): ?>
                                <ul>
                                    <li style="color:<?=h($msg[0])?>;"><?=h($msg[1])?></li>
                                </ul>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endif; ?>
                    <?php if (!empty($rows)): ?>
                        <fieldset>
                            <legend>サムネイル一覧(クリックすると原寸大表示)</legend>
                            <?php foreach ($rows as $i => $row): ?>
                                <?php if ($i): ?>
                                    <hr />
                                <?php endif; ?>
                                <p>
                                    <?=sprintf(
                                        '<a href="?id=%d"><img src="data:%s;base64,%s" alt="%s" /></a>',
                                        $row['id'],
                                        image_type_to_mime_type($row['type']),
                                        base64_encode($row['thumb_data']),
                                        h($row['name'])
                                    )?><br />
                                    ファイル名: <?=h($row['name'])?><br />
                                    日付: <?=h($row['date'])?><br clear="all" />
                                </p>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endif; ?>

                </div>
        <!--<div  class="container">-->
         <div  class="col-xl-9 ">

       <table class="table">




        <!-- <div><font color="#ff0000"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></font></div> -->
        <?php


//投稿  <div class="bg-info text-white">
        if(isset($_POST['text_sub'])) {
          if (empty($_POST["title"])) {  // 値が空のとき
                 $errorMessage = 'titleが未入力です。';
             }
          else if (empty($_POST["text"])) {
                 $errorMessage = 'textが未入力です。';
             }
        //  else if (empty($_POST["password2"])) {
          //       $errorMessage = 'パスワードが未入力です。';
            // }
        if (!empty($_POST["title"]) && !empty($_POST["text"]) ) {
            /* アップロードがあったとき */

          date_default_timezone_set('Asia/Tokyo');
            $format=date("Y-m-d H:i:s");
            try{
              $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
              $sql='insert into board (id_user,user_name,date,text,title) VALUES(:id,:name,:date,:text,:title)';
              $prepare=$pdo->prepare($sql);
              $prepare->bindValue(':id',$id);
              $prepare->bindValue(':name',$username);
              $prepare->bindValue(':date',$format);
              $prepare->bindValue(':text',$_POST['text']);
              $prepare->bindValue(':title',$_POST['title']);
              $prepare->execute();
                $autoincrement = $pdo->lastInsertId();
            }
            catch (PDOException $e) {
                $errorMessage = 'データベースエラー';
            }
            if (isset($_FILES['upfile']['error']) && is_int($_FILES['upfile']['error'])&&$_FILES['upfile']['size']!=0) {

                // バッファリングを開始
                ob_start();

                try {

                    // $_FILES['upfile']['error'] の値を確認
                    switch ($_FILES['upfile']['error']) {
                        case UPLOAD_ERR_OK: // OK
                            break;

                        case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
                        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                            throw new RuntimeException('ファイルサイズが大きすぎます', 400);
                        default:
                            throw new RuntimeException('その他のエラーが発生しました', 500);
                    }

                    // $_FILES['upfile']['mime']の値はブラウザ側で偽装可能なので
                    // MIMEタイプを自前でチェックする
                    if (!$info = @getimagesize($_FILES['upfile']['tmp_name'])) {
                        throw new RuntimeException('有効な画像ファイルを指定してください', 400);
                    }
                    if (!in_array($info[2], [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                        throw new RuntimeException('未対応の画像形式です', 400);
                    }

                    // サムネイルをバッファに出力
                    $create = str_replace('/', 'createfrom', $info['mime']);
                    $output = str_replace('/', '', $info['mime']);
                    if ($info[0] >= $info[1]) {
                        $dst_w = 120;
                        $dst_h = ceil(120 * $info[1] / max($info[0], 1));
                    } else {
                        $dst_w = ceil(120 * $info[0] / max($info[1], 1));
                        $dst_h = 120;
                    }
                    if (!$src = @$create($_FILES['upfile']['tmp_name'])) {
                        throw new RuntimeException('画像リソースの生成に失敗しました', 500);
                    }
                    $dst = imagecreatetruecolor($dst_w, $dst_h);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $dst_w, $dst_h, $info[0], $info[1]);
                    $output($dst);
                    imagedestroy($src);
                    imagedestroy($dst);

                    // INSERT処理
                    $stmt = $pdo->prepare('INSERT INTO image(id,name,type,raw_data,thumb_data,date) VALUES(?,?,?,?,?,?)');
                    //$stmt->bindValue(':id',$autoincrement);
                    $stmt->execute([
                            $autoincrement,
                        $_FILES['upfile']['name'],
                        $info[2],
                        file_get_contents($_FILES['upfile']['tmp_name']),
                        ob_get_clean(), // バッファからデータを取得してクリア
                        (new DateTime('now', new DateTimeZone('Asia/Tokyo')))->format('Y-m-d H:i:s'),
                    ]);

                    $msgs[] = ['green', 'ファイルは正常にアップロードされました'];

                } catch (RuntimeException $e) {

                    while (ob_get_level()) {
                        ob_end_clean(); // バッファをクリア
                    }
                    http_response_code($e instanceof PDOException ? 500 : $e->getCode());
                    $msgs[] = ['red', $e->getMessage()];

                }

                /* ID指定があったとき */
            }
          }
        }
         try {

            $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
            $sql4='select COUNT(*) from board';
            $prepare4=$pdo->prepare($sql4);
            $prepare4->execute();
            $count_sum=$prepare4->fetchColumn();
            $page=1;
            $start=0;
            $count=0;
            $sql2='select * from board order by date DESC';
            $prepare2=$pdo->prepare($sql2);
            //$prepare2->bindValue(':count',$start);
            $prepare2->execute();
            while($result=$prepare2->fetch(PDO::FETCH_ASSOC)){
              $board[$count]['user']=$result['user_name'];
              $board[$count]['title']=$result['title'];
              $board[$count]['date']=$result['date'];
              $board[$count]['text']=$result['text'];
              $board[$count]['board_id']=$result['id_board'];
              $board[$count]['user_id']=$result['id_user'];
              $sql='select count(*) from reply where id_board=:id';
              $prepare=$pdo->prepare($sql);
              $prepare->bindValue(':id',$board[$count]['board_id']);
              $prepare->execute();
              $sum[$count]=$prepare->fetchColumn();
              $count++;
            }
            $start=0;
            if(isset($_POST['page_num'])){
              $start=$_POST['page_num']-1;
            }
            if(isset($_POST['page'])){
              $start=$_POST['page']-1;
              unset($_POST['page']);
            }
            for($count=$start*9;$count<$start+10&&$count<$count_sum;$count++){

              // echo "<div class=\"card card-container\">";
              echo "<table><div class=\"row\"><tr class=\"card card-container\"><div class=\"col-xl-3\"><td ><form name=\"form1\" method=\"post\" action=\"home2.php\"><input type=\"hidden\" name=\"board_id\" value={$board[$count]['board_id']}
              ><a class=\"link\" href=\"javascript:form1[$count].submit()\">rink</a></form>";
                if($board[$count]['user_id']==$id) {
                    echo "<form method=\"post\"> <input type=\"submit\" value=\"削除\" name=\"delete\"> <input type=\"hidden\" name=\"board_id\" value={$board[$count]['board_id']}
              ></form>";
                }
              echo htmlspecialchars("ユーザ名:",ENT_NOQUOTES);
              echo htmlspecialchars($board[$count]['user'],ENT_QUOTES)."　　　　　　　　".
              htmlspecialchars("タイトル: ").
              htmlspecialchars($board[$count]['title'],ENT_QUOTES)."　　　　　　　　　".
              htmlspecialchars(" 投稿日時:").
              htmlspecialchars($board[$count]['date'],ENT_QUOTES)."　　　　　　".
                htmlspecialchars("コメント数:").
                htmlspecialchars($sum[$count],ENT_QUOTES);


              echo "</td></div><td class=\"td1\"><div class=\"col-xl-9\">";

                try {

                    $stmt = $pdo->prepare('SELECT type, raw_data FROM image WHERE id = :id ');
                    $stmt->bindValue(':id',$board[$count]['board_id']);
                    $stmt->execute();
                    if (!$row = $stmt->fetch()) {
                        throw new RuntimeException('該当する画像は存在しません', 404);
                    }
                    //header('X-Content-Type-Options: nosniff');
                   // header('Content-Type: ' . image_type_to_mime_type($row['type']));
                    //echo $row['raw_data'];
                } catch (RuntimeException $e) {

                    //http_response_code($e instanceof PDOException ? 500 : $e->getCode());
                    //$msgs[] = ['red', $e->getMessage()];

                }
              echo nl2br(htmlspecialchars($board[$count]['text'],ENT_QUOTES))."　　　　　　　　　　　　　";

              if($board[$count]['user_id']==$id){
              $html="<br></td></tr></div></div>";
              echo $html;

            }
              else{echo "</tr></div></div></div>";}
            }
            echo"</table>";
            for($i=1;$i<$count_sum/10+1;$i++){
              $html2="<form method=\"post\" ><input type=\"submit\" class=\"btn btn-primary btn-sm\"　value=\"$i\" name=\"page\"<input type=\"hidden\" name=\"page_num\" value=$i></form>";

              echo $html2;
            }
            echo "</div></div></div></div>";
            $max_page=$i;

        }
        catch (PDOException $e) {
            $errorMessage = 'データベースエラー';
        }

    //削除処理
if(isset($_POST['delete'])) {
  $sql3='delete from board where id_board=:id';
  $prepare3=$pdo->prepare($sql3);
  try{
    $prepare3->bindValue(':id',$_POST['board_id'],PDO::PARAM_INT);
  //  $prepare3->bindValue(':userid',$id);
    $prepare3->execute();
    unset($_POST['delete']);
    header("Location: home.php");
  }
  catch (PDOException $e) {
      $errorMessage = 'データベースエラー';
  }
}


//ページ切り替え
if(isset($_POST['page'])){
  $page=$_post['page_num'];
  header("Locating:home.php");
}
        ?>

    </body>
</html>
