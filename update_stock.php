<?php 
ini_set('max_execution_time', 3600);

// $argv[1] is used for put warehouse as argument
//$stockIndex = $argv[1];
// Silent mode
define('SILENT', false);

// connect model
require 'model.php';

// Get JSON from source file
$products = file_get_contents('stock.json');

// Convert JSON file into Array of rows
$productsArray = json_decode($products, true);

// 
foreach ($productsArray as $currentProduct) {
    $currentID = lineExists($currentProduct['sku'] . '_' . $currentProduct['warehouse']);
    if ($currentID) {
        echo('Product_exists - updating# ' . $currentID . "\n");
        updateProductStock($currentProduct);;
    } else { 
        echo('Product does not exist - skipping # ' . $currentID . "\n"); 
        //exit;  
    }
}

