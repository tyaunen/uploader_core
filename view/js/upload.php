<?php

if((isset($_GET["id"]))&&(isset($_GET["update"]))){
    $userID = $_GET["id"];
    $updateFlag = $_GET["update"];
    $dir = 'files/'.$userID.'/';
    $fileList = scandir($dir);
    $dirSize = 0;
    $isFileNameDouble = false;
    $isHeaderNotAlf = false;
    $timestamp = $_SERVER['REQUEST_TIME'];
    
    require('connectdb.php');

    $isBadHeader = false;
    $isSetHeader = false;
    if(isset($_GET["header"])){
        $isSetHeader = true;
        if(!preg_match("/^[a-zA-Z0-9:.()_-]+$/", $_GET["header"])){
            $isBadHeader = true;
        }
    }
    
    try{
        $pdo = new PDO($dsn, $user, $DbPass);
        $sql = "select * from user_table where userID='$userID'";
        $stmt = $pdo->query($sql);
        $count = $stmt->rowCount();
        if($count==0){
                        echo '<div class="col-xs-12 col-md-4 text-center">';
                        echo '<div class="panel panel-danger">';
                        echo '<div class="panel-body">';
                        echo    '<img src="img/oh.jpg" width=100px height=50px>';
                        echo '</div>';
                        echo '<div class="panel-footer panel-danger dengar-font" style="word-break: break-all;">';
                        echo    "<p>".$_FILES['file']['name'][0]."</p>";
                        echo    '<span style="color:#ff0000;">アップロード失敗！<br>セッション切れかも。ログインし直してみてください！</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
        }else{
            $gyo=$stmt->fetch(PDO::FETCH_ASSOC);
            $maxsize = $gyo['size'];
           
            if(in_array($_FILES['file']['name'][0], $fileList)){
                    $isFileNameDouble = true;
            }
                        
            if($isFileNameDouble && ($updateFlag==0)){
                        echo '<div class="col-xs-6 col-md-3 text-center col-nomargin">';
                        echo '<div class="panel panel-primary">';
                        echo '<div class="panel-body">';
                        echo    '<img src="img/oh.jpg" width=100px height=50px>';
                        echo '</div>';
                        echo '<div class="panel-footer" style="word-break: break-all;">';
                        echo    "<p>".$_FILES['file']['name'][0]."</p>";
                        echo    '<span style="color:#0000ff;">アップロードキャンセル！<br>同名ファイルがあるみたいです！</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
            }else if(($dirSize/(1024*1024))>$maxsize){
                        echo '<div class="col-xs-6 col-md-3 text-center col-nomargin">';
                        echo '<div class="panel panel-danger">';
                        echo '<div class="panel-body">';
                        echo    '<img src="img/oh.jpg" width=100px height=50px>';
                        echo '</div>';
                        echo '<div class="panel-footer panel-dange dengar-fontr" style="word-break: break-all;">';
                        echo    "<p>".$_FILES['file']['name'][0]."</p>";
                        echo    '<span style="color:#ff0000;">アップロード失敗！<br>使える容量が足りないみたいです！</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
            }else if((!preg_match("/^[a-zA-Z0-9:.()_-]+$/", $_FILES['file']['name'][0]))&&(!$isSetHeader)){
                        echo '<div class="col-xs-6 col-md-3 text-center col-nomargin">';
                        echo '<div class="panel panel-danger">';
                        echo '<div class="panel-body">';
                        echo    '<img src="img/oh.jpg" width=100px height=50px>';
                        echo '</div>';
                        echo '<div class="panel-footer panel-danger dengar-font" style="word-break: break-all;">';
                        echo    "<p>".$_FILES['file']['name'][0]."</p>";
                        echo    '<span style="color:#ff0000;">アップロード失敗！<br>ファイル名は半角英数記号のみにしてください！</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
            }else if($isBadHeader){
                        echo '<div class="col-xs-6 col-md-3 text-center col-nomargin">';
                        echo '<div class="panel panel-danger">';
                        echo '<div class="panel-body">';
                        echo    '<img src="img/oh.jpg" width=100px height=50px>';
                        echo '</div>';
                        echo '<div class="panel-footer panel-danger dengar-font" style="word-break: break-all;">';
                        echo    "<p>".$_FILES['file']['name'][0]."</p>";
                        echo    '<span style="color:#ff0000;">アップロード失敗！<br>指定するファイル名の前半部分は半角英数記号のみにしてください！！</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
            }else{
                    //ファイル命名　指定が無ければ自動連番命名
                    $fileName = $_FILES['file']['name'][0];
                    if((isset($_GET["header"]))){
                        $header = $_GET["header"];
                        $ext = substr($fileName, strrpos($fileName, '.'));
                        $isSearchingIndex = true;
                        $index = 0;

                        //現在のファイル名一覧から拡張子なしの名前一覧を取り出す
                        for($i=0; $i<count($fileList); $i++){
                            $pathData = pathinfo($fileList[$i]);
                            $fileNameList[$i] = $pathData["filename"];
                        }

                        //同名ファイルの検索、なかった次点でindexの決定
                        while($isSearchingIndex){
                            $index++;
                            $index = sprintf('%03d', $index);    
                            $isSearchingIndex = in_array($header.$index, $fileNameList);
                        }
                        $fileName = $header.$index.$ext;
                    }

                    //一字ファイルができているか（アップロードされているか）チェック
                    if(is_uploaded_file($_FILES['file']['tmp_name'][0])){
                        if(move_uploaded_file($_FILES['file']['tmp_name'][0],"files/".$userID."/".$fileName)){
                            //$imagesize = getimagesize($domeinUrl.'picture.php?user='.$userID.'&file='.$fileName);
                            echo '<div class="col-xs-6 col-md-3 text-center col-nomargin">';
                                echo '<div class="panel panel-default">';
                                $escapedFileName = preg_replace('/\(/', '\(', $fileName);
                                $escapedFileName = preg_replace('/\)/', '\)', $escapedFileName);
                                    echo '<div class="panel-body copy" data-clipboard-text="'.$domeinUrl.'picture.php?user='.$userID.'&file='.$fileName.'" copyElements  onclick=\'this.style.backgroundColor = "#D7EEFF"; \'>';
                                        echo '<div class="picWindow" style="min-height: 100px; background-image: url(picture_upload.php?d='. $timestamp .'&user='.$userID.'&file='.$escapedFileName.')">';
                                        echo '</div>';
                                    echo '</div>';
                                    echo '<div class="panel-footer" style="word-break: break-all;">';
                                    echo    'アップロード完了！<br>';
                                    echo    '<a href="https://moo-tyaunen.ssl-lolipop.jp/txiloda/picture.php?user='.$userID.'&file='.$fileName.'">'.$fileName.'</a><br>';

                                    $ua = $_SERVER['HTTP_USER_AGENT'];
                                    if (strpos($ua, 'iPhone') !== false){
                                        echo '<button class="btn btn-default iosbtn copy" type="button"  data-clipboard-text="'.$domeinUrl.'picture.php?user='.$userID.'&file='.$fileName.'" onclick=\'this.style.backgroundColor = "#D7EEFF"; \'>コピー</button>';
                                    }
                                    echo '</div>';                               
                                echo '</div>';
                            echo '</div>';
                           
                        }else{
                            echo "ファイルの保存に失敗しました。";
                        }
                    }else{
                        //そもそもファイルが来ていない。
                        echo "ファイルの送信に失敗しました。";
                        echo $_FILES['file']['tmp_name'][0];
                    }
            }
        }
    } catch (PDOException $Exception) {
         die('接続エラー:'.$Exception->getMessage());
    }
}else{
    echo "送信データが不正みたいです。";
}