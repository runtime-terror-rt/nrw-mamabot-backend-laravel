<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QaRegistration extends Model {
    protected $fillable = ['user_id', 'qa_session_id'];
    public function user() 
    { 
        return $this->belongsTo('App\Models\User');
    }
    public function qaSession()
    { 
        return $this->belongsTo('App\Models\QaSession'); 
    }
}