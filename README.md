# oc_products_json
`Version 0.0.1`

Special import tool for products management for Opencart 3
Parses JSON-formatted price list and creates entries in webshop database

## JSON Entries sample
----------------------
```json
{
"7700500168": {
    "sku": "7700500168",
    "warehouse": "msk",
    "manufacturer": "MOTRIO",
    "name": "СВЕЧА 3АЖИГАНИЯ (224013682R)",
    "pr_3": 101,
    "pr_2": 201,
    "pr_1": 301,
    "pr_0": 401,
    "quantity": 0
  }, 
  ....
  {
      ...
  }
}
```
## Product 'model' field
To enable different warehouses handling we will use ubique model reference value:
Common pattern is ```reference_warehouse```.
For example: let's create model reference # for product with sku ***7700500168*** which is available on Moscow's warehouse with index ***msk***: ```7700500168_msk```.
