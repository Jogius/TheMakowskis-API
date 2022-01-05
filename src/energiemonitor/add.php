<?php
// Load composer libraries
require "../vendor/autoload.php";
// Load other dependencies
require "../utils/DatabaseConnector.php";

// Set response headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Initialize values from `.env`
(Dotenv\Dotenv::createImmutable(__DIR__))->load();

// Initialize database connection
$initStatement = "
CREATE TABLE IF NOT EXISTS data (
	timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	bezug DOUBLE NULL DEFAULT NULL,
	einspeisung DOUBLE NULL DEFAULT NULL,
	ertrag DOUBLE NULL DEFAULT NULL,
	soc FLOAT NULL DEFAULT NULL,
	verbrauch FLOAT NULL DEFAULT NULL,
	PRIMARY KEY (timestamp)
) ENGINE=INNODB;
";
$dbConnection = (new DatabaseConnector($initStatement))->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (
	!isset($data->token) ||
	strcmp($data->token, $_ENV["TOKEN"]) != 0
)
{
	http_response_code(401);
	echo json_encode(array("message" => "Invalid Token."));
	return;
}

// Set data to null if unset
if (!isset($data->bezug)) $data->bezug = null;
if (!isset($data->einspeisung)) $data->einspeisung = null;
if (!isset($data->ertrag)) $data->ertrag = null;
if (!isset($data->soc)) $data->soc = null;
if (!isset($data->verbrauch)) $data->verbrauch = null;
if (!isset($data->timestamp)) $data->timestamp = null;

try
{
  $query = "INSERT INTO data(bezug, einspeisung, ertrag, soc, verbrauch, timestamp) VALUES(:bezug, :einspeisung, :ertrag, :soc, :verbrauch, :timestamp);";

  $statement = $dbConnection->prepare($query);
  $statement->bindParam(":bezug", $data->bezug);
  $statement->bindParam(":einspeisung", $data->einspeisung);
  $statement->bindParam(":ertrag", $data->ertrag);
  $statement->bindParam(":soc", $data->soc);
  $statement->bindParam(":verbrauch", $data->verbrauch);
  $statement->bindParam(":timestamp", date("Y-m-d H:i:s", $data->timestamp / 1000));

  $success = $statement->execute();

  if ($success)
	{
    http_response_code(201);
    echo json_encode(array("message" => "Success."));
  }
	else
	{
    http_response_code(403);
    echo json_encode(array("message" => "Internal error."));
  }
}
catch (PDOException $e) {
  http_response_code(400);
  echo json_encode(array("message" => $e->getmessage()));
}
