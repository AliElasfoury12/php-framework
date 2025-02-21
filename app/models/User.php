<?php 

namespace app\models;

use core\Auth;

class User extends Model {
    use Auth;

    public static $fillable = [
        'name',
        'email',
        'password'
    ];

    public function posts () 
    {
       return $this->hasMany(Post::class);
    }

    public function followers () 
    {
        return $this->manyToMany(User::class,'followers', 'user_id', 'follower_id');
    }

    public function followings () 
    {
        return $this->manyToMany(User::class,'followers', 'follower_id', 'user_id');
    }

    public function follows () 
    {
        // authuser->id = 109
        return $this->followers()->where('follower_id', 109);
    }
}