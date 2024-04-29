<?php
header('Content-Type: application/json');
include "neko_class.php";


$conn = new mysqli('localhost', 'root', 'root', 'neko');
if ($conn->connect_error) die("параша не подключилась" . $conn->connect_error);

// specs
$res = $conn->query("select * from specs");
$specs = [];
if ($res->num_rows > 0)
    foreach ($res->fetch_all() as $sft)
        if (!array_key_exists($sft[0], $specs))
            $specs[(string)$sft[0]] = [(string)$sft[1] => (string)$sft[2]];
        else
            $specs[(string)$sft[0]][(string)$sft[1]] = (string)$sft[2];

// photos
$res = $conn->query("select * from neko_photos");
$photos = [];
if ($res->num_rows > 0)
    foreach ($res->fetch_all() as $pft)
        if (!array_key_exists($pft[0], $photos))
            $photos[$pft[0]] = [$pft[1]];
        else
            $photos[$pft[0]][] = $pft[1];

// blobs
$res = $conn->query("select * from neko_blobs");
if ($res->num_rows > 0)
    foreach ($res->fetch_all() as $bft)
        if (!array_key_exists($bft[0], $photos))
            $photos[$bft[0]] = ["data:image/octet-stream;base64," . base64_encode($bft[1])];
        else
            $photos[$bft[0]][] = "data:image/octet-stream;base64," . base64_encode($bft[1]);

// simple nekos
$res = $conn->query("select * from nekos");

$nekos = [];
if ($res->num_rows > 0){
    while ($row = $res->fetch_assoc()){
        $id = $row['id'];

        $specs_for_this = array_key_exists($id, $specs) ? $specs[$id] : json_decode('{}');

        $photos_for_this = array_key_exists($id, $photos) ? $photos[$id] : [];
        $image = $row["image"];
        if ($image == "любое")
            $image = count($photos_for_this) == 0 ? "любое" : $photos_for_this[0];

        $neko = new NekoPost();
        $neko->Id = (int)$id;
        $neko->Image = $image;
        $neko->Price = (float)$row["price"];
        $neko->Name = $row["name"];
        $neko->Description = $row["desction"];
        $neko->Specifications = $specs_for_this;
        $neko->Photos = $photos_for_this;
        $nekos[] = $neko;
    }
}
$conn->close();
echo json_encode($nekos);
