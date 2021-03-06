<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Lukas
 * Date: 29.05.13
 * Time: 13:59
 * To change this template use File | Settings | File Templates.
 */
include "dbconnection.php";

function getContactsForUserID($user_id)
{

    $values = array();
    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    $result = mysql_query('SELECT contact_id,nickname FROM contacts WHERE ' . 'origin_user_id="' . $user_id . '"')
    or die("SQL Error:" . mysql_error() . " with param" . var_dump($user_id) . " <br>");

    if (mysql_num_rows($result) > 0) {
        for ($i = 0; $i < mysql_num_rows($result); ++$i) {
            $contact_id = mysql_result($result, $i, 0);
            $contact_nickname = mysql_result($result, $i, 1);
            $data = array($contact_id, $contact_nickname);
            array_push($values, $data);
        }
    }
    mysql_close($connection);
    return $values;
}

function getOpponentContactsForUserID($user_id)
{

    $values = array();
    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    $result = mysql_query('SELECT contact_id,nickname FROM contacts WHERE ' . 'destination_user_id=' . $user_id . '')
    or die("SQL Error:" . mysql_error() . " with param" . var_dump($user_id) . " <br>");

    if (mysql_num_rows($result) > 0) {
        for ($i = 0; $i < mysql_num_rows($result); ++$i) {
            $contact_id = mysql_result($result, $i, 0);
            $contact_nickname = mysql_result($result, $i, 1);
            $data = array($contact_id, $contact_nickname);
            array_push($values, $data);
        }
    }
    mysql_close($connection);
    return $values;
}

function getOppositeContactID($parties)
{

    $contact_id = 0;

    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    $result = mysql_query('SELECT DISTINCT contact_id FROM contacts WHERE ' . 'origin_user_id="' . $parties[1] . '" AND destination_user_id="' . $parties[0] . '"')
    or die("SQL Error:" . mysql_error() . " with param" . var_dump($parties) . " <br>");

    if (mysql_num_rows($result) <> 0) {
        $contact_id = mysql_result($result, 0, 0);
    }

    mysql_close($connection);
    return $contact_id;
}

function create_contacts($pID, $pContacts)
{
    $newContactsCreated = false;

    if (isset($pID) && isset($pContacts)) {
        $origin_id = $pID;
        $contacts = $pContacts;
    } else {
        return $newContactsCreated;
    }

    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    $destinationInformation = array();

    foreach ($contacts as $item) {
        if (!isset($item['contactID'])) {
            $destinationIDResult = mysql_query('SELECT id FROM users WHERE mobileNumber ="' . $item['number'] . '";')
            or die("There was an error running the query to look for existing users!<br>");
            if (mysql_num_rows($destinationIDResult) <> 0) {
                $destinationID = mysql_result($destinationIDResult, 0, 0);
                // SourceID => SourceName
                $destinationInformation[$destinationID] = $item['name'];
            }
        }
    }

    if (isset($destinationInformation)) {
        foreach ($destinationInformation as $key => $value) {
            $newContactsCreated = mysql_query('INSERT INTO contacts (origin_user_id,destination_user_id,nickname) VALUES ("' . $origin_id . '","' . $key . '","' . $value . '")')
            or die("There was an error running the query to create contacts!<br>");
        }
    }

    mysql_close($connection);
    return $newContactsCreated;
}

function getContactIDsForNumbers($pMatchedContacts, $pUser_id)
{

    $ContactInfo = array();
    if (isset($pMatchedContacts) && isset($pUser_id)) {
        $matchedContacts = $pMatchedContacts;
        $user_id = $pUser_id;
    } else {
        return "Not all required parameters are given";
    }

    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    foreach ($matchedContacts as $contact) {
        $ContactID = mysql_query('SELECT contact_id FROM contacts WHERE origin_user_id ="' . $user_id . '" AND destination_user_id="' . $contact['id'] . '";')
        or die("There was an error running the query get contact ids!<br>" . var_dump($matchedContacts));
        if (mysql_num_rows($ContactID) <> 0) {
            $cID = mysql_result($ContactID, 0, 0);
            $singleContactInfo = array($contact['number'], $cID);
            array_push($ContactInfo, $singleContactInfo);
        }
    }

    mysql_close($connection);
    return $ContactInfo;
}

function checkDatabaseForContact($pSourceID, $pDestinationID)
{
    $existingContactID = 0;
    if (isset($pSourceID) && isset($pDestinationID)) {
        $source_id = $pSourceID;
        $destination_id = $pDestinationID;
    } else {
        return "Not all required parameters are given";
    }

    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    $result = mysql_query('SELECT contact_id FROM contacts WHERE ' . 'origin_user_id="' . $source_id . '" AND destination_user_id="' . $destination_id . '"')
    or die("SQL Error:" . mysql_error() . " with param" . var_dump($source_id) . var_dump($destination_id) . " <br>");

    if (mysql_num_rows($result) <> 0) {
        $existingContactID = mysql_result($result, 0, 0);
    }

    mysql_close($connection);
    //return Contact ID or 0 if no contact exists, yet.
    return $existingContactID;
}

/*
 * returns an array() with [0] => source_id, [1] => destination_id
 * and null if something went wrong
 */
function getPartiesID($contactID)
{
    $parties = array();
    if (isset($contactID)) {
        $contact_id = $contactID;
    } else {
        return null;
    }

    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    $result = mysql_query('SELECT DISTINCT origin_user_id, destination_user_id FROM contacts WHERE ' . 'contact_id="' . $contact_id . '"')
    or die("SQL Error:" . mysql_error() . " with param" . var_dump($contact_id) . " <br>");

    if (mysql_num_rows($result) <> 0) {
        $parties[0] = mysql_result($result, 0, 0);
        $parties[1] = mysql_result($result, 0, 1);
    }

    mysql_close($connection);
    return $parties;
}

function insertMessageIntoDB($messageData)
{
    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    $messageSuccessful = mysql_query('INSERT INTO messages (contact_id, content, date_time) VALUES ("' . $messageData[0] . '","' . $messageData[1] . '","' . $messageData[2] . '")')
    or die("There was an error running the query to create a message!<br> " . mysql_error() . var_dump($messageData));

    mysql_close($connection);
    return $messageSuccessful;
}

/*
 * If optional parameter $all isset then the latest 10 messages of the given ids are selected
 * if not only tne new ones are selected.
 */
function getMessagesFromDB($contact_id, $opposite_contact_id, $all)
{
    $messages = array();
    if (isset($all)) {
        $query = 'SELECT * FROM messages WHERE contact_id=' . $contact_id . ' OR contact_id=' . $opposite_contact_id . ' ORDER BY date_time DESC LIMIT 15;';

    } else { // nur die vom opponent abholen
        $query = 'SELECT * FROM messages WHERE contact_id=' . $opposite_contact_id . ' AND read_status = 0 ORDER BY date_time DESC ;';
    }
    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    $resultMessages = mysql_query($query)
    or die("There was an error running the query to receive messages!<br> " . mysql_error() . var_dump($contact_id) . var_dump($opposite_contact_id));

    if (mysql_num_rows($resultMessages) <> 0) {
        for ($i = 0; $i < mysql_num_rows($resultMessages); ++$i) {
            $row = mysql_fetch_assoc($resultMessages);
            array_push($messages, $row);
            if (!mysql_query('UPDATE messages SET read_status = 1 WHERE message_id="' . $row['message_id'] . '";')) {
                die("Error during updating message" . $row['message_id']);
            }
        }
    }

    mysql_close($connection);
    return $messages;
}

function lookForNewMessages($user_id)
{

    // Get all contactIDs from the user that
    $contact_IDs = getOpponentContactsForUserID($user_id);
    $pending_contact_IDs = array();

    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    foreach ($contact_IDs as $contact) {
        $result = mysql_query('SELECT DISTINCT contact_id FROM messages WHERE contact_id=' . $contact[0] . ' AND read_status=0;')
        or die("There was an error running the query to look for new messages!<br> " . mysql_error() . var_dump($contact[0]) . var_dump($user_id));
        if (mysql_num_rows($result) <> 0) {
            $pending_contact_IDs[] = mysql_result($result, 0, 0);
        }
    }

    mysql_close($connection);
    return $pending_contact_IDs;
}

function getOwnCIDFromOppositeCID($pending_contact_IDs)
{

    $ownContactIDs = array();
    //Connect to DB
    $connection = initializeConnectionToDB();
    $db = selectDB();
    $selected = mysql_select_db($db, $connection)
    or die("Could not select Database");

    //encode contactID
    $contactInfo = array();
    foreach ($pending_contact_IDs as $contact_id) {
        $result = mysql_query('SELECT origin_user_id, destination_user_id FROM contacts WHERE ' . 'contact_id=' . $contact_id . '')
        or die("SQL Error:" . mysql_error() . " with param" . var_dump($contact_id) . " <br>");
        if (mysql_num_rows($result) > 0) {
            for ($i = 0; $i < mysql_num_rows($result); ++$i) {
                $opponent = mysql_result($result, $i, 0);
                $own = mysql_result($result, $i, 1);
                $data = array($opponent, $own);
                array_push($contactInfo, $data);
            }
        }
    }

    // find contact id with data
    foreach ($contactInfo as $info) {
        $result = mysql_query('SELECT contact_id FROM contacts WHERE origin_user_id=' . $info[1] . ' AND destination_user_id=' . $info[0] . ';')
        or die("SQL Error:" . mysql_error() . " with param" . var_dump($info) . " <br>");

        if (mysql_num_rows($result) > 0) {
            for ($i = 0; $i < mysql_num_rows($result); ++$i) {
                $ownContactIDs[] =  mysql_result($result, $i, 0);
            }
        }
    }
    mysql_close($connection);
    return $ownContactIDs;
}