<?php
header('Content-Type: application/json');
include 'neko_class.php';
$json_neko = json_decode(file_get_contents('php://input'));

if(!empty($json_neko)){
    try {
        $neko = NekoPost::get($json_neko);
        $id = $neko->Id;
        // updating
        $query_nekos_update = "update nekos set name = '$neko->Name', image = '$neko->Image', price = '$neko->Price', desction = '$neko->Description' where id = '$id'";
        // deletions
        $query_specs_delete = "delete from specs where id = '$id'";
        $query_photos_delete = "delete from neko_photos where id = '$id'";
        $query_blobs_delete = "delete from neko_blobs where id = '$id'";
        // sql
        $conn = new mysqli('localhost', 'root', 'root', 'neko');
        $conn->begin_transaction();
        // upd
        $conn->execute_query($query_nekos_update);
        // delete
        $conn->execute_query($query_specs_delete);
        $conn->execute_query($query_photos_delete);
        $conn->execute_query($query_blobs_delete);
        // insert
        $neko->insert_into_tables($conn, $id);
        //close
        $conn->commit();
        $conn->close();
        echo json_encode("УДАЧНО ОБНОВЛЕННО");
    }
    catch (Exception $e){
        echo json_encode("ЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭ");
    }
}
else
    echo json_encode("ЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭ");
