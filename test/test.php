<?php 


$sql = "SELECT alias3.id,alias3.name, likes.post_id AS pivot, likes.user_id AS related , posts.id AS mainKey FROM posts
INNER JOIN shared_posts ON posts.id = shared_posts.post_id
INNER JOIN posts AS alias1 ON alias1.id = shared_posts.shared_post_id
INNER JOIN likes ON alias1.id = likes.post_id
INNER JOIN users AS alias3 ON alias3.id = likes.user_id
WHERE posts.id IN () ORDER BY posts.created_at DESC ";

$sql = '';
preg_match_all('/\s*alias\d*\s*/', $sql, $matches);
var_dump($matches);
$matches = $matches[0];
$lastAlias = $matches[count($matches) - 1];
$lastAlias = str_replace('alias','', $lastAlias);
$lastAlias++;
