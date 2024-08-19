<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingValue extends Model
{
    use HasFactory;


    protected $fillable = ['setting_id', 'value_id', 'name'];

    public function setting()
    {
        return $this->belongsTo(Setting::class);
    }
}
