<?php

//DB연결
require_once("../require/mysql.php");

$sql = "SELECT `user_id`,`file_date`
        FROM    `line_message`";
if($res = $_link->query($sql)){
    $list = array();
    while($row = $res->fetch_array(MYSQLI_ASSOC)){
        $list[] = $row;
    }
    $count = count($list);
}
// var_dump($list);

// echo $count;
// echo $list[11]['file_date'];
// echo $sql;
// var_dump($list);
// $list['file_date'] = base64_encode($list['file_date']);
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$type = array();
for ($i=0; $i < $count; $i++) {
    $type[$i] = finfo_buffer($finfo,$list[$i]['file_date']);
}


// // echo $type;
// var_dump($type);
// echo "<br>";
// for ($i=0; $i < $count; $i++) {
//     echo $i."番：";
//     echo $type[$i];
//     echo "<br>";
// }
// echo "<br>";
// for ($i=0; $i < $count; $i++) {
//     switch($type[$i]){
//         case "image/png":
//             echo $i."番：png <br>";
//             break;
//         case "image/jpeg":
//             echo $i."番：jpg <br>";
//             break;
//         case "image/png":
//             echo $i."番：png <br>";
//             break;
//         case "image/gif":
//             echo $i."番：gif <br>";
//             break;
//         case "video/mp4":
//             echo $i."番：mp4 <br>";
//             break;
//         case "video/avi":
//             echo $i."番：avi <br>";
//             break;
//         case "application/pdf":
//             echo $i."番：pdf <br>";
//             break;
//         case "application/zip":
//             echo $i."番：zip <br>";
//             break;
//         case "audio/mp4":
//             echo $i."番：mp4 <br>";
//             break;
//     }
// }

// echo $type[11];

// for ($i=0; $i < $count-1; $i++) {
//     if($img_extension = array_search($type[$i],$extension_array,true)){
//         echo $img_extension;
//     }
// }

// header('Content-type: image/png');
// header('Content-type: image/gif');
// header('Content-type: image/bmp');
// header('Content-Type: image/jpg');
// echo $list['file_date'];
// header('Content-type: image/gif');
// header('Content-Type: audio/mpeg');
// header('Content-Type: video/mp4');


 ?>
<script>
var fileTypes = ['image/gif'];
var file = <?=$list[11]['file_date']?>
function validFileType(file){
    for(var i =0; i < fileTypes.length; i++){
        if(file.type){
            if(file.type === fileTypes[i]){
                return true;
            }
        } else if(file.name.toLowerCase().endsWith('gif')){
            return true;
        }
    }
    return false;
}
if(file.type){
    if(file.type === fileTypes[i]){
            document.write("確認1");
        return true;
    }
} else if(file.name.toLowerCase().endsWith('gif')){
    document.write("確認2");
    return true;
} else {
    document.write("確認3");
}
</script>
