<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

use App\Item;
use App\Sale;

class SaleController extends Controller
{
    public function store(Request $request)
    {
        $items = json_decode($request->items, true);
        $itemCount = count($items);

        // Begin Transaction
        DB::beginTransaction();
        try {
            $sale = new Sale;
            $sale->payment_mode = $request->payment_mode;
            $sale->total_amount = $request->total_amount;
            $sale->amount_repay = $request->amount_repay;
            $sale->amount_paid = $request->amount_paid;
            $sale->discount = $request->discount;
            $sale->customer_id = $request->customer_id;
            $sale->save();
    
            // adding multiple items that belong to sale in pivot table
            for($i = 0; $i < $itemCount; $i++) {
                $sale->items()->attach($items[$i]["id"], ['qty' => $items[$i]["qty"], 'price' => $items[$i]["price"]]);

                // decrease the qty of the sold item from inventory
                $sold_item = Item::findOrFail($items[$i]["id"]);
                $sold_item->rem_qty = $sold_item->rem_qty - $items[$i]["qty"];
                $sold_item->save();
            }

            // Commit Transaction
            DB::commit();
            return response()->json(['success'=> true, 'sale' => $sale], 201);
        } catch(\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            return response()->json(['success'=> false, 'error' => $e], 400);
        }
    }

    public function show($id)
    {
        $sale = Sale::where('id', $id)->with(['customer', 'items'])->first();
        return response()->json(['success' => true, 'sale' => $sale]);
    }

    public function cancel_invoice($id)
    {
        // Begin Transaction
        DB::beginTransaction();
        try {
            $sale = Sale::findOrFail($id);
            foreach($sale->items as $item) {
                $foundItem = Item::findOrFail($item->id);
                $foundItem->rem_qty = $foundItem->rem_qty + $item->pivot->qty;
                $foundItem->save();
            }
            $sale->status = 0;
            $sale->save();
            // Commit Transaction
            DB::commit();
            return response()->json(['success' => true]);
        } catch(\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            return response()->json(['success'=> false, 'error' => $e], 400);
        }
    }

    public function cancelled_invoice()
    {
        $sale = Sale::where(['id' => $id, 'status' => 0])->with(['customer', 'items'])->first();
        return response()->json(['success' => true, 'sale' => $sale]);
    }

    public function update_payment_mode(Request $request)
    {
        $sale = Sale::findOrFail($request->id);
        $sale->payment_mode = $request->payment_mode;
        if($sale->save()) {
            return response()->json(['success' =>  true, 'message' => 'Payment mode updated successfully']);
        } else {
            return response()->json(['success' =>  false, 'message' => 'Failed to udpate payment mode']);
        }
    }
}
