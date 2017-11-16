<?php
ob_start();
session_start();
if( isset($_SESSION['user']) != "") {
  header("Location: home.php");
}
include_once 'dbconnect.php';

 ?>

<?php
// ログインボタンがクリックされたときに下記を実行
$errorMessage="";
if(isset($_POST['login'])) {
  // 1. ユーザIDの入力チェック
    //  if (empty($_POST["username"])) {  // emptyは値が空のとき
    //      $errorMessage = 'ユーザーIDが未入力です。';
    //  }
      if (empty($_POST["password"])) {
         $errorMessage = 'パスワードが未入力です。';
     }
     if (empty($_POST["address"])){
       $errorMessage='アドレスが未入力です';
     }

     if (!empty($_POST["address"]) && !empty($_POST["password"])) {
         // 入力したユーザIDを格納
       $address=$_POST['address'];
         // 2. ユーザIDとパスワードが入力されていたら認証する

         $dsn = sprintf('mysql: host=%s; dbname=%s;port=3333; charset=utf8', $db['host'], $db['dbname']);

         // 3. エラー処理
         try {
             $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

             $stmt = $pdo->prepare('SELECT * FROM account WHERE address=:address');
             $stmt->bindValue(':address',$address,PDO::PARAM_STR);
             $stmt->execute();

             $password = $_POST['password'];
             if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                 if (password_verify($password, $row['password'])) {
                     session_regenerate_id(true);
                     // 入力したIDのユーザー名を取得
                     $id = $row['id'];
                     $sql = "SELECT * FROM account WHERE id = $id";  //入力したIDからユーザー名を取得
                     $stmt = $pdo->query($sql);
                     foreach ($stmt as $row) {
                         $row['name'];  // ユーザー名
                     }
                     $_SESSION['user'] = $row['name'];
                     $_SESSION['id']=$id;
                     header("Location: home.php");  // メイン画面へ遷移
                     exit();  // 処理終了
                 }
                 else {
                     // 認証失敗
                     $errorMessage = 'ユーザーIDあるいはパスワードに誤りがあります。';
                 }
             }
             else {
                 // 4. 認証成功なら、セッションIDを新規に発行する
                 // 該当データなし
                 $errorMessage = 'ユーザーIDあるいはパスワードに誤りがあります。';
             }

         } catch (PDOException $e) {
             $errorMessage = 'データベースエラー';
             //$errorMessage = $sql;
             // $e->getMessage() でエラー内容を参照可能（デバック時のみ表示）
             // echo $e->getMessage();
         }
     }
   }

 ?>

 <!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PHPのログイン機能</title>
<link rel="stylesheet" href="style.css">
<!-- Bootstrap読み込み（スタイリングのため） -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="style2.css">
</head>
</head>
<body>
<!-- <div class="col-xs-6 col-xs-offset-3">



</div> -->
<div class="container">

       <div id="first" class="card card-container">

           <img id="profile-img" class="profile-img-card" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
           <p id="profile-name" class="profile-name-card"></p>
           <form method="post" class="form-signin">
               <span id="reauth-email" class="reauth-email"></span>
               <input type="email" name="address" class="form-control" placeholder="Email address" required autofocus>
               <input type="password" name="password" class="form-control" placeholder="Password" required>
               <div id="remember" class="checkbox">

               </div>
               <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit" name="login">Sign in</button>
           </form><!-- /form -->
           <a href="register.php" class="forgot-password">
               会員登録はこちら
           </a>
       </div><!-- /card-container -->
   </div><!-- /container -->

</body>
</html>
