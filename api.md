## Frontend

##### Отримати дані всіх типів цін:

```php
premmerce_get_price_types(); 
```
Return:
```php 
array [
    <priceTypeID> => [
        "ID" => ...
        "name" => ...
        "roles" => [
            <roleId> => "administrator"
            ...
        ]
    ]
    ...
]
```

##### Всі типи цін для даного товару:

```php
premmerce_get_prices(<$productId>)
```
Return:
```php
[
    <priceTypesId> => <product price>
    ...
]
```

##### Отримати дані головної ціни:
 
```php
$p = wc_get_product($productId/$variantId);
$p->get_data()['regular_price'];
    OR
$p->get_data()['sale_price'];
```
 
##### Отримати дані поточної ціни:

```php
$p = wc_get_product($productId/variantId);
$p->get_price();
```