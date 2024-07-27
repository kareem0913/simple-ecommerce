<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->page) {
            // Paginate results if 'pages' query parameter is true
            $categories = Category::paginate(5);
        } else {
            // Get all results without pagination
            $categories = Category::all();
        }
        // dd($categories);
        return view('categories.index', compact('categories'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'unique:categories,name'],
            'description' => ['required', 'string']
        ];

        $validate = $request->validate($rules);

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description
        ]);
        session()->flash('success', 'Category added successfully!');
        return redirect('/categories?page=true');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
