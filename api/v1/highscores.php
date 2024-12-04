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

    function insertHighScore($playername, $hits, $accuracy, $playtime)
    {
        if ($this->conn) {
            // Sanitize inputs
            $pn = htmlspecialchars($playername, ENT_QUOTES, 'UTF-8');
            $ht = filter_var($hits, FILTER_VALIDATE_INT);
            $acc = filter_var($accuracy, FILTER_VALIDATE_FLOAT);
            $pt = filter_var($playtime, FILTER_VALIDATE_INT);

            if ($pn && $ht !== false && $acc !== false && $pt !== false) {
                // Use prepared statements to prevent SQL injection
                $stmt = $this->conn->prepare("INSERT INTO HIGHSCORES (playername, hits, accuracy, playtime) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sidi", $pn, $ht, $acc, $pt); // "sidi" means string, int, double, int
                if ($stmt->execute()) {
                    return 'OK';
                } else {
                    return "Error inserting high score: " . $this->conn->error;
                }
            } else {
                return 'Parameters cannot be empty or invalid';
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
            $reply = array("status" => "OK", "scores" => array());
            $sql = "SELECT id, playername, score FROM HIGHSCORES ORDER BY score DESC LIMIT 3";

            if (($result = $this->conn->query($sql))) {
                while ($row = $result->fetch_assoc()) {
                    $reply["scores"][] = $row;
                }
            } else {
                $reply['status'] = "Error";
                $reply['msg'] = "Error fetching high scores: " . $this->conn->error;
            }

            return $reply;
        } else {
            return array("status" => "Error", "msg" => "DB connection not open");
        }
    }
}

$hs = new HighScores();

header('Content-Type: application/json');

// Handle the POST request and get the raw POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the raw POST data (JSON input)
    $inputData = file_get_contents('php://input');

    // Decode the JSON data
    $data = json_decode($inputData, true); // true returns associative array

    if (isset($data['playername'], $data['score'], $data['accuracy'], $data['playtime'])) {
        $playername = $data['playername'];
        $score = $data['score'];
        $accuracy = $data['accuracy'];
        $playtime = $data['playtime'];

        // Insert high score
        $ret = $hs->insertHighScore($playername, $score, $accuracy, $playtime);
        $response["status"] = $ret;
        $response["dbg"] = "POST received: Player: $playername, Score: $score, Accuracy: $accuracy, Playtime: $playtime";
    } else {
        // Missing required fields in POST data
        $response["status"] = "Error";
        $response["dbg"] = "Missing fields in POST data";
    }

    echo json_encode($response);  // Return the JSON response
}

$hs->closeConnection();

?>