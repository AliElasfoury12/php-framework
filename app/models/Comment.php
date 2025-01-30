<?php 

namespace app\models;

class Comment extends Model {

    public static $fillable = [
     
    ];

    public function user (): array 
    {
        return $this->belongsTo(User::class);
    }
}