<?php 

namespace app\models;

class Shared_post extends Model {

    public static $fillable = [
     
    ];

    public function user (): array 
    {
      return $this->belongsTo(User::class);
    }


}