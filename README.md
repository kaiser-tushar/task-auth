# Task-Auth-CakePHP

Task-Auth is a Simple PHP application that lets you register user, login with that user and you will see user list.

## Description
This application can create user by simple registration and show user list after login.

There are three task you can do - **Register** an user, **Login** with user email and password, **View** all user list.

### Prerequisites
```
PHP - minumum version PHP 5.6
Javascript - need to enable javascript on browser
Composer to install required depndency
Database like MySQL
```

### Installing
 For cloning this project need to run
 ```
git clone https://github.com/kaiser-tushar/task-auth
```
Go to project folder (ex: cd task-auth) and run in cmd / terminal
```
composer install
```
Create a database on your preferred database engine like MySQL.

Go to Database folder alter_query.sql and run listed queries from that file on newly created database.Or you can run below SQL
```
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
```

Open src/Core/Config.php and update **$databaseCredentials** for Database connection.
```
private $databaseCredentials = [
        'host' => 'Your Database Host',
        'username' => 'Database username',
        'password' => 'Database password',
        'db_name' => 'Database name',
        'database_type' => 'Database type like mysql',
    ];
```
![DB connection](https://i.imgur.com/40IcQRN.png)

### Deployment
To run  this application on PHP server locally in your machine run

```
php -S localhost:8000
```
Change the localhost port as needed

Go to localhost:8000 or localhost:your_given_port_number

### Built With
This application is build with PHP and Javascript. I use PHP OOP, MVC design, jQuery for Javascript, MySQL for database. I use [Meddo](https://medoo.in/) for ORM. Also for token based authentication I use  [JWT](http://jwt.io/)
