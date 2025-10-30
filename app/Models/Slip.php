<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slip extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'create_by_id',
        'approve_by_id',
        'receipt_details',
        'expenses_details',
        'total_receipt',
        'total_expenses',
        'status',
        'monthly',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by_id');
    }

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approve_by_id');
    }
}
