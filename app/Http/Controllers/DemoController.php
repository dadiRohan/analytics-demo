<?php

namespace App\Http\Controllers;

use App\Support\Analytics;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function menu()
    {
        Analytics::track('menu_viewed', ['items' => 3]);
        return view('menu');
    }

    public function orderForm()
    {
        return view('order');
    }

    public function placeOrder(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'item' => 'required|string|in:Pizza,Burger,Pasta',
            'qty'  => 'required|integer|min:1|max:20',
        ]);

        $orderId = random_int(1000, 9999);
        $total   = $data['qty'] * 12.50;

        Analytics::track('order_created', [
            'order_id' => $orderId,
            'item'     => $data['item'],
            'qty'      => $data['qty'],
            'total'    => round($total, 2),
        ], null, 'commerce');

        return redirect('/order')->with(
            'success',
            "Order #$orderId placed! Total: $" . number_format($total, 2)
        );
    }
}