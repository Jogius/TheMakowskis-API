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
$dbConnection = (new DatabaseConnector())->getConnection();

// Check for `from` and `to` query parameters
$params = array();
parse_str($_SERVER["QUERY_STRING"], $params);
if (
  isset($params["from"]) &&
  isset($params["to"])
)
{
  $range = true;
  $from = $params["from"];
  $to = $params["to"];
}
else
{
  $range = false;
}

try
{
  if ($range)
	{
    $query = "SELECT * FROM data WHERE timestamp BETWEEN FROM_UNIXTIME(:from) AND FROM_UNIXTIME(:to) ORDER BY timestamp DESC;";

    $statement = $dbConnection->prepare($query);
    $statement->bindParam(":from", $from);
    $statement->bindParam(":to", $to);
  }
	else
	{
    $query = "SELECT * FROM data ORDER BY timestamp ASC;";

    $statement = $dbConnection->query($query);
  }

  $success = $statement->execute();

	if ($success)
	{
    $data = $statement->fetchAll();
    echo json_encode($data);
  }
	else
	{
    http_response_code(403);
    echo json_encode(array("message" => "Internal error."));
  }
}
catch (PDOException $e) {
  http_response_code(400);
  echo json_encode(array("message" => $e->getMessage()));
}
