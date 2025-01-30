<?php

namespace app\controllers;

use app\models\Post;
use core\App;


class PostController extends Controller {

    public function index () {
       $res = Post::select('id,post,created_at')
       ->paginate(5)
       ->withCount('likes,comments')
       ->with([
        'user:id,name',
        'user.followings:id',
        'postImg:id,post_id,img',
        'likes:id',
        'shared_posts.user'
        ]);
       // $res = Post::all('id,post');
       //$res = Post::find(40);
       App::dump([$res]);
       //return json_encode($res, JSON_PRETTY_PRINT);
    }
}