<?php
session_cache_limiter('private_no_expire');
session_start();
include_once 'dbconnect.php';
// ログイン状態チェック
if (!isset($_SESSION["user"])) {
    header("Location: Logout.php");
    exit;
}
  if(isset($_POST['board_id'])){
    $_SESSION['board_id']=$_POST['board_id'];

  }

//db接続
$dsn = sprintf('mysql: host=%s; dbname=%s; port=3333;charset=utf8', $db['host'], $db['dbname']);
try {

  $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
  $sql='select * from board where id_board=:id';
  $prepare=$pdo->prepare($sql);
  $prepare->bindValue(':id',$_SESSION['board_id'],PDO::PARAM_STR);
  $prepare->execute();
  $result=$prepare->fetch(PDO::FETCH_ASSOC);
  $user_id=$result['id_user'];
  $user_name=$result['user_name'];
  $date=$result['date'];
  $text=$result['text'];
  $title=$result['title'];
}
catch (PDOException $e) {
    $errorMessage = 'データベースエラー';
    echo $errorMessage;
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
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#"><?php echo htmlspecialchars($_SESSION['user'],ENT_QUOTES);?></a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="ナビゲーションの切替">
          <span class="navbar-toggler-icon"></span>
        </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
            <li class="nav-item active">
              <a class="nav-link" href="home.php">ホーム <span class="sr-only">(現位置)</span></a>
            </li>
            <li class="nav-item ">
                <?php

                if(isset($_SESSION['page_num'])) {
                    $page = $_SESSION['page_num'];
//                    echo "<form method=\"post\" ><a class=\"nav-link\" href=\"home.php\">戻る</a>
//                    <input type=\"hidden\" name=\"session_page\" value=$page></form>";
                    echo "<form method=\"post\" name=\"form1\" action=\"home.php\">
                    <input type=\"hidden\" name=\"session_page\" value=\"$page\">
                    <a href=\"javascript:form1.submit()\">戻る</a>
                    </form>";
                }
                else echo "<a class=\"nav-link\" href=\"home.php\">戻る</a>";
                ?>
            </li>
                <li class="nav-item">
              <a class="nav-link" href="logout.php">ログアウト</a>
            </li>

            <li class="nav-item">
             <a class="nav-link disabled" href="#">無効</a>
            </li>
          </ul>
        </div>
      </nav>
      <div class="container-fluid">
        <?php
          $sql2='select * from reply where id_board=:id order by date desc';
          $prepare2=$pdo->prepare($sql2);
          $prepare2->bindValue(':id',$_SESSION['board_id']);
          $prepare2->execute();

          $count=0;
          while($result=$prepare2->fetch(PDO::FETCH_ASSOC)){
            $reply[$count]['id_board']=$result['id_board'];
            $reply[$count]['id_user']=$result['id_user'];
            $reply[$count]['user_name']=$result['user_name'];
            $reply[$count]['text']=$result['text'];
            $reply[$count]['id_reply']=$result['id_reply'];
            $reply[$count]['date']=$result['date'];
            $count++;
          }
          $sql2='select count(*) from reply where id_board=:id';
          $prepare2=$pdo->prepare($sql2);
          $prepare2->bindValue(':id',$_SESSION['board_id']);
          $prepare2->execute();
          $sum=$prepare2->fetchColumn();
         ?>
          <div class="row">
              <div class="col-xl-10 col-xl-offset-2">
              <div class="card card-container">
                 <?php if(file_exists("resize_image/".$_SESSION['board_id'].".jpg")){

                  echo "<image src= "."resize_image/".$_SESSION['board_id'].".jpg>";
                  }
                  ?>
                <font class="title"><?php echo htmlspecialchars($title,ENT_QUOTES)?></font>
                <font class="data"><?php echo htmlspecialchars("name:".$user_name."日付:".$date,ENT_QUOTES)?></font><p>
                <font class="text"><?php echo htmlspecialchars($text,ENT_QUOTES)?></font>
                  <div class="row">
                  <div class="box">
                  <div class="col-xl-6 ">コメント数 <?php echo $sum ?></div></div>
                  <div class="box">
                  <div class="col-xl-6">テスト</div></div>
                </div>
               </div>
             </div>
           </div>
        <div class="row">

          <div class="col-xl-10 col-xl-offset-2">
          <div id="toukou" class="card card-container">
            <form method="post">
            <textarea id="reply"name="text" placeholder="コメントを書く"></textarea><br>
            <input type="submit" value="返信" name="toukou_submit">
            </form>
          </div>
        </div>
          </div>

          <?php

          $i=0;
          while($i<$sum){
              echo "<div class=\"row\"><div class=\"col-xl-10 col-xl-offset-2\"><div id=\"toukou2\"class=\"card card-container\">";
          echo htmlspecialchars( $reply[$i]['user_name'] . "　　". $reply[$i]['date'], ENT_QUOTES);
          echo "<p>";
          echo htmlspecialchars($reply[$i]['text'], ENT_QUOTES);
          echo "</div></div></div>";

          $i++;
          }
           ?>


        </div>



    </body>
</html>
<?php
if(isset($_POST['toukou_submit'])){
  if (empty($_POST["text"])) {
         $errorMessage = 'textが未入力です。';
     }
  else{
    date_default_timezone_set('Asia/Tokyo');
      $format=date("Y-m-d H:i:s");
      try{
          $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
          $sql='insert into reply (id_board,id_user,user_name,date,text,title) values(:board,:user_id,:name,:date,:text,:title)';
          $prepare=$pdo->prepare($sql);
          $prepare->bindValue(':board',$_SESSION['board_id'],PDO::PARAM_INT);
          $prepare->bindValue(':user_id',$user_id,PDO::PARAM_INT);
          $prepare->bindValue(':name',$_SESSION['user'],PDO::PARAM_STR);
          $prepare->bindValue(':date',$format);
          $prepare->bindValue(':text',$_POST['text']);
          $prepare->bindValue(':title',$title);
          $prepare->execute();

        }
        catch (PDOException $e) {
            $errorMessage = 'データベースエラー';
        }
     }
}
 ?>
