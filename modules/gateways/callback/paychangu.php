<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . '/../../../includes/clientfunctions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$gatewayModuleName = 'paychangu';
$gateway = getGatewayVariables($gatewayModuleName);

if (!$gateway['type']) {
    die("Module Not Activated");
}

// Get the transaction reference
$tx_ref = $_GET['tx_ref'] ?? '';

if (empty($tx_ref)) {
    die("No transaction reference provided");
}

// Extract invoice ID from tx_ref (format: WHMCS-{invoiceId}-{random})
$tx_parts = explode('-', $tx_ref);
if (count($tx_parts) < 2) {
    die("Invalid transaction reference format");
}
$invoiceId = $tx_parts[1];

try {
    // Verify the payment with PayChangu API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paychangu.com/verify-payment/" . $tx_ref);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Accept: application/json",
        "Authorization: Bearer " . $gateway['secretKey']
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        logTransaction($gateway['name'], array(
            'tx_ref' => $tx_ref,
            'error' => 'Failed to verify payment',
            'response' => $response
        ), 'Failed');
        throw new Exception("Payment verification failed");
    }

    $result = json_decode($response, true);

    if ($result['status'] === 'success' && $result['data']['status'] === 'success') {
        $paymentAmount = $result['data']['amount'];
        $paymentCurrency = $result['data']['currency'];
        $transactionId = $result['data']['reference'];
        
        // Verify invoice exists and is unpaid
        $invoiceId = checkCbInvoiceID($invoiceId, $gateway['name']);
        
        // Check transaction hasn't already been processed
        checkCbTransID($transactionId);
        
        // Add payment to invoice
        addInvoicePayment(
            $invoiceId,
            $transactionId,
            $paymentAmount,
            0, // No fee for now
            $gatewayModuleName
        );
        
        // Get invoice details using WHMCS API
        $command = 'GetInvoice';
        $postData = array(
            'invoiceid' => $invoiceId,
        );
        
        $results = localAPI($command, $postData);
        
        if ($results['result'] == 'success') {
            $userId = $results['userid'];
            
            // Update order status
            $command = 'UpdateClientProduct';
            $postData = array(
                'clientid' => $userId,
                'servicestatus' => 'Active'
            );
            localAPI($command, $postData);
            
            // Clear shopping cart
            $_SESSION['cart'] = array();
        }
        
        // Log successful transaction
        logTransaction($gateway['name'], $result, 'Success');
        
        // Redirect back to invoice
        header("Location: " . $gateway['systemurl'] . "viewinvoice.php?id=" . $invoiceId);
        exit;
    } else {
        throw new Exception("Payment failed");
    }
} catch (Exception $e) {
    // Log failed transaction
    logTransaction($gateway['name'], array(
        'tx_ref' => $tx_ref,
        'error' => $e->getMessage(),
        'response' => $result ?? null
    ), 'Failed');
    
    // Redirect to invoice with error
    header("Location: " . $gateway['systemurl'] . "viewinvoice.php?id=" . $invoiceId . "&paymentfailed=true");
    exit;
} 