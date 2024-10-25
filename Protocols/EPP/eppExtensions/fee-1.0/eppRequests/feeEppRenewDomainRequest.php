<?php

namespace Metaregistrar\EPP;

/**
 * Class feeEppRenewDomainRequest
 * @package Metaregistrar\EPP
 */
class feeEppRenewDomainRequest extends eppRenewRequest
{
    function __construct($domain, $expdate = null, $namespacesinroot = true) {
        parent::__construct($domain, $expdate, $namespacesinroot);
    }

    public function addFee($fee, $currency = 'USD')
    {
        $create = $this->createElement('fee:renew');
        $create->setAttribute('xmlns:fee','urn:ietf:params:xml:ns:epp:fee-1.0');
        $create->appendChild($this->createElement('fee:currency', $currency));
        $create->appendChild($this->createElement('fee:fee', $fee));

        $this->getExtension()->appendChild($create);
        $this->addSessionId();
    }
}
