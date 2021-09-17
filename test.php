<?php 

//$products = file_get_contents('p1.json');
//$arrayOfProducts =  explode(']', explode('[', $products)[1]);

$content = '{"pr_0":"9,731.11","sku":"550027940","warehouse":"msk","manufacturer":"SHELL","name":"Spirax S6 GXME75W-80 20L","quantity":"100.00","pr_3":"9,267.72","pr_2":"9,545.75","pr_1":"9,360.40"}';
$test = '{"test1":{"a":{"a":1,"b":2,"c":3},"b":2,"c":3}}';
$arr = json_decode($test, false);

var_dump($arr);

// foreach ($products as $productJSON) {
//     $inner = json_decode($productJSON, true);
//     echo($inner['sku'] . '_' . $inner['warehouse']);
// }