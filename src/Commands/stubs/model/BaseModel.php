<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $guarded = ['id'];

    // protected $fillable=[];
    // protected $hidden=[];
}
