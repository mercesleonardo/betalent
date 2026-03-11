<?php

namespace App\Models;

use App\Observers\GatewayObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([GatewayObserver::class])]
class Gateway extends Model
{
    /** @use HasFactory<\Database\Factories\GatewayFactory> */
    use HasFactory;

    protected $fillable = ['name', 'is_active', 'priority'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }
}
