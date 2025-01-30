<?php 

namespace app\models;

class Post extends Model {

    public static $fillable = [
     
    ];

    public function user (): array 
    {
      return $this->belongsTo(User::class);
    }

    public function postImg (): array 
    {
      return $this->hasMany(Post_img::class);
    }

    public function comments () 
    {
        return $this->hasMany(Comment::class);
    }

    public function likes (): array 
    {
      return $this->manyToMany(User::class,'likes', 'post_id', 'user_id');
    }

    public function shared_posts (): array 
    {
      return $this->hasOne(Shared_post::class);
    }
}