<?php

session_start();

include("PayflowNVPAPI.php");

global $environment;

$environment = "sandbox";

//Change it live if you are going for live transaction  require_once('PayflowNVPAPI.php');

//////// //// ////  First, handle any return/responses ////

/*Check if we just returned inside the iframe.  If so, store payflow response and redirect parent window with javascript.Purpose for adding this script is to get responce where ever you want on your php file otherwise paypal return response in iframe*/

if (isset($_REQUEST['RESULT']) || isset($_GET['RESULT']) ) {   $_SESSION['payflowresponse'] = array_merge($_GET, $_REQUEST);   echo '<script type="text/javascript">window.top.location.href = "' . script_url() .  '";</script>';   exit(0); }  echo "<span style='font-family:sans-serif;font-size:160%;font-weight:bold;'>PayPal PayFlow Pro Demo TNT</span> <p style='margin-left:1em;font-family:monospace;'>

Hosted checkout page (Layout C) embedded in your site as an iframe</p>

<hr/>";

//Check whether we stored a server response.If so, print it out.


if(!empty($_SESSION['payflowresponse'])) {

$response= $_SESSION['payflowresponse'];

unset($_SESSION['payflowresponse']);

$success = ($response['RESULT'] == 0);

/*Now Here you can print all responce returning from paypal sucess or error   respectivelly */ 

if($success) echo "<span style='font-family:sans-serif;font-weight:bold;'>Transaction approved! Thank you for your order.</span>";

else echo "<span style='font-family:sans-serif;'>Transaction failed! Please try again with another payment method.</span>";

echo "<pre>(server response follows)\n";

print_r($response);

echo "</pre>";

exit(0);

}


echo '<form action=';
echo htmlspecialchars($_SERVER["PHP_SELF"]);
echo '>
	  <input name = "AMT" value="10.00"><label for "AMT"> Amt </label><br>
	  <input name = "BILLTOFIRSTNAME" value="Test"><label for "BILLTOFIRSTNAME"> Bill To First Name </label><br>
	  <input name = "BILLTOLASTNAME" value="Tester"><label for "BILLTOLASTNAME"> Bill To Last Name </label><br>
	  <input name = "BILLTOSTREET" value="500 Huntsville"><label for "BILLTOSTREET"> Bill to Street </label><br>
	  <input name = "BILLTOCITY" value="Robbins"><label for "BILLTOCITY"> Bill to City </label><br>
	  <input name = "BILLTOSTATE" value="TN"><label for "BILLTOSTATE"> Bill to State </label><br>
	  <input name = "BILLTOZIP" value="37852"><label for "BILLTOZIP"> Bill To Zip </label><br>
	  <input name = "BILLTOCOUNTRY" value="US"><label for "BILLTOCOUNTRY"> Bill to Country </label> <br>
	  <input name = "SHIPTOFIRSTNAME" value="Test"><label for "SHIPTOFIRSTNAME"> Ship To First Name </label><br>
	  <input name = "SHIPTOLASTNAME" value="Tester"><label for "SHIPTOLASTNAME"> Ship To Last Name </label><br>
	  <input name = "SHIPTOSTREET" value="500 Huntsville"><label for "SHIPTOSTREET"> Ship to Street </label><br>
	  <input name = "SHIPTOCITY" value="Robbins"><label for "SHIPTOCITY"> Ship To City </label><br>
	  <input name = "SHIPTOSTATE" value="TN"><label for "SHIPTOSTATE"> Ship To State </label><br>
	  <input name = "SHIPTOZIPT" value="37852"><label for "SHIPTOZIP"> Ship To Zip </label><br>
	  <input name = "SHIPTOCOUNTRY" value="US"><label for "SHIPTOCOUNTRY"> Ship to Country </label><br>
	  <input name = "SILENTTRAN" value="false"><label for "SILENTTRAN"> Silent Transaction </label><br>
	  <input type = "submit">
	  </form>';

if(!isset($_REQUEST['AMT'])){
	exit(0);
}

// Otherwise, begin hosted checkout pages flow 

//Build the Secure Token request

$request = array("PARTNER" => "PayPal", //change it paypal manager account setting 

"VENDOR" => "trollandtoad", //change it paypal manager account setting 

"USER" => "trollandtoad", //change it paypal manager account setting 

"PWD" => "chili88pepper",  //change it paypal manager account setting 

"TRXTYPE" => "S", //'S'=>Sale,'A'=>Authorize,'D'=>Delay Captured, 

"AMT" => $_REQUEST["AMT"],   "CURRENCY" => "USD",   "CREATESECURETOKEN" => "Y", //Set Y=>'Yes' to create secure token 

"SECURETOKENID" => uniqid('MySecTokenID-'), //Should be unique, never used before 

"RETURNURL" => script_url(), 

"CANCELURL" => script_url(), 

"ERRORURL" => script_url(),  // In practice you'd collect billing and shipping information with your own form, // then request a secure token and display the payment iframe. // --> See page 7 of https://cms.paypal.com/cms_content/US/en_US/files/developer/Embedded_Checkout_Design_Guide.pdf // This example uses hardcoded values for simplicity.  

"BILLTOFIRSTNAME" => $_REQUEST["BILLTOFIRSTNAME"], 

"BILLTOLASTNAME" => $_REQUEST["BILLTOLASTNAME"], 

"BILLTOSTREET" => $_REQUEST["BILLTOSTREET"], 

"BILLTOCITY" => $_REQUEST["BILLTOCITY"], 

"BILLTOSTATE" => $_REQUEST["BILLTOSTATE"], 

"BILLTOZIP" => $_REQUEST["BILLTOZIP"], 

"BILLTOCOUNTRY" => $_REQUEST["BILLTOCOUNTRY"],  

"SHIPTOFIRSTNAME" => $_REQUEST["SHIPTOFIRSTNAME"], 

"SHIPTOLASTNAME" => $_REQUEST["SHIPTOLASTNAME"], 

"SHIPTOSTREET" => $_REQUEST["SHIPTOSTREET"], 

"SHIPTOCITY" => $_REQUEST["SHIPTOCITY"], 

"SHIPTOSTATE" => $_REQUEST["SHIPTOSTATE"], 

"SHIPTOZIP" => $_REQUEST["SHIPTOZIP"], 

"SHIPTOCOUNTRY" => $_REQUEST["SHIPTOCOUNTRY"],

"SILENTTRAN" => $_REQUEST["SILENTTRAN"]
 );

//Run request and get the secure token response

$response = run_payflow_call($request);

if ($response['RESULT'] != 0) {

pre($response, "Payflow call failed");

exit(0);

}else {

$securetoken = $response['SECURETOKEN'];

$securetokenid = $response['SECURETOKENID'];

}


if($environment == "sandbox" || $environment == "pilot")

$mode='TEST';

else

$mode='LIVE';

echo '<div style="border: 1px dashed; margin-left:40px; width:492px; height:567px;">

';

// wrap iframe in a dashed wireframe for demo purposes

echo "<iframe src='https://payflowlink.paypal.com?SECURETOKEN=$securetoken&SECURETOKENID=$securetokenid&MODE=$mode' width='700' height='700' border='0' frameborder='0' scrolling='no' allowtransparency='true'>\n</iframe>";
