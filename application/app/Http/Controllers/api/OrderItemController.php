<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderItemProductResource;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\UpdateOrderItemRequest;

class OrderItemController extends Controller
{

    public function index($id)
    {
        global $id2;
        $id2 = $id;

        $orderItem = DB::table('products')
            ->select(
                'order_items.id as id',
                'orders.id as order_id',
                'order_items.order_local_number as order_local_number',
                'products.id as product_id',
                'products.description as description',
                'products.name as name',
                'products.type as type',
                'products.photo_url as photo_url',
                'order_items.status as status',
                'order_items.preparation_by as preparation_by'
            )
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->when(request()->ticket != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('orders.ticket_number', 'LIKE', '%' . request()->ticket . '%');
                });
            })
            ->when(request()->local != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('order_items.order_local_number', 'LIKE', '%' . request()->local . '%');
                });
            })

            ->where('order_items.status', '=', 'W')
            ->orWhere(function ($query) {
                $query->where('order_items.status', '=', 'P')
                    ->where('preparation_by', '=', $GLOBALS['id2']);
            })
            ->get();

        return OrderItemProductResource::collection($orderItem);
    }

    //create request
    public function update(UpdateOrderItemRequest $request, OrderItem $item)
    {
        $item->update($request->validated());
        return new OrderItemResource($item);
    }

    public function store(Request $request)
    {
        $productsInOrder = $request->products;
        $order_id = $request->id;
        $order_local_number = 0;

        foreach ($productsInOrder as $product) {
            $orderItem = new OrderItem;

            $order_local_number++;
            $orderItem->order_id = $order_id;
            $orderItem->order_local_number = $order_local_number;
            $orderItem->product_id = $product['id'];
            $orderItem->price = $product['price'];
            $orderItem->preparation_by = null;
            $orderItem->notes = null;
            if ($product['type'] != 'hot dish') {
                $orderItem->status = 'R';
            } else {
                $orderItem->status = 'W';
            }
            $orderItem->save();
        }
    }
}
