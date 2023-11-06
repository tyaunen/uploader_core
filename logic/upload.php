<?php

$user_id = $_GET["user_id"] ?? "";
$update_mode = $_GET["update_mode"] ?? "99";
$file_name_header = $_GET["header"] ?? "";

// POSTデータチェック
if($user_id === "" || $update_mode === "99"){
    echo json_encode(
        [
            "is_success" => 9,
            "message" => "送信データが不正みたいです。"
        ],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$upload_dir = "../files/{$user_id}/";
$file_in_upload_dir = scandir($upload_dir);
$upload_file_name = $_FILES['file']['name'][0];

// 同名ファイルアップロードチェック
if(in_array($upload_file_name, $file_in_upload_dir) && $update_mode == "0"){
    echo json_encode(
        [
            "is_success" => 9,
            "message" => "同名のファイルが存在します。"
        ],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

// ファイル名指定チェック
if($file_name_header !== ""){
    if(!preg_match("/^[a-zA-Z0-9._-]+$/", $file_name_header)){
        echo json_encode(
            [
                "is_success" => 9,
                "message" => "ファイル名に使えない文字が指定されています。"
            ],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}

if(!preg_match("/^[a-zA-Z0-9._-]+$/", $upload_file_name) && $update_mode != "2"){
    echo json_encode(
        [
            "is_success" => 9,
            "message" => "ファイル名に使えない文字が指定されています。"
        ],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}


//ファイル命名　指定が無ければ自動連番命名
if($update_mode == "2"){
    
    // 拡張子を取得
    $ext = substr($upload_file_name, strrpos($upload_file_name, '.'));

    // 現在のファイル名一覧から拡張子なしの名前一覧を取り出す
    $file_name_in_upload_dir = [];
    for($i=0; $i<count($file_in_upload_dir); $i++){
        $path_data = pathinfo($file_in_upload_dir[$i]);
        $file_name_in_upload_dir[] = $path_data["filename"];
    }

    $index = 0;
    $is_searched_index = true;

    // 同名ファイルの検索、なかった次点でindexの決定
    while($is_searched_index){
        $index++;
        $padding_index = sprintf('%05d', $index);    
        $is_searched_index = in_array($file_name_header.$padding_index, $file_name_in_upload_dir);
    }
    $upload_file_name = $file_name_header . $padding_index . $ext;
}

// 一時ファイルができているか（アップロードされているか）チェック
if(is_uploaded_file($_FILES['file']['tmp_name'][0])){
    // アップロード
    if(move_uploaded_file($_FILES['file']['tmp_name'][0], "../files/{$user_id}/{$upload_file_name}")){
        echo json_encode(
            [
                "is_success" => 0,
                "message" => "アップロード完了！",
                "file_name" => $upload_file_name,
            ],
            JSON_UNESCAPED_UNICODE
        );
    }else{
        echo json_encode(
            [
                "is_success" => 9,
                "message" => "ファイルの保存に失敗しました。"
            ],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}else{
    // そもそもファイルが来ていない
    echo json_encode(
        [
            "is_success" => 9,
            "message" => "ファイルの送信に失敗しました。"
        ],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}
