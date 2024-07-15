<?php
require('../autoloader.php');

use Metaregistrar\EPP\eppConnection;
use Metaregistrar\EPP\eppException;
use Metaregistrar\EPP\eppContactHandle;
use Metaregistrar\EPP\eppCheckRequest;
use Metaregistrar\EPP\eppContactPostalInfo;
use Metaregistrar\EPP\eppContact;
use Metaregistrar\EPP\eppCreateContactRequest;
use Metaregistrar\EPP\eppHost;
use Metaregistrar\EPP\eppCreateHostRequest;
use Metaregistrar\EPP\eppDomain;
use Metaregistrar\EPP\eppCreateDomainRequest;

/*
 * This sample script registers a domain name within your account
 * 
 * The nameservers of metaregistrar are used as nameservers
 * In this scrips, the same contact id is used for registrant, admin-contact, tech-contact and billing contact
 * Recommended usage is that you use a tech-contact and billing contact of your own, and set registrant and admin-contact to the domain name owner or reseller.
 */


if ($argc <= 1)
{
    echo "Usage: createdomain.php <domainname>\n";
	echo "Please enter the domain name to be created\n\n";
	die();
}

$domainname = $argv[1];

echo "Registering $domainname\n";
try {
// Please enter your own settings file here under before using this example
    if ($conn = eppConnection::create('../settings.ini')) {
// Connect to the EPP server
        if ($conn->login()) {
            if (!checkhosts($conn, array('rob.ns.cloudflare.com'))) {
                createhost($conn, 'rob.ns.cloudflare.com');
            }
            if (!checkhosts($conn, array('dawn.ns.cloudflare.com'))) {
                createhost($conn, 'dawn.ns.cloudflare.com');
            }
            $nameservers = array('rob.ns.cloudflare.com','dawn.ns.cloudflare.com');
            $contactid = createcontact($conn,'test@tolu.dev','+2348030000000','Tolu',null,'25 Milverton Rd','223101','Lagos','NG');
            echo "contactID: $contactid\n";
            if ($contactid) {
                createdomain($conn, $domainname, $contactid, $contactid, $contactid, $contactid, $nameservers);
            }
            $conn->logout();
        }
    }
} catch (eppException $e) {
    echo $e->getMessage();
}

function checkcontact($conn, $contactid) {
    /* @var $conn Metaregistrar\EPP\eppConnection */
    try {
        $check = new eppCheckRequest(new eppContactHandle($contactid));
        $check->setNamespacesinroot(false);
        if ($response = $conn->request($check)) {
            /* @var $response Metaregistrar\EPP\eppCheckResponse */
            $checks = $response->getCheckedContacts();
            foreach ($checks as $contact => $check) {
                echo "Contact $contact " . ($check ? 'does not exist' : 'exists') . "\n";
            }
        }
    } catch (eppException $e) {
        echo $e->getMessage() . "\n";
    }
}


function createcontact($conn, $email, $telephone, $name, $organization, $address, $postcode, $city, $country) {
    /* @var $conn Metaregistrar\EPP\eppConnection */
    try {
        $contactinfo = new eppContact(new eppContactPostalInfo($name, $city, $country, $organization, $address, null, $postcode, Metaregistrar\EPP\eppContact::TYPE_LOC), $email, $telephone);
        $contactinfo->setPassword('fubar');
        $contact = new eppCreateContactRequest($contactinfo, false);
        $contact->setNamespacesinroot(false);
        if ($response = $conn->request($contact)) {
            /* @var $response Metaregistrar\EPP\eppCreateContactResponse */
            echo "Contact created on " . $response->getContactCreateDate() . " with id " . $response->getContactId() . "\n";
            return $response->getContactId();
        }
    } catch (eppException $e) {
        echo $e->getMessage() . "\n";
    }
    return null;
}


/**
 * @param $conn eppConnection
 * @param $hosts
 * @return bool|null
 */
function checkhosts($conn, $hosts) {
    try {
        $checkhost = array();
        foreach ($hosts as $host) {
            $checkhost[] = new eppHost($host);
        }
        $check = new eppCheckRequest($checkhost);
        $check->setNamespacesinroot(false);
        if ($response = $conn->request($check)) {
            /* @var $response Metaregistrar\EPP\eppCheckResponse */
            $checks = $response->getCheckedHosts();
            $allchecksok = true;
            foreach ($checks as $hostname => $check) {
                echo "$hostname " . ($check ? 'does not exist' : 'exists') . "\n";
                if ($check) {
                    $allchecksok = false;
                }
            }
            return $allchecksok;
        }
    } catch (eppException $e) {
        echo $e->getMessage() . "\n";
    }
    return null;
}

/**
 * @param eppConnection $conn
 * @param string $hostname
 * @param string $ipaddress
 */
function createhost($conn, $hostname, $ipaddress=null) {

    try {
        $create = new eppCreateHostRequest(new eppHost($hostname,$ipaddress), false);
        $create->setNamespacesinroot(false);
        if ($response = $conn->request($create)) {
            /* @var $response Metaregistrar\EPP\eppCreateHostResponse */
            echo "Host created on " . $response->getHostCreateDate() . " with name " . $response->getHostName() . "\n";
        }
    } catch (eppException $e) {
        echo $e->getMessage() . "\n";
    }
}


/**
 * @param eppConnection $conn
 * @param string $domainname
 * @param string $registrant
 * @param string $admincontact
 * @param string $techcontact
 * @param string $billingcontact
 * @param array $nameservers
 */
function createdomain($conn, $domainname, $registrant, $admincontact, $techcontact, $billingcontact, $nameservers) {
    /* @var $conn Metaregistrar\EPP\eppConnection */
    try {
        $domain = new eppDomain($domainname, $registrant);
        $domain->setRegistrant(new eppContactHandle($registrant));
        $domain->addContact(new eppContactHandle($admincontact, eppContactHandle::CONTACT_TYPE_ADMIN));
        $domain->addContact(new eppContactHandle($techcontact, eppContactHandle::CONTACT_TYPE_TECH));
        $domain->addContact(new eppContactHandle($billingcontact, eppContactHandle::CONTACT_TYPE_BILLING));
        $domain->setAuthorisationCode('rand0m');
        if (is_array($nameservers)) {
            foreach ($nameservers as $nameserver) {
                $domain->addHost(new eppHost($nameserver));
            }
        }
        $create = new eppCreateDomainRequest($domain, false, false);
        $create->setNamespacesinroot(false);
        if ($response = $conn->request($create)) {
            /* @var $response Metaregistrar\EPP\eppCreateDomainResponse */
            echo "Domain " . $response->getDomainName() . " created on " . $response->getDomainCreateDate() . ", expiration date is " . $response->getDomainExpirationDate() . "\n";
        }
    } catch (eppException $e) {
        echo $e->getMessage() . "\n";
    }
}
