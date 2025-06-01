Necessário após subir o banco rodar 

docker exec -it auth_mysql mysql -u root -p

E criar a tabela

USE auth_api;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  lastname VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255)
);
