<?php
/**
 * Messenger
 * @version 1.0.0
 */

require_once __DIR__ . '/vendor/autoload.php';
// require_once __DIR__ . '/db.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);



/**
 * DELETE deleteMessage
 * Summary: Update Message if it is unread
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->DELETE('/messages/{messageId}/delete', function($request, $response, $args) {
            
            
            
            
            $response->write('How about implementing deleteMessage as a DELETE method ?');
            return $response;
            });


/**
 * GET readMessages
 * Summary: Get unread messages
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->GET('/messages/{userId}', function($request, $response, $args) {
//             $dbConn = pg_connect($host, $dbName, $user, $password);
//             $dbConn = pg_connect("host=ec2-54-246-85-234.eu-west-1.compute.amazonaws.com dbname=dcr3qut0dr1rit user=huxpssrspgwwum password=5282cc466a257a47f323a72891b12b3fce7dd9478a25a028364f422652bb380e")
//             or die('Connection failed: ' . pg_last_error());
            
            $dbConn = pg_pconnect("host=ec2-54-246-85-234.eu-west-1.compute.amazonaws.com dbname=dcr3qut0dr1rit user=huxpssrspgwwum password=5282cc466a257a47f323a72891b12b3fce7dd9478a25a028364f422652bb380e");
            if (!$dbConn) {
                echo "Ein Fehler ist aufgetreten.\n";
            }
            
            
            $result = pg_query($dbConn, "SELECT id, username, password FROM users");
            if (!$result) {
                echo "Ein Fehler ist aufgetreten.\n";
            }

            while ($row = pg_fetch_row($result)) {
                echo "UserId: $row[0]  UserName: $row[1]";
                $response->write($row[1]);
                echo "<br />\n";
            }
            $dbConn->close();
            $response = $response->withStatus(200);
            $response->write('How about implementing sendMessage as a GET method ?');
            return $response;
            });


/**
 * POST sendMessage
 * Summary: Send a new message
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->POST('/messages', function($request, $response, $args) {
            $parsedbody = $request->getParsedBody();
            $end = false;
            $id = null;
            $senderId = null;
            $text = null;
            $read = null;
            if(array_key_exists("id",$parsedbody) & !$end) {
                $id =       $parsedbody["id"];
                $response->write('id must not be in message!');
                $response = $response->withStatus(422);
                return $response;
            }
            
            if(array_key_exists("senderId",$parsedbody)) {
                $senderId = $parsedbody["senderId"];
            } else {
                $response->write('senderId is missing in message!');
                $response = $response->withStatus(422);
                return $response;
            }
            if(array_key_exists("text",$parsedbody)) {
                $text = $parsedbody["text"];
            } else {
                $response->write('text is missing in message!');
                $response = $response->withStatus(422);
                return $response;
            }
            if(array_key_exists("read",$parsedbody)) {
                $read = $parsedbody["read"];
            } else {
                $response->write('read is missing in message!');
                $response = $response->withStatus(422);
                return $response;
            }
            
            $response = $response->withStatus(200);
//             $response->write('How about implementing sendMessage as a POST method ?');
            return $response;
            });


/**
 * PUT updateMessage
 * Summary: Update Message if it is unread
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->PUT('/messages', function($request, $response, $args) {
            
            
            
            $body = $request->getParsedBody();
            $response->write('How about implementing updateMessage as a PUT method ?');
            return $response;
            });



$app->run();
