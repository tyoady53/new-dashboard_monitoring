<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashVisitation extends Model
{
    use HasFactory;
    protected $table = 'dash_visitation';
    protected $guarded = [
    ];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;
}
