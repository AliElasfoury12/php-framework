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
        'user:id,name',
        'user.follows:id',
        'postImg:id,post_id,img',
        'sharedPost:id,user_id,post',
        'sharedPost.user:id,name'
        //'user.posts',
        //'likes:id',
        ])->get();
       // $res = Post::all('id,post');
       //$res = Post::find(40);

       $string = 'PostImgUser';
        function toSnack (string $string) {
          return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
        }

        $start = microtime(true);
      
        $end = microtime(true);
        $time = ($end - $start)*1000;

       App::dump([$res]);
       //return json_encode($res, JSON_PRETTY_PRINT);
    }
}