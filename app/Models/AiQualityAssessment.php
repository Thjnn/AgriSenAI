<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiQualityAssessment extends Model
{
    public $timestamps = false;   // bang chi co assessed_at
    protected $fillable = [
        'product_id', 'image_url', 'image_sha256', 'ripeness_level', 'quality_score',
        'defect_detected', 'defect_note', 'raw_ai_response', 'integrity_hash', 'assessed_at',
    ];
    protected $casts = [
        'raw_ai_response' => 'array',
        'quality_score' => 'decimal:2',
        'defect_detected' => 'boolean',
        'assessed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
