<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashVisitHour extends Model
{
    use HasFactory;
    protected $table = 'dash_visit_hour';
    protected $guarded = [
    ];
    protected $primaryKey = null;
}
