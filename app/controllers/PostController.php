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
        'sharedPost.user:id,name',
        'sharedPost.postImg:id,post_id,img'
        //'user.posts',
        //'likes:id',
        ])->latest()->get();
       // $res = Post::all('id,post');
       //$res = Post::find(40);
        App::dump([$res]);
       //return json_encode($res, JSON_PRETTY_PRINT);
    }
}