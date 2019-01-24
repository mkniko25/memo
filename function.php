<?php
//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true;
//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}


//================================
// 定数
//================================
// エラーメッセージを定数に設定
define('MSG01','何も入力されていません。');
define('MSG02','255文字以内で入力してください');
define('MSG03','エラーが発生しました。しばらく経ってからやり直してください。');


//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();



//================================
// バリデーション関数
//================================
// エラーメッセージ格納用の配列
$err_msg = array();

// 未入力チェック
function validRequired($str, $key) {
  if($str === ''){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}

// 最大文字数チェック
function validMaxLen($str, $key, $max = 255) {
  if(mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}



//================================
// データベース
//================================
//DB接続関数
function dbConnect(){
  //DBへの接続準備
  $dsn = 'mysql:dbname=memo;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

//SQL実行関数
function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました。');
    debug('失敗したSQL：'.print_r($stmt,true));
    $err_msg['common'] = MSG03;
    return 0;
  }
  debug('クエリ成功。');
  return $stmt;
}

// メモのデータ取得
function getMemo($seach) {
  // 例外処理
  try {
    // DBへ接続準備
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM detail WHERE delete_flg IS NULL';
    // 検索文字が入っていたら
    if(!empty($seach)) $sql .= " AND comment LIKE '%" . $seach . "%'";

    //降順にするSQL文
    $sql .= ' ORDER BY create_date DESC';

    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
     
    

    if($stmt){
      //クエリの実行結果を取得
      $result['total'] = $stmt->rowCount();
      $result['data'] = $stmt->fetchAll();
      // $result = $stmt->rowCount();
    
      return $result;
      }
    } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}








 









//================================
// その他
//================================
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}





?>