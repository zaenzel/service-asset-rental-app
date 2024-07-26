<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query();

        $category = $request->query('category');

        $products->when($category, function ($query) use ($category){
            return $query->where('category', $category);
        });

        return new ProductResource(true, 'List Data Product', $products->paginate(10));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price_per_hour' => 'required|integer',
            'category' => 'required',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $image = $request->file('image');
        $image->storeAs('public/product', $image->hashName());

        $product = Product::create([
            'name'     => $request->name,
            'price_per_hour'   => $request->price_per_hour,
            'category'   => $request->category,
            'description'     => $request->description,
            'image'     => $image->hashName(),
        ]);

        // Debugging: Periksa slug yang dihasilkan
        error_log('Generated Slug: ' . $product->slug);

        return new ProductResource(true, 'Data product Berhasil Ditambahkan!', $product);
    }

    public function show($slug)
    {
        // $product = Product::find($slug);
        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is not found'
            ], 404);
        }

        return new ProductResource(true, 'Detail Data Post!', $product);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'required|integer',
            'category' => 'required',
            'description' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }


        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is not found'
            ], 404);
        }

        //check if image is not empty
        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/product', $image->hashName());

            //delete old image
            Storage::delete('public/product/' . basename($product->image));

            //update post with new image
            $product->update([
                'name'     => $request->name,
                'price'   => $request->price,
                'description'     => $request->description,
                'image'     => $image->hashName(),
                'category'   => $request->category,
            ]);
        } else {

            //update post without image
            $product->update([
                'name'     => $request->name,
                'price'   => $request->price,
                'description'     => $request->description,
                'category'   => $request->category,
            ]);
        }

        //return response
        return new ProductResource(true, 'Data Product Berhasil Diubah!', $product);
    }

    public function destroy($id)
    {

        //find post by ID
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is not found'
            ], 404);
        }

        //delete image
        Storage::delete('public/product/' . basename($product->image));

        //delete post
        $product->delete();

        //return response
        return new ProductResource(true, 'Data Post Berhasil Dihapus!', null);
    }
}
