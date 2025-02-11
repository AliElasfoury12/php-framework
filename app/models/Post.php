<?php 

namespace app\models;

class Post extends Model {

    public static $fillable = [
     
    ];

    public function user ()
    {
      return $this->belongsTo(User::class);
    }

    public function postImg ()
    {
      return $this->hasMany(PostImg::class);
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
      return $this->manyToMany(Post::class, 'shared_posts', 'post_id', 'shared_post_id');
    }
    /*
    post        user    shared_post
    id          id      shared_post_id
    user_id 
                post_id
    posts = [
        [
            id => 1,
            post => post1,
            shared_post => [
                id => 3,
                user_id => 1,
                post => post3,
            ]
            
        ],[
            id => 2,
            post=> post2,
        
        ]
    ]
    
    select * from users where id = posts[shared_post][id]
     */
}