<?php
namespace eWAY;

/* check examples for usage */
class RapidAPI {
    private $_url;
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
        } else {
            $this->_url = 'https://api.ewaypayments.com/';
        }
    }

    public function CreateAccessCode($request) {
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
                $tempClass->Items[$i] = $LineItem;
                $i++;
            }
            $request->Items = $tempClass->Items;
        }

        $request = json_encode($request);
        $response = $this->PostToRapidAPI("AccessCodes", $request);
        return json_decode($response);
    }

    public function GetAccessCodeResult($request) {
        $request = json_encode($request);
        $response = $this->PostToRapidAPI("AccessCode/" . $_GET['AccessCode'], $request, false);
        return json_decode($response);
    }

    public function CreateAccessCodesShared($request) {
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
                $tempClass->Items[$i] = $LineItem;
                $i++;
            }
            $request->Items = $tempClass->Items;
        }

        $request = json_encode($request);
        $response = $this->PostToRapidAPI("AccessCodesShared", $request);
        return json_decode($response);
    }

    public function DirectPayment($request) {
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
                $tempClass->Items[$i] = $LineItem;
                $i++;
            }
            $request->Items = $tempClass->Items;
        }

        $request = json_encode($request);
        $response = $this->PostToRapidAPI("Transaction", $request);
        return json_decode($response);
    }

    /* alias */
    public function getMessage($code) {
        return ResponseCode::getMessage($code);
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
        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        if (curl_errno($ch) != CURLE_OK) {
            echo "<h2>POST Error: " . curl_error($ch) . " URL: $url</h2><pre>";
            die();
        } else {
            $info = curl_getinfo($ch);
            if ($info['http_code'] == 401) {
                echo "<h2>Please check the API Key and Password</h2><pre>";
                die();
            }

            curl_close($ch);
            return $response;
        }
    }
}

class ResponseCode {
    private static $_codes = array(
        'F7000' => "Undefined Fraud",
        'V5000' => "Undefined System",
        'A0000' => "Undefined Approved",
        'A2000' => "Transaction Approved",
        'A2008' => "Honour With Identification",
        'A2010' => "Approved For Partial Amount",
        'A2011' => "Approved VIP",
        'A2016' => "Approved Update Track 3",
        'V6000' => "Undefined Validation",
        'V6001' => "Invalid Request CustomerIP",
        'V6002' => "Invalid Request DeviceID",
        'V6011' => "Invalid Payment Amount",
        'V6012' => "Invalid Payment InvoiceDescription",
        'V6013' => "Invalid Payment InvoiceNumber",
        'V6014' => "Invalid Payment InvoiceReference",
        'V6015' => "Invalid Payment CurrencyCode",
        'V6016' => "Payment Required",
        'V6017' => "Payment CurrencyCode Required",
        'V6018' => "Unknown Payment CurrencyCode",
        'V6021' => "Cardholder Name Required",
        'V6022' => "Card Number Required",
        'V6023' => "CVN Required",
        'V6031' => "Invalid Card Number",
        'V6032' => "Invalid CVN",
        'V6033' => "Invalid Expiry Date",
        'V6034' => "Invalid Issue Number",
        'V6035' => "Invalid Start Date",
        'V6036' => "Invalid Month",
        'V6037' => "Invalid Year",
        'V6040' => "Invalid Token Customer Id",
        'V6041' => "Customer Required",
        'V6042' => "Customer First Name Required",
        'V6043' => "Customer Last Name Required",
        'V6044' => "Customer Country Code Required",
        'V6045' => "Customer Title Required",
        'V6046' => "Token Customer ID Required",
        'V6047' => "RedirectURL Required",
        'V6051' => "Invalid Customer First Name",
        'V6052' => "Invalid Customer Last Name",
        'V6053' => "Invalid Customer Country Code",
        'V6054' => "Invalid Customer Email",
        'V6055' => "Invalid Customer Phone",
        'V6056' => "Invalid Customer Mobile",
        'V6057' => "Invalid Customer Fax",
        'V6058' => "Invalid Customer Title",
        'V6059' => "Redirect URL Invalid",
        'V6060' => "Redirect URL Invalid",
        'V6061' => "Invalid Customer Reference",
        'V6062' => "Invalid Customer CompanyName",
        'V6063' => "Invalid Customer JobDescription",
        'V6064' => "Invalid Customer Street1",
        'V6065' => "Invalid Customer Street2",
        'V6066' => "Invalid Customer City",
        'V6067' => "Invalid Customer State",
        'V6068' => "Invalid Customer Postalcode",
        'V6069' => "Invalid Customer Email",
        'V6070' => "Invalid Customer Phone",
        'V6071' => "Invalid Customer Mobile",
        'V6072' => "Invalid Customer Comments",
        'V6073' => "Invalid Customer Fax",
        'V6074' => "Invalid Customer Url",
        'V6075' => "Invalid ShippingAddress FirstName",
        'V6076' => "Invalid ShippingAddress LastName",
        'V6077' => "Invalid ShippingAddress Street1",
        'V6078' => "Invalid ShippingAddress Street2",
        'V6079' => "Invalid ShippingAddress City",
        'V6080' => "Invalid ShippingAddress State",
        'V6081' => "Invalid ShippingAddress PostalCode",
        'V6082' => "Invalid ShippingAddress Email",
        'V6083' => "Invalid ShippingAddress Phone",
        'V6084' => "Invalid ShippingAddress Country",
        'V6091' => "Unknown Country Code",
        'V6100' => "Invalid ProcessRequest name",
        'V6101' => "Invalid ProcessRequest ExpiryMonth",
        'V6102' => "Invalid ProcessRequest ExpiryYear",
        'V6103' => "Invalid ProcessRequest StartMonth",
        'V6104' => "Invalid ProcessRequest StartYear",
        'V6105' => "Invalid ProcessRequest IssueNumber",
        'V6106' => "Invalid ProcessRequest CVN",
        'V6107' => "Invalid ProcessRequest AccessCode",
        'V6108' => "Invalid ProcessRequest CustomerHostAddress",
        'V6109' => "Invalid ProcessRequest UserAgent",
        'V6110' => "Invalid ProcessRequest Number",
        'D4401' => "Refer to Issuer",
        'D4402' => "Refer to Issuer, special",
        'D4403' => "No Merchant",
        'D4404' => "Pick Up Card",
        'D4405' => "Do Not Honour",
        'D4406' => "Error",
        'D4407' => "Pick Up Card, Special",
        'D4409' => "Request In Progress",
        'D4412' => "Invalid Transaction",
        'D4413' => "Invalid Amount",
        'D4414' => "Invalid Card Number",
        'D4415' => "No Issuer",
        'D4419' => "Re-enter Last Transaction",
        'D4421' => "No Method Taken",
        'D4422' => "Suspected Malfunction",
        'D4423' => "Unacceptable Transaction Fee",
        'D4425' => "Unable to Locate Record On File",
        'D4430' => "Format Error",
        'D4431' => "Bank Not Supported By Switch",
        'D4433' => "Expired Card, Capture",
        'D4434' => "Suspected Fraud, Retain Card",
        'D4435' => "Card Acceptor, Contact Acquirer, Retain Card",
        'D4436' => "Restricted Card, Retain Card",
        'D4437' => "Contact Acquirer Security Department, Retain Card",
        'D4438' => "PIN Tries Exceeded, Capture",
        'D4439' => "No Credit Account",
        'D4440' => "Function Not Supported",
        'D4441' => "Lost Card",
        'D4442' => "No Universal Account",
        'D4443' => "Stolen Card",
        'D4444' => "No Investment Account",
        'D4451' => "Insufficient Funds",
        'D4452' => "No Cheque Account",
        'D4453' => "No Savings Account",
        'D4454' => "Expired Card",
        'D4455' => "Incorrect PIN",
        'D4456' => "No Card Record",
        'D4457' => "Function Not Permitted to Cardholder",
        'D4458' => "Function Not Permitted to Terminal",
        'D4460' => "Acceptor Contact Acquirer",
        'D4461' => "Exceeds Withdrawal Limit",
        'D4462' => "Restricted Card",
        'D4463' => "Security Violation",
        'D4464' => "Original Amount Incorrect",
        'D4466' => "Acceptor Contact Acquirer, Security",
        'D4467' => "Capture Card",
        'D4475' => "PIN Tries Exceeded",
        'D4482' => "CVV Validation Error",
        'D4490' => "Cutoff In Progress",
        'D4491' => "Card Issuer Unavailable",
        'D4492' => "Unable To Route Transaction",
        'D4493' => "Cannot Complete, Violation Of The Law",
        'D4494' => "Duplicate Transaction",
        'D4496' => "System Error",
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
