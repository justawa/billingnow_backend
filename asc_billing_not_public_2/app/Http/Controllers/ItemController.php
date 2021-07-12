<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\FixItem;
use App\Item;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Item::all();
        return response()->json(['success' => true, 'items' => $items]);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Item::findOrFail($id);
        return response()->json(['success' => true, 'item' => $item]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        return response()->json(['success' => true, 'item' => $item]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        return response()->json(['success' => true, 'item' => $item]);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'q' => 'required'
        ]);
        $items = Item::where('product_name', 'like', $request->q . '%')
            ->orWhere('product_code', 'like', $request->q . '%')
            ->get();

        return response()->json(['success' => true, 'searched_items' => $items]);
    }

    public function search_single_item(Request $request)
    {
        $item = Item::where('product_code', $request->product_code)->where('rem_qty', '>', 0)->first();
        if($request->has('customerType')){
            $item->sale_price = $request->customerType == 'wholesale' ? $item->whole_sale_price : $item->sale_price;
        }

        // setting wholesale price or sale price based on customer type in a same property so that there is no confusion in frontend
        // and then unsetting the wholesale price
        unset($item->whole_sale_price);
        return response()->json(['success' => true, 'item' => $item]);
    }

    public function stock_less_than_ten()
    {
        $items = Item::where('rem_qty', '<', 10)->get();
        return response()->json(['success' => true, 'items' => $items]);
    }

    public function most_sold_items(Request $request)
    {
        if($request->has('from') && $request->has('to')) {
            $itemsWithCount = DB::table('items')
                ->leftjoin('item_sale', 'items.id', '=', 'item_sale.item_id')
                ->selectRaw('items.id, items.product_name, items.rem_qty, count(item_sale.item_id) as count')
                ->whereBetween('item_sale.created_at', [$request->from, $request->to])
                ->groupBy('items.product_name', 'items.id', 'items.rem_qty')
                ->orderBy('count', 'desc')
                ->get();
        } else {
            $itemsWithCount = DB::table('items')
                ->leftjoin('item_sale', 'items.id', '=', 'item_sale.item_id')
                ->selectRaw('items.id, items.product_name, items.rem_qty, count(item_sale.item_id) as count')
                ->groupBy('items.product_name', 'items.id', 'items.rem_qty')
                ->orderBy('count', 'desc')
                ->get();
        }

        return response()->json(['success' => true, 'itemsWithCount' => $itemsWithCount]);
    }

    public function add_fixed_item(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'hsn' => 'string|nullable|max:255',
            'brand' => 'string|nullable|max:255',
            'wholesale_percent' => 'required',
            'retail_percent' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->toJson()], 422);
        }

        $imagePath = '';
        if ($request->hasFile('image')) {
            $imagePath = request('image')->store('uploads', 'public');
        }

        $fixedItem = new FixItem;
        $fixedItem->product_code = $request->product_code;
        $fixedItem->product_name = $request->product_name;
        $fixedItem->hsn = $request->hsn ?? null;
        $fixedItem->image = $imagePath ?? null;
        $fixedItem->brand = $request->brand ?? null;
        $fixedItem->ws_margin = $request->wholesale_percent;
        $fixedItem->r_margin = $request->retail_percent;
        $fixedItem->gst = $request->gst;
        $fixedItem->save();

        return response()->json(['success' => true]);
    }

    public function get_fixed_item(Request $request)
    {
        $item = FixItem::where('product_code', $request->product_code)->first();
        $item->full_image_path = $item->image ? asset('storage/'.$item->image) : '';
        if($item) {
            return response()->json(['success' => true, 'item' => $item], 200);
        } else {
            return response()->json(['success' => false, 'item' => ''], 404);
        }
    }

    public function get_fixed_item_by_id($id)
    {
        $item = FixItem::findOrFail($id);
        $item->full_image_path = $item->image ? asset('storage/'.$item->image) : '';
        if($item) {
            return response()->json(['success' => true, 'item' => $item], 200);
        } else {
            return response()->json(['success' => false, 'item' => ''], 404);
        }
    }

    public function fixed_items()
    {
        $fixedItems = FixItem::all();
        foreach($fixedItems as $item) {
            $item->full_image_path = $item->image ? asset('storage/'.$item->image) : '';
        }
        return response()->json(['success' => true, 'items' => $fixedItems]);
    }

    public function update_fixed_item(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'hsn' => 'string|nullable|max:255',
            'brand' => 'string|nullable|max:255',
            'wholesale_percent' => 'required',
            'retail_percent' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->toJson()], 422);
        }

        $imagePath = '';
        if ($request->hasFile('image')) {
            $imagePath = request('image')->store('uploads', 'public');
        }

        $fixedItem = FixItem::findOrFail($id);
        $fixedItem->product_code = $request->product_code;
        $fixedItem->product_name = $request->product_name;
        $fixedItem->hsn = $request->hsn ?? null;
        $fixedItem->image = $imagePath ?? null;
        $fixedItem->brand = $request->brand ?? null;
        $fixedItem->ws_margin = $request->wholesale_percent;
        $fixedItem->r_margin = $request->retail_percent;
        $fixedItem->gst = $request->gst;
        $fixedItem->save();

        return response()->json(['success' => true]);
    }
}
