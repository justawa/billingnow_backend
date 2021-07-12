<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Item;
use App\Purchase;
use App\Sale;

class ReportController extends Controller
{
    public function daily_purchase(Request $request)
    {
        // $purchases = Purchase::whereDate('created_at', Carbon::today()->toDateString())->with(['party'])->get();
        $q = Purchase::where('status', 1)->orderBy('created_at', 'asc');
        if($request->has('from') && $request->has('to')) {
            $q = $q->whereBetween('created_at', [$request->from, $request->to]);
        }
        $purchases = $q->with(['party'])->get();
        return response()->json(['success' => true, 'purchases' => $purchases]);
    }

    public function daily_sale(Request $request)
    {
        // $sales = Sale::whereDate('created_at', Carbon::today()->toDateString())->with(['customer'])->get();
        $q = Sale::where('status', 1)->orderBy('created_at', 'asc');
        if($request->has('from') && $request->has('to')) {
            $q = $q->whereBetween('created_at', [$request->from, $request->to]);
        }
        $sales = $q->with(['customer'])->get();
        return response()->json(['success' => true, 'sales' => $sales]);
    }

    public function sale_profit(Request $request)
    {
        $q = Sale::where('status', 1)->orderBy('created_at', 'desc');
        if($request->has('from') && $request->has('to')) {
            $q = $q->whereBetween('created_at', [$request->from, $request->to]);
        }
        $sales = $q->with(['items'])->get();
        foreach($sales as $sale) {
            $sale->profit = 0;
            foreach($sale->items as $item) {
                $sale->profit += ($item->sale_price - $item->unit_cost) * $item->pivot->qty;
            }
        }

        return response()->json(['success' => true,  'sales' => $sales]);
    }

    public function stock_summary(Request $request)
    {
        //$q = Item::where('rem_qty', '>', 0);
        $q = Item::selectRaw('product_name, unit_cost, rem_qty, sale_price, SUM(qty) as qty, SUM(rem_qty) as rem_qty')
        // ('product_name','unit_cost','rem_qty','sale_price',
        //             Item::raw('sum(qty) qty'))
                    ->where('rem_qty', '>', 0)
                    ->groupBy('product_code')
                    ->orderBy('rem_qty', 'desc');

        if($request->has('from') && $request->has('to')) {
            $q = $q->whereBetween('created_at', [$request->from, $request->to]);
        }
        //$q = $q->groupBy('product_code');
    //    $items = $q->toSql();
    //    echo $items;
        // $q = $q->groupBy('product_name');
        $items = $q->get();
        
        return response()->json(['success' => true, 'items' => $items]);
    }

    public function cancelled_invoice(Request $request)
    {
        $q = Sale::where('status', 0)->orderBy('created_at', 'desc');
        if($request->has('from') && $request->has('to')) {
            $q = $q->whereBetween('created_at', [$request->from, $request->to]);
        }
        $sales = $q->with(['items'])->get();
        foreach($sales as $sale) {
            $sale->profit = 0;
            foreach($sale->items as $item) {
                $sale->profit += ($item->sale_price - $item->unit_cost) * $item->pivot->qty;
            }
        }

        return response()->json(['success' => true,  'sales' => $sales]);
    }

    public function item_gst(Request $request)
    {
        $gst = $request->gst ?? 5;
        $q = Item::where('gst', $gst);
        if($request->has('from') && $request->has('to')) {
            $q = $q->whereBetween('created_at', [$request->from, $request->to]);
        }
        $items = $q->get();

        foreach($items as $item) {
            // first_part * ( 100 / ( 100 + parseFloat(qgst) ) )
            $gst_amount_from_unit_cost = $item->unit_cost - ($item->unit_cost * ( 100 / ( 100 + $item->gst ) ));
            $gst_amount_from_sale_price = $item->sale_price - ($item->sale_price * ( 100 / ( 100 + $item->gst ) ));

            $item->unit_cost_without_gst = $item->unit_cost - $gst_amount_from_unit_cost;
            $item->gst_amount_from_unit_cost = $gst_amount_from_unit_cost;
            $item->sale_price_without_gst = $item->sale_price - $gst_amount_from_sale_price;
            $item->gst_amount_from_sale_price = $gst_amount_from_sale_price;
            $item->sold_qty = $item->qty - $item->rem_qty;
        }

        return response()->json(['success' => true, 'items' => $items]);
    }
}
