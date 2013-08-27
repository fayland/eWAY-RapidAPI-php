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

		if ($params['sandbox']) {
			$this->_url = 'https://api.sandbox.ewaypayments.com/';
		} else {
			$this->_url = 'https://api.ewaypayments.com/';
		}
	}

	public function CreateAccessCode($request) {
		$i = 0;
        $tempClass = new \stdClass();
        foreach ($request->Options->Option as $Option) {
            $tempClass->Options[$i] = $Option;
            $i++;
        }
        $request->Options = $tempClass->Options;
        $i = 0;
        $tempClass = new \stdClass();
        foreach ($request->Items->LineItem as $LineItem) {
            $tempClass->Items[$i] = $LineItem;
            $i++;
        }
        $request->Items = $tempClass->Items;

        $request = json_encode($request);
        $response = $this->PostToRapidAPI("AccessCodes", $request);
        return json_decode($response);
    }

    public function GetAccessCodeResult($request) {
        $request = json_encode($request);
        $response = $this->PostToRapidAPI("AccessCode/" . $_GET['AccessCode'], $request, false);
        return json_decode($response);
    }

    public function DirectPayment($request) {
    	$i = 0;
        $tempClass = new \stdClass();
        foreach ($request->Options->Option as $Option) {
            $tempClass->Options[$i] = $Option;
            $i++;
        }
        $request->Options = $tempClass->Options;
        $i = 0;
        $tempClass = new \stdClass();
        foreach ($request->Items->LineItem as $LineItem) {
            $tempClass->Items[$i] = $LineItem;
            $i++;
        }
        $request->Items = $tempClass->Items;

        $request = json_encode($request);
        $response = $this->PostToRapidAPI("Transaction", $request);
        return json_decode($response);
    }

    /*
     * Description A Function for doing a Curl GET/POST
     */
    private function PostToRapidAPI($url, $request, $IsPost = true) {
    	$url = $this->_url . $url;
        $ch = curl_init($url);

        error_log("POST TO $url");

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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        if (curl_errno($ch) != CURLE_OK) {
            echo "<h2>POST Error: " . curl_error($ch) . " URL: $url</h2><pre>";
            die();
        } else {
            curl_close($ch);
            return $response;
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
    private $CustomerIP;
    private $DeviceID;

    function __construct() {
        $this->Customer = new Customer();
        $this->ShippingAddress = new ShippingAddress();
        $this->Payment = new Payment();
        $this->CustomerIP = $_SERVER["SERVER_NAME"];
    }
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
