<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Product;
use App\Services\WebHooksService;
use App\Traits\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OrderStatusUpdate",
 *     type="object",
 *     @OA\Property(property="status", type="string", example="shipped")
 * ),
 * 
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="product_id", type="integer", format="int64"),
 *     @OA\Property(property="quantity", type="integer"),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * ),
 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class OrdersController extends Controller
{
    use Res;

    protected $webHooksService;

    public function __construct(WebHooksService $webHooksService)
    {
        $this->webHooksService = $webHooksService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     summary="List user orders",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Order"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        $orders = Orders::with('product')->where('user_id', $request->user()->id)->get();
        return $this->sendRes('success', true, $orders, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders",
     *     summary="Create a new order",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Not enough product quantity")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $rules = [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendRes('error', false, $validator->errors(), 400);
        }

        DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        DB::beginTransaction();
        try {
            $product = Product::where('id', $request->product_id)
                ->lockForUpdate()->first();

            if ($product->quantity < $request->quantity) {
                DB::rollBack();
                return $this->sendRes('error', false, 'Not enough product quantity', 400);
            }

            $order = Orders::create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'user_id' => $request->user()->id,
            ]);

            $product->update(['quantity' => $product->quantity - $request->quantity]);
            DB::commit();

            return $this->sendRes('success', true, $order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendRes('error', false, $e->getMessage(), 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     summary="Get a specific order",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="there is no order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */

    public function show(Request $request, string $id)
    {
        $order = Orders::where(['user_id' => $request->user()->id, 'id' => $id])->first();
        if ($order) {
            return $this->sendRes('success', true, $order, 200);
        }
        return $this->sendRes('error', false, 'there is no order', 404);
    }


    /**
     * @OA\Put(
     *     path="/api/v1/orders/{id}",
     *     summary="Update the status of an order",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "completed", "cancelled"}, example="shipped")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="there is no order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:pending,processing,shipped,completed,cancelled']
        ]);

        if ($validator->fails()) {
            return $this->sendRes('error', false, $validator->errors(), 400);
        }

        $order = Orders::where(['id' => $id, 'user_id' => auth()->user()->id])->first();

        if ($order) {
            DB::beginTransaction();
            try {
                $order->update(['status' => $request->status]);

                // TODO - implement webhooks
                try {
                    $res = $this->webHooksService->orderStatus([$order->id, $request->status]);
                    DB::commit();
                    return $this->sendRes('success', true, $order, 200);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $this->sendRes('error', false, $e->getMessage(), 500);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->sendRes('error', false, $e->getMessage(), 500);
            }
        } else {
            return $this->sendRes('error', false, 'there is no order', 404);
        }
    }


    public function destroy(string $id)
    {
        //
    }
}
