<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

use App\Item;
use App\Purchase;

class PurchaseController extends Controller
{
    public function store(Request $request)
    {
        $items = json_decode($request->items, true);
        $itemCount = count($items);

        // Begin Transaction
        DB::beginTransaction();
        try {
            $purchase = new Purchase;
            $purchase->payment_mode = $request->payment_mode;
            $purchase->total_amount = $request->total_amount;
            $purchase->amount_repay = $request->amount_repay;
            $purchase->amount_paid = $request->amount_paid;
            $purchase->discount = $request->discount;
            $purchase->party_id = $request->party_id;
            $purchase->save();
    
            // adding multiple items to item table
            // all the items bought in purchase will be used for future sale, so should be added to items table
            for($i = 0; $i < $itemCount; $i++) {
                $calculated_unit_cost = $items[$i]["unit_cost"] + ($items[$i]["gst"] * $items[$i]["unit_cost"]) / 100;
                $unit_cost = $request->tax_type == 'i' ? $items[$i]["unit_cost"] : $calculated_unit_cost;
                $item = new Item;
                $item->product_code = $items[$i]["product_code"];
                $item->product_name = $items[$i]["product_name"];
                $item->hsn = $items[$i]["hsn"];
                $item->mrp = $items[$i]["mrp"];
                $item->unit_cost = $unit_cost;
                $item->discount = $items[$i]["discount"] ?? 0;
                $item->final_unit_cost = $items[$i]["final_unit_cost"] ?? 0;
                $item->sale_price = $items[$i]["sale_price"];
                $item->whole_sale_price = $items[$i]["whole_sale_price"];
                $item->gst = $items[$i]["gst"];
                $item->qty = $items[$i]["qty"];
                $item->rem_qty = $items[$i]["qty"];
                $item->mfg = !empty($items[$i]["mfg"]) ? $items[$i]["mfg"] : null;
                $item->expiry = !empty($items[$i]["expiry"]) ? $items[$i]["expiry"] : null;
                $item->purchase_id = $purchase->id;
                $item->save();
            }

            // Commit Transaction
            DB::commit();
            return response()->json(['success'=> true, 'purchase' => $purchase], 201);
        } catch(\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            return response()->json(['success'=> false, 'error' => $e], 500);
        }
    }

    public function show($id)
    {
        $purchase = Purchase::where(['id' => $id, 'status' => 1])->with(['party', 'items'])->first();
        return response()->json(['success' => true, 'purchase' => $purchase]);
    }

    public function cancel_bill($id)
    {
        // Begin Transaction
        DB::beginTransaction();
        try {
            $purchase = Purchase::findOrFail($id);
            foreach($purchase->items as $item) {
                $foundItem = Item::findOrFail($item->id);
                if($foundItem->sales->count() > 0){ 
                    throw new \Exception("The bill cannot be cancelled because 1 or more items in this bill have already been sold");
                }
                $foundItem->delete();
            }
            $purchase->status = 0;
            $purchase->save();
            // Commit Transaction
            DB::commit();
            return response()->json(['success' => true]);
        } catch(\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            return response()->json(['success'=> false, 'error' => $e->getMessage()], 400);
        }
    }
}
