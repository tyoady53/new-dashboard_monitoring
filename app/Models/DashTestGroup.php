<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashTestGroup extends Model
{
    use HasFactory;
    protected $table = 'dash_test_group';
    protected $guarded = [
    ];
    protected $primaryKey = null;
}
