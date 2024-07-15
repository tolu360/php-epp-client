<?php
require('../autoloader.php');

use Metaregistrar\EPP\eppConnection;
use Metaregistrar\EPP\eppException;
use Metaregistrar\EPP\eppCheckDomainRequest;
use Metaregistrar\EPP\eppCheckDomainResponse;


/*
 * This script checks for the availability of domain names
 * You can specify multiple domain names to be checked
 */

if ($argc <= 1) {
    echo "Usage: checkdomain.php <domainnames>\n";
    echo "Please enter one or more domain names to check\n\n";
    die();
}

for ($i = 1; $i < $argc; $i++) {
    $domains[] = $argv[$i];
}

echo "Checking " . count($domains) . " domain names\n";
try {
    // Set login details for the service in the form of
    // interface=metaregEppConnection
    // hostname=ssl://epp.test2.metaregistrar.com
    // port=7443
    // userid=xxxxxxxx
    // password=xxxxxxxxx
    // Please enter the location of the file with these settings in the string location here under
//    if ($conn = eppConnection::create('../settings.ini')) {
//        $conn->useExtension('fee-1.0');
//        // Connect and login to the EPP server
//        if ($conn->login()) {
//            // Check domain names
//            checkdomains($conn, $domains);
//            $conn->logout();
//        }
//    }
    if ($conn = new \Metaregistrar\EPP\sidnEppConnection(true)) {
        $conn->setHostname('ssl://registry.ola.cv'); // Hostname may vary depending on the registry selected
        $conn->setPort(700); // Port may vary depending on the registry selected
        $conn->setUsername('apitestregistrar');
        $conn->setPassword('Testing321?tolu');
//        $conn->setAllowSelfSigned(false);
        $conn->setVerifyPeer(false);
        $conn->setVerifyPeerName(false);
        $conn->useExtension('fee-1.0');
        // Connect and login to the EPP server
        if ($conn->login()) {
            // Check domain names
            checkdomains($conn, $domains);
            $conn->logout();
        }
    }
} catch (eppException $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

/**
 * @param $conn eppConnection
 * @param $domains array of domain names
 */
function checkdomains($conn, $domains) {
    // Create request to be sent to EPP service
//    $check = new eppCheckDomainRequest($domains);
    $check = new \Metaregistrar\EPP\feeEppCheckDomainRequest($domains, false);
    $check->addFee('create', 'USD', 1);
//        $check->addFee('renew');
    // Write request to EPP service, read and check the results
    if ($response = $conn->request($check)) {
        /* @var $response \Metaregistrar\EPP\feeEppCheckdomainResponse */
        // Walk through the results
        $checks = $response->getCheckedDomains();
        foreach ($checks as $check) {
            echo $check['domainname'] . " is " . ($check['available'] ? 'available' : 'taken');
            if ($check['available']) {
                echo ' (' . $check['reason'] .')';
            }
            echo "\n";
        }

//        echo "Fees:\n";
//        echo "\n";
//        echo $response->getFees();
    }
}