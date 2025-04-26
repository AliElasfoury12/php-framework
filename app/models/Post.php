<?php 

namespace app\models;

class Post extends Model {

    public static $fillable = [
     
    ];

    public function user ()
    {
      return $this->belongsTo(User::class)->select('id,name');
    }

    public function postImg ()
    {
      return $this->hasMany(PostImg::class)->select('id,post_id,img');
    }

    public function comments ()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes ()
    {
      return $this->manyToMany(User::class,'likes', 'post_id', 'user_id');
    }

    public function sharedPost ()
    {
      return $this->manyToMany(Post::class, 'shared_posts', 'post_id', 'shared_post_id')
      ->select('id,user_id,content')
      ->with(['user','postImg:id,post_id,img','likes:id,name']);
    }
}