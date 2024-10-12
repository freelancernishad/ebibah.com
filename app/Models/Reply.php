<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'admin_id',
        'reply',
    ];

    protected $with = [
        'admin',
    ];

    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class); // Assuming you have an Admin model
    }
}
