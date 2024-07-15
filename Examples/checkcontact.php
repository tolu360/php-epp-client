<?php
require('../autoloader.php');

use Metaregistrar\EPP\eppCheckRequest;
use Metaregistrar\EPP\eppConnection;
use Metaregistrar\EPP\eppContactHandle;
use Metaregistrar\EPP\eppException;
use Metaregistrar\EPP\eppInfoContactRequest;

if ($argc <= 1)
{
    echo "Usage: checkcontact.php <contactId>\n";
    echo "Please enter the contact ID to be checked\n\n";
    die();
}

$contactId = $argv[1];

echo "Checking contact with ID: $contactId\n";

try {
    // Please enter your own settings file here under before using this example
    if ($conn = eppConnection::create('../settings.ini')) {
        // Connect to the EPP server
        if ($conn->login()) {
            checkcontact($conn, $contactId);
            $conn->logout();
        }
    }
} catch (eppException $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

function checkcontact($conn, $contactId) {
    /* @var $conn Metaregistrar\EPP\eppConnection */
    try {
        $check = new eppInfoContactRequest(new eppContactHandle($contactId), false);
        $check->setNamespacesinroot(false);
        if ($response = $conn->request($check)) {
            /* @var $response Metaregistrar\EPP\eppInfoContactResponse */
            $checks = $response->getContact();
//            foreach ($checks as $contact => $check) {
//                echo "Contact $contact " . ($check ? 'does not exist' : 'exists') . "\n";
//            }
        }
    } catch (eppException $e) {
        echo $e->getMessage() . "\n";
    }
}
