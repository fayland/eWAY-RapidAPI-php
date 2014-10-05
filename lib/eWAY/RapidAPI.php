<?php
namespace eWAY;

/* check examples for usage */
class RapidAPI {
    private $_url;
    private $sandbox;
    private $username;
    private $password;

    function __construct($username, $password, $params=array()) {
        if (strlen($username) === 0 || strlen($password) === 0) {
            die("Username and Password are required");
        }

        $this->username = $username;
        $this->password = $password;

        if (count($params) && isset($params['sandbox']) && $params['sandbox']) {
            $this->_url = 'https://api.sandbox.ewaypayments.com/';
            $this->sandbox = true;
        } else {
            $this->_url = 'https://api.ewaypayments.com/';
            $this->sandbox = false;
        }
    }

    public function CreateAccessCode($request) {
        $request = $this->__fixObjtoJSON($request);
        $response = $this->PostToRapidAPI("AccessCodes", $request);
        return json_decode($response);
    }

    public function GetAccessCodeResult($request) {
        $request = json_encode($request);
        $response = $this->PostToRapidAPI("AccessCode/" . $_GET['AccessCode'], $request, false);
        return json_decode($response);
    }

    public function CreateAccessCodesShared($request) {
        $request = $this->__fixObjtoJSON($request);
        $response = $this->PostToRapidAPI("AccessCodesShared", $request);
        return json_decode($response);
    }

    public function DirectPayment($request) {
        $request = $this->__fixObjtoJSON($request);
        $response = $this->PostToRapidAPI("Transaction", $request);
        return json_decode($response);
    }

    public function Refund($request) {
        $TransactionID = $request->Refund->TransactionID;
        $request = $this->__fixObjtoJSON($request);
        $response = $this->PostToRapidAPI("Transaction/$TransactionID/Refund", $request);
        return json_decode($response);
    }

    /* alias */
    public function getMessage($code) {
        return ResponseCode::getMessage($code);
    }

    private function __fixObjtoJSON($request) {
        if ( isset($request->Options) && count($request->Options->Option) ) {
            $i = 0;
            $tempClass = new \stdClass();
            foreach ($request->Options->Option as $Option) {
                $tempClass->Options[$i] = $Option;
                $i++;
            }
            $request->Options = $tempClass->Options;
        }
        if ( isset($request->Items) && count($request->Items->LineItem) ) {
            $i = 0;
            $tempClass = new \stdClass();
            foreach ($request->Items->LineItem as $LineItem) {
                // must be strings
                if (isset($LineItem->Quantity)) $LineItem->Quantity = strval($LineItem->Quantity);
                if (isset($LineItem->UnitCost)) $LineItem->UnitCost = strval($LineItem->UnitCost);
                if (isset($LineItem->Tax)) $LineItem->Tax = strval($LineItem->Tax);
                if (isset($LineItem->Total)) $LineItem->Total = strval($LineItem->Total);
                $tempClass->Items[$i] = $LineItem;
                $i++;
            }
            $request->Items = $tempClass->Items;
        }

        // fix blank issue
        if (isset($request->RedirectUrl)) $request->RedirectUrl = str_replace(' ', '%20', $request->RedirectUrl);
        if (isset($request->CancelUrl)) $request->CancelUrl = str_replace(' ', '%20', $request->CancelUrl);

        return json_encode($request);
    }

    /*
     * Description A Function for doing a Curl GET/POST
     */
    private function PostToRapidAPI($url, $request, $IsPost = true) {
        $url = $this->_url . $url;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        if ($IsPost)
            curl_setopt($ch, CURLOPT_POST, true);
        else
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        $response = curl_exec($ch);

        if (curl_errno($ch) != CURLE_OK) {
            echo "<h2>POST Error: " . curl_error($ch) . " URL: $url</h2><pre>";
            die();
        } else {
            $info = curl_getinfo($ch);
            if ($info['http_code'] == 401 || $info['http_code'] == 404) {
                $__is_in_sandbox = $this->sandbox ? ' (Sandbox)' : ' (Live)';
                echo "<h2>Please check the API Key and Password $__is_in_sandbox</h2><pre>";
                die();
            }

            curl_close($ch);
            return $response;
        }
    }
}

class ResponseCode {
    private static $_codes = array(
        'A2000' => 'Transaction Approved',
        'A2008' => 'Honour With Identification',
        'A2010' => 'Approved For Partial Amount',
        'A2011' => 'Approved, VIP',
        'A2016' => 'Approved, Update Track 3',
        'D4401' => 'Refer to Issuer',
        'D4402' => 'Refer to Issuer, special',
        'D4403' => 'No Merchant',
        'D4404' => 'Pick Up Card',
        'D4405' => 'Do Not Honour',
        'D4406' => 'Error',
        'D4407' => 'Pick Up Card, Special',
        'D4409' => 'Request In Progress',
        'D4412' => 'Invalid Transaction',
        'D4413' => 'Invalid Amount',
        'D4414' => 'Invalid Card Number',
        'D4415' => 'No Issuer',
        'D4419' => 'Re-enter Last Transaction',
        'D4421' => 'No Action Taken',
        'D4422' => 'Suspected Malfunction',
        'D4423' => 'Unacceptable Transaction Fee',
        'D4425' => 'Unable to Locate Record On File',
        'D4430' => 'Format Error',
        'D4431' => 'Bank Not Supported By Switch',
        'D4433' => 'Expired Card, Capture',
        'D4434' => 'Suspected Fraud, Retain Card',
        'D4435' => 'Card Acceptor, Contact Acquirer, Retain Card',
        'D4436' => 'Restricted Card, Retain Card',
        'D4437' => 'Contact Acquirer Security Department, Retain Card',
        'D4438' => 'PIN Tries Exceeded, Capture',
        'D4439' => 'No Credit Account',
        'D4440' => 'Function Not Supported',
        'D4441' => 'Lost Card',
        'D4442' => 'No Universal Account',
        'D4443' => 'Stolen Card',
        'D4444' => 'No Investment Account',
        'D4451' => 'Insufficient Funds',
        'D4452' => 'No Cheque Account',
        'D4453' => 'No Savings Account',
        'D4454' => 'Expired Card',
        'D4455' => 'Incorrect PIN',
        'D4456' => 'No Card Record',
        'D4457' => 'Function Not Permitted to Cardholder',
        'D4458' => 'Function Not Permitted to Terminal',
        'D4459' => 'Suspected Fraud',
        'D4460' => 'Acceptor Contact Acquirer',
        'D4461' => 'Exceeds Withdrawal Limit',
        'D4462' => 'Restricted Card',
        'D4463' => 'Security Violation',
        'D4464' => 'Original Amount Incorrect',
        'D4466' => 'Acceptor Contact Acquirer, Security',
        'D4467' => 'Capture Card',
        'D4475' => 'PIN Tries Exceeded',
        'D4482' => 'CVV Validation Error',
        'D4490' => 'Cut off In Progress',
        'D4491' => 'Card Issuer Unavailable',
        'D4492' => 'Unable To Route Transaction',
        'D4493' => 'Cannot Complete, Violation Of The Law',
        'D4494' => 'Duplicate Transaction',
        'D4496' => 'System Error',
        'D4497' => 'MasterPass Error',
        'D4498' => 'PayPal Create Transaction Error',
        'F7000' => 'Undefined Fraud Error',
        'F7002' => 'Country Match Fraud',
        'F7003' => 'High Risk Country Fraud',
        'F7004' => 'Anonymous Proxy Fraud',
        'F7005' => 'Transparent Proxy Fraud',
        'F7006' => 'Free Email Fraud',
        'F7007' => 'International Transaction Fraud',
        'F7008' => 'Risk Score Fraud',
        'F9010' => 'High Risk Billing Country',
        'F9011' => 'High Risk Credit Card Country',
        'F9012' => 'High Risk Customer IP Address',
        'F9013' => 'High Risk Email Address',
        'F9014' => 'High Risk Shipping Country',
        'F9015' => 'Multiple card numbers for single email address',
        'F9016' => 'Multiple card numbers for single location',
        'F9017' => 'Multiple email addresses for single card number',
        'F9018' => 'Multiple email addresses for single location',
        'F9019' => 'Multiple locations for single card number',
        'F9020' => 'Multiple locations for single email address',
        'F9021' => 'Suspicious Customer First Name',
        'F9022' => 'Suspicious Customer Last Name',
        'F9023' => 'Transaction Declined',
        'F9024' => 'Multiple transactions for same address with known credit card',
        'F9025' => 'Multiple transactions for same address with new credit card',
        'F9026' => 'Multiple transactions for same email with new credit card',
        'F9027' => 'Multiple transactions for same email with known credit card',
        'F9028' => 'Multiple transactions for new credit card',
        'F9029' => 'Multiple transactions for known credit card',
        'F9030' => 'Multiple transactions for same email address',
        'F9031' => 'Multiple transactions for same credit card',
        'F9032' => 'Invalid Customer Last Name',
        'F9033' => 'Invalid Billing Street',
        'F9034' => 'Invalid Shipping Street',
        'F9037' => 'Suspicious Customer Email Address',
        'S5000' => 'System Error',
        'S5085' => 'Started 3dSecure',
        'S5086' => 'Routed 3dSecure',
        'S5087' => 'Completed 3dSecure',
        'S5088' => 'PayPal Transaction Created',
        'S5099' => 'Incomplete (Access Code in progress/incomplete)',
        'S5010' => 'Unknown error returned by gateway',
        'V6000' => 'Validation error',
        'V6001' => 'Invalid CustomerIP',
        'V6002' => 'Invalid DeviceID',
        'V6003' => 'Invalid Request PartnerID',
        'V6004' => 'Invalid Request Method',
        'V6010' => 'Invalid TransactionType, account not certified for eCome only MOTO or Recurring available',
        'V6011' => 'Invalid Payment TotalAmount',
        'V6012' => 'Invalid Payment InvoiceDescription',
        'V6013' => 'Invalid Payment InvoiceNumber',
        'V6014' => 'Invalid Payment InvoiceReference',
        'V6015' => 'Invalid Payment CurrencyCode',
        'V6016' => 'Payment Required',
        'V6017' => 'Payment CurrencyCode Required',
        'V6018' => 'Unknown Payment CurrencyCode',
        'V6021' => 'EWAY_CARDHOLDERNAME Required',
        'V6022' => 'EWAY_CARDNUMBER Required',
        'V6023' => 'EWAY_CARDCVN Required',
        'V6033' => 'Invalid Expiry Date',
        'V6034' => 'Invalid Issue Number',
        'V6035' => 'Invalid Valid From Date',
        'V6040' => 'Invalid TokenCustomerID',
        'V6041' => 'Customer Required',
        'V6042' => 'Customer FirstName Required',
        'V6043' => 'Customer LastName Required',
        'V6044' => 'Customer CountryCode Required',
        'V6045' => 'Customer Title Required',
        'V6046' => 'TokenCustomerID Required',
        'V6047' => 'RedirectURL Required',
        'V6048' => 'CheckoutURL Required when CheckoutPayment specified',
        'V6051' => 'Invalid Customer FirstName',
        'V6052' => 'Invalid Customer LastName',
        'V6053' => 'Invalid Customer CountryCode',
        'V6058' => 'Invalid Customer Title',
        'V6059' => 'Invalid RedirectURL',
        'V6060' => 'Invalid TokenCustomerID',
        'V6061' => 'Invalid Customer Reference',
        'V6062' => 'Invalid Customer CompanyName',
        'V6063' => 'Invalid Customer JobDescription',
        'V6064' => 'Invalid Customer Street1',
        'V6065' => 'Invalid Customer Street2',
        'V6066' => 'Invalid Customer City',
        'V6067' => 'Invalid Customer State',
        'V6068' => 'Invalid Customer PostalCode',
        'V6069' => 'Invalid Customer Email',
        'V6070' => 'Invalid Customer Phone',
        'V6071' => 'Invalid Customer Mobile',
        'V6072' => 'Invalid Customer Comments',
        'V6073' => 'Invalid Customer Fax',
        'V6074' => 'Invalid Customer URL',
        'V6075' => 'Invalid ShippingAddress FirstName',
        'V6076' => 'Invalid ShippingAddress LastName',
        'V6077' => 'Invalid ShippingAddress Street1',
        'V6078' => 'Invalid ShippingAddress Street2',
        'V6079' => 'Invalid ShippingAddress City',
        'V6080' => 'Invalid ShippingAddress State',
        'V6081' => 'Invalid ShippingAddress PostalCode',
        'V6082' => 'Invalid ShippingAddress Email',
        'V6083' => 'Invalid ShippingAddress Phone',
        'V6084' => 'Invalid ShippingAddress Country',
        'V6085' => 'Invalid ShippingAddress ShippingMethod',
        'V6086' => 'Invalid ShippingAddress Fax',
        'V6091' => 'Unknown Customer CountryCode',
        'V6092' => 'Unknown ShippingAddress CountryCode',
        'V6100' => 'Invalid EWAY_CARDNAME',
        'V6101' => 'Invalid EWAY_CARDEXPIRYMONTH',
        'V6102' => 'Invalid EWAY_CARDEXPIRYYEAR',
        'V6103' => 'Invalid EWAY_CARDSTARTMONTH',
        'V6104' => 'Invalid EWAY_CARDSTARTYEAR',
        'V6105' => 'Invalid EWAY_CARDISSUENUMBER',
        'V6106' => 'Invalid EWAY_CARDCVN',
        'V6107' => 'Invalid EWAY_ACCESSCODE',
        'V6108' => 'Invalid CustomerHostAddress',
        'V6109' => 'Invalid UserAgent',
        'V6110' => 'Invalid EWAY_CARDNUMBER',
        'V6111' => 'Unauthorised API Access, Account Not PCI Certified',
        'V6112' => 'Redundant card details other than expiry year and month',
        'V6113' => 'Invalid transaction for refund',
        'V6114' => 'Gateway validation error',
        'V6115' => 'Invalid DirectRefundRequest, Transaction ID',
        'V6116' => 'Invalid card data on original TransactionID',
        'V6117' => 'Invalid CreateAccessCodeSharedRequest, FooterText',
        'V6118' => 'Invalid CreateAccessCodeSharedRequest, HeaderText',
        'V6119' => 'Invalid CreateAccessCodeSharedRequest, Language',
        'V6120' => 'Invalid CreateAccessCodeSharedRequest, LogoUrl',
        'V6121' => 'Invalid TransactionSearch, Filter Match Type',
        'V6122' => 'Invalid TransactionSearch, Non numeric Transaction ID',
        'V6123' => 'Invalid TransactionSearch,no TransactionID or AccessCode specified',
        'V6124' => 'Invalid Line Items. The line items have been provided however the totals do not match the TotalAmount field',
        'V6125' => 'Selected Payment Type not enabled',
        'V6126' => 'Invalid encrypted card number, decryption failed',
        'V6127' => 'Invalid encrypted cvn, decryption failed',
        'V6128' => 'Invalid Method for Payment Type',
        'V6129' => 'Transaction has not been authorised for Capture/Cancellation',
        'V6130' => 'Generic customer information error',
        'V6131' => 'Generic shipping information error',
        'V6132' => 'Transaction has already been completed or voided, operation not permitted',
        'V6133' => 'Checkout not available for Payment Type',
        'V6134' => 'Invalid Auth Transaction ID for Capture/Void',
        'V6135' => 'PayPal Error Processing Refund',
        'V6140' => 'Merchant account is suspended',
        'V6141' => 'Invalid PayPal account details or API signature',
        'V6142' => 'Authorise not available for Bank/Branch',
        'V6150' => 'Invalid Refund Amount',
        'V6151' => 'Refund amount greater than original transaction',
        'V6152' => 'Original transaction already refunded for total amount',
        'V6153' => 'Card type not support by merchant',
    );

    public static function getMessage($code) {
        if (isset(ResponseCode::$_codes[$code])) {
            return ResponseCode::$_codes[$code];
        } else {
            return $code;
        }
    }
}

class CreateAccessCodeRequest {
    public $Customer;

    public $ShippingAddress;
    public $Items;
    public $Options;

    public $Payment;
    public $RedirectUrl;
    public $Method;
    public $TransactionType;
    private $CustomerIP;
    private $DeviceID;

    function __construct() {
        $this->Customer = new Customer();
        $this->ShippingAddress = new ShippingAddress();
        $this->Payment = new Payment();
        $this->CustomerIP = $_SERVER["SERVER_NAME"];
    }
}

class CreateAccessCodesSharedRequest extends CreateAccessCodeRequest {
    public $CancelUrl;
    public $LogoUrl;
    public $HeaderText;
    public $CustomerReadOnly;
}

class CreateDirectPaymentRequest {
    public $Customer;

    public $ShippingAddress;
    public $Items;
    public $Options;

    public $Payment;
    private $CustomerIP;
    private $DeviceID;
    public $TransactionType;
    public $PartnerID;

    function __construct() {
        $this->Customer = new CardCustomer();
        $this->ShippingAddress = new ShippingAddress();
        $this->Payment = new Payment();
        $this->CustomerIP = $_SERVER["SERVER_NAME"];
    }
}

class CreateRefundRequest {
    public $Refund;
    public $Customer;

    public $ShippingAddress;
    public $Items;
    public $Options;

    private $CustomerIP;
    private $DeviceID;
    public $PartnerID;

    function __construct() {
        $this->Refund = new Payment();
        $this->Customer = new CardCustomer();
        $this->ShippingAddress = new ShippingAddress();
        $this->CustomerIP = $_SERVER["SERVER_NAME"];
    }
}

/**
 * Description of Customer
 */
class Customer {
    public $TokenCustomerID;
    public $Reference;
    public $Title;
    public $FirstName;
    public $LastName;
    public $CompanyName;
    public $JobDescription;
    public $Street1;
    public $Street2;
    public $City;
    public $State;
    public $PostalCode;
    public $Country;
    public $Email;
    public $Phone;
    public $Mobile;
    public $Comments;
    public $Fax;
    public $Url;
}

class CardCustomer extends Customer {
    function __construct() {
        $this->CardDetails = new CardDetails();
    }
}

class ShippingAddress {
    public $FirstName;
    public $LastName;
    public $Street1;
    public $Street2;
    public $City;
    public $State;
    public $Country;
    public $PostalCode;
    public $Email;
    public $Phone;
    public $ShippingMethod;
}

class Items {
    public $LineItem = array();
}

class LineItem {
    public $SKU;
    public $Description;
    public $Quantity;
    public $UnitCost;
    public $Tax;
    public $Total;
}

class Options {
    public $Option = array();
}

class Option {
    public $Value;
}

class Payment {
    public $TotalAmount;
    /// <summary>The merchant's invoice number</summary>
    public $InvoiceNumber;
    /// <summary>merchants invoice description</summary>
    public $InvoiceDescription;
    /// <summary>The merchant's invoice reference</summary>
    public $InvoiceReference;
    /// <summary>The merchant's currency</summary>
    public $CurrencyCode;

    /// For Refund
    public $TransactionID;
}

class GetAccessCodeResultRequest {
    public $AccessCode;
}

class CardDetails {
    public $Name;
    public $Number;
    public $ExpiryMonth;
    public $ExpiryYear;
    public $StartMonth;
    public $StartYear;
    public $IssueNumber;
    public $CVN;
}
