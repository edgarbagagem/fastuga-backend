<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePhoto;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $produto = Product::when(request()->search != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request()->search . '%')
                    ->orWhere('type', 'LIKE', '%' . request()->search . '%')
                    ->orWhere('price', 'LIKE', '%' . request()->search . '%');
            });
        })->paginate(15);

        return ProductResource::collection($produto);
    }

    public function show(Product $product){
        
        $this->authorize('view',$product);
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update',$product);
        $product->update($request->validated());
        return new ProductResource($product);
    }

    public function create(UpdateProductRequest $request, Product $param){
        $this->authorize('create',$param);
        $request->validated();

        $product = new Product;

        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;

        if ($request->photo_file) {
            $path = 'storage/products/';

            //rename photo
            $imageName = time() . '.' . $request->photo_file->getClientOriginalExtension();
            //move photo
            $request->photo_file->move($path, $imageName);
            $product->photo_url = $imageName;
        } else {
            $product->photo_url = '';
        }

        $product->save();
        return new ProductResource($product);
    }

    public function delete(Product $product){
        
        $this->authorize('delete',$product);
        $product->delete();

        return new ProductResource($product);
    }

    public function upload_photo(UpdatePhoto $request, Product $product)
    {

        $request->validated();

        if ($request->photo_file) {
            $path = 'storage/products/';

            //rename photo
            $imageName = time() . '.' . $request->photo_file->getClientOriginalExtension();
            //move photo
            $request->photo_file->move($path, $imageName);
            $product->photo_url = $imageName;
        }

        $product->save();
    }
}
