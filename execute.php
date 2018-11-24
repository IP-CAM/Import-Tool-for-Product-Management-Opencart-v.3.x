<?php 
ini_set('max_execution_time', 3600);

// Silent mode
define('SILENT', false);

// connect model
require 'model.php';

// Get JSON from source file
$products = file_get_contents('upload.json');

// Convert JSON file into Array of rows
$productsArray = json_decode($products, true);
$counter = 0;
// 
foreach ($productsArray as $currentProduct) {
    $currentID = lineExists($currentProduct['sku'] . '_' . $currentProduct['warehouse']);
    if ($currentID) {
        echo('Product_exists - updating# ' . $currentID . "\n");
        updateProduct($currentProduct);;
    } else {  
        $success = putProduct($currentProduct);
        echo('Created-> ' . $success . "\n");  
    }
}

