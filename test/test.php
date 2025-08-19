<?php 

include_once './test/models/Post.php';
include_once './test/models/User.php';

$post = new Post;
echo $post->table;

echo "\n";

$user = new User;
echo $user->table;
