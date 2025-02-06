<?php

namespace app\controllers;

use app\models\Post;
use core\App;
use core\request\Request;


class PostController extends Controller {

    public function index () 
    {
       $res = Post::select('id,user_id,content,created_at')
       ->paginate(10)
       //->withCount('likes,comments')
       ->with([
        'user:id,name',
        'user.followings:id',
        //'postImg:id,post_id,img',
        //'likes:id',
        'shared_posts.user'
        ])->get();
       // $res = Post::all('id,post');
       //$res = Post::find(40);

       $string = 'PostImgUser';
        function toSnack (string $string) {
          return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
        }

       App::dump([$string, $res]);
       //return json_encode($res, JSON_PRETTY_PRINT);
    }
}