<?php
#
# Load the fee-1.0 specific additions
# SOURCE: https://tools.ietf.org/html/draft-ietf-regext-epp-fees-15
#
include_once(dirname(__FILE__) . '/eppRequests/feeEppCheckDomainRequest.php');
include_once(dirname(__FILE__) . '/eppResponses/feeEppCheckDomainResponse.php');
include_once(dirname(__FILE__) . '/eppRequests/feeEppCreateDomainRequest.php');
include_once(dirname(__FILE__) . '/eppResponses/feeEppCreateDomainResponse.php');

$this->addCommandResponse('Metaregistrar\EPP\feeEppCheckDomainRequest', 'Metaregistrar\EPP\feeEppCheckDomainResponse');
$this->addCommandResponse('Metaregistrar\EPP\feeEppCreateDomainRequest', 'Metaregistrar\EPP\feeEppCreateDomainResponse');

