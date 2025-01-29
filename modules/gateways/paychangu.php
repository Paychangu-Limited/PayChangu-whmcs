<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * @return array
 */
function paychangu_MetaData()
{
    return array(
        'DisplayName' => 'PayChangu',
        'APIVersion' => '1.1',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
        'LogoURL' => 'https://firebasestorage.googleapis.com/v0/b/paychangu-dbdaa.firebasestorage.app/o/assets%2Ftag2.png?alt=media&token=c832d3ce-9ff6-4c40-bf54-705bb1fb9fec'
    );
}

/**
 * Define gateway configuration options.
 *
 * @return array
 */
function paychangu_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'PayChangu',
        ),
        'publicKey' => array(
            'FriendlyName' => 'Public Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter your PayChangu Public Key here',
        ),
        'secretKey' => array(
            'FriendlyName' => 'Secret Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter your PayChangu Secret Key here',
        ),
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        ),
    );
}

/**
 * Payment link.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @return string
 */
function paychangu_link($params)
{
    // Gateway Configuration Parameters
    $publicKey = $params['publicKey'];
    
    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];
    
    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    
    // System Parameters
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    
    // Check if we're on the invoice page
    $isInvoicePage = (strpos($_SERVER['SCRIPT_NAME'], 'viewinvoice.php') !== false);
    
    if (!$isInvoicePage) {
        // If not on invoice page, redirect to it
        header("Location: " . $systemUrl . "viewinvoice.php?id=" . $invoiceId);
        exit;
    }
    
    $callbackUrl = $systemUrl . 'modules/gateways/callback/paychangu.php';
    
    $code = '
    <form>
        <script src="https://in.paychangu.com/js/popup.js"></script>
        <div id="wrapper"></div>
        <button type="button" onClick="makePayment()" class="btn btn-primary">' . $langPayNow . '</button>
    </form>
    <script>
        function makePayment(){
            PaychanguCheckout({
                "public_key": "' . $publicKey . '",
                "tx_ref": "WHMCS-' . $invoiceId . '-" + Math.floor((Math.random() * 1000000000) + 1),
                "amount": ' . $amount . ',
                "currency": "' . $currencyCode . '",
                "callback_url": "' . $callbackUrl . '",
                "return_url": "' . $returnUrl . '",
                "customer":{
                    "email": "' . $email . '",
                    "first_name": "' . $firstname . '",
                    "last_name": "' . $lastname . '",
                },
                "customization": {
                    "title": "Invoice #' . $invoiceId . '",
                    "description": "' . $description . '",
                },
                "meta": {
                    "invoice_id": "' . $invoiceId . '"
                }
            });
        }
    </script>';
    
    return $code;
} 