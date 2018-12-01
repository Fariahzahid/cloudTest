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
            $dbConn = pg_pconnect("host=ec2-54-246-85-234.eu-west-1.compute.amazonaws.com dbname=dcr3qut0dr1rit user=huxpssrspgwwum password=5282cc466a257a47f323a72891b12b3fce7dd9478a25a028364f422652bb380e");
            if (!$dbConn) {
                echo "An error occured while connecting to database!\n";
            }
            $userId = $args["userId"];
            $result = pg_query($dbConn,
                "SELECT * FROM read_confirmations WHERE receiver_id=" . $userId . " AND message_read=false;");
            if (!$result) {
                echo "An error occured while querying database.\n";
            }
            $messages_read = array();
            while ($row = pg_fetch_row($result)) {
                array_push($messages_read, $row[0]);
            }
            
            if(count($messages_read) >= 1) {
                $int_mess_read = implode(',', $messages_read);
                $result = pg_query($dbConn,
                    "UPDATE read_confirmations SET message_read=true WHERE id IN ( $int_mess_read );");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                }
                
                $result = pg_query($dbConn,
                    "SELECT senderid, text FROM messages WHERE id IN ( $int_mess_read );");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                }
    
                while ($row = pg_fetch_row($result)) {
                    echo "User with Id $row[0], wrote you to you: $row[1]";
                    echo "<br />\n";
                }
            } else {
                echo "All messages already delivered!";
            }
            $response = $response->withStatus(200);
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
            $receivers = null;
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
            if(array_key_exists("receivers",$parsedbody) & count($parsedbody["receivers"]) >= 1) {
                $receivers = $parsedbody["receivers"];
            } else {
                $response->write('receivers are missing in message!');
                $response = $response->withStatus(422);
                return $response;
            }
            
            $dbConn = pg_pconnect("host=ec2-54-246-85-234.eu-west-1.compute.amazonaws.com dbname=dcr3qut0dr1rit user=huxpssrspgwwum password=5282cc466a257a47f323a72891b12b3fce7dd9478a25a028364f422652bb380e");
            if (!$dbConn) {
                echo "'There was an error while connecting to the database...";
            }
            
            $result = pg_query($dbConn, 'SELECT max(id) from messages');
            $highestId = pg_fetch_result($result, 0, 0);
            $highestId = $highestId + 1;
            
            $result = pg_query($dbConn, 'INSERT INTO public.messages(
                                        	id, "senderId", text, read)
                                    	VALUES (' . $highestId . ', ' . $senderId . ', \'' . $text . '\', ' . $highestId . ');');
            if (!$result) {
                //echo "Ein Fehler ist aufgetreten.\n";
            }
            
            foreach ($receivers AS $receiverId) {
                $result = pg_query($dbConn, 'INSERT INTO public.read_confirmations(
                                            	id, message_read, receiver_id)
                                        	VALUES (' . $highestId . ', false,' . $receiverId . ');');
            }
            
            $response = $response->withStatus(200);
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
