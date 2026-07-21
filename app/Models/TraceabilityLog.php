<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraceabilityLog extends Model
{
    public $timestamps = false;   // bang chi co happened_at
    protected $fillable = ['product_id', 'stage', 'description', 'location', 'happened_at'];
    protected $casts = ['happened_at' => 'datetime'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
