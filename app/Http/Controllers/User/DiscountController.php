<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $discounts = Discount::where('visible', 1)
            ->where('duration_start', '<=', Carbon::now())
            ->where('duration_end', '>=', Carbon::now())
            ->get();
        return view('user.promo.index', compact('discounts'));
    }

    public function search(Request $request)
    {
        if ($request->code) {
            $discounts = Discount::where('code', $request->code)
                ->where('duration_start', '<=', Carbon::now())
                ->where('duration_end', '>=', Carbon::now())
                ->get();
        } else {
            $discounts = Discount::where('visible', 1)
                ->where('duration_start', '<=', Carbon::now())
                ->where('duration_end', '>=', Carbon::now())
                ->get();
        }
        return view('user.promo.inc.discount', compact('discounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function show(Discount $discount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function edit(Discount $discount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discount $discount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discount $discount)
    {
        //
    }

    public function productSearch($keyword = null)
    {
        $productDiscounts = Discount::where('type_id', '2');

        if (isset($keyword)) {
            $productDiscounts->where('code', $keyword);
        }

        $productDiscounts = $productDiscounts->get();

        return view('user.cart.itemProductDiscCheckout', [
            'product_discounts' => $productDiscounts,
        ]);
    }

    public function shippingSearch($keyword = null)
    {
        $shippingDiscounts = Discount::where('type_id', '1');

        if (isset($keyword)) {
            $shippingDiscounts->where('code', $keyword);
        }

        $shippingDiscounts = $shippingDiscounts->get();

        return view('user.cart.itemShipDiscCheckout', [
            'shipping_discounts' => $shippingDiscounts,
        ]);
    }

    public function applyVoucher(Request $request)
    {
        session()->forget('product-voucher-used');
        session()->forget('shipping-voucher-used');
        session()->save();

        $productVoucher = $request->product_voucher;
        $shippingVoucher = $request->shipping_voucher;
        $output = [];
        $totalPrice = session()->get('grandtotal-checkout-price');

        if (isset($productVoucher)) {
            $voucher = Discount::where('code', $productVoucher)->first();
            $output['product_voucher'] = $voucher;
            $totalPrice -= $voucher->amount;

            session()->put('product-voucher-used', $voucher);
            session()->save();
        }

        if (isset($shippingVoucher)) {
            $voucher = Discount::where('code', $shippingVoucher)->first();
            $output['shipping_voucher'] = $voucher;
            $totalPrice -= $voucher->amount;

            session()->put('shipping-voucher-used', $voucher);
            session()->save();
        }

        $output["total_price_after_discount"] = $totalPrice;

        return $output;
    }

    public function cancelVoucher()
    {
        session()->forget('product-voucher-used');
        session()->forget('shipping-voucher-used');

        return session()->get('grandtotal-checkout-price');
    }
}
