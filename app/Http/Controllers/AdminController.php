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
}
