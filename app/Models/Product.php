<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'stocks'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'on_hand' => 'integer',
        'taken' => 'integer'
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Get the stock units associated with the product.
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Scope a query to include on hand count.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStockOnHand($query)
    {
        return $query->withCount(['stocks AS on_hand' => function ($query) {
            $query->select(DB::raw('SUM(on_hand) as on_hand'));
        }]);
    }

    /**
     * Scope a query to exclude products with no stock
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStockAvailable($query)
    {
        return $query->withCount(['stocks AS on_hand' => function ($query) {
            $query->select(DB::raw('SUM(on_hand) as on_hand'));
        }]);
    }

    /**
     * Scope a query to include taken count.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStockTaken($query)
    {
        return $query->withCount(['stocks AS taken' => function ($query) {
            $query->select(DB::raw('SUM(taken) as taken'));
        }]);
    }

    /**
     * Return stock on hand.
     *
     * @return Integer
     */
    public function getOnHandAttribute()
    {
        $onHand = $this->stocks->where("on_hand", ">", 0)->sum('on_hand');
        return $onHand;
    }

    /**
     * Return stock taken.
     *
     * @return Integer
     */
    public function getTakenAttribute()
    {
        $taken = $this->stocks->where("taken", ">", 0)->sum('taken');
        return $taken;
    }

    /**
     * Get a product by its ID
     * @param $id
     * @return Product|Illuminate\Http\Response
     */
    public static function getByID($id, $withStock = false)
    {
        try {
            $product = Product::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "error" => "Product not found"
            ], 404);
        }

        if ($withStock) {
            // Alternative method to get stock summary
            // scoped query could also be used here
            // instead of appends/attributes
            $product->setAppends(['on_hand', 'taken']);
        }

        return $product;
    }

}
