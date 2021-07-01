<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    /**
     * The party that belong to the purchase.
     */
    public function party()
    {
        return $this->belongsTo('App\Party');
    }

    /**
     * The items that belong to the purchase.
     */
    public function items()
    {
        return $this->hasMany('App\Item');
    }
}
