<?php
use Dotenv\Dotenv;

class DatabaseConnector
{
	private $dbConnection = null;

	public function __construct($initStatement = null)
	{
		$host     = $_ENV["DB_HOST"];
		$port     = $_ENV["DB_PORT"];
		$db       = $_ENV["DB_NAME"];
		$username = $_ENV["DB_USERNAME"];
		$password = $_ENV["DB_PASSWORD"];

		try
		{
			$this->dbConnection = new PDO(
        "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db",
          $username,
          $password
      );
      $this->dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

			if (isset($initStatement))
				$this->dbConnection->exec($initStatement);
		}
		catch (PDOException $e)
		{
			exit("Unable to connect to database.");
		}
	}

	public function getConnection()
	{
		return $this->dbConnection;
	}
}
