### 1. Create a new Laravel/Lumen Project.
Choose to create a Lumen project based on simple requirements.
If this wasn't a demo I would have choosen the Laravel Framework so I have more features state e.g. sessions.

### 2. Setup a database using the following diagram.
Completed using migrations however I would have made a few changes if I
had the chance as the diagrams vs spreadsheets didn't match exactly.

### 3. Create the following API routes
#### 3.1. Add, Update, Delete product.
```php
$router->post('products', [ 'uses' => 'ProductController@store']);
$router->put('products/{id}', [ 'uses' => 'ProductController@update']);
$router->delete('products/{id}', [ 'uses' => 'ProductController@destroy']);
```

#### 3.2. Add a stock onHand for a product.
```php
$router->get('products/{id}/addstock', [ 'uses' => 'ProductController@addStock']);
```

#### 3.3. Able to get products and product details.
```php
$router->get('products', [ 'uses' => 'ProductController@index']);
$router->get('products/{id}', [ 'uses' => 'ProductController@show']);
```

#### 3.4. Able to pass optional stock parameter in get products and product details API to get stock onHand summary.
You can use the following GET params to get additional data.
- ?withStock=1
- ?page=1 

#### 3.5. Able to sort products by stock onHand by both asc and desc order.
You can use the following GET param to sort.
- ?sortByStock=DESC

#### 3.6. Able to filter products by stock availability.
You can use the following GET params to get availability.
- ?available=1

### 4. Able to bulk (5k +) insert/update products into database
I wasn't sure how exqactly you wanted this built but for demo purposes I made a simple form
where users can choose a type e.g. propducts/stocks and upload their CSV for bulk updates/inserts.

There's many ways to improve performance here if required such as push data straight to a queue/job.

You could also just use an API but would need to becareful about not hitting the `max_input_vars` limit.

When you upload a CSV in this demo products can be either created or updated from the same CSV. Stocks however is just simply create only.

Ideally I would have returned nice error responses but Lumen doesn't support returning state so for now json is being returned.

### 5. Able to bulk (20k +) insert stock into the database.
Same as above.

Note I did notice some data issues in the CSV - mostly around product ID's/Codes not actually existing.
In this demo if the product doesn't exist that record is ignored on import.
