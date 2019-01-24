
<?php


//共通変数・関数ファイルの読み込み
require('function.php');



// 検索
$seach = (!empty($_GET['seach'])) ? $_GET['seach'] : '';

// DBからメモのデータを取得
$dbmemoData = getMemo($seach);

 // var_dump($dbmemoData);
 // print_r($dbmemoData);
   



// コメントが保存のPOST送信されていた場合
if(!empty($_POST['save'])){
	debug('保存のPOST送信があります。');

	//変数に代入
	$comment = $_POST['comment'];

	// 文字数チェック
	validMaxLen($comment, 'comment');

	// 未入力チェック
	validRequired($comment,'comment');

	if(empty($err_msg)){
		debug('バリデーションOKです。');

			// 例外処理
			try {
				// DBへ接続
				$dbh = dbConnect();
				// SQL文作成
				$sql = 'INSERT INTO detail (comment) VALUES(:comment)';
				$data = array(':comment' => $comment);

				// クエリ実行
				queryPost($dbh, $sql, $data);

				header("Location:index.php");

			} catch (Exception $e) {
				error_log('エラー発生:' . $e->getMessage());
				$err_msg['common'] = MSG03;
			}
		}
	}



// 削除のPOST送信がされていた場合
if(!empty($_POST['delete_id'])){
	debug('削除のPOST送信があります。');

	// 変数の代入
	$id = $_POST['delete_id'];

	// 例外処理
	try {
		// 例外処理
		$dbh = dbConnect();
		// SQL文作成
		$sql = 'UPDATE detail SET delete_flg = 1 WHERE id = :delete_id';
		$data = array (':delete_id' => $id);
		// クエリ実行
		$stmt = queryPost($dbh, $sql, $data);

		header("Location:index.php");


	} catch (Exception $e) {
		error_log('エラー発生:' .$e->getMessage());
		$err_msg['common'] = MSG03;
	}
}


//編集のPOST送信されていた場合
if(!empty($_POST['edit_id'])){
  debug('編集のPOST送信があります。');

  global $id;
  $id = $_POST['edit_id'];

 // 例外処理
    try {
     	// DBへ接続準備
     	$dbh = dbConnect();
    	// SQL文作成
    	$sql = 'SELECT * FROM detail WHERE delete_flg IS NULL AND id = :edit_id';
    	$data = array(':edit_id' => $id);
		// クエリ実行
		$stmt = queryPost($dbh, $sql, $data);

    	$result = $stmt->fetch(PDO::FETCH_ASSOC);
    	global $editMemo;
    	$editMemo = $result['comment'];
  
        debug('クエリ結果の中身：'.print_r($result,true)); 
        debug('クエリ結果の中身：'.print_r($editMemo,true)); 

   

	} catch (Exception $e) {
    	error_log('エラー発生:' .$e->getMessage());
    	$err_msg['common'] = MSG03;
	}

}

// コメントが更新のPOST送信されていた場合
if(!empty($_POST['edit'])){
	debug('更新のPOST送信があります。');

	//変数に代入
	$comment = $_POST['comment'];

	// 文字数チェック
	validMaxLen($comment, 'comment');

	// 未入力チェック
	validRequired($comment,'comment');

	if(empty($err_msg)){
		debug('バリデーションOKです。');

			// 例外処理
			try {
				// DBへ接続
				$dbh = dbConnect();
				// SQL文作成
				$sql = 'UPDATE detail SET comment = :comment WHERE id = :edit_id';
				$data = array(':comment' => $comment, ':edit_id' => $id);

				// クエリ実行
				queryPost($dbh, $sql, $data);

				// debug('SQL：'.print_r($data,true));

				header("Location:index.php");

			} catch (Exception $e) {
				error_log('エラー発生:' . $e->getMessage());
				$err_msg['common'] = MSG03;
			}
		}
	}







?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>MEMO</title>
	<link rel="stylesheet" type="text/css" href="style.css">
	<link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
</head>

<body class="page-home page-2colum">

	<!-- メニュー -->
	<header>
		<div class="site-width">
			<h1><a href="index.php">MEMO</a></h1>
		</div>
	</header>

	<!-- メインコンテンツ -->
	<div id="contents" class="site-width">

		<!-- サイドバー -->
		<section id="sidebar">
			<form>
				<h1 class="title">検索</h1>

				<input type="text" name="seach">
				<div class="btn-container">
					<input type="submit" action="" class="btn btn-mid" value="検索">
				</div>
			</form>
		</section>



		<!-- Main -->
		<section id="main">

			<div class="form-container">
				
				<form action="" method="post" class="form">
					<!-- メモ入力フォーム -->
					<div class="area-msg">
						<?php 
						if(!empty($err_msg['common'])) echo $err_msg['common']; 
						?>
					</div>

					<label class="<?php if(!empty($err_msg['comment'])) echo 'err'; ?>">
						<textarea name="comment" id="js-count" cols="10" rows="10"><?php if(!empty($_POST['edit_id'])) echo $editMemo; ?></textarea> 
					</label>

					<div class="area-msg">
             		<?php 
             		if(!empty($err_msg['comment'])) echo $err_msg['comment'];
             		?>
           			</div>

					<!-- 登録ボタン -->
					<div class="btn-container">
						<input type="submit" class="btn btn-mid" value="登録" name="save">

						<!-- 更新ボタン -->
						
						<?php if(!empty($_POST['edit_id'])) { ?>
						<input type="submit" class="btn btn-mid" value="更新" name="edit">
						<input type="hidden" name="edit_id" value="<?php echo $_POST['edit_id']; ?>" >
						<?php } ?>

					</div>
				</form>


			<div class="search-title">
				<div class="seach-right">
					<span>全<?php echo sanitize($dbmemoData['total']); ?>件</span>
				</div>
			</div>


			<!-- メモ一覧表示 -->
			<div class="list">
				
				<?php
					foreach ((array)$dbmemoData['data'] as $key => $val):
				?>
				
				<table>
		          	<tr>
		          		<td><?php echo sanitize($val['update_date']); ?></td>

		          		<td class="edit">
							<form action="" method="post" class="form">
		          				<input type="submit" name="" class="btn btn-mid" value="編集">
		          				<input type="hidden" name="edit_id" value="<?php echo sanitize($val['id']); ?>">
							</form>
		          		</td>

		          		<td class="delete">
							<form action="" method="post" class="form">
		          				<input type="submit" name="" class="btn btn-mid" value="削除">
		          				<input type="hidden" name="delete_id" value="<?php echo sanitize($val['id']); ?>">
							</form>
		          		</td>
		          	</tr>
		          		
		          	<tr><td><?php echo sanitize($val['comment']); ?></td></tr>

		          	

		        </table>
					
				<?php
		            endforeach;
		        ?>	
          	</div>






				







			</div>

		</section>
		
	</div>
</body>
</html>