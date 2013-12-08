<?php

session_start();

// Include RapidAPI Library
require('../../lib/eWAY/RapidAPI.php');

$in_page = 'before_submit';
if ( isset($_POST['btnSubmit']) ) {

    // we skip all validation but you should do it in real world

    // Create Refund Request Object
    $request = new eWAY\CreateRefundRequest();

    // ALL are optional expect Refund TotalAmount and TransactionID
    $request->Refund->TotalAmount = intval($_POST['txtAmount']);
    $request->Refund->InvoiceNumber = $_POST['txtInvoiceNumber'];
    $request->Refund->InvoiceDescription = $_POST['txtInvoiceDescription'];
    $request->Refund->InvoiceReference = $_POST['txtInvoiceReference'];
    $request->Refund->CurrencyCode = $_POST['txtCurrencyCode'];
    $request->Refund->TransactionID = $_POST['txtTransactionID'];

    // Populate values for Customer Object
    // Note: TokenCustomerID is Required Field When Update an exsiting TokenCustomer
    if(!empty($_POST['txtTokenCustomerID']))
        $request->Customer->TokenCustomerID = $_POST['txtTokenCustomerID'];

    $request->Customer->Reference = $_POST['txtCustomerRef'];
    $request->Customer->Title = $_POST['ddlTitle'];
    $request->Customer->FirstName = $_POST['txtFirstName'];
    $request->Customer->LastName = $_POST['txtLastName'];
    $request->Customer->CompanyName = $_POST['txtCompanyName'];
    $request->Customer->JobDescription = $_POST['txtJobDescription'];
    $request->Customer->Street1 = $_POST['txtStreet'];
    $request->Customer->City = $_POST['txtCity'];
    $request->Customer->State = $_POST['txtState'];
    $request->Customer->PostalCode = $_POST['txtPostalcode'];
    $request->Customer->Country = $_POST['txtCountry'];
    $request->Customer->Email = $_POST['txtEmail'];
    $request->Customer->Phone = $_POST['txtPhone'];
    $request->Customer->Mobile = $_POST['txtMobile'];
    $request->Customer->Comments = $_POST['txtComments'];
    $request->Customer->Fax = $_POST['txtFax'];
    $request->Customer->Url = $_POST['txtUrl'];

    $request->Customer->CardDetails->Name = $_POST['txtCardName'];
    $request->Customer->CardDetails->Number = $_POST['txtCardNumber'];
    $request->Customer->CardDetails->ExpiryMonth = $_POST['ddlCardExpiryMonth'];
    $request->Customer->CardDetails->ExpiryYear = $_POST['ddlCardExpiryYear'];
    $request->Customer->CardDetails->StartMonth = $_POST['ddlStartMonth'];
    $request->Customer->CardDetails->StartYear = $_POST['ddlStartYear'];
    $request->Customer->CardDetails->IssueNumber = $_POST['txtIssueNumber'];
    $request->Customer->CardDetails->CVN = $_POST['txtCVN'];

    // Populate values for ShippingAddress Object.
    // This values can be taken from a Form POST as well. Now is just some dummy data.
    $request->ShippingAddress->FirstName = "John";
    $request->ShippingAddress->LastName = "Doe";
    $request->ShippingAddress->Street1 = "9/10 St Andrew";
    $request->ShippingAddress->Street2 = " Square";
    $request->ShippingAddress->City = "Edinburgh";
    $request->ShippingAddress->State = "";
    $request->ShippingAddress->Country = "gb";
    $request->ShippingAddress->PostalCode = "EH2 2AF";
    $request->ShippingAddress->Email = "your@email.com";
    $request->ShippingAddress->Phone = "0131 208 0321";
    // ShippingMethod, e.g. "LowCost", "International", "Military". Check the spec for available values.
    $request->ShippingAddress->ShippingMethod = "LowCost";

    // Populate values for LineItems
    $item1 = new eWAY\LineItem();
    $item1->SKU = "SKU1";
    $item1->Description = "Description1";
    $item2 = new eWAY\LineItem();
    $item2->SKU = "SKU2";
    $item2->Description = "Description2";
    $request->Items->LineItem[0] = $item1;
    $request->Items->LineItem[1] = $item2;

    // Populate values for Options
    $opt1 = new eWAY\Option();
    $opt1->Value = $_POST['txtOption1'];
    $opt2 = new eWAY\Option();
    $opt2->Value = $_POST['txtOption2'];
    $opt3 = new eWAY\Option();
    $opt3->Value = $_POST['txtOption3'];
    $request->Options->Option[0]= $opt1;
    $request->Options->Option[1]= $opt2;
    $request->Options->Option[2]= $opt3;

    // Call RapidAPI
    $eway_params = array();
    if ($_POST['ddlSandbox']) $eway_params['sandbox'] = true;
    $service = new eWAY\RapidAPI($_POST['txtUsername'], $_POST['txtPassword'], $eway_params);
    $result = $service->Refund($request);

    // Check if any error returns
    if(isset($result->Errors)) {
        // Get Error Messages from Error Code. Error Code Mappings are in the Config.ini file
        $ErrorArray = explode(",", $result->Errors);
        $lblError = "";
        foreach ( $ErrorArray as $error ) {
            $error = $service->getMessage($error);
            $lblError .= $error . "<br />\n";;
        }
    } else {
        $in_page = 'view_result';
    }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title></title>
    <link href="../assets/Styles/Site.css" rel="stylesheet" type="text/css" />
    <link href="../assets/Styles/jquery-ui-1.8.11.custom.css" rel="stylesheet" type="text/css" />
    <script src="../assets/Scripts/jquery-1.4.4.min.js" type="text/javascript"></script>
    <script src="../assets/Scripts/jquery-ui-1.8.11.custom.min.js" type="text/javascript"></script>
    <script src="../assets/Scripts/jquery.ui.datepicker-en-GB.js" type="text/javascript"></script>
    <script type="text/javascript" src="../assets/Scripts/tooltip.js"></script>
</head>
<body>
    <form method="POST">
    <center>
        <div id="outer">
            <div id="toplinks">
                <img alt="eWAY Logo" class="logo" src="../assets/Images/companylogo.gif" width="960px" height="65px" />
            </div>
            <div id="main">

<?php
    if ($in_page === 'view_result') {
?>

    <div id="titlearea">
        <h2>Sample Response</h2>
    </div>

    <div id="maincontent">
        <div class="response">
            <div class="fields">
                <label for="lblAuthorisationCode">
                    Authorisation Code</label>
                <label id="lblAuthorisationCode"><?php echo isset($result->AuthorisationCode) ? $result->AuthorisationCode:""; ?></label>
            </div>
            <div class="fields">
                <label for="lblInvoiceNumber">
                    Invoice Number</label>
                <label id="lblInvoiceNumber"><?php echo $result->Refund->InvoiceNumber; ?></label>
            </div>
            <div class="fields">
                <label for="lblInvoiceReference">
                    Invoice Reference</label>
                <label id="lblInvoiceReference"><?php echo $result->Refund->InvoiceReference; ?></label>
            </div>
            <div class="fields">
                <label for="lblResponseCode">
                    Response Code</label>
                <label id="lblResponseCode"><?php echo $result->ResponseCode; ?></label>
            </div>
            <div class="fields">
                <label for="lblResponseMessage">
                    Response Message</label>
                <label id="lblResponseMessage">
                 <?php
                        if(isset($result->ResponseMessage))
                        {
                            //Get Error Messages from Error Code. Error Code Mappings are in the Config.ini file
                            $ResponseMessageArray = explode(",", $result->ResponseMessage);
                            $responseMessage = "";
                            foreach ( $ResponseMessageArray as $message ) {
                                $real_message = $service->getMessage($message);
                                if($message != $real_message)
                                    $responseMessage .= $message . " " . $real_message . "<br>";
                                else
                                    $responseMessage .= $message;
                            }
                            echo $responseMessage;
                        }

                 ?>
                </label>
            </div>
            <div class="fields">
                <label for="lblTotalAmount">
                    Total Amount</label>
                <label id="lblTotalAmount"><?php
                    if (isset($result->Refund->TotalAmount)) {
                        echo $result->Refund->TotalAmount;
                    }
                ?></label>
            </div>
            <div class="fields">
                <label for="lblTransactionID">
                    TransactionID</label>
                <label id="lblTransactionID"><?php
                    if (isset($result->TransactionID)) {
                            echo $result->TransactionID;
                    }
                ?></label>
            </div>
            <div class="fields">
                <label for="lblTransactionStatus">
                    Transaction Status</label>
                <label id="lblTransactionStatus"><?php
                    if (isset($result->TransactionStatus) && $result->TransactionStatus && (is_bool($result->TransactionStatus) || $result->TransactionStatus != "false")) {
                        echo 'True';
                    } else {
                        echo 'False';
                    }
                ?></label>
            </div>
        </div>
    </div>

        <br />
        <br />
        <a href="index.php">[Start Over]</a>

    <div id="maincontentbottom">
    </div>

<?php
    } else { // for if ($in_page === 'view_result') {
?>

    <div id="titlearea">
        <h2>Sample Merchant Page</h2>
    </div>
<?php
    if (isset($lblError)) {
?>
    <div id="error">
        <label style="color:red"><?php echo $lblError ?></label>
    </div>
<?php } ?>
    <div id="maincontent">
        <p style='font-weight: strong; color: red'>Only Amount and Transaction ID are required.</p>
        <div class="transactioncustomer">
            <div class="header first">
                Request Options
            </div>
            <div class="fields">
                <label for="txtUsername">API Username</label>
                <input id="txtUsername" name="txtUsername" type="text" value="" />
            </div>
            <div class="fields">
                <label for="txtPassword">API Password</label>
                <input id="txtPassword" name="txtPassword" type="password" />
            </div>
            <div class="fields">
                <label for="ddlSandbox">API Sandbox</label>
                <select id="ddlSandbox" name="ddlSandbox">
                <option value="1" selected="selected">Yes</option>
                <option value="">No</option>
                </select>
            </div>

          <div id='refund_details'>
            <div class="header">
                Refund Details
            </div>
            <div class="fields">
                <label for="txtAmount">Amount &nbsp;<img src="../assets/Images/question.gif" alt="Find out more" id="amountTipOpener" border="0" /></label>
                <input id="txtAmount" name="txtAmount" type="text" value="100" />
            </div>
            <div class="fields">
                <label for="txtAmount">Transaction ID</label>
                <input id="txtAmount" name="txtTransactionID" type="text" value="" placeholder="The original Transaction ID to refund" />
            </div>
            <div class="fields">
                <label for="txtCurrencyCode">Currency Code </label>
                <input id="txtCurrencyCode" name="txtCurrencyCode" type="text" value="AUD" />
            </div>
            <div class="fields">
                <label for="txtInvoiceNumber">Invoice Number</label>
                <input id="txtInvoiceNumber" name="txtInvoiceNumber" type="text" value="Inv 21540" />
            </div>
            <div class="fields">
                <label for="txtInvoiceReference">Invoice Reference</label>
                <input id="txtInvoiceReference" name="txtInvoiceReference" type="text" value="513456" />
            </div>
            <div class="fields">
                <label for="txtInvoiceDescription">Invoice Description</label>
                <input id="txtInvoiceDescription" name="txtInvoiceDescription" type="text" value="Individual Invoice Description" />
            </div>
            <div class="header">
                Custom Fields
            </div>
            <div class="fields">
                <label for="txtOption1">Option 1</label>
                <input id="txtOption1" name="txtOption1" type="text" value="Option1" />
            </div>
            <div class="fields">
                <label for="txtOption2">Option 2</label>
                <input id="txtOption2" name="txtOption2" type="text" value="Option2" />
            </div>
            <div class="fields">
                <label for="txtOption3">Option 3</label>
                <input id="txtOption3" name="txtOption3" type="text" value="Option3" />
            </div>
          </div> <!-- end for <div id='refund_details'> -->
        </div>
        <div class="transactioncard">
            <div class="header first">
                Customer Details
            </div>
            <div class="fields">
                <label for="txtTokenCustomerID">Token Customer ID &nbsp;<img src="../assets/Images/question.gif" alt="Find out more" id="tokenCustomerTipOpener" border="0" /></label>
                <input id="txtTokenCustomerID" name="txtTokenCustomerID" type="text" />
            </div>
            <div class="fields">
                <label for="ddlTitle">Title</label>
                <select id="ddlTitle" name="ddlTitle">
                <option></option>
                <option value="Mr." selected="selected">Mr.</option>
                <option value="Miss">Miss</option>
                <option value="Mrs.">Mrs.</option>
                </select>
            </div>
            <div class="fields">
                <label for="txtCustomerRef">Customer Reference</label>
                <input id="txtCustomerRef" name="txtCustomerRef" type="text" value="A12345" />
            </div>
            <div class="fields">
                <label for="txtFirstName">First Name</label>
                <input id="txtFirstName" name="txtFirstName" type="text" value="John" />
            </div>
            <div class="fields">
                <label for="txtLastName">Last Name</label>
                <input id="txtLastName" name="txtLastName" type="text" value="Doe" />
            </div>
            <div class="fields">
                <label for="txtCompanyName">Company Name</label>
                <input id="txtCompanyName" name="txtCompanyName" type="text" value="WEB ACTIVE" />
            </div>
            <div class="fields">
                <label for="txtJobDescription">Job Description</label>
                <input id="txtJobDescription" name="txtJobDescription" type="text" value="Developer" />
            </div>
            <div class="header">
                Customer Address
            </div>
            <div class="fields">
                <label for="txtStreet">Street</label>
                <input id="txtStreet" name="txtStreet" type="text" value="15 Smith St" />
            </div>
            <div class="fields">
                <label for="txtCity">City</label>
                <input id="txtCity" name="txtCity" type="text" value="Phillip" />
            </div>
            <div class="fields">
                <label for="txtState">State</label>
                <input id="txtState" name="txtState" type="text" value="ACT" />
            </div>
            <div class="fields">
                <label for="txtPostalcode">Post Code</label>
                <input id="txtPostalcode" name="txtPostalcode" type="text" value="2602" />
            </div>
            <div class="fields">
                <label for="txtCountry">Country</label>
                <input id="txtCountry" name="txtCountry" type="text" value="au" maxlength="2" />
            </div>
            <div class="fields">
                <label for="txtEmail">Email</label>
                <input id="txtEmail" name="txtEmail" type="text" value="" />
            </div>
            <div class="fields">
                <label for="txtPhone">Phone</label>
                <input id="txtPhone" name="txtPhone" type="text" value="1800 10 10 65" />
            </div>
            <div class="fields">
                <label for="txtMobile">Mobile</label>
                <input id="txtMobile" name="txtMobile" type="text" value="1800 10 10 65" />
            </div>
            <div class="fields">
                <label for="txtFax">Fax</label>
                <input id="txtFax" name="txtFax" type="text" value="02 9852 2244" />
            </div>
            <div class="fields">
                <label for="txtUrl">Website</label>
                <input id="txtUrl" name="txtUrl" type="text" value="http://www.yoursite.com" />
            </div>
            <div class="fields">
                <label for="txtComments">Comments</label>
                <textarea id="txtComments" name="txtComments"/>Some comments here</textarea>
            </div>
            <div class="header">
                Customer Card Details
            </div>
            <div class="fields">
                <label for="txtCardName">
                    Card Holder</label>
                <input type='text' name='txtCardName' id='txtCardName' value="TestUser" />
            </div>
            <div class="fields">
                <label for="txtCardNumber">
                    Card Number</label>
                <input type='text' name='txtCardNumber' id='txtCardNumber' value="4444333322221111" />
            </div>
            <div class="fields">
                <label for="ddlCardExpiryMonth">
                    Expiry Date</label>
                <select ID="ddlCardExpiryMonth" name="ddlCardExpiryMonth">
                    <?php
                        $expiry_month = date('m');
                        for($i = 1; $i <= 12; $i++) {
                            $s = sprintf('%02d', $i);
                            echo "<option value='$s'";
                            if ( $expiry_month == $i ) {
                                echo " selected='selected'";
                            }
                            echo ">$s</option>\n";
                        }
                    ?>
                </select>
                /
                <select ID="ddlCardExpiryYear" name="ddlCardExpiryYear">
                    <?php
                        $i = date("y");
                        $j = $i+11;
                        for ($i; $i <= $j; $i++) {
                            echo "<option value='$i'>$i</option>\n";
                        }
                    ?>
                </select>
            </div>
            <div class="fields">
                <label for="ddlStartMonth">
                    Valid From Date</label>
                <select ID="ddlStartMonth" name="ddlStartMonth">
                    <?php
                        $expiry_month = "";//date('m');
                        echo  "<option></option>";

                        for($i = 1; $i <= 12; $i++) {
                            $s = sprintf('%02d', $i);
                            echo "<option value='$s'";
                            if ( $expiry_month == $i ) {
                                echo " selected='selected'";
                            }
                            echo ">$s</option>\n";
                        }
                    ?>
                </select>
                /
                <select ID="ddlStartYear" name="ddlStartYear">
                    <?php
                        $i = date("y");
                        $j = $i-11;
                        echo  "<option></option>";
                        for ($i; $i >= $j; $i--) {
                            $year = sprintf('%02d', $i);
                            echo "<option value='$year'>$year</option>\n";
                        }
                    ?>
                </select>
            </div>
            <div class="fields">
                <label for="txtIssueNumber">
                    Issue Number</label>
                <input type='text' name='txtIssueNumber' id='txtIssueNumber' value="22" maxlength="2" style="width:40px;"/> <!-- This field is optional but highly recommended -->
            </div>
            <div class="fields">
                <label for="txtCVN">
                    CVN</label>
                <input type='text' name='txtCVN' id='txtCVN' value="123" maxlength="4" style="width:40px;"/> <!-- This field is optional but highly recommended -->
            </div>
        </div>
        <div class="button">
            <br />
            <br />
            <input type="submit" id="btnSubmit" name="btnSubmit" value="Refund" />
        </div>
    </div>
    <div id="maincontentbottom">
    </div>
    <div id="amountTip" style="font-size: 8pt !important">
        The amount in cents. For example for an amount of $1.00, enter 100
    </div>
    <div id="tokenCustomerTip" style="font-size: 8pt !important">
        If this field has a value, the details of an existing customer will be loaded when the request is sent.
    </div>
    <div id="saveTokenTip" style="font-size: 8pt !important">
        If this field is checked, the details in the customer fields will be used to either create a new token customer, or (if Token Customer ID has a value) update an existing customer.
    </div>

<?php
    } // for if ($in_page === 'view_result') {
?>
            </div>
            <div id="footer"></div>
        </div>
    </center>
    </form>

</body>
</html>