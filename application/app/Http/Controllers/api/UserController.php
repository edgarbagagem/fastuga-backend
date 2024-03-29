<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Customer;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdatePhoto;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Order;

class UserController extends Controller
{
    public function index()
    {
        $users = User::when(request()->search != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request()->search . '%')
                    ->orWhere('email', 'LIKE', '%' . request()->search . '%')
                    ->orWhere('type', 'LIKE', '%' . request()->search . '%');
            });
        })->paginate(10);
        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        if ($user->type == 'C') {
            $customer = Customer::where('user_id', '=', $user->id)->first();

            $request->validate(
                [
                    'phone' => 'required|string|regex:/(^[1-9]{1}[0-9]{8}$)/',
                    'nif' => 'required|string|max:9',
                    'points' => 'required|numeric',
                    'default_payment_type' => 'required|in:PAYPAL,MBWAY,VISA',
                ],
            );

            $customer->phone = $request->phone;
            $customer->nif = $request->nif;
            $customer->points = $request->points;
            $customer->default_payment_type = $request->default_payment_type;


            if ($customer->default_payment_type == 'VISA') {
                $validated_reference = $request->validate([
                    'default_payment_reference' => 'required|string|regex:/(^[1-9]{1}[0-9]{15}$)/'
                ], [
                    'default_payment_reference.regex' => 'Visa payment reference format is invalid. Must have 16 digits and not start with a 0.'
                ]);
                $customer->default_payment_reference = $validated_reference['default_payment_reference'];
            } else if ($customer->default_payment_type == 'MBWAY') {
                $validated_reference = $request->validate([
                    'default_payment_reference' => 'required|string|regex:/(^[1-9]{1}[0-9]{8}$)/'
                ], [
                    'default_payment_reference.regex' => 'Mbway payment reference format is invalid. Must be a phone number having 9 digits and not starting with 0.',
                ]);
                $customer->default_payment_reference = $validated_reference['default_payment_reference'];
            } else if ($customer->default_payment_type == 'PAYPAL') {
                $validated_reference = $request->validate([
                    'default_payment_reference' => 'required|email'
                ], [
                    'default_payment_reference.email' => 'Paypal payment reference format is invalid. Must be a valid email.',
                ]);
                $customer->default_payment_reference = $validated_reference['default_payment_reference'];
            }

            $customer->save();
        }


        return new UserResource($user);
    }

    public function update_password(UpdateUserPasswordRequest $request, User $user)
    {
        $user->password = bcrypt($request->validated()['password']);
        $user->save();
        return new UserResource($user);
    }

    public function show_me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function upload_photo(UpdatePhoto $request, User $user)
    {

        $validated_data = $request->validated();

        if ($validated_data['photo_file']) {
            $path = 'storage/fotos/';

            //rename photo
            $imageName = time() . '.' . $validated_data['photo_file']->getClientOriginalExtension();
            //move photo
            $validated_data['photo_file']->move($path, $imageName);
            $user->photo_url = $imageName;
        }

        $user->save();
    }

    public function delete_user(User $user)
    {
        $user->blocked = 1;
        $user->save();
        $user->delete();
        return new UserResource($user);
    }

    public function create(Request $request)
    {

        $validated_data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|max:30',
            'type' => 'required|in:C,EC,ED,EM',
            'blocked' => 'required|in:0,1',
            'photo_file' => 'nullable|image|max:2048',
        ]);

        $user = new User;
        $user->name = $validated_data['name'];
        $user->email = $validated_data['email'];
        $user->password = bcrypt($validated_data['password']);
        $user->type = $validated_data['type'];
        $user->blocked = $validated_data['blocked'];

        if ($request->photo_file) {
            $path = 'storage/fotos/';

            //rename photo
            $imageName = time() . '.' . $validated_data['photo_file']->getClientOriginalExtension();
            //move photo
            $validated_data['photo_file']->move($path, $imageName);
            $user->photo_url = $imageName;
        }



        if ($user->type == 'C') {

            $validated_data_customer = $request->validate(
                [
                    'phone' => 'required|string|regex:/(^[1-9]{1}[0-9]{8}$)/',
                    'nif' => 'required|string|max:9',
                    'points' => 'required|numeric',
                    'default_payment_type' => 'required|in:PAYPAL,MBWAY,VISA',
                ],
            );

            $customer = new Customer;
            $customer->phone = $validated_data_customer['phone'];
            $customer->nif = $validated_data_customer['nif'];
            $customer->points = $validated_data_customer['points'];
            $customer->default_payment_type = $validated_data_customer['default_payment_type'];
            if ($customer->default_payment_type == 'VISA') {
                $validated_reference = $request->validate([
                    'default_payment_reference' => 'required|string|regex:/(^[1-9]{1}[0-9]{15}$)/'
                ], [
                    'default_payment_reference.regex' => 'Visa payment reference format is invalid. Must have 16 digits and not start with a 0.'
                ]);
                $customer->default_payment_reference = $validated_reference['default_payment_reference'];
            } else if ($customer->default_payment_type == 'MBWAY') {
                $validated_reference = $request->validate([
                    'default_payment_reference' => 'required|string|regex:/(^[1-9]{1}[0-9]{8}$)/'
                ], [
                    'default_payment_reference.regex' => 'Mbway payment reference format is invalid. Must be a phone number having 9 digits and not starting with 0.',
                ]);
                $customer->default_payment_reference = $validated_reference['default_payment_reference'];
            } else if ($customer->default_payment_type == 'PAYPAL') {
                $validated_reference = $request->validate([
                    'default_payment_reference' => 'required|email'
                ], [
                    'default_payment_reference.email' => 'Paypal payment reference format is invalid. Must be a valid email.',
                ]);
                $customer->default_payment_reference = $validated_reference['default_payment_reference'];
            }

            $user->save();
            $customer->user()->associate($user)->save();
        } else {
            $user->save();
        }

        return new UserResource($user);
    }


    public function show_stats(User $user)
    {

        if ($user->type == 'EC') {
            $total_orders_prepared = OrderItem::where('preparation_by', '=', $user->id)->count();

            if ($total_orders_prepared == null) {
                $total_orders_prepared = 'No items prepared';
            }

            $avg_item_price = OrderItem::where('preparation_by', '=', $user->id)->avg('price');

            if ($avg_item_price == null) {
                $avg_item_price = 0;
            }

            $most_prepared_item = OrderItem::select('product_id')->where('preparation_by', '=', $user->id)
                ->groupBy('product_id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1)
                ->get();

            $most_prepared_item = Product::withTrashed()->find($most_prepared_item);

            return Response()->json([
                'total_order_items_prepared' => $total_orders_prepared,
                'average_price' => round($avg_item_price, 2),
                'most_prepared_item' => isset($most_prepared_item[0]) ? $most_prepared_item[0]->name : 'No items prepared'
            ]);
        } else if ($user->type == 'ED') {
            $total_orders_delivered = Order::where('delivered_by', '=', $user->id)->count();

            $orders = Order::select('id', 'ticket_number', 'total_price', 'payment_type', 'payment_reference', 'date', 'status')
                ->where('delivered_by', '=', $user->id)->orderBy('date', 'desc')->paginate(5);

            return Response()->json([
                'total_order_items_delivered' => $total_orders_delivered,
                'orders' => $orders
            ]);
        } else if ($user->type == 'EM') {
            $customerCount = Customer::all()->count();
            $mostUsedPaymentType = Order::select('payment_type')->groupBy('payment_type')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1)
                ->get();

            $moneyMade = Order::sum('total_paid');

            $bestChef = OrderItem::select('preparation_by')
                ->groupBy('preparation_by')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1)
                ->get();

            $bestChefName = User::withTrashed()->select('name')->where('id', '=', $bestChef[0]->preparation_by)->first();

            $bestDelivery = Order::select('delivered_by')
                ->groupBy('delivered_by')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1)
                ->get();

            $bestDeliveryName = User::withTrashed()->select('name')->where('id', '=', $bestDelivery[0]->delivered_by)->first();

            $bestProduct = OrderItem::select('product_id')
                ->groupBy('product_id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1)
                ->get();


            $bestProductName = Product::withTrashed()->select('name')->where('id', '=', $bestProduct[0]->product_id)->first();

            $managerCount = User::where('type', '=', 'EM')->count();
            $chefCount = User::where('type', '=', 'EC')->count();
            $deliveryCount = User::where('type', '=', 'ED')->count();

            $totalDiscountToClientsWithPoints = Order::sum('total_paid_with_points');

            return Response()->json([
                'customerCount' => $customerCount,
                'mostUsedPaymentType' => $mostUsedPaymentType[0]->payment_type,
                'moneyMade' => round($moneyMade),
                'bestChef' => $bestChefName->name,
                'bestDelivery' => $bestDeliveryName->name,
                'bestProduct' => $bestProductName->name,
                'managerCount' => $managerCount,
                'chefCount' => $chefCount,
                'deliveryCount' => $deliveryCount,
                'totalDiscount' => round($totalDiscountToClientsWithPoints)
            ]);
        } else {
            return Response()->json([
                'error' => 'User type not employee'
            ]);
        }
    }
}
