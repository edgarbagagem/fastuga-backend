<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderItemProduct2Resource;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem;

use App\Http\Requests\UpdateOrderRequest;

use App\Models\Customer;

class OrderController extends Controller
{

    public function index()
    {
        $order = Order::query()->get();

        return OrderResource::collection($order);
    }

    public function ordersForEmployees($id)
    {
        global $id2;
        $id2 = $id;

        $order = Order::query();
        $order = $order
            ->where('status', '=', 'P')
            ->orWhere(function ($query) {
                $query->where('delivered_by', '=', $GLOBALS['id2'])
                    ->where('status', '=', 'R');
            })
            ->get();

        return OrderResource::collection($order);
    }

    public function productsInOrdersForEmployees($id)
    {
        global $id2;
        $id2 = $id;

        $order = Order::query();
        $order = DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select(
                'orders.id',
                'orders.ticket_number',
                'orders.status',
                'order_items.status as prod_stat',
                'products.id as prod_id',
                'products.photo_url',
                'products.name',
                'products.type'
            )
            ->where('orders.status', '=', 'P')
            ->orWhere(function ($query) {
                $query->where('delivered_by', '=', $GLOBALS['id2'])
                    ->where('orders.status', '=', 'R');
            })
            ->get();

        return OrderItemProduct2Resource::collection($order);
    }


    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());
        return new OrderResource($order);
    }


    public function store(Request $request)
    {

        $validated_data = $request->validate([
            'status' => 'required|string|in:P,R,D,C',
            'ticket_number' => 'required|integer|min:0|max:100'
            //date =>
        ]);

        $order = new Order;
        $order->ticket_number = $request->ticket_number;
        $order->total_paid_with_points = $request->total_paid_with_points;
        $order->points_gained = $request->points_gained;
        $order->points_used_to_pay = $request->points_used_to_pay;
        $order->payment_reference = $request->payment_reference;
        $order->payment_type = $request->payment_type;
        $order->status = $validated_data['status'];
        $order->total_price = $request->total_price;
        $order->total_paid = $request->total_paid;
        $order->date = $request->date;


        if ($request->userId != 0) {
            $customer_id = Customer::where('user_id', '=', $request->userId)->first();
            $order->customer_id = $customer_id->id;
            $order->save();
        }

        $order->save();
        return new OrderResource($order);
    }

    public function show_preparing_orders()
    {
        $orders = Order::when(request()->search != '', function ($query) {
            $query->where(function ($q) {
                $q->where('id', 'LIKE', '%' . request()->search . '%')
                    ->orWhere('customer_id', 'LIKE', '%' . request()->search . '%')
                    ->orWhere('ticket_number', 'LIKE', '%' . request()->search . '%');
            });
        })->where('status', '=', 'P')->orderBy('id', 'desc')->paginate(10);

        return OrderResource::collection($orders);
    }

    public function cancel_order(Order $order)
    {


        OrderItem::where('order_id', '=', $order->id)->delete();

        $order->status = 'C';

        if ($order->customer_id != null) {
            $customer = Customer::find($order->customer_id);
            if ($order->points_used_to_pay != 0) {
                $customer->points = $customer->points + $order->points_used_to_pay;
            }

            if ($order->points_gained != 0) {
                $customer->points = $customer->points - $order->points_gained;
            }

            $customer->save();
        }
        $order->save();
    }
}
