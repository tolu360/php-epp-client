<?php

namespace Metaregistrar\EPP;

/**
 * Class feeEppCreateDomainRequest
 * @package Metaregistrar\EPP
 */
class feeEppCreateDomainRequest extends eppCreateDomainRequest
{
    function __construct($createinfo, $forcehostattr = false, $namespacesinroot=true, $usecdata = true) {
        parent::__construct($createinfo, $forcehostattr, $namespacesinroot, $usecdata);
    }

    public function addFee($fee, $currency = 'USD', $period = 1)
    {
        $create = $this->createElement('fee:create');
        $create->setAttribute('xmlns:fee','urn:ietf:params:xml:ns:epp:fee-1.0');
        $create->appendChild($this->createElement('fee:currency', $currency));
        $create->appendChild($this->createElement('fee:fee', $fee));

        $this->getExtension()->appendChild($create);
        $this->addSessionId();
    }
}
