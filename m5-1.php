<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
</head>
<body>

<?php
// DB接続設定
$dsn = 'mysql:dbname=xxxxdb;host=localhost';
    $user = 'username';
    $password = 'password';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

// テーブルの作成
$sql = "CREATE TABLE IF NOT EXISTS abcdefg"
    . " ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name CHAR(32),"
    . "comment TEXT,"
    . "password VARCHAR(255),"
    . "date TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    . ");";
$stmt = $pdo->query($sql);

// フォームが送信されたかどうかを確認
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start(); // セッションを開始

    // 新規追加、編集、削除の処理
    if (isset($_POST["send"])) {
        // 新規投稿または編集モードの場合
        if (isset($_SESSION['editMode']) && $_SESSION['editMode'] == true) {
            // 編集モードの処理
            if (!empty($_POST["name"]) && !empty($_POST["txt"])) {
                $editNum = $_SESSION['editNum'];
                $name = $_POST["name"];
                $text = $_POST["txt"];
                $password = $_POST["password"];

                // データベース内の情報を更新
                $sql = "UPDATE abcdefg SET name=:name, comment=:comment, date=DEFAULT, password=:password WHERE id=:id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $text, PDO::PARAM_STR);
                $stmt->bindParam(':id', $editNum, PDO::PARAM_INT);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    echo "編集しました！<br>";
                    // 編集モードのフラグを解除
                    unset($_SESSION['editMode']);
                } else {
                    echo "Error updating record: " . $stmt->errorInfo()[2];
                }
            } else {
                echo "名前とコメントを入力してください<br>";
            }
        } else {
            // 新規投稿の処理
            if (!empty($_POST["name"]) && !empty($_POST["txt"]) && !empty($_POST["password"])) {
                $name = $_POST["name"];
                $text = $_POST["txt"];
                $password = $_POST["password"];

                // 新規投稿をデータベースに挿入
                $sql = "INSERT INTO abcdefg (name, comment, password) VALUES (:name, :comment, :password)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $text, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    echo "追加しました！<br>";
                    // セッション変数をクリア
                    unset($_SESSION['editNum']);
                } else {
                    echo "Error inserting record: " . $stmt->errorInfo()[2];
                }
            } else {
                echo "名前、コメント、およびパスワードを入力してください<br>";
            }
        }
    }

    // 削除フォームが送信されたかどうかを確認
    elseif (isset($_POST["delete"])) {
        if (!empty($_POST["deleteNum"]) && !empty($_POST["deletePassword"])) {
            $deleteNum = $_POST["deleteNum"];
            $deletePassword = $_POST["deletePassword"];

            // データベースからコメントを削除
            $sql = "DELETE FROM abcdefg WHERE id=:id AND password=:password";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $deleteNum, PDO::PARAM_INT);
            $stmt->bindParam(':password', $deletePassword, PDO::PARAM_STR);

            if ($stmt->execute()) {
                echo "削除しました！<br>";
            } else {
                echo "削除できませんでした。<br>";
            }
        } else {
            echo "パスワードが一致しません！<br>削除したい番号とパスワードを入力してください<br>";
        }
    }

    // 編集フォームが送信されたかどうかを確認
    elseif (isset($_POST["edit"])) {
        if (!empty($_POST["editNum"]) && !empty($_POST["editPassword"])) {
            $editNum = $_POST["editNum"];
            $editPassword = $_POST["editPassword"];

            // データベースから編集対象のコメントを取得
            $sql = "SELECT * FROM abcdefg WHERE id=:id AND password=:password";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $editNum, PDO::PARAM_INT);
            $stmt->bindParam(':password', $editPassword, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $editName = $row["name"];
                $editText = $row["comment"];

                $_SESSION['editNum'] = $editNum;
                $_SESSION['editName'] = $editName;
                $_SESSION['editText'] = $editText;

                // 編集モードのフラグをセット
                $_SESSION['editMode'] = true;
            } else {
                echo "編集対象番号が見つかりませんでした。<br>";
            }
        } else {
            echo "パスワードが一致しません！<br>編集対象番号とパスワードを入力してください<br>";
        }
    }

    // セッション終了
    session_write_close();
}

// フォーム
echo '<form method="post" action="">';
echo '名前：<input type="text" name="name" placeholder="名前を入力" value="' . (isset($_SESSION['editMode']) && $_SESSION['editMode'] && isset($_SESSION['editName']) ? $_SESSION['editName'] : '') . '"><br>';
echo 'コメント：<input type="text" name="txt" placeholder="コメントを入力" value="' . (isset($_SESSION['editMode']) && $_SESSION['editMode'] && isset($_SESSION['editText']) ? $_SESSION['editText'] : '') . '">';
echo 'パスワード：<input type="password" name="password" placeholder="パスワードを入力">';
echo '<button type="submit" name="send">送信</button><br><br>';

// 削除フォーム
echo '<form method="post" action="">';
echo '削除番号：<input type="text" name="deleteNum" placeholder="削除対象番号を入力">';
echo 'パスワード：<input type="password" name="deletePassword" placeholder="パスワードを入力">';
echo '<button type="submit" name="delete">削除</button><br>';
echo '</form>';

// 編集フォーム
echo '<form method="post" action="">';
echo '編集番号：<input type="text" name="editNum" placeholder="編集対象番号を入力" value="' . (isset($_SESSION['editMode']) && $_SESSION['editMode'] && isset($_SESSION['editNum']) ? $_SESSION['editNum'] : '') . '">';
echo 'パスワード：<input type="password" name="editPassword" placeholder="パスワードを入力">';
echo '<button type="submit" name="edit">編集</button>';
echo '</form>';

// 保存されたコメントを表示
$sql = "SELECT * FROM abcdefg";
$stmt = $pdo->query($sql);

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row["id"] . " : " . $row["name"] . "　". $row["date"]  . "<br>".  $row["comment"] . "<br>";
    }
} 

// データベース接続を閉じる
$pdo = null;
?>

</body>
</html>