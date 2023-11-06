<?php
/**
 * アップロード画面
 * 
 * この機能は、以下の2つに依存します
 * 
 * DropZone.js
 * ドラッグアンドドロップによるファイル送信をサポートするライブラリ
 * https://www.dropzone.dev/
 * 
 * clipboard.js
 * クリップボードにアクセスするライブラリ
 * https://clipboardjs.com/
 * 
 * 
 */

 $user_id = "test_chan";
 $DOMEIN = "localhost/teikiloda";

?>
<!DOCTYPE html>
<html lang="jp">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>アップロード</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    </head>
    <body>  
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-12">
                    <div id="input-box" style="height:200px; background-color:bisque;">
                        ここにファイルをD&Dしてください！
                    </div>
                    <div id="update-info">
                        <p style="color:#0000ff">
                            同名ファイル維持モードです！<br>
                            同じ名前のファイルが既にあるなら、<br>
                            ファイルはアップロードされません！
                        </p>
                    </div>
                    <div class="input-group">
                        <button type="button" onclick="updateModeChange(0)" class="btn btn-primary">同名維持</button>
                        <button type="button" onclick="updateModeChange(1)" class="btn btn-primary">上書き</button>
                        <button type="button" onclick="updateModeChange(2)" class="btn btn-primary">オート連番</button>
                    </div>
                    <div id="nowloading">
                        <!-- アップロード中はここにgifが表示される -->
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div id="output-box" class="row g-0">             
                        <!-- ここにアップデート結果が表示される --> 
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.4/clipboard.min.js"></script>
        <script src="js/dropzone.js"></script>
        <script type="text/javascript">
            new ClipboardJS('.copy-target');
            
            var updateMode = 0;
            var header = "picture";
            
            myDropzone = new Dropzone("#input-box", {
                url: "../logic/upload.php?user_id=<?= $user_id ?>&update_mode=0", 
                clickable: true,          //　クリックでアップロード
                method: "post",           // POSTでアップロード
                paramName : "file",       // パラメタ名
                uploadMultiple: true,     //　複数同時アップロードの許可
                parallelUploads: 1,       // 複数ファイルを投げられた時は一つずつアップロード
                maxFiles: 100,            // 同時に100ファイルまで
                maxFilesize: 5,           // 最大サイズ5MB
                acceptedFiles:'image/*',  //　画像のみを受け付ける
                previewsContainer:  "#output-box",
                dictFileTooBig: "ファイルが大きすぎます。 ({{filesize}}MiB). 最大サイズ: {{maxFilesize}}MiB.",
                dictInvalidFileType: "画像ファイルしかアップロードできません！ごめんね！",
                dictMaxFilesExceeded: "一度にアップロード出来るのは100ファイルまでです。",
                accept: function(file, done) {
                    // ナウローディング画像を表示する
                    document.getElementById("nowloading").innerHTML = '<img src="img/nowloading.gif" class="img-responsive" alt="がんばってるよ！">';
                    return done();
                },
                complete: function(file, message) {
                    // アップロードが全て終了したら、ナウローディング画像をdomから削除
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                        document.getElementById("nowloading").innerHTML = "";
                    }
                },
                success:function(file, response, e) {
                    response_obj = JSON.parse(response);
                    if(response_obj.is_success == "0"){
                        result_html = `
                        <div class="col-lg-3 col-md-4 col-sm-2 col-12">
                            <div class="copy-target" data-clipboard-text="<?= $DOMEIN ?>/logic/picture.php?user=<?= $user_id ?>&file=${response_obj.file_name}" onclick=\'this.style.backgroundColor = "#D7EEFF"; \'>
                                <img src="../logic/picture.php?user=<?= $user_id ?>&file=${response_obj.file_name}" class="img-fluid">
                                <a href="../logic/picture.php?user=<?= $user_id ?>&file=${response_obj.file_name}">${response_obj.file_name}</a>
                            </div>
                        </div>
                        `
                    }else{
                        result_html = `
                        <div class="col-lg-3 col-md-4 col-sm-2 col-12">
                            エラー：${response_obj.message}
                        </div>
                        `
                    }

                    // アップロード成功ごとに、アップロードした内容を表示する
                    document.getElementById("output-box").innerHTML = result_html + document.getElementById("output-box").innerHTML;
                },
                error: function(file, message) {
                    this.removeFile(file);
                }
            });
            myDropzone.options.previewTemplate = "<div></div>";

            function updateModeChange(mode){
                switch(mode){
                    case 0:
                        updateMode = 0;
                        myDropzone.options.url = "../logic/upload.php?user_id=<?= $user_id ?>&update_mode=0"
                        document.getElementById("update-info").innerHTML = '<p style="color:#0000ff">同名ファイル維持モードです！<br>同じ名前のファイルが既にあるなら、<br>ファイルはアップロードされません！</p>';
                        break;
                    case 1:
                        updateMode = 1;
                        myDropzone.options.url = "../logic/upload.php?user_id=<?= $user_id ?>&update_mode=1"
                        document.getElementById("update-info").innerHTML = '<p style="color:#ff0000">同名ファイル上書きモードです！<br>同じ名前のファイルは上書き更新されます！<br>元々あったファイルは消えるので注意してください！</p>';
                        break;
                    case 2:
                        updateMode = 2;
                        myDropzone.options.url = "../logic/upload.php?user_id=<?= $user_id ?>&update_mode=2&header=" + header;
                        document.getElementById("update-info").innerHTML = '<p style="color:#00aa00">オートリネームモードです！<br>ファイル名は自動的に<br><input type="text" class="form-control col-auto" style="width:auto; display:inline;" onKeyUp="textChange(this)" value="'+ header +'"> + 番号 <br>でアップロードされます！</p>';
                        break;
                }
            }
            
            
            function textChange($this){
                header = $this.value;
                if(updateMode == 2){
                    myDropzone.options.url = "../logic/upload.php?user_id=<?= $user_id ?>&update_mode=2&header=" + header ;
                }
            }
        </script>
    </body>
</html>
