<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('products.index');
    }

    /*
     * method for get all product list
     * */
    public function productList()
    {
        $products = Product::with([
            'productImages',
            'productVariantPrices'=>function($q){
                $q->with([
                    'productVariantOne'=>function($q){$q->select('id','variant');},
                    'productVariantTwo'=>function($q){$q->select('id','variant');},
                    'productVariantThree'=>function($q){$q->select('id','variant');},
                ])->get();
            }
        ])->get();
        return response()->json($products,200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Validator::make($request->all(),[
            'title'=>'required|string',
            'sku'=>'required|string',
            'description'=>'required',
            'product_variant'=>'required',
            'product_variant_prices'=>'required',
        ])->validate();
        $product = Product::create($request->only(['title','sku','description']));
        if (isset($product->id)) {
            $ids=[];
            foreach ($request->product_variant as $key=>$vv){
                $itms = collect($vv['tags'])->map(function ($item) use ($product, $vv) {
                    $its=['variant'=>$item,'variant_id'=>$vv['option'],'product_id'=>$product->id];
                    return $its;
                });
                $product->variants()->attach($itms);
                $idd=ProductVariant::where(['product_id'=>$product->id,'variant_id'=>$vv['option']])->pluck('id')->toArray();
                $ids[]=$idd;
            }
            if (count($ids)>0) {
                $matrix = collect($ids[0]);
                if(count($ids)>2) $matrix = $matrix->crossJoin($ids[1], $ids[2]);
                else if(count($ids)>1) $matrix = $matrix->crossJoin($ids[1]);
                $itms=[];
                foreach ($request->product_variant_prices as $key=>$vv){
                    $itms[] =[
                        'price'=>$vv['price'],
                        'stock'=>$vv['stock'],
                        'product_id'=>$product->id,
                        'product_variant_one'=>$matrix[$key][0]??null,
                        'product_variant_two'=>$matrix[$key][1]??null,
                        'product_variant_three'=>$matrix[$key][2]??null,
                        'created_at'=>now(),
                        'updated_at'=>now(),
                    ];
                }
                ProductVariantPrice::insert($itms);
            }
            return  response()->json(['status'=>'success','message'=>'Successfully stored'],200);
        }
        return  response()->json(['status'=>'error','message'=>'Invalid request'],404);
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        $product = $product->with([
            'productVariant',
            'productVariantPrices'=>function($q){
                $q->with([
                    'productVariantOne'=>function($q){$q->select('id','variant');},
                    'productVariantTwo'=>function($q){$q->select('id','variant');},
                    'productVariantThree'=>function($q){$q->select('id','variant');},
                ])->get();
            }
        ])->where(['id'=>$product->id])->first();
        return view('products.edit', compact('variants','product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        Validator::make($request->all(),[
            'title'=>'required|string',
            'sku'=>'required|string',
            'description'=>'required',
            'product_variant'=>'required',
            'product_variant_prices'=>'required',
        ])->validate();
        $product_u = $product->update($request->only(['title','sku','description']));
        if (isset($product_u)) {
            ProductVariantPrice::where(['product_id'=>$product->id])->delete();
            $ids=[];
            foreach ($request->product_variant as $key=>$vv){
                $itms = collect($vv['tags'])->map(function ($item) use ($product, $vv) {
                    $its=['variant'=>$item,'variant_id'=>$vv['option'],'product_id'=>$product->id];
                    return $its;
                });
                $product->variants()->detach();
                $product->variants()->attach($itms);
                $idd=ProductVariant::where(['product_id'=>$product->id,'variant_id'=>$vv['option']])->pluck('id')->toArray();
                $ids[]=$idd;
            }
            if (count($ids)>0) {
                $matrix = collect($ids[0]);
                if(count($ids)>2) $matrix = $matrix->crossJoin($ids[1], $ids[2]);
                else if(count($ids)>1) $matrix = $matrix->crossJoin($ids[1]);
                $itms=[];
                foreach ($request->product_variant_prices as $key=>$vv){
                    $itms[] =[
                        'price'=>$vv['price'],
                        'stock'=>$vv['stock'],
                        'product_id'=>$product->id,
                        'product_variant_one'=>$matrix[$key][0]??null,
                        'product_variant_two'=>$matrix[$key][1]??null,
                        'product_variant_three'=>$matrix[$key][2]??null,
                        'created_at'=>now(),
                        'updated_at'=>now(),
                    ];
                }
                ProductVariantPrice::insert($itms);
            }
            return  response()->json(['status'=>'success','message'=>'Successfully updated'],200);
        }
        return  response()->json(['status'=>'error','message'=>'Invalid request'],404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
