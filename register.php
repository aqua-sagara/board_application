<?php
session_start();
  if( isset($_SESSION['user']) != "") {
  header("Location:home.php");
  }
include_once('dbconnect.php');
?>

<?php
// エラーメッセージ、登録完了メッセージの初期化
$errorMessage = "";
$signUpMessage = "";

// signupがPOSTされたときに下記を実行
if(isset($_POST['signup'])) {
  if (empty($_POST["username"])) {  // 値が空のとき
         $errorMessage = 'ユーザーIDが未入力です。';
     }
  else if (empty($_POST["password"])) {
         $errorMessage = 'パスワードが未入力です。';
     }
//  else if (empty($_POST["password2"])) {
  //       $errorMessage = 'パスワードが未入力です。';
    // }
if (!empty($_POST["username"]) && !empty($_POST["password"]) && !empty($_POST["address"])) {
             // 入力したユーザIDとパスワードを格納
      $username = $_POST["username"];
      $password = $_POST["password"];
      $address= $_POST["address"];
      // 2. ユーザIDとパスワードが入力されていたら認証する
      $dsn = sprintf('mysql: host=%s; dbname=%s; port=3333; charset=utf8', $db['host'], $db['dbname']);
      // 3. エラー処理

      try{
          $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
          $sql='select count(*) from account where address=:address';
          $pre=$pdo->prepare($sql);
          $pre->bindValue(':address',$address,PDO::PARAM_STR);
          $pre->execute();
          $count=$pre->fetchColumn();
        }
    catch (PDOException $e) {
                $errorMessage = 'データベースエラー';
                 //$e->getMessage() でエラー内容を参照可能（デバック時のみ表示）
                 //echo $e->getMessage();
        }

        if($count!=0){
          $errorMessage="すでにこのメールアドレスは登録されています";
        }
        if($count==0){
          try {

              if (preg_match('|^[0-9a-z_./?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$|', $address)==0) {
                echo "メールアドレスが正しくありません";
          }

            else{
              $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

              $stmt = $pdo->prepare("INSERT INTO account(name, password,address) VALUES (?,?,?)");
              $stmt->execute(array($username, password_hash($password, PASSWORD_DEFAULT),$address));  // パスワードのハッシュ化を行う（今回は文字列のみなのでbindValue(変数の内容が変わらない)を使用せず、直接excuteに渡しても問題ない）
              $userid = $pdo->lastinsertid();  // 登録した(DB側でauto_incrementした)IDを$useridに入れる
              $signUpMessage = '登録が完了しました。あなたの登録IDは '. $userid. ' です。';  // ログイン時に使用するIDとパスワード
            }
        }
          catch (PDOException $e) {
                  $errorMessage = 'データベースエラー';
                  // $e->getMessage() でエラー内容を参照可能（デバック時のみ表示）
                  // echo $e->getMessage();
              }

          }
        }
        $count=0;



  //$userid = $mysqli->real_escape_string($_POST['userid']);
  //$username = $mysqli->real_escape_string($_POST['username']);
  //$password = $mysqli->real_escape_string($_POST['password']);
  //$password = password_hash($password, PASSWORD_DEFAULT);
  // POSTされた情報をDBに格納する
  //$query = "INSERT INTO account(id,password,username) VALUES('$userid','$password','$username')";




  }


 ?>
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PHPの会員登録機能</title>
<link rel="stylesheet" href="style.css">

<!-- Bootstrap読み込み（スタイリングのため） -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="style2.css">]
</head>
<body>
  <div
<div class="col-xs-6 col-xs-offset-3">

<form method="post">
  <h1>会員登録フォーム</h1>
  <div><font color="#ff0000"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></font></div>
  <div><font color="#0000ff"><?php echo htmlspecialchars($signUpMessage, ENT_QUOTES); ?></font></div>
  <div class="form-group">
   <input type="text"  class="form-control" name="username" placeholder="ユーザー名" required />
  </div>
  <div class="form-group">
   <input type="text"  class="form-control" name="address" placeholder="メールアドレス" required />
  </div>
  <div class="form-group">
    <input type="password" class="form-control" name="password" placeholder="パスワード" required />
  </div>
  <button type="submit" class="btn btn-default" name="signup">会員登録する</button>
  <a href="index.php"><font color="res">ログインはこちら</font></a>
</form>

</div>
</body>
</html>
