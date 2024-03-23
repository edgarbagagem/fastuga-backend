<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;

class CustomerController extends Controller
{
    public function show($id)
    {
        $customer = Customer::where('user_id', '=', $id)->first();

        $this->authorize('view', [Customer::class, $customer]);

        return new CustomerResource($customer);
    }

    public function show_orders($id)
    {
        $customer = Customer::where('user_id', '=', $id)->first();

        $this->authorize('view', [Customer::class, $customer]);

        $orders = Order::where('customer_id', '=', $customer->id)->paginate(5);

        return OrderResource::collection($orders);
    }

    public function update_points(Request $request, $id){

        $customer = Customer::where('user_id', '=', $id)->first();            //rename photo
        $customer->points = $request->points;
        $customer->save();
        return new CustomerResource($customer);
    }
}
