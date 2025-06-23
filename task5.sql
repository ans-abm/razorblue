SELECT u.id,u.username,u.email, SUM(o.amount) AS totalAmount FROM `orders` AS o 
INNER JOIN users AS u ON o.user_id = u.id GROUP BY u.id ORDER BY totalAmount DESC; 