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
  id INT NOT NULL AUTO_INCREMENT,
  start TIMESTAMP NOT NULL,
  end TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  energy INT NOT NULL,
  note TEXT NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=INNODB;
";
$dbConnection = (new DatabaseConnector($initStatement))->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (
  !isset($data->token) ||
  strcmp($data->token, $_ENV["TOKEN"]) != 0
) {
  http_response_code(401);
  echo json_encode(array("message" => "Invalid Token."));
  return;
}

// Set data to null if unset
if (!isset($data->start)) $data->start = null;
if (!isset($data->end)) $data->end = null;
if (!isset($data->energy)) $data->energy = null;
if (!isset($data->note)) $data->note = null;

try {
  $query = "INSERT INTO data(`start`, `end`, energy, note) VALUES(:start, :end, :energy, :note);";

  $statement = $dbConnection->prepare($query);
  $statement->bindParam(":start", date("Y-m-d H:i:s", $data->start / 1000));
  $statement->bindParam(":end", date("Y-m-d H:i:s", $data->end / 1000));
  $statement->bindParam(":energy", $data->energy);
  $statement->bindParam(":note", $data->note);

  $success = $statement->execute();

  if ($success) {
    http_response_code(201);
    echo json_encode(array("message" => "Success."));
  } else {
    http_response_code(403);
    echo json_encode(array("message" => "Internal error."));
  }
} catch (PDOException $e) {
  http_response_code(400);
  echo json_encode(array("message" => $e->getmessage()));
}
