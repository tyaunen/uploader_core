<?php
/**
 * picture.php
 * 
 * picture.php リクエストを受け取った時に、リクエストに応じたファイルを出力する。
 * ファイルが存在しなかった場合はエラーとして単純にエラー文字列を出力する。
 */

$user_name = $_GET['user'] ?? "";
$file_name = $_GET['file'] ?? "";

$file_path = "../files/{$user_name}/{$file_name}";

// ファイル名、ユーザー名のいずれかが未指定なら、エラーとして終了
if($user_name === "" || $file_name === "") {
    echo "不正なURLです。";
	exit;
}

// ファイルパスにファイルが存在していないなら、エラーとして終了
if(!file_exists($file_path)){
	echo "ファイルが存在しません。";
	exit;
}

// ファイルのMIMEタイプを確認
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($file_path);

// キャッシュ関係
// Last-modified と ETag 生成
$last_modified = gmdate( "D, d M Y H:i:s T", filemtime( $file_path ) );
$etag = md5( $last_modified . $file_name );

// リクエストヘッダの If-Modified-Since と If-None-Match を取得
$if_modified_since = filter_input( INPUT_SERVER, 'HTTP_IF_MODIFIED_SINCE' );
$if_none_match = filter_input( INPUT_SERVER, 'HTTP_IF_NONE_MATCH' );

// Last-modified または Etag と一致していたら 304 Not Modified ヘッダを返して終了
if ($if_modified_since === $last_modified || $if_none_match === $etag ) {
	header( 'HTTP', true, 304 );
	exit;
}

// 最終更新でキャッシュされてない + ファイルの存在を確認できたならコンテンツ表示
// 参考：https://www.php.net/manual/ja/function.readfile.php
header("Content-Type: {$mime_type}");
header("Last-Modified: {$last_modified}");
header("Etag: {$etag}");
readfile($file_path);
exit;


