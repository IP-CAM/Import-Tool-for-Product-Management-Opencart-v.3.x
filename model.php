<?php
/**
 * PHP Version 7
 * 
 * Adds or updates entries in database
 *        "7700500168": {
 *                       "sku": "7700500168", // Puts to 'model' of oc_product 
 *                       "warehouse": "m", // Puts to 'mpn' of oc_product
 *                       "manufacturer": "MOTRIO", // Puts id pf manufacturer (see specs.php) oc_product
 *                       "name": "СВЕЧА 3АЖИГАНИЯ (224013682R)", // Puts name into oc_product_description
 *                       "pr_3": 101, // Puts special prices into oc_product_special (see specs.php)
 *                       "pr_2": 201, // Puts special prices into oc_product_special (see specs.php)
 *                       "pr_1": 301, // Puts special prices into oc_product_special (see specs.php)
 *                       "pr_0": 401, // Puts price into oc_product (general price)
 *                       "quantity": 501 // Puts quantity into oc_product 'quantity' column
 *                       },
 * 
 * @author Oleg Kholyk <oleg@kholyk.ru>
 */

require 'db_config.php';
require 'specs.php';



$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}


/**
 * Checks if product line exists
 * 
 * @param string $model 'model' field in oc_product table
 * 
 * @return $currendId
 */
function lineExists($model)
{
    global $pdo;
    
    $sql = "SELECT product_id AS id FROM oc_product WHERE model = ?;"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$model]);
    $answer = $stmt->fetch();  

    return ($answer['id'] != null) ? $answer['id'] : false;
}

/**
 * Creates meta description for product
 * 
 * @param array $product array of JSON properties
 * 
 * @return string
 */
function makeMetaDescription($product)
{
    // meta description
    $metaDesc[] = "Купить ";
    $metaDesc[] = $product['name'];
    $metaDesc[] = ", ";
    $metaDesc[] = $product['sku'];
    $metaDesc[] = ", ";
    $metaDesc[] = $product['manufacturer'];
    $metaDesc[] = " на Ириновском 2 СПб";
    return implode('', $metaDesc);      
}

/**
 * Creates meta keywords for product
 * 
 * @param array $product array of JSON properties
 * 
 * @return string
 */
function makeMetaKeywords($product)
{
    // meta keywords
    $metaWords[] = $product['name'];
    $metaWords[] = ", ";
    $metaWords[] = $product['sku'];
    $metaWords[] = ", ";
    $metaWords[] = $product['manufacturer'];
    $metaWords[] = ", Ириновский 2";
    return implode('', $metaWords);      
}

/**
 * Updates product tables: main (1) and 3 dependent (2,3, URL)
 * (4,5,6) are skipped here
 * Uses $currentID as global
 * 
 * @param array $product data from JSON
 *                       Example of single JSON product item:
 *                       "7700500168": {
 *                       "sku": "7700500168",
 *                       "warehouse": "m",
 *                       "manufacturer": "MOTRIO",
 *                       "name": "СВЕЧА 3АЖИГАНИЯ (224013682R)",
 *                       "pr_3": 101,
 *                       "pr_2": 201,
 *                       "pr_1": 301,
 *                       "pr_0": 401,
 *                       "quantity": 501
 *                       },
 * 
 * @return void
 */
function updateProduct($product)
{
    global $pdo, $currentID, $manufacturers, $priceToGroup, $mainCategory;
    global $mainStore, $mainLayout;
    $manufacturerId = $manufacturers[$product['manufacturer']];
    

    // 1: Update product line itself
    $oc_product_sql = "UPDATE oc_product SET sku = ?, mpn = ?, quantity = ?, price = ?, manufacturer_id = ?, date_modified = CURRENT_DATE() WHERE model = ?;";
    $stmt = $pdo->prepare($oc_product_sql);
    $stmt->execute(
        [
            $product['sku'],
            $product['warehouse'],
            $product['quantity'],
            $product['pr_0'],
            $manufacturerId,
            $product['sku']
        ]
    );
    
    // 2: Update description
    $prodDesc_sql = "UPDATE `oc_product_description` SET `name` = ?, `meta_title` = ?, `meta_description` = ?, `meta_keyword` = ? WHERE `oc_product_description`.`product_id` = ? AND `oc_product_description`.`language_id` = 2;";
    $stmt = $pdo->prepare($prodDesc_sql);
    $stmt->execute(
        [
            $product['name'],
            $product['name'] . " - " . $product['sku'],
            makeMetaDescription($product),
            makeMetaKeywords($product),
            $currentID
        ]
    );
    
    // 3: Update prices
    $specialPrice_sql = "UPDATE `oc_product_special` SET `price` = ? WHERE `product_id` = ? AND `customer_group_id` = ?";
    $stmt = $pdo->prepare($specialPrice_sql);
    foreach ($priceToGroup as $index => $groupId) {
        $stmt->execute([$product[$index], $currentID, $groupId]);   
    }
    
    // 4: *
    // 5: *
    // 6: *
    
    // Update URL alias
    $seoURL_sql = "UPDATE `oc_seo_url` SET `keyword` = ? WHERE `query` = ?;";
    $stmt = $pdo->prepare($seoURL_sql);
    $stmt->execute([$product['sku'], "product_" . $currentID]);
}

/**
 * Creates product tables: main (1) and 6 dependent (2,3,4,5,6,7)
 * Uses $currentID as global
 * 
 * @param array $product data from JSON
 *                       Example of single JSON product item:
 *                       "7700500168": {
 *                       "sku": "7700500168",
 *                       "warehouse": "m",
 *                       "manufacturer": "MOTRIO",
 *                       "name": "СВЕЧА 3АЖИГАНИЯ (224013682R)",
 *                       "pr_3": 101,
 *                       "pr_2": 201,
 *                       "pr_1": 301,
 *                       "pr_0": 401,
 *                       "quantity": 501
 *                       },
 * 
 * @return $lastAddedID
 */
function putProduct($product)
{
    global $pdo, $manufacturers, $priceToGroup, $mainCategory;
    global $mainStore, $mainLayout;
    $manufacturerId = $manufacturers[$product['manufacturer']];

    // 1: Put data into oc_product table
    $manufacturerId = $manufacturers[$product['manufacturer']];

    $oc_product_sql = "INSERT INTO oc_product (model, sku, upc, ean, jan, isbn, mpn, location, quantity, stock_status_id, image, manufacturer_id, shipping, price, points, tax_class_id, date_available, weight, weight_class_id, length, width, height, length_class_id, subtract, minimum, sort_order, status, viewed, date_added, date_modified) VALUES ("
            . "?, ? , '', '', '', '', ?, '', ?, 8, '', ?, 1, ?, 0, 0, CURRENT_DATE(), '0.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 2, 1, 1, 1, 1, 0, CURRENT_DATE(), CURRENT_DATE());";

    $stmt = $pdo->prepare($oc_product_sql);
    $stmt->execute(
        [
            $product['sku'] . '_' . $product['warehouse'],
            $product['sku'],
            $product['warehouse'],
            $product['quantity'], $manufacturerId,
            $product['pr_0']
        ]
    );

    // Get last inserted row ID
    $lastAddedID = $pdo->lastInsertId();

    // 2: Put description for current product
    $metaTitle = $product['name'] . ' - ' . $product['sku'];
    
    $oc_product_description_sql = "INSERT INTO `oc_product_description` (`product_id`, `language_id`, `name`, `description`, `tag`, `meta_title`, `meta_description`, `meta_keyword`) VALUES ("
    . " ?, '2', ?, '', '', ?, ?, ?);";
    
    $stmt = $pdo->prepare($oc_product_description_sql);
    $stmt->execute(
        [
            $lastAddedID, $product['name'],
            $metaTitle,
            makeMetaDescription($product),
            makeMetaKeywords($product) 
        ]
    );

    // 3: Put other prices (special) for current product
    $oc_product_special_sql = "INSERT INTO `oc_product_special` (`product_special_id`, `product_id`, `customer_group_id`, `priority`, `price`, `date_start`, `date_end`) VALUES (NULL, "
        . " ?, ?, '1', ?, '0000-00-00', '0000-00-00');";

    $stmt = $pdo->prepare($oc_product_special_sql);
    foreach ($priceToGroup as $index => $groupId) {
        $stmt->execute(
            [
                $lastAddedID,
                $groupId,
                $product[$index]
            ]
        );
    }

    // 4: Product -> to category
    $oc_product_to_category_sql = "INSERT INTO `oc_product_to_category` (`product_id`, `category_id`) VALUES (?, ?);";
    $stmt = $pdo->prepare($oc_product_to_category_sql);
    $stmt->execute(
        [
            $lastAddedID, $mainCategory
        ]
    );

    // 5: Product -> to Store
    $oc_product_to_store_sql = "INSERT INTO `oc_product_to_store` (`product_id`, `store_id`) VALUES (?, ?);";
    $stmt = $pdo->prepare($oc_product_to_store_sql);
    $stmt->execute(
        [
            $lastAddedID,  $mainStore
        ]
    );

    // 6: Product -> to Layout
    $oc_product_to_layout_sql = "INSERT INTO `oc_product_to_layout` (`product_id`, `store_id`, `layout_id`) VALUES (?, ?, ?);";
    $stmt = $pdo->prepare($oc_product_to_layout_sql);
    $stmt->execute(
        [
            $lastAddedID, $mainStore, $mainLayout
        ]
    );

    // 7: Put URL query alias
    $currentQuery = 'product_id=' . $lastAddedID;
    $oc_seo_url_sql = "INSERT INTO `oc_seo_url` (`seo_url_id`, `store_id`, `language_id`, `query`, `keyword`) VALUES (NULL, ?, '2', ?, ?);";
    $stmt = $pdo->prepare($oc_seo_url_sql);
    $stmt->execute(
        [
            $mainStore, $currentQuery, $product['sku']
        ]
    );
    return $lastAddedID;
}