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

$data = json_decode(file_get_contents("php://input"));

// Check if `token` in query parameters
$params = array();
parse_str($_SERVER["QUERY_STRING"], $params);
if (isset($params["token"]) && !isset($data->token)) {
  $data->token = $params["token"];
}

if (
  !isset($data->token) ||
  strcmp($data->token, $_ENV["TOKEN"]) != 0
) {
  http_response_code(401);
  echo json_encode(array("message" => "Invalid Token."));
  return;
}

// Return error code 400 if data unset
if (!isset($data->id) || !isset($data->note)) {
  http_response_code(400);
  echo json_encode(array("message" => "ID or note missing."));
  return;
}

try {
  $query = "UPDATE data SET note=:note WHERE id =:id;";

  $statement = $dbConnection->prepare($query);
  $statement->bindParam(":note", $data->note);
  $statement->bindParam(":id", $data->id);

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
