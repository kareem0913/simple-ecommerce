<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index(Request $request)
    {
        if ($request->search) {
            $products = Product::with('category')->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orwhereHas('category', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            })->paginate(12);

            // ->when($request->query, function ($query) use ($request) {
            //     $query->whereHas('category', function ($q) use ($request) {
            //         $q->where('name', 'like', '%' . $request->category . '%');
            //     });
            // })->paginate(12);

            return view('products.index', compact('products'));
        }
        $products = Product::with('category')->latest()->paginate(12);
        return view('products.index', compact('products'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('products.add', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $rule = [
            'name' => ['string', 'required', 'max:255'],
            'price' => ['required', 'integer'],
            'quantity' => ['required', 'integer'],
            'categorie_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'image.*' => ['image', 'mimes:jpeg,png,jpg', 'max:10240'],
        ];
        $validate = $request->validate($rule);

        unset($validate['image']);
        $product = Product::create($validate);

        $this->fileUploadService->uploadFiles($request, 'image', $product);

        session()->flash('success', 'Product added successfully!');

        return redirect('/products');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('category');
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
