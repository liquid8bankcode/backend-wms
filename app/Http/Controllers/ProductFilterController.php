<?php

namespace App\Http\Controllers;

use App\Models\New_product;
use Illuminate\Http\Request;
use App\Models\Product_Filter;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ResponseResource;
use App\Models\Category;
use App\Models\Color_tag;

class ProductFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $product_filters = Product_Filter::latest()->paginate(100);

        $totalNewPriceWithCategory = Product_Filter::whereNotNull('new_category_product')->sum('new_price_product');
        $totalOldPriceWithoutCategory = Product_Filter::whereNull('new_category_product')->sum('old_price_product');

        $totalNewPrice = $totalNewPriceWithCategory + $totalOldPriceWithoutCategory;

        $category = null;

        if ($totalNewPrice > 99999) {
            $category = Category::all();
        } else {
            foreach ($product_filters as $product_filter) {
                $product_filter->new_tag_product = Color_tag::where('min_price_color', '<=', $totalNewPrice)
                    ->where('max_price_color', '>=', $totalNewPrice)
                    ->select('fixed_price_color', 'name_color')->get();

            }
        }

        return new ResponseResource(true, "list product filter", [
            'total_new_price' => $totalNewPrice,
            'category' => $category,
            'data' => $product_filters
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        //
    }


    public function store($id)
    {
        DB::beginTransaction();
        try {
            $product = New_product::findOrFail($id);
            $productFilter = Product_Filter::create($product->toArray());
            $product->delete();
            DB::commit();
            return new ResponseResource(true, "berhasil menambah list product bundle", $productFilter);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product_Filter $product_Filter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product_Filter $product_Filter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product_Filter $product_Filter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $product_filter = Product_Filter::findOrFail($id);
            New_product::create($product_filter->toArray());
            $product_filter->delete();
            DB::commit();
            return new ResponseResource(true, "berhasil menghapus list product bundle", $product_filter);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
