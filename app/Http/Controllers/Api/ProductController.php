<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\FileUploadService;
use App\Traits\Res;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;


/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="quantity", type="number"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="category_id", type="integer", format="int64"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class ProductController extends Controller
{
    use Res;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }


    /**
     * @OA\Get(
     *      path="/api/v1/products",
     *      summary="Get list of products",
     *      description="Returns a list of all products",
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search by product name or category",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Product")
     *              )
     *          )
     *      ),
     *  )
     */
    public function index(Request $request)
    {
        if ($request->search) {
            $products = Product::with('category')->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orwhereHas('category', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            })->paginate(12);

            return $this->sendRes('success', true, $products, 200);
        }

        $products = Product::with('category')->latest()->paginate(10);
        return $this->sendRes('success', true, $products, 200);
    }


    /**
     * @OA\Post(
     *      path="/api/v1/products",
     *      summary="Create a new product",
     *      description="Creates a new product with the provided details.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "price", "quantity", "categorie_id", "title"},
     *              @OA\Property(property="name", type="string", example="Product Name"),
     *              @OA\Property(property="price", type="integer", example=1000),
     *              @OA\Property(property="quantity", type="integer", example=10),
     *              @OA\Property(property="categorie_id", type="integer", example=1),
     *              @OA\Property(property="title", type="string", example="Product Title"),
     *              @OA\Property(property="description", type="string", example="Product Description"),
     *              @OA\Property(
     *                  property="image",
     *                  type="array",
     *                  @OA\Items(type="string", format="binary")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Product created successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid input",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="Validation errors")
     *          )
     *      ),
     * )
     */

    public function store(ProductRequest $request)
    {
        $validatedData = $request->validated();

        $product = Product::create($validatedData);

        $this->fileUploadService->uploadFiles($request, 'image', $product);

        return $this->sendRes('success', true, $product, 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/products/{id}",
     *      summary="Get a product by ID",
     *      description="Returns details of a product specified by ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the product to retrieve",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product retrieved successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="There is no product with this ID")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *      )
     * )
     */

    public function show(string $id)
    {
        $product = Product::find($id);
        if ($product) {
            return $this->sendRes('success', true, $product, 200);
        }
        return $this->sendRes('error', false, 'there is no product', 404);
    }

    public function update(Request $request, string $id)
    {
        // Update logic
    }

    public function destroy(string $id)
    {
        // Delete logic
    }
}
