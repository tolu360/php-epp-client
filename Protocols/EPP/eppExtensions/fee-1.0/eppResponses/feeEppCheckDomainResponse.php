<?php
namespace Metaregistrar\EPP;
use DOMElement;

/**
 * <?xml version="1.0" encoding="utf-8" standalone="no"?>
 * <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
 * <response>
 * <result code="1000">
 * <msg>Command completed successfully</msg>
    * </result>
    * <resData>
      * <domain:chkData
        * xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        * <domain:cd>
          * <domain:name avail="1">example.com</domain:name>
        * </domain:cd>
        * <domain:cd>
          * <domain:name avail="1">example.net</domain:name>
        * </domain:cd>
        * <domain:cd>
          * <domain:name avail="1">example.xyz</domain:name>
        * </domain:cd>
      * </domain:chkData>
    * </resData>
    * <extension>
      * <fee:chkData
          * xmlns:fee="urn:ietf:params:xml:ns:fee-1.0">
        * <fee:currency>USD</fee:currency>
        * <fee:cd avail="1">
          * <fee:objID>example.com</fee:objID>
          * <fee:class>Premium</fee:class>
          * <fee:command name="create">
            * <fee:period unit="y">2</fee:period>
            * <fee:fee
              * description="Registration Fee"
              * refundable="1"
              * grace-period="P5D">10.00</fee:fee>
          * </fee:command>
          * <fee:command name="renew">
            * <fee:period unit="y">1</fee:period>
            * <fee:fee
              * description="Renewal Fee"
              * refundable="1"
              * grace-period="P5D">5.00</fee:fee>
          * </fee:command>
          * <fee:command name="transfer">
            * <fee:period unit="y">1</fee:period>
            * <fee:fee
              * description="Transfer Fee"
              * refundable="1"
              * grace-period="P5D">5.00</fee:fee>
          * </fee:command>
          * <fee:command name="restore">
            * <fee:fee
              * description="Redemption Fee">5.00</fee:fee>
          * </fee:command>
        * </fee:cd>
        * <fee:cd avail="1">
          * <fee:objID>example.net</fee:objID>
          * <fee:command name="create">
            * <fee:period unit="y">2</fee:period>
            * <fee:fee
              * description="Registration Fee"
              * refundable="1"
              * grace-period="P5D">10.00</fee:fee>
          * </fee:command>
          * <fee:command name="renew">
            * <fee:period unit="y">1</fee:period>
            * <fee:fee
              * description="Renewal Fee"
              * refundable="1"
              * grace-period="P5D">5.00</fee:fee>
          * </fee:command>
          * <fee:command name="transfer">
            * <fee:period unit="y">1</fee:period>
            * <fee:fee
              * description="Transfer Fee"
              * refundable="1"
              * grace-period="P5D">5.00</fee:fee>
          * </fee:command>
          * <fee:command name="restore">
            * <fee:fee
              * description="Redemption Fee">5.00</fee:fee>
          * </fee:command>
        * </fee:cd>
        * <fee:cd avail="0">
          * <fee:objID>example.xyz</fee:objID>
          * <fee:command name="create">
            * <fee:period unit="y">2</fee:period>
            * <fee:reason>Only 1 year registration periods are
              * valid.</fee:reason>
          * </fee:command>
        * </fee:cd>
      * </fee:chkData>
    * </extension>
    * <trID>
      * <clTRID>ABC-12345</clTRID>
      * <svTRID>54322-XYZ</svTRID>
    * </trID>
  * </response>
* </epp>
 */


/**
 * Class feeEppCheckdomainResponse
 * @package Metaregistrar\EPP
 */
class feeEppCheckdomainResponse extends eppCheckDomainResponse {
    function __construct() {
        parent::__construct();
    }

    public function getFees() {
        if ($this->getResultCode() == self::RESULT_SUCCESS) {
            $xpath = $this->xPath();
            $details = $xpath->query('/epp:epp/epp:response/epp:extension/fee:chkData/fee:cd');

            $result = [];

            foreach ($details as $fees) {
                $feeDetails = [
                    'domain' => '',
                    'class' => '',
                    'create' => ['period' => '', 'unit' => '', 'description' => '', 'fee' => ''],
                    'renew' => ['period' => '', 'unit' => '', 'description' => '', 'fee' => ''],
                    'transfer' => ['period' => '', 'unit' => '', 'description' => '', 'fee' => ''],
                    'restore' => ['period' => '', 'unit' => '', 'description' => '', 'fee' => ''],
                    'update' => ['period' => '', 'unit' => '', 'description' => '', 'fee' => ''],
                    'delete' => ['period' => '', 'unit' => '', 'description' => '', 'fee' => ''],
                    'available' => 1
                ];

                $feeDetails['available'] = (int) $fees->getAttribute('avail');

                foreach ($fees->childNodes as $feeNode) {
                    if ($feeNode instanceof \DOMElement) {
                        if ($feeNode->localName == 'objID') {
                            $feeDetails['domain'] = $feeNode->nodeValue;
                        }

                        if ($feeNode->localName == 'class') {
                            $feeDetails['class'] = $feeNode->nodeValue;
                        }

                        if ($feeNode->localName == 'command') {
                            foreach ($feeNode->childNodes as $commandNode) {
                                if ($commandNode instanceof \DOMElement) {
                                    if ($commandNode->localName == 'period') {
                                        $feeDetails[$feeNode->getAttribute('name')]['period'] = (int) $commandNode->nodeValue;
                                        $feeDetails[$feeNode->getAttribute('name')]['unit'] = $commandNode->getAttribute('unit');
                                    }

                                    if ($commandNode->localName == 'fee' && (empty($feeDetails[$feeNode->getAttribute('name')]['fee']) || $feeDetails[$feeNode->getAttribute('name')]['fee'] < (float) $commandNode->nodeValue)) {
                                        $feeDetails[$feeNode->getAttribute('name')]['fee'] = (float) $commandNode->nodeValue;
                                        $feeDetails[$feeNode->getAttribute('name')]['description'] = $commandNode->getAttribute('description');
                                    }

                                    if ($commandNode->localName == 'reason') {
                                        $feeDetails[$feeNode->getAttribute('name')]['reason'] = $commandNode->nodeValue;
                                    }
                                }
                            }
                        }
                    }
                }

                $result[] = array_filter(array_map(fn($value) => is_array($value) ? array_filter($value, fn($value) => is_numeric($value) || !empty($value)) : $value, $feeDetails));

            }

            return $result;
        }

        return null;
    }

    public function getFeeCurrency()
    {
        if ($this->getResultCode() == self::RESULT_SUCCESS) {
            $xpath = $this->xPath();
            $nodes = $xpath->query('/epp:epp/epp:response/epp:extension/fee:chkData/fee:currency');

            $currency = null;

            foreach ($nodes as $node) {
                if ($node instanceof DOMElement) {
                    if ($node->localName == 'currency') {
                        $currency = $node->nodeValue;
                    }
                }
            }

            return $currency;
        }

        return null;
    }
}