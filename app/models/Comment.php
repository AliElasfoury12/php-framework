<?php 

namespace app\models;

class Comment extends Model {

    public static $fillable = [
     
    ];

    public function user ()
    {
        return $this->belongsTo(User::class);
    }
}