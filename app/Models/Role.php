<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Role extends Model
{
    /** Nhan vien diem thu gom / HTX: tao lo, chup anh, xac nhan, cong bo. */
    public const NHAN_VIEN = 'nhan_vien';

    /** Xem thong ke + xuat dataset cua moi to chuc. */
    public const ADMIN = 'admin';

    public $timestamps = false;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
