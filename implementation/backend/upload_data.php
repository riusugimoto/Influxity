<?php
include "db_auth.php";
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Connect to Oracle database
$conn = db_log_in();

if (!$conn) {
    $e = oci_error();
    echo "Connection failed: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

session_start();
$userID = $_SESSION['userID'];
$dataRequestID = $_POST['dataRequestID'];
$data = $_POST['data'];

if (!$userID) {
    echo "User ID is not set. Please log in again.";
    exit;
}

try {
    $query = "INSERT INTO TransactionIDUserID (TransactionID, UserID, DataText) VALUES (TransactionID_seq.NEXTVAL, :userID, :data)";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":userID", $userID);
    oci_bind_by_name($stid, ":data", $data);

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Error inserting into TransactionIDUserID table: " . htmlentities($e['message'], ENT_QUOTES));
    }
    oci_free_statement($stid);

    // Insert into TransactionIDDataRequestID
    $query = "INSERT INTO TransactionIDDataRequestID (TransactionID, DataRequestID) VALUES (TransactionID_seq.CURRVAL, :dataRequestID)";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":dataRequestID", $dataRequestID);

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Error inserting into TransactionIDDataRequestID table: " . htmlentities($e['message'], ENT_QUOTES));
    }
    oci_free_statement($stid);

    // Insert into TransactionIDAmount
    $query = "INSERT INTO TransactionIDAmount (TransactionID, Amount) VALUES (TransactionID_seq.CURRVAL, 0)"; // Initial amount 0
    $stid = oci_parse($conn, $query);

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Error inserting into TransactionIDAmount table: " . htmlentities($e['message'], ENT_QUOTES));
    }
    oci_free_statement($stid);

    // Insert into TransactionIDTimestamp
    $query = "INSERT INTO TransactionIDTimestamp (TransactionID, Timestamp) VALUES (TransactionID_seq.CURRVAL, SYSDATE)";
    $stid = oci_parse($conn, $query);

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Error inserting into TransactionIDTimestamp table: " . htmlentities($e['message'], ENT_QUOTES));
    }
    oci_free_statement($stid);

    // Insert into TransactionIDCurrency
    $query = "INSERT INTO TransactionIDCurrency (TransactionID, Currency) VALUES (TransactionID_seq.CURRVAL, 'USD')";
    $stid = oci_parse($conn, $query);

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Error inserting into TransactionIDCurrency table: " . htmlentities($e['message'], ENT_QUOTES));
    }
    oci_free_statement($stid);

    // Insert into TransactionIDExchangeRate
    $query = "INSERT INTO TransactionIDExchangeRate (TransactionID, ExchangeRate) VALUES (TransactionID_seq.CURRVAL, 1)";
    $stid = oci_parse($conn, $query);

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Error inserting into TransactionIDExchangeRate table: " . htmlentities($e['message'], ENT_QUOTES));
    }
    oci_free_statement($stid);

    // Commit the transaction
    if (!oci_commit($conn)) {
        $e = oci_error($conn);
        throw new Exception("Error committing transaction: " . htmlentities($e['message'], ENT_QUOTES));
    }
    header("Location: ../frontend/user_dashboard.html");
    //echo "Data uploaded successfully!";
} catch (Exception $e) {
    error_log($e->getMessage());
    echo $e->getMessage();
}

// Close the connection
oci_free_statement($stid);
oci_close($conn);
?>
