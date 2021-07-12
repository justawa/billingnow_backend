<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\Sale;

class CustomerController extends Controller
{
    public function search_list(Request $request)
    {
        $this->validate($request, [
            'q' => 'required|string'
        ]);

        $customers = Customer::where('phone', 'like', '%'.trim($request->q).'%')
                        ->orWhere('name', 'like', '%'.trim($request->q).'%')
                        ->get();
        return response()->json(['success' => true, 'customers' => $customers]);
    }

    public function search_by_phone(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required|string'
        ]);

        $customer = Customer::where('phone', trim($request->phone))->first();

        if(!$customer) {
            $customer = new Customer;
            $customer->phone = trim($request->phone);
            // default card number for all customers intially
            $customer->card = '14642248Y';
            $customer->save();
        }

        return response()->json(['success' => true, 'searched_customer' => $customer]);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $customer->name = $request->name;
        $customer->billing_address = $request->billing_address;
        $customer->billing_state = $request->billing_state;
        $customer->billing_city = $request->billing_city;
        $customer->billing_pincode = $request->billing_pincode;
        // update card number of user with provided value, otherwise use the default value
        $customer->card = $request->card ?? '14642248Y';

        $customer->save();

        return response()->json(['success' => true, 'selected_customer' => $customer]);
    }

    public function index()
    {
        $customers = Customer::with('sales')->get();

        foreach($customers as $customer){
            $customer->paid_total = $customer->sales()->where('payment_mode', ['cash', 'bank'])->sum('total_amount');
            $customer->credit_total = $customer->sales()->where('payment_mode', 'credit')->sum('amount_repay');
        }

        return response()->json(['success' => true, 'customers' => $customers]);
    }

    public function last_ten_customers()
    {
        $latestSales = Sale::latest()->limit(10)->with('customer')->get();
        return response()->json(['success' => true, 'latestSales' => $latestSales]);
    }
    
    public function show($id)
    {
        $customer = Customer::where('id', $id)->with('sales')->first();
        return response()->json(['success' => true, 'customer' => $customer]);
    }
}
