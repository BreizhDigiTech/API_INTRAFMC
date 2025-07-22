<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class CbdArrival extends Model
{
    use HasFactory;
    protected $table = 'cbd_arrivals';
    protected $fillable = ['amount', 'status'];

    public function products()
    {
        return $this->hasMany(ArrivalProductCbd::class, 'arrival_id');
    }

    public static function boot()
    {
        parent::boot();

        // Événement déclenché après validation
        static::updated(function ($arrival) {
            if ($arrival->status === 'validated') {
                DB::transaction(function () use ($arrival) {
                    foreach ($arrival->products as $arrivalProduct) {
                        $product = $arrivalProduct->product;
                        $product->stock += $arrivalProduct->quantity;
                        $product->save();
                    }
                });
            }
        });
    }
}