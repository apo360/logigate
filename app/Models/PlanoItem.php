<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanoItem extends Model
{
    //
    protected $table = 'plano_items';

    protected $primaryKey = 'id';

    protected $fillable = [
        'plano_id', 'item', 'text_color', 'icon', 'observacao',
    ];
}
