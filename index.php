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
 * Summary: Delete Message
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->DELETE('/messages/{messageId}/delete', function($request, $response, $args) {
            $dbConn = pg_pconnect("host=ec2-54-246-85-234.eu-west-1.compute.amazonaws.com dbname=dcr3qut0dr1rit user=huxpssrspgwwum password=5282cc466a257a47f323a72891b12b3fce7dd9478a25a028364f422652bb380e");
            if (!$dbConn) {
                echo "An error occured while connecting to database!\n";
            }
            $messageId = $args["messageId"];
            $messages_read = array();
            
            if(is_numeric($messageId)) {
                $result = pg_query($dbConn,
                    "SELECT id FROM messages WHERE id=$messageId;");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                }
                while ($row = pg_fetch_row($result)) {
                    array_push($messages_read, $row[0]);
                }
                if(count($messages_read) == 0) {
                    $response = $response->withStatus(404);
                    return $response->write("Message not found!");
                } else {
                    $result = pg_query($dbConn,
                        "SELECT COUNT(message_read) FROM read_confirmations WHERE id=$messageId AND message_read=true;");
                    if (!$result) {
                        echo "An error occured while querying database.\n";
                        return $response->withStatus(500);
                    }
                    $row = pg_fetch_row($result);
                    $messagesFound = $row[0];
                    echo $messagesFound;
                    if($messagesFound >= 1) {
                        return $response->withStatus(404)->write("Message already read");
                    }
                    
                    $result = pg_query($dbConn,
                        "DELETE FROM read_confirmations WHERE id=$messageId;");
                    if (!$result) {
                        echo "An error occured while querying database.\n";
                        return $response->withStatus(500);
                    }
                    $result = pg_query($dbConn,
                        "DELETE FROM messages WHERE id=$messageId;");
                    if (!$result) {
                        echo "An error occured while querying database.\n";
                        return $response->withStatus(500);
                    }
                    $response = $response->write("Message successfully deleted.");
                    return $response->withStatus(200);
                }
            }
            $response = $response->write("Invalid messageId");
            return $response->withStatus(404);
            });


/**
 * GET usersReadMessage
 * Summary: Get which users already read the message
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/messages/{messageId}/read', function($request, $response, $args) {
            $dbConn = pg_pconnect("host=ec2-54-246-85-234.eu-west-1.compute.amazonaws.com dbname=dcr3qut0dr1rit user=huxpssrspgwwum password=5282cc466a257a47f323a72891b12b3fce7dd9478a25a028364f422652bb380e");
            if (!$dbConn) {
                echo "An error occured while connecting to database!\n";
                return $response->withStatus(500);
            }
            $messageId = $args["messageId"];
            $usersIdsWhoReadMessage = array();
            
            if(is_numeric($messageId)) {
                $result = pg_query($dbConn,
                    "SELECT receiver_id FROM read_confirmations WHERE id=" . $messageId . " AND message_read=true;");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                    return $response->withStatus(500);
                }
                while ($row = pg_fetch_row($result)) {
                    array_push($usersIdsWhoReadMessage, $row[0]);
                    echo "User with id $row[0] already read the message";
                }
                if(count($usersIdsWhoReadMessage) == 0) {
                    echo "Nobody has read the message yet.";
                }
                return $response->withStatus(200);
            }
            return $response->withStatus(404)->write("Message not found!");
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
                return $response->withStatus(500);
            }
            $userId = $args["userId"];
            $messages_read = array();
            
            if(is_numeric($userId)) {
                $result = pg_query($dbConn,
                    "SELECT * FROM read_confirmations WHERE receiver_id=" . $userId . " AND message_read=false;");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                    return $response->withStatus(500);
                }
                while ($row = pg_fetch_row($result)) {
                    array_push($messages_read, $row[0]);
                }
            }
            
            if(count($messages_read) >= 1) {
                $int_mess_read = implode(',', $messages_read);
                $result = pg_query($dbConn,
                    "UPDATE read_confirmations SET message_read=true WHERE id IN ( $int_mess_read );");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                    return $response->withStatus(500);
                }
                
                $result = pg_query($dbConn,
                    "SELECT senderid, text FROM messages WHERE id IN ( $int_mess_read );");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                    return $response->withStatus(500);
                }
    
                while ($row = pg_fetch_row($result)) {
                    echo "User with Id $row[0], wrote to you: \"$row[1]\"";
                    echo "<br />\n";
                }
            } else {
                echo "All messages already delivered!";
            }
            $response = $response->withStatus(200);
            return $response;
            });


/**
 * GET readAllMessages
 * Summary: Get unread messages
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/messages/{userId}/all', function($request, $response, $args) {
            $dbConn = pg_pconnect("host=ec2-54-246-85-234.eu-west-1.compute.amazonaws.com dbname=dcr3qut0dr1rit user=huxpssrspgwwum password=5282cc466a257a47f323a72891b12b3fce7dd9478a25a028364f422652bb380e");
            if (!$dbConn) {
                echo "An error occured while connecting to database!\n";
                return $response->withStatus(500);
            }
            $userId = $args["userId"];
            $messages_read = array();
            
            if(is_numeric($userId)) {
                $result = pg_query($dbConn,
                    "SELECT * FROM read_confirmations WHERE receiver_id=$userId");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                    return $response->withStatus(500);
                }
                while ($row = pg_fetch_row($result)) {
                    array_push($messages_read, $row[0]);
                }
            }
            
            if(count($messages_read) >= 1) {
                $int_mess_read = implode(',', $messages_read);
                $result = pg_query($dbConn,
                    "UPDATE read_confirmations SET message_read=true WHERE id IN ( $int_mess_read );");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                    return $response->withStatus(500);
                }
                
                $result = pg_query($dbConn,
                    "SELECT senderid, text FROM messages WHERE id IN ( $int_mess_read );");
                if (!$result) {
                    echo "An error occured while querying database.\n";
                    return $response->withStatus(500);
                }
                
                while ($row = pg_fetch_row($result)) {
                    echo "User with Id $row[0], wrote to you: \"$row[1]\"";
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
            if(array_key_exists("id",$parsedbody)) {
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
                return $response->withStatus(500);
            }
            
            $result = pg_query($dbConn, 'SELECT max(id) from messages');
            $highestId = pg_fetch_result($result, 0, 0);
            $highestId = $highestId + 1;
            
            $result = pg_query($dbConn, 'INSERT INTO public.messages(
                                        	id, "senderid", text, read)
                                    	VALUES (' . $highestId . ', ' . $senderId . ', \'' . $text . '\', ' . $highestId . ');');
            if (!$result) {
                echo "An error occured while querying the database.\n";
                return $response->withStatus(500);
            }
            
            foreach ($receivers AS $receiverId) {
                $result = pg_query($dbConn, 'INSERT INTO public.read_confirmations(
                                            	id, message_read, receiver_id)
                                        	VALUES (' . $highestId . ', false,' . $receiverId . ');');
                if (!$result) {
                    echo "An error occured while querying the database.\n";
                    return $response->withStatus(500);
                }
            }
            return $response->withStatus(200)->write("Message sent");
            });


/**
 * PUT updateMessage
 * Summary: Update Message
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->PUT('/messages', function($request, $response, $args) {
            $parsedbody = $request->getParsedBody();
            $id = null;
            $senderId = null;
            $text = null;
            $receivers = null;
            if(array_key_exists("id",$parsedbody)) {
                $id = $parsedbody["id"];
            } else {
                $response->write('id is missing in message!');
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
            if(array_key_exists("receivers",$parsedbody)) {
                $response->write('receivers must not be in message!');
                $response = $response->withStatus(422);
                return $response;
            }
            
            $dbConn = pg_pconnect("host=ec2-54-246-85-234.eu-west-1.compute.amazonaws.com dbname=dcr3qut0dr1rit user=huxpssrspgwwum password=5282cc466a257a47f323a72891b12b3fce7dd9478a25a028364f422652bb380e");
            if (!$dbConn) {
                echo "'There was an error while connecting to the database...";
                return $response->withStatus(500);
            }
            
            $result = pg_query($dbConn, "SELECT COUNT(id) FROM messages WHERE id=$id;");
            if (!$result) {
                echo "An error occured while querying the database.\n";
            } else {
                $row = pg_fetch_row($result);
                $messageFound = $row[0];
                if($messageFound == 0) {
                    return $response->withStatus(404)->write("Message not found!");
                }
            }
            
            $result = pg_query($dbConn, "UPDATE messages SET senderid=$senderId, text='$text' WHERE id=$id;");
            if (!$result) {
                echo "An error occured while querying the database.\n";
                return $response->withStatus(500);
            } else {
                $response = $response->write('Message succesfully UPDATED');
            }
            return $response;
            });


$app->run();
