<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Stock;

class ProductController extends Controller
{

    /**
     * Show all products.
     * {arameters withStock and page can be passed
     * to modify results.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        // optional param to include stock summary
        if ($request->input('withStock')) {
            $products = Product::withStockOnHand()->withStockTaken()->orderBy("on_hand")->get();
        } else {
            $products = Product::get();
        }

        // If sorting is requested then stock summary will be returned
        if ($sortby = $request->input('sortByStock')) {
            if ($sortby == "ASC") {
                $products = Product::withStockOnHand()->withStockTaken()->orderBy("on_hand")->get();
            } elseif($sortby == 'DESC') {
                $products = Product::withStockOnHand()->withStockTaken()->orderByDesc("on_hand")->get();
            }
        }

        // We could also paginate here if required either using
        // offset method or cursor.

        return response()->json($products, 200);
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
     * Add stock on hand ot a product.
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
}
