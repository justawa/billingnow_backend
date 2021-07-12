<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * Customer has many sale.
     */
    public function sales()
    {
        return $this->hasMany('App\Sale');
    }
}
