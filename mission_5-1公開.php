<!DOCTYPE html> <!-- DOCTYPE宣言(HTMLのversionを宣言) -->
<html lang="ja"> <!-- 言語を日本語に設定 -->
<head> <!-- 以下、webページの設定 -->
    <meta charset="utf-8" /> <!-- 文字コードをUTF-8に設定 -->
    <title>mission_5-1</title> <!-- webページのタイトル -->
</head>

<body>

<p>
<strong>mission_5-1</strong> <!-- 投稿をデータベースに保存 -->
</p>

<?php
//編集時の投稿番号を受信
if(!empty($_POST["number"])){
    $number=$_POST["number"];
}else{
    $number="";
} //このif文がないと、未入力の時に未定義エラーが出る
//名前を受信
if(!empty($_POST["name"])){
    $name=$_POST["name"];
}else{
    $name="";
}
//コメントを受信
if(!empty($_POST["comment"])){
    $comment=$_POST["comment"];
}else{
    $comment="";
}
//パスワードを受信
if(!empty($_POST["pass1"])){
    $pass1=$_POST["pass1"];
}else{
    $pass1="";
}

//削除する番号を受信
if(!empty($_POST["delete"])){
    $delete=$_POST["delete"];
}else{
    $delete="";
}
//削除時のパスワードを受信
if(!empty($_POST["pass2"])){
    $pass2=$_POST["pass2"];
}else{
    $pass2="";
}

//編集する番号を受信
if(!empty($_POST["edit"])){
    $edit=$_POST["edit"];
}else{
    $edit="";
}
//編集時のパスワードを受信
if(!empty($_POST["pass3"])){
    $pass3=$_POST["pass3"];
}else{
    $pass3="";
}

$date = date("Y/m/d H:i:s"); //表示例：2017/10/20 0:00:00

//DB接続設定↓
$dsn = 'mysql:dbname=tb******db;host=localhost'; //スペースNG
	//DB名,ホスト名を設定。(dsn：Data Source Name)
$user = 'tb-******';
$password = 'PASSWORD';

$pdo = new PDO( //PDOクラスのインスタンスを作成＝指定されたデータベースへの接続
	$dsn, $user, $password, 
	array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)
    //エラーモード →エラーコードを設定、E_WARNINGメッセージを出力
);

//DBのテーブルtb_kejibanを作成↓
$sql = "CREATE TABLE IF NOT EXISTS tb_kejiban" 
    . " ("   //以下カラム(テーブルの列)を設定
    . "id INT AUTO_INCREMENT PRIMARY KEY," 
        //id：ナンバリング。INT：整数。
        //AUTO_INCREMENT：整数を1ずつ増やしながら割り当てる。
        //PRIMARY KEY：主キー。データの出席番号のようなもの。
    . "name char(32)," //名前(半角英数で32文字までの文字列)
    . "comment TEXT," //コメント(文字列)。
    . "date TEXT," //日付。
    . "pass TEXT" //パスワード(文字列)。
    . ");" ;
$stmt = $pdo->query($sql); //実行


//投稿機能
if (isset($comment) && empty($number)){
    if(!empty($name) && !empty($pass1)){ //名前とパスワードがちゃんとある時
    
        //tb_kejibanにレコードを入力↓
        $sql = $pdo -> prepare( 
	        //prepare：ユーザからの入力情報を含む時の命令 (queryと区別)
            "INSERT INTO tb_kejiban (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)"
        );  //Insert into テーブル名 (カラム名) values (ユーザからの入力情報)
	       
        $sql -> bindParam(':name', $name, PDO::PARAM_STR);
        $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
        $sql -> bindParam(':date', $date, PDO::PARAM_STR);
        $sql -> bindParam(':pass', $pass1, PDO::PARAM_STR);
        //:nameや:commentに$変数(パラメータ)をバインドする
        //PARAM_STR：(CHARなどの)文字列のパラメータ
        $sql -> execute(); //実行
        $success1 = "投稿しました"; //フォーム下に出力
        
    } elseif(empty($name)){ //名前(必須)がない時
        $error1A = "名前を入力して下さい"; //フォーム下に出力
    } elseif(empty($pass1)){ //パスワード(必須)がない時
        $error1B = "パスワードを設定して下さい"; //フォーム下に出力
    }
}

//削除機能
if (!empty($delete)){ //削除番号が入力されたとき
    if (!empty($pass2)){
        //特定のレコードを抽出↓
        $id = $delete ; // 抽出したいレコードのid
        $sql = 'SELECT * FROM tb_kejiban where id=:id '; 
        $stmt = $pdo -> prepare($sql); //差し替えるパラメータを含めたSQLを準備
        $stmt -> bindParam(':id', $id, PDO::PARAM_INT);
        $stmt -> execute(); //実行

        $results = $stmt -> fetchAll(); //抽出した全レコードを2次元配列に格納
        foreach ($results as $row){ //連想配列$rowに繰り返し格納
            if($row['pass'] == $pass2){ //パスワードが投稿時と一致するとき
                //テーブルのレコード削除↓
                $sql = 'DELETE from tb_kejiban where id=:id';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':id', $id, PDO::PARAM_INT);
                $stmt -> execute();
                $success2 = "削除しました"; //フォーム下に出力
            } else{
                $error2C = "パスワードが違います";
            }
        }        
    //削除したいデータidが存在しない時のエラー2A?

    } else{
        $error2B = "パスワードの入力が必要です"; 
    }
}

//編集機能
//①編集する投稿を取得
if (!empty($edit)){ //編集番号が入力されたとき
    if (!empty($pass3)){ 
        //特定のレコードを抽出↓
        $id = $edit ; // 抽出したいレコードのid
        $sql = 'SELECT * FROM tb_kejiban where id=:id '; 
        $stmt = $pdo -> prepare($sql); //差し替えるパラメータを含めたSQLを準備
        $stmt -> bindParam(':id', $id, PDO::PARAM_INT);
        $stmt -> execute(); //実行

        $results = $stmt -> fetchAll(); //抽出した全レコードを2次元配列に格納
        foreach ($results as $row){ //連想配列$rowに繰り返し格納
            if($row['pass'] == $pass3){ //パスワードが投稿時と一致するとき 
                $editnum =$row['id'];
                $editname =$row['name'];
                $editcom =$row['comment'];
                $editpass =$row['pass'];
                //編集するものを取得、フォームに反映
                $success3A = "投稿フォームから編集して下さい"; //フォーム下に出力
            } else{
                $error3C = "パスワードが違います";
            }
        }
    //編集したいデータidが存在しない時のエラー3A?

    } else{
        $error3B = "パスワードの入力が必要です"; 
    }
}
//②編集して、送信後
if (!empty($number) && isset($comment)){
    if(!empty($name) && !empty($pass1)){ //名前とパスワードがちゃんとある時
        //テーブルのレコード編集↓
        $id = $number ; //このidのレコードを編集
        $date = date("Y/m/d H:i:s")." (編集済)";
        $sql = 'UPDATE tb_kejiban 
            SET name=:name, comment=:comment, date=:date, pass=:pass
            WHERE id=:id';
            //whereで特定のidのレコードを抽出して、update
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindParam(':id', $id, PDO::PARAM_INT);
        $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
        $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt -> bindParam(':date', $date, PDO::PARAM_STR);
        $stmt -> bindParam(':pass', $pass1, PDO::PARAM_STR);
        $stmt -> execute();
        $success3B = "編集しました"; //フォーム下に出力
        
    } elseif(empty($name)){ //名前(必須)がない時
        $error1A = "名前を入力して下さい"; //フォーム下に出力
    } elseif(empty($pass1)){ //パスワード(必須)がない時
        $error1B = "パスワードを設定して下さい"; //フォーム下に出力
    }
}
?>

<p>
↓投稿フォーム↓
<form action="" method="post"><!-- postメソッドでデータを送信 -->
    <input type="hidden" name="number" 
        value="<?php if(!empty($editnum)){echo $editnum;} ?>">
    <!-- hidden：ブラウザで非表示(投稿番号は編集されないように) -->
    <input type="text" name="name" placeholder="名前（必須）" 
        value="<?php if(!empty($editname)){echo $editname;} ?>"><br>
    <textarea name="comment" rows="2" cols="25" wrap="soft" 
        placeholder="コメント"><?php if(!empty($editcom)){echo $editcom;} ?></textarea><br>
    <!-- textareaにはvalue属性がない -->
    <input type="password" name="pass1" placeholder="Passwordを設定(必須)"
        value="<?php if(!empty($editpass)){echo $editpass;} ?>">
    <input type="submit" value= "投稿"><!-- 送信ボタン -->
</form>
    <?php 
    if(!empty($success1)){echo $success1;} //未定義エラー防止のif文
    if(!empty($success3B)){echo $success3B;}
    if(!empty($error1A)){echo $error1A;}
    if(!empty($error1B)){echo $error1B;}
    ?>
</p><br>
<p>
↓削除フォーム(投稿時のパスワードが必要)↓
<form action="" method="post">   
    <input type="number" name="delete" placeholder="削除番号(半角)"><br>
    <input type="password" name="pass2" placeholder="Password">
    <input type="submit" value= "削除"><!-- 削除ボタン -->
</form>
    <?php 
    if(!empty($success2)){echo $success2;}
    if(!empty($error2A)){echo $error2A;} //保留中
    if(!empty($error2B)){echo $error2B;}
    if(!empty($error2C)){echo $error2C;}
    ?>
</p><br>
<p>
↓編集フォーム(投稿時のパスワードが必要)↓
<form action="" method="post">   
    <input type="number" name="edit" placeholder="編集番号(半角)"><br>
    <input type="password" name="pass3" placeholder="Password">
    <input type="submit" value= "編集"><!-- 編集ボタン -->
</form>
    <?php 
    if(!empty($success3A)){echo $success3A;}
    if(!empty($error3A)){echo $error3A;} //保留中
    if(!empty($error3B)){echo $error3B;}
    if(!empty($error3C)){echo $error3C;}
    ?>
</p><br>
<p>
===========<投稿一覧>===========<br>
</p>

<?php
//ブラウザ表示
$sql = 'SELECT * FROM tb_kejiban';
$stmt = $pdo -> query($sql); //実行
$results = $stmt -> fetchAll(); //fetchAll：全ての結果行を配列に格納
foreach ($results as $row){ //$rowの中にはテーブルのカラム名が入る
	echo $row['id']."<br>";
	echo $row['name']."<br>";
    echo $row['comment']."<br>";
    echo $row['date']."<br>";
	echo "<hr>";
}

?>

</body>
</html>