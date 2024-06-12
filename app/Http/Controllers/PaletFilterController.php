<?php

namespace App\Http\Controllers;

use App\Models\PaletFilter;
use App\Models\New_product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ResponseResource;

class PaletFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product_filters = PaletFilter::latest()->paginate(100);
        $totalNewPrice = PaletFilter::sum('new_price_product');

        $totalNewPriceWithCategory = PaletFilter::whereNotNull('new_category_product')->sum('new_price_product');
        $totalOldPriceWithoutCategory = PaletFilter::whereNull('new_category_product')->sum('old_price_product');
    
        $totalNewPrice = $totalNewPriceWithCategory + $totalOldPriceWithoutCategory;

        return new ResponseResource(true, "list product filter", [
            'total_new_price' => $totalNewPrice,
            'data' => $product_filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($id)
    {
        DB::beginTransaction();
        try {
            $product = New_product::findOrFail($id);
            $productFilter = PaletFilter::create($product->toArray());
            $product->delete();
            DB::commit();
            return new ResponseResource(true, "berhasil menambah list product palet", $productFilter);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PaletFilter $paletFilter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaletFilter $paletFilter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaletFilter $paletFilter)
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
            $product_filter = PaletFilter::findOrFail($id);
            New_product::create($product_filter->toArray());
            $product_filter->delete();
            DB::commit();
            return new ResponseResource(true, "berhasil menghapus list product palet", $product_filter);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
