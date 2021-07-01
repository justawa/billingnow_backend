<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * The sales that belong to the item.
     */
    public function sales()
    {
        return $this->belongsToMany('App\Sale')
                    ->withPivot('qty', 'price')
                    ->withTimestamps();
    }
}
