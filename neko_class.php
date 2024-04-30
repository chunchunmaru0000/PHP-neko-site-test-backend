<?php
class NekoPost {
    public int $Id;
    public string $Image;
    public float $Price;
    public string $Name;
    public string $Description;
    public object $Specifications;
    public array $Photos;

    public static function get(stdClass $json_neko): NekoPost {
        $neko = new NekoPost();
        $neko->Id = $json_neko->id;
        $neko->Name = $json_neko->name;
        $neko->Image = $json_neko->image;
        $neko->Description = $json_neko->description;
        $neko->Price = $json_neko->price;
        $neko->Specifications = $json_neko->specifications;
        $neko->Photos = $json_neko->photos;
        return $neko;
    }

    public function insert_into_tables($conn, $id): void {
        $neko_photos = $this->Photos;
        $neko_specs =  $this->Specifications;
        // specs
        $specs = [];
        foreach ($neko_specs as $spec => $spec_value)
            $specs[] = "('$id', '$spec', '$spec_value')";
        $query_specs_insert = "insert into specs values" . implode(", ", $specs);
        // photos and blobs
        $photos = [];
        $blobs = [];
        foreach ($neko_photos as $photo)
            if (str_starts_with($photo, 'http'))
                $photos[] = "('$id', '$photo')";
            else{
                $blob = implode("", array_slice(explode(",", $photo), 1));
                $blob = base64_decode($blob);
                $blobs[] = $blob;
         }

        $query_photos_insert = "insert into neko_photos values" . implode(", ", $photos);
        $query_blobs_insert = "insert into neko_blobs values" . "('$id', ?)";

        if (count($specs) > 0)
            $conn->execute_query($query_specs_insert);
        if (count($photos) > 0)
            $conn->execute_query($query_photos_insert);

        if (count($blobs) > 0){
            $blob = 0; // error below i dunno but it works
            $query_blobs_insert = $conn->prepare($query_blobs_insert);
            $query_blobs_insert->bind_param("b", $blob);
            foreach ($blobs as $blob) {
                $query_blobs_insert->send_long_data(0, $blob);
                $query_blobs_insert->execute();
            }
        }
    }
}
