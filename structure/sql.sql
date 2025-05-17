#post.user.follower

SELECT alias1.id, followers.user_id AS pivot, followers.follower_id AS related , posts.id AS mainKey FROM posts
INNER JOIN users ON posts.user_id = users.id 
INNER JOIN followers ON users.id = followers.user_id 
INNER JOIN users AS alias1 ON followers.follower_id = alias1.id
WHERE posts.id IN ($ids)
AND follower_id = '112' ORDER BY posts.created_at DESC 

#post.user
SELECT users.id,users.name , posts.id AS mainKey FROM posts
INNER JOIN users ON posts.user_id = users.id 
WHERE posts.id IN ($ids) ORDER BY posts.created_at DESC 

#post.post (shared_post)
SELECT alias0.id,alias0.user_id,alias0.content,
shared_posts.post_id AS pivot, shared_posts.shared_post_id AS related , posts.id AS mainKey FROM posts
INNER JOIN shared_posts ON posts.id = shared_posts.post_id 
INNER JOIN posts AS alias0 ON alias0.id = shared_posts.shared_post_id 
WHERE posts.id IN ($ids) ORDER BY posts.created_at DESC 

#post.imgs
SELECT post_imgs.id,post_imgs.post_id,post_imgs.img , posts.id AS mainKey FROM posts
INNER JOIN post_imgs ON posts.id = post_imgs.post_id 
WHERE posts.id IN ($ids) ORDER BY posts.created_at DESC 

#post.post.user (shared_post.user)
SELECT users.id,users.name , posts.id AS mainKey FROM posts
INNER JOIN shared_posts ON posts.id = shared_posts.post_id 
INNER JOIN posts AS alias0 ON alias0.id = shared_posts.shared_post_id 
INNER JOIN users ON alias0.user_id = users.id 
WHERE posts.id IN ($ids) ORDER BY posts.created_at DESC 

#post.post.post_imgs (shared_post.images)
SELECT post_imgs.id,post_imgs.post_id,post_imgs.img , posts.id AS mainKey FROM posts
INNER JOIN shared_posts ON posts.id = shared_posts.post_id 
INNER JOIN posts AS alias0 ON alias0.id = shared_posts.shared_post_id 
INNER JOIN post_imgs ON alias0.id = post_imgs.post_id 
WHERE posts.id IN ($ids) ORDER BY posts.created_at DESC 

#post.post.users (shared_post.likes)
SELECT alias1.id,alias1.name, likes.post_id AS pivot, likes.user_id AS related , posts.id AS mainKey FROM posts
INNER JOIN shared_posts ON posts.id = shared_posts.post_id 
INNER JOIN posts AS alias0 ON alias0.id = shared_posts.shared_post_id 
INNER JOIN likes ON alias0.id = likes.post_id 
INNER JOIN users AS alias1 ON alias1.id = likes.user_id 
WHERE posts.id IN ($ids) ORDER BY posts.created_at DESC

#post.user (post.likesCount)
SELECT COUNT(*) AS count , posts.id AS mainKey FROM posts 
INNER JOIN likes ON posts.id = likes.post_id 
INNER JOIN users AS alias0 ON alias0.id = likes.user_id 
WHERE posts.id IN ($ids) 
GROUP BY posts.id ORDER BY posts.created_at DESC 

#post.comments (post.commentsCount)
SELECT COUNT(*) AS count , posts.id AS mainKey FROM posts 
INNER JOIN comments ON posts.id = comments.post_id 
WHERE posts.id IN ($ids) 
GROUP BY posts.id ORDER BY posts.created_at DESC
