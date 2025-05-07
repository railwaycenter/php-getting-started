<?php
    //    header("Content-Type: application/json;charset=utf-8");
    require '../config.php';
    $share_link   = $_POST["share_link"];
    $share_point  = $_POST["share_point"];
    $share_source = $_POST["share_source"];
    //    $share_pwd    = $_POST["share_pwd"];
    $share_id = $_POST["share_id"];
    $edition  = $_POST["edition"];
    $version  = $_POST["version"];
    $browser  = $_POST["browser"];
    $uid      = $_POST["uid"];
    $aid      = $_POST["aid"];
    $mode     = $_POST["mode"];


    //    if (isset($_POST["price"]))
    //    {
    //        $price = $_POST["price"];
    //    }
    //
    //    $today = date('Y-m-d H:i:s');
    //
    if (isset($_POST["share_link"]))
    {
        $queryData = $database->get("share_pwd", "*", ["share_id" => $share_id]);
    }
    if ($queryData["share_pwd"] === null)
    {
        echo json_encode(array("code" => 0,
                               "msg" => "error"));
    }
    else
    {
        echo json_encode(array("code" => 1,
                               "msg" => "OK",
                               "data" => array("share_pwd" => $queryData["share_pwd"])));
    }

?>