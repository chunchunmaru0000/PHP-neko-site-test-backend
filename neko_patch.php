<?php
header('Content-Type: application/json');
include 'neko_class.php';
$json_neko = json_decode(file_get_contents('php://input'));

if(!empty($json_neko)){
    try {
        $neko = new NekoPost();
        $neko->Id = $json_neko->id;
        $neko->Name = $json_neko->name;
        $neko->Image = $json_neko->image;
        $neko->Description = $json_neko->description;
        $neko->Price = $json_neko->price;
        $neko->Specifications = $json_neko->specifications;
        $neko->Photos = $json_neko->photos;

        $id = $neko->Id;
        $query_nekos_update = "update nekos set name = '$neko->Name', image = '$neko->Image', price = '$neko->Price', desction = '$neko->Description' where id = '$id'";
        // deletions
        $query_specs_delete = "delete from specs where id = {$id}";
        $query_photos_delete = "delete from neko_photos where id = {$id}";
        $query_blobs_delete = "delete from neko_blobs where id = {$id}";
        // specs
        $specs = [];
        foreach ($neko->Specifications as $spec => $spec_value)
            $specs[] = "('$id', '$spec', '$spec_value')";
        $query_specs_insert = "insert into specs values" . implode(", ", $specs);
        // photos and blobs
        $photos = [];
        //$blobs_str = [];
        $blobs = [];
        foreach ($neko->Photos as $photo)
            if (str_starts_with($photo, 'http'))
                $photos[] = "('$id', '$photo')";
            else{
                //$blobs_str[] = "('$id', ?)";
                $blob = implode("", array_slice(explode(",", $photo), 1));
                $blob = base64_decode($blob);
                $blobs[] = $blob;
            }

        $query_photos_insert = "insert into neko_photos values" . implode(", ", $photos);
        $query_blobs_insert = "insert into neko_blobs values" . "('$id', ?)";//implode(", ", $blobs_str);
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
        $conn->execute_query($query_specs_insert);
        $conn->execute_query($query_photos_insert);

        $query_blobs_insert = $conn->prepare($query_blobs_insert);
        $query_blobs_insert->bind_param("b", $blob);
        foreach ($blobs as $blob) {
            $query_blobs_insert->send_long_data(0, $blob);
            $query_blobs_insert->execute();
        }

        $conn->commit();
        $conn->close();
        echo json_encode("УДАЧНО ОБНОВЛЕННО");
    }
    catch (Exception){
       echo json_encode("ЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭ");
    }
}
else
    echo json_encode("ЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭЭ");
