<?php
    require '_session.php';
?><?php
    require 'config.php';
    $value = $_POST['value'];
    $id    = $_POST['id'];
    if ($_POST['field'] == 'user_price' && $value)
    {
        $database->update("monitor", ["user_price" => $value], ["id" => $id]);
        $itemdata = $database->get("monitor", "*", ["id" => $id]);
        echo $itemdata["user_price"];
        return;
    }

    if ($_POST['field'] == 'status')
    {
        $database->update("monitor", ["status" => intval($value)], ["id" => $id]);
        $itemdata = $database->get("monitor", "*", ["id" => $id]);
        echo $itemdata["status"];
        return;
    }

    if ($_POST['field'] == 'chartInfo')
    {
        $data = $database->select("monitor_item_history", ["[>]monitor" => ["monitor_id" => "id"]], ["item_name","monitor_item_history.monitor_date","monitor_item_history.monitor_price"], ["monitor_item_history.monitor_id" => $id,"ORDER" => ["monitor_date" => "ASC"]]);
        echo json_encode($data);
        return;
    }

    if ($_POST['field'] == 'item_name')
    {
        if(stristr($value,"://"))
        {
            preg_match("/(http.*?html)/", $value, $pat_array);//去除html后面的内容,只保留原始链接
            $value = $pat_array[1]?$pat_array[1]:$value;

            $database->update("monitor", ["item_url" => $value], ["id" => $id]);
            $itemdata = $database->get("monitor", "*", ["id" => $id]);
            echo $itemdata["item_url"];
            if(stristr($value,"jd.com"))
            {
                $database->update("monitor", ["item_name" => ''], ["id" => $id]);
                $itemdata = $database->get("monitor", "*", ["id" => $id]);
            }
            return;
        }
        else
        {
            $database->update("monitor", ["item_name" => $value], ["id" => $id]);
            $itemdata = $database->get("monitor", "*", ["id" => $id]);
            echo $itemdata["item_name"];
            return;
        }
    }

    if ($_POST['field'] == 'mall_name')
    {
        $database->update("monitor", ["mall_name" => $value], ["id" => $id]);
        $itemdata = $database->get("monitor", "*", ["id" => $id]);
        echo $itemdata["mall_name"];
        return;
    }

    if ($_POST['field'] == 'jdvalue' && $value)
    {
        $database->update("monitor_config", ["value" => $value], ["id" => $id]);

        if($id=='3')
        {
            $allpages = ceil($database->count("monitor", [
                    "status" => 1,
                    "mall_name" => "京东"
                ]) / $value);
            $database->update("monitor_config", ["value" => $allpages], ["key" => "allpages"]);
        }

        $itemdata = $database->get("monitor_config", "*", ["id" => $id]);
        echo $itemdata["value"];
        return;
    }

?>