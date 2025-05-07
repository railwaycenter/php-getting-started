<?php
    require '_session.php';
?>

<?php
    require  'config.php';
    if(isset($_POST["url"]))
    {
        $url = $_POST["url"];
        preg_match("/(http.*?html)/", $url, $pat_array);//去除html后面的内容,只保留原始链接
        $url = $pat_array[1]?$pat_array[1]:$url;
    }

    $data = $database->select("monitor", "*", ["item_url" => $url]);
    if($data)
    {
        echo json_encode(array("code" => "100"));
        return;
    }


    if(isset($_POST["price"]))
    {
        $price = $_POST["price"];
    }

    $today = date('Y-m-d H:i:s');

    $database->insert("monitor", [
        "item_url" => $url,
        "user_price" => $price,
        "status" => 1,
        "add_date" =>$today,
        // "user_send_message"=>10,
        "mall_name" =>$_POST["mallname"],
        "item_name" => $_POST["itemname"]
    ]);
    echo json_encode(array("addid" => "{$database->id()}"));
    ?>