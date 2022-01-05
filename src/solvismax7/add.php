<?php
// Load composer libraries
require "../vendor/autoload.php";
// Load other dependencies
require "../utils/DatabaseConnector.php";

// Set response headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Initialize values from `.env`
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize database connection
$initStatement = "
CREATE TABLE IF NOT EXISTS data (
	id INT NOT NULL AUTO_INCREMENT,
	timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	speicheroben DOUBLE(3, 1) NULL DEFAULT NULL,
	speicherreferenz DOUBLE(3, 1) NULL DEFAULT NULL,
	heizungspufferoben DOUBLE(3, 1) NULL DEFAULT NULL,
	heizungspufferunten DOUBLE(3, 1) NULL DEFAULT NULL,
	kaltwasser DOUBLE(3, 1) NULL DEFAULT NULL,
	warmwasser DOUBLE(3, 1) NULL DEFAULT NULL,
	zirkulation DOUBLE(3, 1) NULL DEFAULT NULL,
	aussen DOUBLE(3, 1) NULL DEFAULT NULL,
	vorlauf DOUBLE(3, 1) NULL DEFAULT NULL,
	brennerleistung DOUBLE(3, 1) NULL DEFAULT NULL,
	brennerstarts INT NULL DEFAULT NULL,
	brennerlaufzeit INT NULL DEFAULT NULL,
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
if (!isset($data->timestamp)) $data->timestamp = null;
if (!isset($data->speicheroben)) $data->speicheroben = null;
if (!isset($data->speicherreferenz)) $data->speicherreferenz = null;
if (!isset($data->heizungspufferoben)) $data->heizungspufferoben = null;
if (!isset($data->heizungspufferunten)) $data->heizungspufferunten = null;
if (!isset($data->kaltwasser)) $data->kaltwasser = null;
if (!isset($data->warmwasser)) $data->warmwasser = null;
if (!isset($data->zirkulation)) $data->zirkulation = null;
if (!isset($data->aussen)) $data->aussen = null;
if (!isset($data->vorlauf)) $data->vorlauf = null;
if (!isset($data->brennerleistung)) $data->brennerleistung = null;
if (!isset($data->brennerstarts)) $data->brennerstarts = null;
if (!isset($data->brennerlaufzeit)) $data->brennerlaufzeit = null;

try
{
  $query = "INSERT INTO data(timestamp, speicheroben, speicherreferenz, heizungspufferoben, heizungspufferunten, kaltwasser, warmwasser, zirkulation, aussen, vorlauf, brennerleistung, brennerstarts, brennerlaufzeit) VALUES(:timestamp, :speicheroben, :speicherreferenz, :heizungspufferoben, :heizungspufferunten, :kaltwasser, :warmwasser, :zirkulation, :aussen, :vorlauf, :brennerleistung, :brennerstarts, :brennerlaufzeit);";

  $statement = $dbConnection->prepare($query);
	$statement->bindParam(":timestamp", $data->timestamp);
  $statement->bindParam(":speicheroben", $data->speicheroben);
  $statement->bindParam(":speicherreferenz", $data->speicherreferenz);
  $statement->bindParam(":heizungspufferoben", $data->heizungspufferoben);
  $statement->bindParam(":heizungspufferunten", $data->heizungspufferunten);
  $statement->bindParam(":kaltwasser", $data->kaltwasser);
  $statement->bindParam(":warmwasser", $data->warmwasser);
  $statement->bindParam(":zirkulation", $data->zirkulation);
  $statement->bindParam(":aussen", $data->aussen);
  $statement->bindParam(":vorlauf", $data->vorlauf);
	$statement->bindParam(":brennerleistung", $data->brennerleistung);
	$statement->bindParam(":brennerstarts", $data->brennerstarts);
	$statement->bindParam(":brennerlaufzeit", $data->brennerlaufzeit);

  $success = $statement->execute();

  if ($success)
	{
    http_response_code(201);
    echo json_encode(array("message" => "Success."));
  }
	else
	{
    http_response_code(403);
    // echo json_encode(array("message" => "Internal error."));
		echo json_encode(array("message" => $statement->errorInfo()));
  }
}
catch (PDOException $e) {
  http_response_code(400);
  echo json_encode(array("message" => $e->getmessage()));
}
