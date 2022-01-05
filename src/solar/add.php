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
	timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	status VARCHAR(10) NULL DEFAULT NULL,
	flowtemp DOUBLE(4, 2) NULL DEFAULT NULL,
	refluxtemp DOUBLE(4, 2) NULL DEFAULT NULL,
	tank1 DOUBLE(4, 2) NULL DEFAULT NULL,
	tank2 DOUBLE(4, 2) NULL DEFAULT NULL,
	hflowtemp DOUBLE(4, 2) NULL DEFAULT NULL,
	houtsidetemp DOUBLE(4, 2) NULL DEFAULT NULL,
	hofficetemp DOUBLE(4, 2) NULL DEFAULT NULL,
	PRIMARY KEY (id)
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
if (!isset($data->status)) $data->status = null;
if (!isset($data->flowtemp)) $data->flowtemp = null;
if (!isset($data->refluxtemp)) $data->refluxtemp = null;
if (!isset($data->tank1)) $data->tank1 = null;
if (!isset($data->tank2)) $data->tank2 = null;
if (!isset($data->hflowtemp)) $data->hflowtemp = null;
if (!isset($data->houtsidetemp)) $data->houtsidetemp = null;
if (!isset($data->hofficetemp)) $data->hofficetemp = null;
if (!isset($data->glasshousetemp)) $data->glasshousetemp = null;
if (!isset($data->timestamp)) $data->timestamp = null;

try
{
  $query = "INSERT INTO data(status, flowtemp, refluxtemp, tank1, tank2, hflowtemp, houtsidetemp, hofficetemp, glasshousetemp, timestamp) VALUES(:status, :flowtemp, :refluxtemp, :tank1, :tank2, :hflowtemp, :houtsidetemp, :hofficetemp, :glasshousetemp, :timestamp);";

  $statement = $dbConnection->prepare($query);
  $statement->bindParam(":status", $data->status);
  $statement->bindParam(":flowtemp", $data->flowtemp);
  $statement->bindParam(":refluxtemp", $data->refluxtemp);
  $statement->bindParam(":tank1", $data->tank1);
  $statement->bindParam(":tank2", $data->tank2);
  $statement->bindParam(":hflowtemp", $data->hflowtemp);
  $statement->bindParam(":houtsidetemp", $data->houtsidetemp);
  $statement->bindParam(":hofficetemp", $data->hofficetemp);
  $statement->bindParam(":glasshousetemp", $data->glasshousetemp);
  $statement->bindParam(":timestamp", $data->timestamp);

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
