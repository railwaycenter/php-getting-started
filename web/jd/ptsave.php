<?php
    require '_session.php';
?><?php
    require 'config.php';
    $value = $_POST['value'];
    $id    = $_POST['id'];

    if ($_POST['field'] == 'dl')
    {
        $database->update("siteinfo", ["site_download" => floatval($value)], ["id" => $id]);
        $itemdata = $database->get("siteinfo", "*", ["id" => $id]);
        echo $itemdata["site_download"];

        return;
    }

    if ($_POST['field'] == 'cdl')
    {
        $database->update("siteinfo", ["current_download" => floatval($value)], ["id" => $id]);
        $itemdata = $database->get("siteinfo", "*", ["id" => $id]);
        echo $itemdata["current_download"];

        return;
    }

    if ($_POST['field'] == 'cul')
    {
        $database->update("siteinfo", ["current_upload" => floatval($value)], ["id" => $id]);
        $itemdata = $database->get("siteinfo", "*", ["id" => $id]);
        echo $itemdata["current_upload"];

        return;
    }

    if ($_POST['field'] == 'addsite')
    {
        $database->insert("siteinfo", ["site_name" => $_POST["site_name"],
                                       "site_url" => $_POST["site_url"],
                                       "site_userclass" => $_POST["site_userclass"],
                                       "site_userclassinfo" => $_POST["site_userclassinfo"],
                                       "site_download" => $_POST["site_download"],
                                       "site_ratio" => $_POST["site_ratio"],
                                       "current_class" => $_POST["current_class"],
                                       "current_class_info" => $_POST["current_class_info"],
                                       "current_upload" => $_POST["current_upload"],
                                       "current_download" => $_POST["current_download"]]);
        //        $database->update("siteinfo", ["current_upload" => floatval($value)], ["id" => $id]);
        //        $itemdata = $database->get("siteinfo", "*", ["id" => $id]);
        echo json_encode(array("code" => "{$database->id()}"));

        return;
    }

    if ($_POST['field'] == 'desc')
    {
        $database->update("siteinfo", ["description" => $value], ["id" => $id]);
        $itemdata = $database->get("siteinfo", "*", ["id" => $id]);
        echo $itemdata["description"];

        return;
    }
?>