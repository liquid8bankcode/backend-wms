<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\New_product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ResponseResource;
use App\Models\Product_Bundle;

class BundleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->input('q');

        $bundles = Bundle::latest()
            ->with('product_bundles')
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('name_bundle', 'LIKE', '%' . $query . '%')
                    ->orWhereHas('product_bundles', function ($subQueryBuilder) use ($query) {
                        $subQueryBuilder->where('new_name_product', 'LIKE', '%' . $query . '%')
                            ->orWhere('new_barcode_product', 'LIKE', '%' . $query . '%')
                            ->orWhere('new_tag_product', 'LIKE', '%' . $query . '%')
                            ->orWhere('new_category_product', 'LIKE', '%' . $query . '%')
                            ->orWhere('new_tag_product', 'LIKE', '%' . $query . '%');
                    });
            })
            ->paginate(50);


        return new ResponseResource(true, "list bundle", $bundles);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Bundle $bundle)
    {
        $query = $request->input('q'); 
        $bundle->load(['product_bundles' => function ($productBundles) use ($query) {
            if (!empty($query)) {
                $productBundles->where('new_name_product', 'LIKE', '%' . $query . '%')
                    ->orWhere('new_barcode_product', 'LIKE', '%' . $query . '%')
                    ->orWhere('new_tag_product', 'LIKE', '%' . $query . '%')
                    ->orWhere('new_category_product', 'LIKE', '%' . $query . '%')
                    ->orWhere('new_tag_product', 'LIKE', '%' . $query . '%');
            }
        }]);
        return new ResponseResource(true, "detail bundle", $bundle);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bundle $bundle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bundle $bundle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bundle $bundle)
    {
        DB::beginTransaction();
        try {
            $productBundles = $bundle->product_bundles;

            foreach ($productBundles as $product) {
                New_product::create([
                    'code_document' => $product->code_document,
                    'old_barcode_product' => $product->old_barcode_product,
                    'new_barcode_product' => $product->new_barcode_product,
                    'new_name_product' => $product->new_name_product,
                    'new_quantity_product' => $product->new_quantity_product,
                    'new_price_product' => $product->new_price_product,
                    'new_date_in_product' => $product->new_date_in_product,
                    'new_status_product' => 'display',
                    'new_quality' => $product->new_quality,
                    'new_category_product' => $product->new_category_product,
                    'new_tag_product' => $product->new_tag_product
                ]);

                $product->delete();
            }

            $bundle->delete();

            DB::commit();
            return new ResponseResource(true, "Produk bundle berhasil dihapus", null);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus bundle', 'error' => $e->getMessage()], 500);
        }
    }
}
