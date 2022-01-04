<?php

namespace App\Http\Controllers;


use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Collection|Product[]
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * Display a listing of the specified userresource.
     *
     * @return Collection|Product[]
     */
    public function getUserProducts()
    {
        return Product::where('user_id',Auth::id());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'expiration_date' => 'required|date',
            'price' => 'required|numeric',
            'periods' => 'required|JSON',
            'quantity' => 'required|numeric',
            'category_id' => 'required|Numeric',
        ]);
        $request->validate(['image' => 'required|file']);

        $image = $request->file('image');
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        $image->storeAs('public/images', $image_name);

        $fields['user_id'] = $request->user()->id;
        $fields['image_url'] = '/storage/images/' . $image_name;

        $product = Product::create($fields);
        return response($product, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     * @return Response
     */
    public function show(Product $product)
    {
        $product->increment('views');
        $product['current_price'] = $product->currentPrice();
        $product['user'] = $product->user;
        $product['comments'] = $product->comments()->get();
        $product['likes'] = $product->likes()->get();
        return response($product, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function update(Request $request, Product $product)
    {
        if (Auth::id() != $product->user_id) {
            return response(['message' => 'Unauthorized'], 401);
        }
        $fields = $request->validate([
            'name' => 'string',
            'price' => 'numeric',
            'periods' => 'JSON',
            'quantity' => 'numeric',
        ]);
        $request->validate(['image' => 'File']);

        $product->update($fields);
        return response($product, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @param Request $request
     * @return Response
     */
    public function destroy(Product $product)
    {
        if (Auth::id() != $product->user_id) {
            return response(['message' => 'Unauthorized'], 401);
        }
        Product::destroy($product->id);
        return response("true", 200);
    }

    /**
     * Display the filtered resources.
     *
     * @param Request $request
     * @return Response
     */
    public function search(Request $request)
    {
        $request->validate([
            'name' => 'string',
            'category_id' => 'numeric',
            'upPrice' => 'numeric',
            'downPrice' => 'numeric',
            'expiration_date' => 'Date',
        ]);
        $products = Product::query();
        if ($request->name && $request->name!='zzzzzz') {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
            
        }
        if ($request->category_id && $request->category_id!=6) {
            $products = $products->where('category_id', $request->category_id);
        }
        if ($request->upPrice) {
            $products = $products->where('price', '<=', $request->upPrice);
            return $products->get();
        }
        if ($request->downPrice) {
            $products = $products->where('price', '>=', $request->downPrice);
        }

        if ($request->expiration_date) {
            $products = $products->whereDate('expiration_date', '<=', $request->expiration_date);
        }
        return ($products->get());
    }

    public function sort(Request $request){
        //$sortBy = {'','name','price','category_id'};
        $id = $request-> validate(['id'=>'numeric']);
        if($id==0){return response(Product::all());}
        else if($id==1)
        return Product::orderBy('name','asc')->get();
        else{
            return Product::orderBy('price','asc')->get();
        }
    }
}
