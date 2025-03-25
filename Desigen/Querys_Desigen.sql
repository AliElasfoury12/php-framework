data = #SELECT {columns} FROM posts ORDER BY id DESC;
Ids =  #SELECT id FROM posts ORDER BY id DESC;

#BELONGSTO
/* SELECT users.*  FROM posts
INNER JOIN users ON posts.user_id = users.id
WHERE posts.id IN (101,103,104) {extra_query} ORDER BY posts.id DESC;*/

table2 = users  | table1 = posts
PK2 = id        | FK1 = user_id
                | PK1 = id

SELECT {table2.columns} FROM table1
INNER JOIN table2 ON table2.FK1 = table1.PK2
WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;

#HASMANY
/*SELECT post_imgs.* FROM posts
INNER JOIN post_imgs 
ON posts.id = post_imgs.post_id
WHERE posts.id IN (281,282,283) 
ORDER BY posts.id DESC;*/

#MANYTOMANY
/* SELECT alias .*,posts.id AS pivot FROM posts
INNER JOIN shared_posts ON posts.id = shared_posts.post_id
INNER JOIN posts as alias ON alias.id = shared_posts.shared_post_id
WHERE posts.id IN (325,326,327) {extra_query} ORDER BY posts.id DESC; */

table2 = posts  | table1 = posts    | pivot_table = shared_posts
PK2 = id        | FK1 = user_id     | pivot_key = post_id
                | PK1 = id          | related_key = shared_post_id

SELECT {alias.columns}, table1.PK1 AS pivot FROM table1
INNER JOIN pivot_table ON table1.PK1 = pivot_table.pivot_key
INNER JOIN table2 AS alias ON alias.PK2 = pivot_table.related_key
WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;

#BELONGESTO.MANYTOMANY
/*SELECT alias.*, posts.id AS pivot FROM posts
INNER JOIN users ON posts.user_id = users.id
INNER JOIN followers ON followers.user_id = users.id
INNER JOIN users as alias ON alias.id = followers.follower_id
WHERE posts.id IN (35,36,37) AND followers.follower_id = 112 ORDER BY posts.id DESC;*/

    #BELONGESTO
    SELECT {table2.columns} FROM table1
    (INNER JOIN table2 ON table2.FK1 = table1.PK2)
    WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;

    #MANYTOMANY
    SELECT {alias.columns}, table1.PK1 AS pivot FROM table1
    INNER JOIN pivot_table ON table1.PK1 = pivot_table.pivot_key
    INNER JOIN table2 AS alias ON alias.PK2 = pivot_table.related_key
    WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;

    #BELONGESTO.MANYTOMANY
    SELECT {alias.columns}, table1.PK1 AS pivot FROM table1
    (first_relation_part)
    INNER JOIN pivot_table ON table1.PK1 = pivot_table.pivot_key
    INNER JOIN table2 AS alias ON alias.PK2 = pivot_table.related_key
    WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;

#MANYTOMANY.BELONGESTO
/*SELECT alias .*,posts.id AS pivot FROM posts
INNER JOIN shared_posts ON posts.id = shared_posts.post_id
INNER JOIN posts as alias ON alias.id = shared_posts.shared_post_id
INNER JOIN users ON posts.user_id = users.id
WHERE posts.id IN (325,326,327) 
ORDER BY posts.id DESC;*/

    #MANYTOMANY
    SELECT {alias.columns}, table1.PK1 AS pivot FROM table1
    (INNER JOIN pivot_table ON table1.PK1 = pivot_table.pivot_key
    INNER JOIN table2 AS alias ON alias.PK2 = pivot_table.related_key)
    WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;

    #BELONGESTO
    SELECT {table2.columns} FROM table1
    (first_relation_part)
    INNER JOIN table2 ON table2.FK1 = table1.PK2
    WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;