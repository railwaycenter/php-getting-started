<?php
    require 'config.php';

        $data = $database->select("monitor", ["id","item_url"],["status" => 1,"mall_name" =>"京东","ORDER" => ["id" => "DESC"]]);
        echo json_encode($data);

?>