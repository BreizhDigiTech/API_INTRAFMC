<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArrivalProductCbd extends Model
{
    protected $table = 'arrival_product_cbd';
    protected $fillable = ['arrival_id', 'product_id', 'quantity', 'unit_price'];

    public function arrival()
    {
        return $this->belongsTo(CbdArrival::class, 'arrival_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductCBD::class, 'product_id');
    }
}