<?php
session_start();
include_once 'dbconnect.php';
// ログイン状態チェック
if (!isset($_SESSION["user"])) {
    header("Location: logout.php");
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
     <button type="button" class="btn btn-danger"><a href="logout.php"><font color="#ffffff">ログアウト</a></button></font>
  </nav>
        <!-- ユーザーIDにHTMLタグが含まれても良いようにエスケープする -->
        <div class="container-fluid">
        <div class=".card-container.card">

            <div class="row">

                <div class="col-xl-3">

        <form method="post">
          <div class=".card">
        title:
        <textarea name="title" rows="2" cols="35"></textarea><br></div>
        <div class=."card">
        text:
        <textarea name="text" rows="8" cols="35"></textarea><br>
        <input type="submit" value="投稿" name="text_sub">
      </div>
    </form></div>
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
            }
            catch (PDOException $e) {
                $errorMessage = 'データベースエラー';
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
            for($count=$start*4;$count<$start+5;$count++){

              // echo "<div class=\"card card-container\">";
              echo "<table><div class=\"row\"><tr class=\"card card-container\"><div class=\"col-xl-3\"><td ><form name=\"form1\" method=\"post\" action=\"home2.php\"><input type=\"hidden\" name=\"board_id\" value={$board[$count]['board_id']}
              ><a class=\"link\" href=\"javascript:form1[$count].submit()\">rink</a></form>";
              echo htmlspecialchars("ユーザ名:",ENT_NOQUOTES);
              echo htmlspecialchars($board[$count]['user'],ENT_QUOTES)."　　　　　　　　".
              htmlspecialchars("タイトル: ").
              htmlspecialchars($board[$count]['title'],ENT_QUOTES)."　　　　　　　　　".
              htmlspecialchars(" 投稿日時:").
              htmlspecialchars($board[$count]['date'],ENT_QUOTES)."</td></div><td class=\"td1\"><div class=\"col-xl-9\">".
              htmlspecialchars($board[$count]['text'],ENT_QUOTES)."　　　　　　　　　　　　　";

              if($board[$count]['user_id']==$id){
              $html="<form method=\"post\"> <input type=\"submit\" value=\"削除\" name=\"delete\"> <input type=\"hidden\" name=\"board_id\" value={$board[$count]['board_id']}
              ></form><br></td></tr></div></div>";
              echo $html;

            }
              else{echo "</tr></div></div></div>";}
            }
            echo"</table>";
            for($i=1;$i<$count_sum/5+1;$i++){
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
