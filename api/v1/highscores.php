<?php

class HighScores
{
    const SERVERNAME = "127.0.0.1:3306";
    const USERNAME = "root";
    const PASSWORD = "";
    const DBNAME = "HIGHSCORES";

    private $conn = null;

    function __construct()
    {
        $this->initConnection();
    }

    function initConnection()
    {
        $this->conn = new mysqli(self::SERVERNAME, self::USERNAME, self::PASSWORD, self::DBNAME);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        return true;
    }
    function closeConnection()
    {
        if ($this->conn != null) {
            $this->conn->close();
            $this->conn = null;
        }
    }
    function queryHighScores()
    {
        if ($this->conn) {
            $reply = array("scores" => array());

            $sql = "SELECT * FROM HIGHSCORES ORDER BY score DESC LIMIT 3";
            if (($result = $this->conn->query($sql))) {
                while ($row = $result->fetch_assoc()) {
                    // Remove playtime from the results:
                    unset($row['playtime']);
                    $reply["scores"][] = $row;
                }
                return $reply;
            } else {
                $reply['status'] = "Error";
                $reply['msg'] = "Error fetching high scores: " . $this->conn->error;
                return $reply;
            }
        } else {
            $reply['status'] = "Error";
            $reply['msg'] = "DB connection not open";
            return $reply;
        }
    }
    // Create the HighScores object 
}

$hs = new HighScores();
$hs->initConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = $hs->queryHighScores(); // Call the queryHighScores method
    echo json_encode($response);
}

$hs->closeConnection();  // Call the method with the correct name



?>