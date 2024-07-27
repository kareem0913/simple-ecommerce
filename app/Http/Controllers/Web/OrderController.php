<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Product;
use App\Services\WebHooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\error;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $webHooksService;

    public function __construct(WebHooksService $webHooksService)
    {
        $this->webHooksService = $webHooksService;
    }

    public function index(Request $request)
    {
        $orders = Orders::with('product')->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(5);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer'],
        ];

        $validate = $request->validate($rules);

        DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        DB::beginTransaction();
        try {

            $product =  Product::where('id', $request->product_id)
                ->lockForUpdate()->first();

            if ($product->quantity < $request->quantity) {
                DB::rollBack();
                return redirect()->back()->withErrors(['quantity' => 'Not enough product quantity']);
            }

            $order = Orders::create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'user_id' => auth()->user()->id,
            ]);

            $product->update(['quantity' => $product->quantity - $request->quantity]);
            DB::commit();

            session()->flash('success', 'order is added successfully!');
            return redirect('/orders');
        } catch (\Exception $e) {

            DB::rollBack();
            session()->flash('error', $e->getMessage());
            return redirect()->route('products.show', ['product' => $request->product_id]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $order = Orders::with('product')->where(['user_id' => $request->user()->id, 'id' => $id])->first();

        if (!$order) {
            return abort(404, 'Order not found.');
        }
        return view('orders.show', compact('order'));
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
        // $validator = Validator::make($request->all(), [
        //     'status' => ['required', 'in:pending,processing,shipped,completed,cancelled']
        // ]);

        // if ($validator->fails()) {

        //     return $this->sendRes('error', false, $validator->errors(), 400);
        // }

        $validator = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,completed,cancelled']
        ]);

        $order = Orders::where('id', $id)->first();

        if ($order) {
            DB::beginTransaction();
            try {
                $order->update(['status' => $request->status]);

                //TODO -  implement webhooks
                try {
                    $res = $this->webHooksService->orderStatus([$order->id, $request->status]);
                    DB::commit();
                    return $this->sendRes('success', true, $order, 201);
                } catch (\Exception $e) {
                    return $this->sendRes('errror', false, $e->getMessage(), 500);
                }
            } catch (\Exception $e) {
                return $this->sendRes('errror', false, $e->getMessage(), 500);
            }
        } else {
            return $this->sendRes('error', false, 'there is no order', 404);
        }
    }

    public function changeOrderStatus(Request $request)
    {
        // Validate the request data
        $validator = $request->validate([
            'id' => ['required', 'integer', 'exists:orders,id'],
            'status' => ['required', 'in:pending,processing,shipped,completed,cancelled'],
        ]);

        // Find the order by ID
        $order = Orders::find($request->id);

        if ($order) {
            DB::beginTransaction();
            try {
                // Update the order status
                $order->update(['status' => $request->status]);

                // TODO: Implement webhooks if needed
                try {
                    $res = $this->webHooksService->orderStatus([$order->id, $request->status]);
                    DB::commit();

                    // Return a JSON response for success
                    return response()->json(['success' => true, 'message' => 'Order status updated successfully']);
                } catch (\Exception $e) {
                    DB::rollBack();

                    // Return a JSON response for webhook failure
                    return response()->json(['success' => false, 'message' => 'Failed to send webhook: ' . $e->getMessage()], 500);
                }
            } catch (\Exception $e) {
                DB::rollBack();

                // Return a JSON response for update failure
                return response()->json(['success' => false, 'message' => 'Failed to update order status: ' . $e->getMessage()], 500);
            }
        } else {
            // Return a JSON response if order not found
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
