<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductExemptionReason extends Model
{
    use HasFactory;

    protected $table = 'product_exemption_reasons';

    protected $fillable = [
        'code',
        'name',
    ];

    public $timestamps = true;
}
