<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ButtonMenu extends Model
{
    use HasFactory;
    
    protected $connection = 'settings';
    protected $table = 'button_menus';
    protected $primaryKey = 'id';

    protected $fillable = [
        'menu_name', 
    ];
}
