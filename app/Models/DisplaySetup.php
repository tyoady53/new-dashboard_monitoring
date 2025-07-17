<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisplaySetup extends Model
{
    use HasFactory;
    protected $guarded = [
    ];

    public function details()
    {
        return $this->hasMany(DisplaySetupDetail::class , 'display_id')->orderBy('sequence');
    }
}
