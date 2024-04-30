<?php
header('Content-Type: application/json');
include 'neko_class.php';
$json_neko = json_decode(file_get_contents('php://input'));

if(!empty($json_neko)){
    try {
        $neko = NekoPost::get($json_neko);
        $query_neko_insert = "insert into nekos (name, image, price, desction) values('$neko->Name', '$neko->Image', '$neko->Price', '$neko->Description')";
        $query_ids = "select id from nekos";
        $conn = new mysqli('localhost', 'root', 'root', 'neko');
        // neko
        $conn->execute_query($query_neko_insert);
        $conn->commit();
        // get new neko id cuz of auto increment
        $id = 0;
        foreach ($conn->query($query_ids)->fetch_all() as $conn_id)
            $id = (int)$conn_id[0]; // 0 is position of column 'id' in the table of selected
        $neko->Id = $id;
        // transaction
        $conn->begin_transaction();
        // insert
        $neko->insert_into_tables($conn, $id);
        // close
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