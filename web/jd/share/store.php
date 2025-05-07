<?php
//    header("Content-Type: application/json;charset=utf-8");
    require '../config.php';
    $share_link = $_POST["share_link"];
    $share_point  = $_POST["share_point"];
    $share_source = $_POST["share_source"];
    $share_pwd    = $_POST["share_pwd"];
    $share_id     = $_POST["share_id"];
    $edition      = $_POST["edition"];
    $version      = $_POST["version"];
    $browser      = $_POST["browser"];
    $uid          = $_POST["uid"];
    $aid          = $_POST["aid"];
    $mode         = $_POST["mode"];


    $queryData = $database->get("share_pwd", "*", ["share_link" => $share_link,
                                                   "share_id" => $share_id]);
    if($queryData["share_pwd"] === $share_pwd)
    {
        echo json_encode(array("code"=>1,"msg" => "已添加过","data"=>$database->id()));
        return;
    }
    if (isset($_POST["share_link"]))
    {
        $database->insert("share_pwd", ["share_link" => $share_link,
                                        "share_point" => $share_point,
                                        "share_source" => $share_source,
                                        "share_pwd" => $share_pwd,
                                        "share_id" => $share_id,
                                        "edition" => $edition,
                                        "version" => $version,
                                        "browser" => $browser,
                                        "uid" => $uid,
                                        "aid" => $aid,
                                        "mode" => $mode]);
        echo json_encode(array("code"=>1,"msg" => "添加完毕","data"=>$database->id()));
    }


?>