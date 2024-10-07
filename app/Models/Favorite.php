<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'favoritable_id', 'favoritable_type'];

    /**
     * Get the owning favoritable model.
     */
    public function favoritable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that added the favorite.
     */


    public function user()
    {
        return $this->belongsTo(User::class,'favoritable_id');
    }

    public function senderuser()
    {
        return $this->belongsTo(User::class);
    }



}
