<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArrivalProductCbd extends Model
{
    use HasFactory;
    protected $table = 'arrival_product_cbd';
    protected $fillable = ['arrival_id', 'product_id', 'quantity', 'unit_price'];

    protected $casts = [
        'arrival_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function arrival()
    {
        return $this->belongsTo(CbdArrival::class, 'arrival_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductCBD::class, 'product_id');
    }
}