<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Party;

class PartyController extends Controller
{
    public function search_by_phone(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required|string'
        ]);

        $party = Party::where('phone', $request->phone)->first();

        if(!$party) {
            $party = new Party;
            $party->phone = $request->phone;
            $party->save();
        }

        return response()->json(['success' => true, 'searched_party' => $party]);
    }

    public function update(Request $request, $id)
    {
        $party = Party::findOrFail($id);

        $party->name = $request->name;
        $party->billing_address = $request->billing_address;
        $party->billing_state = $request->billing_state;
        $party->billing_city = $request->billing_city;
        $party->billing_pincode = $request->billing_pincode;

        $party->save();

        return response()->json(['success' => true, 'selected_party' => $party]);
    }
}
