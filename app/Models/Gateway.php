<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
