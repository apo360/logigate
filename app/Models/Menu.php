<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'module_id', 'menu_name', 'slug', 'order_priority', 'route', 'icon', 'description', 'permission'
    ];

    public static function clearMenuCacheForUser($userId = null)
    {
        $userId = $userId ?: Auth::id();
        Cache::forget('menus_user_' . $userId);
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
