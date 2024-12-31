<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use \Illuminate\Support\Facades\File;
use PharIo\Manifest\Extension;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }
    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }
    public function add_brand()
    {
        return view('admin.brand_add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            "name" => 'required',
            "slug" => 'required|unique:brands,slug',
            "image" => 'mimes:png,jpg,jpeg|max:2048'

        ]);
        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateBrandThumbnailsImage($image, $file_name, 'uploads/brands');
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand has been added successfully!');
    }
    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand_edit', compact('brand'));
    }
    public function brand_update(Request $request)
    {
        $request->validate([
            "name" => 'required',
            "slug" => 'required|unique:brands,slug' . $request->id,
            "image" => 'mimes:png,jpg,jpeg|max:2048'

        ]);
        $brand = Brand::find($request->brand_update_id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
                File::delete(public_path('uploads/brands') . '/' . $brand->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateBrandThumbnailsImage($image, $file_name, 'uploads/brands');
            $brand->image = $file_name;
        }

        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand has been updated successfully!');
    }

    public function GenerateBrandThumbnailsImage($image, $imageName, $path)
    {
        $destinationPath = public_path($path);
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }
    public function brand_delete($id)
    {
        $brand =  Brand::find($id);
        if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
            File::delete(public_path('uploads/brands') . '/' . $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Brand has been deleted successfully !');
    }

    public function categories()
    {
        $categories = category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }
    public function category_add()
    {
        return view('admin.category_add');
    }
    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);
        $category = new category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateBrandThumbnailsImage($image, $file_name, 'uploads/categories');
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been added successfully!');
    }
    public function category_edit($id)
    {
        $category = category::find($id);
        return view('admin.category_edit', compact('category'));
    }
    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);
        $category = category::find($request->category_update_id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateBrandThumbnailsImage($image, $file_name, 'uploads/categories');
            $category->image = $file_name;
        }

        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Categories has been updated successfully!');
    }
    public function category_delete($id)
    {
        $category =  category::find($id);
        if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
            File::delete(public_path('uploads/categories') . '/' . $category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Categories has been deleted successfully !');
    }
    public function products()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products', compact('products'));
    }
    public function product_add()
    {
        $categories = category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product_add', compact('categories', 'brands'));
    }
    public function product_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required',
        ]);
        $product = new Product();
        $product->name = $request->name;
        $product->slug = str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $currentTimeStamp = Carbon::now()->timestamp;
        $product->image = $request->image;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $currentTimeStamp . '.' . $image->extension();
            $this->generate_product_thumbnails_image($image, $imageName);
            $product->image = $imageName;
        }
        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;
        if ($request->hasFile('images')) {
            $allowedFileExtension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $getExtension = $file->getClientOriginalExtension();
                $check = in_array($getExtension, $allowedFileExtension);
                if ($check) {
                    $fileName = $currentTimeStamp . '-' . $counter . '.' . $getExtension;
                    $this->generate_product_thumbnails_image($file, $fileName);
                    array_push($gallery_arr, $fileName);
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }
        $product->images =  $gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product has been added successfully');
    }

    public function generate_product_thumbnails_image($image, $imageName)
    {
        $destinationPathForThumbnails = public_path("uploads/products/thumbnails");
        $destinationPath = public_path("uploads/products");
        $img = Image::read($image->path());

        $img->cover(540, 689, "top");
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);


        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathForThumbnails . '/' . $imageName);
    }
}
