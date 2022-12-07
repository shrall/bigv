<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\PaymentGateway\PaynowController;
use App\Models\Cart;
use App\Models\PickupMethod;
use App\Models\PickupTime;
use App\Models\ProductVariation;
use App\Models\UserAddress;
use App\Models\Transaction;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function getCheckout(Request $request)
    {
        if (
            session()->has('checkout-items') &&
            session()->has('shipping-price') &&
            session()->has('total-checkout-price') &&
            session()->has('grandtotal-checkout-price') &&
            session()->has('total-checkout-items')
        ) {
            $addresses = UserAddress::where('user_id', auth()->user()->id)->get();

            $checkout_items = Vendor::with(['products' => function ($q1) {
                $q1->select('vendor_id', 'carts.id as cart_id', 'products.featured_image', 'products.name as product_name', 'product_variations.name as product_variation_name', 'carts.price', 'carts.quantity', 'carts.user_id')
                    ->join('product_variations', 'product_variations.product_id', '=', 'products.id')
                    ->join('carts', 'carts.product_variation_id', '=', 'product_variations.id')
                    ->whereIn('carts.id', session()->get('checkout-items'))
                    ->whereNull('carts.transaction_id')
                    ->where('user_id', auth()->user()->id);
            }, 'location'])->whereHas('products', function ($q1) {
                $q1->select('vendor_id')
                    ->join('product_variations', 'product_variations.product_id', '=', 'products.id')
                    ->join('carts', 'carts.product_variation_id', '=', 'product_variations.id')
                    ->whereIn('carts.id', session()->get('checkout-items'))
                    ->whereNull('carts.transaction_id')
                    ->where('user_id', auth()->user()->id);
            })->orderBy('id', 'ASC')->get();

            $pickup_methods = PickupMethod::all();
            $pickup_times = PickupTime::all();

            return view('user.cart.checkout', [
                'pickup_methods' => $pickup_methods,
                'pickup_times' => $pickup_times,
                'addresses' => $addresses,
                'checkouts' => $checkout_items,
                'total_price' => session()->get('total-checkout-price'),
                'total_items' => session()->get('total-checkout-items'),
                'shipping_price' => session()->get('shipping-price'),
                'grandtotal_price' => session()->get('grandtotal-checkout-price'),
            ]);
        }

        return redirect()->back();
    }

    public function preCheckout(Request $request)
    {
        $carts = json_decode($request->carts, true);

        $cart_checkout_id = [];
        $total_price = 0;
        $total_items = 0;
        if (isset($carts)) {
            if (count($carts) > 0) {
                foreach ($carts as $cart) {
                    array_pop($cart);

                    foreach ($cart as $cart_id => $product) {
                        if (isset($product['sub_total_price']) && isset($product['quantity'])) {
                            $cart_checkout_id[] = $cart_id;
                            $total_price += $product['sub_total_price'];
                            $total_items += $product['quantity'];
                        }
                    }
                }

                if (count($cart_checkout_id) > 0) {
                    $shipping_price = 25;

                    session()->put('total-checkout-items', $total_items);
                    session()->put('total-checkout-price', $total_price);
                    session()->put('grandtotal-checkout-price', $total_price + $shipping_price);
                    session()->put('shipping-price', $shipping_price);
                    session()->put('checkout-items', $cart_checkout_id);
                    session()->save();

                    return redirect('/user/cart/checkout');
                }
            }
        }

        return redirect()->back();
    }

    public function buyNowCheckout(Request $request)
    {
        $request->validate([
            'quantity' => 'required|numeric',
            'product_variation_id' => 'required|numeric',
        ]);

        $productVariation = ProductVariation::where('id', $request->product_variation_id)->first();
        $cart = Cart::whereNull('transaction_id')->where('product_variation_id', $request->product_variation_id)->first();

        if ($request->quantity > 0) {
            if ($cart == null) {
                $data = $request->all();
                $data += [
                    'price' => $productVariation->price,
                    'user_id' => auth()->user()->id,
                ];
                $cart = Cart::create($data);
            } else {
                $qty = $cart->quantity + $request->quantity;
                $cart->update([
                    'quantity' => $qty
                ]);
            }

            $shipping_price = 25;

            $total_price = $cart->quantity * $cart->price; // WARNING (price can be updated by user)
            session()->put('total-checkout-items', $cart->quantity);
            session()->put('total-checkout-price', $total_price);
            session()->put('grandtotal-checkout-price', $total_price + $shipping_price);
            session()->put('shipping-price', $shipping_price);
            session()->put('checkout-items', [$cart->id]);
            session()->save();

            return redirect('/user/cart/checkout');
        } else {
            return redirect()->back()->with('error', 'Please choose at least one product.'); // di UI belum ada modal error
        }

        return redirect()->back();
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'delivery_date' => 'string|date_format:Y-m-d',
            // 'payment_method_id' => 'required|numeric',
            'pickup_method_id' => 'required|numeric',
            'pickup_time_id' => 'required|numeric',
            'billing_address_id' => 'required_without:self_collection_address_id|numeric',
            'self_collection_address_id' => 'required_without:billing_address_id|numeric',
            'shipping_address_id' => 'sometimes|required|numeric',
        ]);

        $data = $request->all();
        $data += [
            'total_price' => session()->get('total-checkout-price'),
            'shipping_fee' => session()->get('shipping-price'),
            'user_id' => auth()->user()->id,
            'status_id' => 1, // default "Order Pending"
            'payment_method_id' => 1, // contoh
        ];

        $transaction = Transaction::create($data);

        // update transaction id in cart
        $checkout_items = session()->get('checkout-items');
        foreach ($checkout_items as $item) {
            Cart::where('id', $item)->update(['transaction_id' => $transaction->id]);
        }

        $paynow = new PaynowController();
        return $paynow->pay(session()->get('grandtotal-checkout-price'), $transaction->id);
        // return $paynow->pay(session()->get('total-checkout-price'), $transaction->id);

        // $transaction = Transaction::create([
        //     'delivery_date' => '2022-11-29',
        //     'pickup_method_id' => '1',
        //     'pickup_time_id' => '1',
        //     'billing_address_id' => '1',
        //     'total_price' => 0.8,
        //     'shipping_fee' => 25,
        //     'user_id' => 1,
        //     'status_id' => 1,
        //     'payment_method_id' => 1,
        // ]);
    }

    public function transitStatusPayment(Request $request)
    {
        $target = Carbon::now()->addSeconds(10);
        $transaction_id = $request->get('id', 0);
        do {
            $now = Carbon::now();
            $timeDiff = $target->diffInRealSeconds($now);
            $transaction = Transaction::where('id', $transaction_id)->first();

            if ($transaction->status_id == 2) {
                break;
            }
        } while ($timeDiff > 0);

        return redirect('/user/transaction');
    }
}
