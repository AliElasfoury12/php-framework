<?php

namespace app\controllers;

use app\models\Post;
use core\App;
use core\request\Request;


class PostController extends Controller {

    public function index () 
    {
        $post = new Post;
        $res = $post->select('id,user_id,content,created_at')
        //->paginate(10)
        //->withCount(['likes','comments'])
        ->with([
            // 'user',
            // 'postImg',
            // 'likes:id',
            // 'sharedPost',
            'comments.user:id'
        ])->latest()->get();
        // $res = Post::all('id,post');
        //$res = Post::find(40);
        App::dump($res->toArray());
        //return json_encode($res, JSON_PRETTY_PRINT);
    }
}