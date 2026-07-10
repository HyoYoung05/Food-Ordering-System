<?php
declare(strict_types=1);

require_once __DIR__.'/Env.php';
Env::load();
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Manila'));

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $host = Env::get('DB_HOST', '127.0.0.1');
            $port = Env::get('DB_PORT', '3306');
            $name = Env::get('DB_NAME', 'food_ordering_system_db');
            $user = Env::get('DB_USER', 'root');
            $password = Env::get('DB_PASSWORD', '');
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            self::$connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$connection->exec("SET time_zone = '+08:00'");
        }

        return self::$connection;
    }
}
