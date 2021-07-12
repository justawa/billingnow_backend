<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Expense;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::where('status', 1)->get();
        return response()->json(['success' => true, 'expenses' => $expenses]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json(['success' => false, 'message' => $validator->errors()->toJson()], 400);
        }

        $expense = new Expense;
        $expense->amount = $request->amount;
        $expense->date = Carbon::parse($request->date);
        $expense->type = $request->type;
        $expense->description = $request->description;
        $expense->save();
        
        return response()->json(['success' => true, 'expense' => $expense]);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->amount = $request->amount;
        $expense->date = Carbon::parse($request->date);
        $expense->type = $request->type;
        $expense->description = $request->description;
        $expense->save();
        
        return response()->json(['success' => true, 'expense' => $expense]);
    }

    public function cancel_expense($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->status = 0;
        $expense->save();
        return response()->json(['success' => true]);
    }
}
