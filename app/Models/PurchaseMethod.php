<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function scopeData($query)
    {
        return $query->select([
            'id',
            'name',
        ]);
    }
}
