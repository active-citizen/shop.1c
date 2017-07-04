<?
$answer = array(
    "offset"=>  10,
    "total" =>  200,
    "error" =>  '',
    "name"  =>  $_GET["name"]
);

echo json_encode($answer);
