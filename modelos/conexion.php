<?php

class Conexion{

	static public function conectar(){

		$host = getenv('DB_HOST') ?: 'localhost';
		$db   = getenv('DB_NAME') ?: 'helpdesk';
		$user = getenv('DB_USER') ?: 'root';
		$pass = getenv('DB_PASS') ?: '';
		$port = getenv('DB_PORT') ?: '3306';


		$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

		try {
			$link = new PDO($dsn, $user, $pass, [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
			]);
		} catch (PDOException $e) {
			error_log('DB connection error: ' . $e->getMessage());
			die("DB connection error.");
		}

		return $link;

	}

}