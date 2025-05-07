<?php
    require '_session.php';

    require 'config.php';
    $keywords = $_POST["keywords"];

    $outData = array();

    $datas = $database->select("monitor", "*", ["item_name[~]" => $keywords, "ORDER" => ["id" => "DESC"]]);
    if ($datas)
    {
        foreach ($datas as $data)
        {
            array_push($outData, array("id" => $data["id"], "item_name" => $data["item_name"], "item_url" => $data["item_url"], "item_price" => $data["item_price"], "user_price" => $data["user_price"], "mall_name" => $data["mall_name"], "status" => $data["status"]));
        }

        echo json_encode($outData);
    }
    else
    {
        echo json_encode(array("error" => "100"));
    }

?>