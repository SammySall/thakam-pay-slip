<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
        'prefix',
        'name',
        'position',
        'role',
        'employee_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function createdSlips()
    {
        return $this->hasMany(Slip::class, 'create_by_id');
    }
    public function ownerSlips()
    {
        return $this->hasMany(Slip::class, 'owner_id');
    }

    public function approvedSlips()
    {
        return $this->hasMany(Slip::class, 'approve_by_id');
    }
}
