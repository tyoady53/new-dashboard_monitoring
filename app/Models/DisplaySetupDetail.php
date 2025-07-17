<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisplaySetupDetail extends Model
{
    use HasFactory;
    protected $guarded = [
    ];

    public function setting()
    {
        return $this->belongsTo(TableSetting::class , 'data_from','table_name');
    }
}
