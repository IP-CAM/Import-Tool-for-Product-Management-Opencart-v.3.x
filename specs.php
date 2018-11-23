<?php
/**
 * Contains current shop
 * 
 * @author Oleg Kholyk <oleg@kholyk.ru>
 */
    // Database specific constants

    // 1. Manufacturers:
    $manufacturers = [
        'Renault' => 11,
        'RENAULT' => 11,
        'Motrio'  => 12,
        'MOTRIO'  => 12,
    ];

    $priceToGroup = [
        'pr_1' => 2, // Registered
        'pr_2' => 3, // Small wholesale
        'pr_3' => 4  // Big wholesale
    ];

    $mainCategory = 59; // Current main category ID

    $mainStore = 0; // Default value

    $mainLayout = 0; // Default value
