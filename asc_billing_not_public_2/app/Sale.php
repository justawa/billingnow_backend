<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    /**
     * The customer that belong to the sale.
     */
    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    /**
     * The items that belong to the sale.
     */
    public function items()
    {
        return $this->belongsToMany('App\Item')
                    ->withPivot('qty', 'price')
                    ->withTimestamps();
    }
}
