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


    public function changeOrderStatus(Request $request)
    {
        // Validate the request data
        $request->validate([
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

                    // Redirect with success message
                    return redirect()->back()->with('success', 'Order status updated successfully');
                } catch (\Exception $e) {
                    DB::rollBack();

                    // Redirect with error message
                    return redirect()->back()->withErrors(['Failed to send webhook: ' . $e->getMessage()]);
                }
            } catch (\Exception $e) {
                DB::rollBack();

                // Redirect with error message
                return redirect()->back()->withErrors(['Failed to update order status: ' . $e->getMessage()]);
            }
        } else {
            // Redirect with error message if order not found
            return redirect()->back()->withErrors(['Order not found']);
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
