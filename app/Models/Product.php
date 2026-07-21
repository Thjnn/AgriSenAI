<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'farmer_id', 'category_id', 'name', 'description', 'price',
        'unit', 'quantity_available', 'qr_code', 'status',
    ];
    protected $casts = ['price' => 'decimal:2', 'quantity_available' => 'decimal:2'];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(AiQualityAssessment::class);
    }

    public function latestAssessment(): HasOne
    {
        return $this->hasOne(AiQualityAssessment::class)->latestOfMany('assessed_at');
    }

    public function traceLogs(): HasMany
    {
        return $this->hasMany(TraceabilityLog::class)->orderBy('happened_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** "AI chung nhan": active + co qr_code + co it nhat 1 assessment. */
    public function scopeCertified(Builder $q): Builder
    {
        return $q->where('status', 'active')
            ->whereNotNull('qr_code')
            ->whereHas('assessments');
    }
}
