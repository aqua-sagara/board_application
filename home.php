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
    <u><font class="btn btn-danger">
    ようこそ<?php echo htmlspecialchars($_SESSION["user"], ENT_QUOTES);
    ?>さん</u></font>
     <!-- ユーザー名をechoで表示 -->
     <button type="button" class="btn btn-danger"><a href="logout.php"><font color="#ffffff">ログアウト</a></button></font>
  </nav>
        <!-- ユーザーIDにHTMLタグが含まれても良いようにエスケープする -->
        <div class="container-fluid">
        <div class=".card-container.card">

            <div class="row">

                <div class="col-xl-3">


            <form enctype="multipart/form-data" method="post" action="">
                <fieldset>
                    <legend>画像ファイルを選択(GIF, JPEG, PNGのみ対応)</legend>
                    <input type="file" name="image" /><br />
          <div class=".card">
        title:
        <textarea name="title" rows="2" cols="35"></textarea><br></div>
        <div class=."card">
        text:
        <textarea name="text" rows="8" cols="35"></textarea><br>
        <input type="submit" value="投稿" name="text_sub">
        <li>
            現在のページ番号<?php
            if(isset($_POST['session_page'])){
            echo $_POST['session_page']+1;
            }
            else if(isset($_POST['page'])){
                echo $_POST['page'];
            }
            else {
                echo "1ページ";

            }
            ?>
        </li>

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
//<input type="file" name="image">なので$_FILES['image']になる
            if (is_uploaded_file($_FILES["image"]["tmp_name"])) {
                $file_nm = $_FILES['image']['name'];

                $tmp_ary = explode('.', $file_nm);
                $extension = $tmp_ary[count($tmp_ary)-1];
                if (move_uploaded_file($_FILES["image"]["tmp_name"], "./image/" . $autoincrement.".$extension")) {
                    chmod("image/" . $autoincrement.".$extension", 0644);
                    echo $_FILES["image"]["name"] . "をアップロードしました。";
                }
                $h = 70; // リサイズしたい大きさを指定
                $w = 70;

                $file = "./image/".$autoincrement.".".$extension; // 加工したいファイルを指定

// 加工前の画像の情報を取得
                list($original_w, $original_h, $type) = getimagesize($file);

// 加工前のファイルをフォーマット別に読み出す（この他にも対応可能なフォーマット有り）
                switch ($type) {
                    case IMAGETYPE_JPEG:
                        $original_image = imagecreatefromjpeg($file);
                        break;
                    case IMAGETYPE_PNG:
                        $original_image = imagecreatefrompng($file);
                        break;
                    case IMAGETYPE_GIF:
                        $original_image = imagecreatefromgif($file);
                        break;
                    default:
                        throw new RuntimeException('対応していないファイル形式です。: ', $type);
                }

// 新しく描画するキャンバスを作成
                $canvas = imagecreatetruecolor($w, $h);
                imagecopyresampled($canvas, $original_image, 0,0,0,0, $w, $h, $original_w, $original_h);

                $resize_path = "resize_image/".$autoincrement.".".$extension; // 保存先を指定

                switch ($type) {
                    case IMAGETYPE_JPEG:
                        imagejpeg($canvas, $resize_path);
                        break;
                    case IMAGETYPE_PNG:
                        imagepng($canvas, $resize_path, 9);
                        break;
                    case IMAGETYPE_GIF:
                        imagegif($canvas, $resize_path);
                        break;
                }

// 読み出したファイルは消去
                imagedestroy($original_image);
                imagedestroy($canvas);
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
            $_SESSION['page_num']=0;
            if(isset($_POST['page_num'])){
              $start=$_POST['page_num']-1;

            }
            if(isset($_POST['page'])){
              $start=$_POST['page']-1;
                $_SESSION['page_num']=$start;
                $start=$_SESSION['page_num'];
              unset($_POST['page']);
            }
            if(isset($_POST['session_page'])){

                $start=$_POST['session_page'];
                unset($_POST['session_page']);
            }
            $count2=0;
            for($count=$start*9;$count<$start*9+10&&$count<$count_sum;$count++){

              // echo "<div class=\"card card-container\">";
              echo "<table><div class=\"row\"><tr class=\"card card-container\"><div class=\"col-xl-3\"><td ><span data-badge-top-left=\"$sum[$count]\"><form name=\"form1\" method=\"post\" action=\"home2.php\"><input type=\"hidden\"
                name=\"board_id\" value={$board[$count]['board_id']}
              ><a class=\"link\" href=\"javascript:form1[$count2].submit()\">rink</a></form></span>";
                if($board[$count]['user_id']==$id) {
                    echo "<form method=\"post\"> <input type=\"submit\" value=\"削除\" name=\"delete\"> <input type=\"hidden\" name=\"board_id\" value={$board[$count]['board_id']}
              ></form>";
                }
                if(file_exists("resize_image/".$board[$count]['board_id'].".jpg")){

                    echo "<image src= "."resize_image/".$board[$count]['board_id'].".jpg>";
                }
                else if (file_exists("resize_image/".$board[$count]['board_id'].".gif")){

                    echo "<image src= "."resize_image/".$board[$count]['board_id'].".gif>";
                }
                else if(file_exists("resize_image/".$board[$count]['board_id'].".png")){

                    echo "<image src= "."resize_image/".$board[$count]['board_id'].".png>";
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


              echo nl2br(htmlspecialchars($board[$count]['text'],ENT_QUOTES))."　　　　　　　　　　　　　";





              if($board[$count]['user_id']==$id){
              $html="<br></td></tr></div></div>";
              echo $html;

            }
              else{echo "</tr></div></div></div>";}
                $count2++;
            }
            echo"</table>";
            echo "<div style=\"display:inline-flex\">";
            for($i=1;$i<$count_sum/10+1;$i++){

              $html2="<form method=\"post\" ><input type=\"submit\" class=\"btn btn-primary btn-sm\"　value=\"$i\" name=\"page\"<input type=\"hidden\" name=\"page_num\" value=$i></form>";

              echo $html2;
            }
            echo "</div>";
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
    exit();
  }
  catch (PDOException $e) {
      $errorMessage = 'データベースエラー';
  }
}


//ページ切り替え
if(isset($_POST['page'])){
  $page=$_post['page_num'];
  header("Locating:home.php");
  exit();
}
        ?>

    </body>
</html>
