<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\Product;
use App\Models\Stock;
use League\Csv\Reader;

class ProductController extends Controller
{

    /**
     * Show all products.
     * Parameters [withStock, sortByStock, available] and [page] can be passed
     * to modify results.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $products = Product::withStockAvailable()->get();
        $currentPage = $request->input('page') ? $request->input('page') : 1;

        // optional param to include stock summary
        if ($request->input('withStock')) {
            $products = Product::withStockOnHand()
                ->withStockTaken()
                ->orderBy("on_hand")
                ->get();
        } elseif ($sortby = $request->input('sortByStock')) {
            if ($sortby == "ASC") {
                $products = Product::withStockOnHand()
                    ->withStockTaken()
                    ->orderBy("on_hand")
                    ->get();
            } elseif($sortby == 'DESC') {
                $products = Product::withStockOnHand()
                    ->withStockTaken()
                    ->orderByDesc("on_hand")
                    ->get();
            }
        }

        // filter the collection by products with stock on_hand > 0.
        if ($request->input('available')) {
            $products = $products->reject(function ($value, $key) {
                return $value->on_hand <= 0;
            });
            $products->all();
        }

        // We could also paginate here if required either using
        // offset method or cursor.

        return response()->json($products->forPage($currentPage, 100), 200);
    }

    /**
     * Show a requested product by id
     *
     * @param  Request  $request
     * @param  Integer  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $product = Product::getByID($id, $request->input('withStock'));
        return response()->json($product, 200);
    }

    /**
     * Store a new product.
     *
     * @param  Request  $request
     * @param  Integer  $id
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|string|unique:products|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $product = Product::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($product, 200);
    }

    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @param  Integer  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            // ensure codes are unique if being updated
            'code' => 'string|max:255|unique:products,code,'.$request->code,
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $product = Product::getByID($id);
        $product->code = $request->code;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->save();

        return response()->json($product, 200);
    }

    /**
     * Delete (softly) a product.
     *
     * @param  Request  $request
     * @param  Integer  $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $product = Product::getByID($id);
        $product->delete();
        return response()->json([
            'response' => 'Product was deleted'
        ], 200);
    }

    /**
     * Add stock on hand to a product.
     *
     * @param  Request  $request
     * @param  Integer  $id
     * @return Response
     */
    public function addStock(Request $request, $id)
    {
        $product = Product::getByID($id);

        $this->validate($request, [
            'on_hand' => 'required|integer',
            'taken' => 'integer',
            'production_date' => 'required|date_format:Y-m-d',
        ]);

        $product->stocks()->saveMany([
            new Stock([
                'on_hand' => $request->on_hand,
                'taken' => $request->taken,
                'production_date' => $request->production_date,
            ])
        ]);

        return response()->json([
            'response' => 'Products stock was updated'
        ], 200);
    }

    /**
     * Do the import based on the request
     *
     * @param  Request  $request
     * @return Response
     */
    public function doImport(Request $request)
    {
        // NOTE:
        // Lumen doesn't have state so we can't return errors.
        // Laravel framework would return them here.

        $this->validate($request, [
            'type' => 'required',
            'file' => 'required|file',
        ]);

        $original_filename = $request->file('file')->getClientOriginalName();
        $original_filename_arr = explode('.', $original_filename);
        $file_ext = end($original_filename_arr);
        $destination_path = './imports/';
        $csv = 'U-' . time() . '.' . $file_ext;

        if ($request->file('file')->move($destination_path, $csv)) {
            $filepath = './imports/' . $csv;
            $csvarray = array_map('str_getcsv', file($filepath));

            if ($request->input('type') == 'products') {
                return $this->importProducts($csvarray);
            } elseif ($request->input('type') == 'stocks') {
                return $this->importStock($csvarray);
            }

        }

        return redirect('import');
    }

    /**
     * Import products
     *
     * @param  Array  $records
     * @return Response
     */
    public function importProducts(Array $records)
    {
        $created = 0;
        $updated = 0;
        array_shift($records); // remove first row;
        $collection = collect($records);

        // Quickly loop over each array item
        foreach ($collection->values()->all() as $product) {

            $update = false;

            // Assume all imported data is correct.
            // id - code seem to be the same? DB restructure could solve this.
            $productModel = Product::firstOrCreate(
                ['id' => $product[0]],
                [
                    'id' => $product[0],
                    'code' => $product[0],
                    'name' => isset($product[1]) ? $product[1] : null,
                    'description' => isset($product[3]) ? $product[3] : null
                ]
            );

            // update counts to return
            if ($productModel->wasRecentlyCreated) {
                $created++;
            }

            // Do the update instead
            if (!$productModel->wasRecentlyCreated) {
                if (isset($product[1]) && $productModel->name != $product[1]) {
                    $productModel->name = $product[1];
                    $update = true;
                }
                if (isset($product[3]) && $productModel->description != $product[3]) {
                    $productModel->description = $product[3];
                    $update = true;
                }
                if ($update) {
                    $productModel->save();
                    $updated++;
                }
            }

        }

        return response()->json([
            'response' => 'Products were imported',
            'created' => $created,
            'updated' => $updated,
        ], 200);

    }

    /**
     * Import stock
     *
     * @param  Request  $request
     * @return Response
     */
    public function importStock(Array $records)
    {
        $dataToInsert = collect();
        $insertErrors = [];
        array_shift($records); // remove first row;
        $collection = collect($records);


        // Quickly loop over each array item
        foreach ($collection->values()->all() as $stock) {

            if (isset($stock[2])) {
                $rawDate = \DateTime::createFromFormat('d/m/Y', $stock[2]);
                $date = date_format($rawDate, 'Y-m-d');
            } else {
                $date = null;
            }

            // assume all imported data is correct.
            $dataToInsert->push([
                'product_id' => $stock[0],
                'on_hand' => $stock[1],
                //'taken' => $stock[X], // missing form supplied csv
                'production_date' => $date
            ]);

        }

        // Chunk inserts into bite sized queries
        // Alternatively jobs could be created and a queue could be run.
        foreach ($dataToInsert->chunk(2000) as $chunk) {
            try {
                DB::table('stocks')->insertOrIgnore($chunk->toArray());
            } catch (QueryException $e) {
                $insertErrors[] = $e->errorInfo;
            }
        }

        return response()->json([
            'response' => 'Stocks were imported',
            'errors' => $insertErrors
        ], 200);

    }

}
