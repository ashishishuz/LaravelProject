<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class SavedJob extends Model
{
    //
    use HasFactory;
    
    public function job(){
        return $this->belongsTo(Job::class);
    }
}
