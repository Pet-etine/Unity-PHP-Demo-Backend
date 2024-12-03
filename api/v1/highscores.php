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
    function insertHighScore($playername, $score)
    {
        if ($this->conn) {
            $pn = filter_var($playername, FILTER_SANITIZE_STRING);
            $sc = filter_var($score, FILTER_VALIDATE_INT);
            if ($pn && $sc) {
                $sql = 'INSERT INTO HIGHSCORES (playername, score) VALUES ("' . $pn . '",' . $sc . ')';
                if ($this->conn->query($sql) === TRUE) {
                    return 'OK';
                } else {

                    return "Error inserting high score: " . $this->conn->error;
                }
            } else {
                return 'Parameters cannot be empty';
            }
        } else {
            return 'DB connection error';
        }
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

            $sql = "SELECT id, playername, score FROM HIGHSCORES ORDER BY score DESC LIMIT 3";
            if (($result = $this->conn->query($sql))) {
                while ($row = $result->fetch_assoc()) {
                    $reply["scores"][] = $row;
                }
                return $reply; // Return the $reply array here
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

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = $hs->queryHighScores();
    echo json_encode($response);
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Use else if here
    $data = file_get_contents('php://input');
    $hsItem = json_decode($data, true); // No need for urldecode

    // Check if playername and score are set
    if (isset($hsItem['playername']) && isset($hsItem['score'])) {
        $ret = $hs->insertHighScore($hsItem['playername'], $hsItem['score']);
        $response["status"] = $ret;
        $response["dbg"] = "POST received: " . $hsItem['playername'] . ': ' . $hsItem['score'];
    } else {
        $response["status"] = "Error";
        $response["dbg"] = "Missing playername or score in POST data"; // More specific error message
    }

    echo json_encode($response);
} else {
    // Handle requests that are not GET
    $response['status'] = 'Error';
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
}

$hs->closeConnection();

?>