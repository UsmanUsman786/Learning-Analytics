<?php
header('Access-Control-Allow-Origin: *');
date_default_timezone_set('Asia/Karachi');
include("servermodal.php");

    if ( 0 < $_FILES['file']['error'] ) {
        echo 'Error here';
    }
    else {
        if(file_exists('uploads/' . $_FILES['file']['name'])) {
            chmod('uploads/' . $_FILES['file']['name'],0755); //Change the file permissions if allowed
            unlink('uploads/' . $_FILES['file']['name']); //remove the file
        }
        $temp = explode(".", $_FILES["file"]["name"]);
        var_dump($_POST);
        $newfilename = $_REQUEST['newname'];     //round(microtime(true)) . '.' . end($temp);
        move_uploaded_file($_FILES["file"]["tmp_name"], 'uploads/' . $newfilename);

        echo $newfilename;
    }
?>