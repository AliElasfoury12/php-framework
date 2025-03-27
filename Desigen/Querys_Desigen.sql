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
INNER JOIN table2 ON table1.FK1 = table2.PK2
WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;

#HASMANY
SELECT post_imgs.* FROM posts
INNER JOIN post_imgs ON posts.id = post_imgs.post_id
WHERE posts.id IN (281,282,283) ORDER BY posts.id DESC;

table2 = post_imgs  | table1 = posts
FK2 = post_id       | PK1 = id
                    
SELECT {table2.columns} FROM table1
INNER JOIN table2 ON table1.PK1 = table2.FK2
WHERE table1.PK1 IN (Ids) ORDER BY table1.PK1 DESC;

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
    (INNER JOIN table2 ON table1.FK1 = table2.PK2)
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
    INNER JOIN table2 ON table1.FK1 = table2.PK2
    WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;

#MANYTOMANY.HASMANY
SELECT * from posts
INNER JOIN shared_posts on posts.id = shared_posts.post_id
INNER JOIN posts AS alias ON shared_posts.shared_post_id = alias.id
INNER JOIN post_imgs ON  alias.id = post_imgs.post_id

table2 = posts  | table1 = posts | pivot_table = shared_posts   | table3 = post_imgs
PK2 = id        | PK1 = id       | pivot_key = post_id          | FK3 = post_id
                |                | related_key = shared_post_id |
    
    #MANYTOMANY
    SELECT {alias.columns}, table1.PK1 AS pivot FROM table1
    (INNER JOIN pivot_table ON table1.PK1 = pivot_table.pivot_key
    INNER JOIN table2 AS alias ON pivot_table.related_key = alias.PK2)
    WHERE table1.PK1 IN (Ids) {extra_query} ORDER BY table1.PK1 DESC;


    #HASMANY
    SELECT {table3.columns} FROM table1
    (first_relation_part)
    INNER JOIN table3 ON alias.PK2 = table3.FK3
    WHERE table1.PK1 IN (Ids) ORDER BY table1.PK1 DESC;

