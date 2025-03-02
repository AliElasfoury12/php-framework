<?php

namespace app\controllers;

use app\models\Post;
use core\App;
use core\request\Request;


class PostController extends Controller {

    public function index () 
    {
       $res = Post::select('id,user_id,post,created_at')
      // ->paginate(10)
       ->withCount('likes,comments')
       ->with([
        'user.follows:id',
        'postImg:id,post_id,img',
        //'sharedPost:id,user_id,post',
        'sharedPost.user:id,name'
        //'user.posts',
        //'likes:id',
        ])->latest()->get();
       // $res = Post::all('id,post');
       //$res = Post::find(40);

       $sql = "SELECT users.id, users.name, posts.id, posts.post, posts.created_at, shared_posts.post_id, shared.id
                FROM posts 
                LEFT JOIN users ON posts.user_id = users.id
                LEFT JOIN shared_posts ON posts.id = shared_posts.shared_post_id
                LEFT JOIN posts as shared ON shared_posts.shared_post_id = shared.id";

       //$res = App::$app->db->fetch($sql);
       App::dump([$res]);
       //return json_encode($res, JSON_PRETTY_PRINT);
    }
}