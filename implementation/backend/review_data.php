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
$transactionID = $_POST['transactionID'];
$action = $_POST['action'];
$compensation = isset($_POST['compensation']) ? $_POST['compensation'] : null;
$status = ($action === 'accept' || $action === 'accept_with_compensation') ? 'Accepted' : 'Rejected';

// Fetch the Company's UserID based on their session
$company_id = $_SESSION['company_id'];

// Insert a new record into the Review table
$query = "INSERT INTO Review (ReviewID, TransactionID, UserID, Status, Compensation) 
          VALUES (Review_seq.NEXTVAL, :transactionID, :company_id, :status, :compensation)";

$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":transactionID", $transactionID);
oci_bind_by_name($stid, ":company_id", $company_id);
oci_bind_by_name($stid, ":status", $status);
oci_bind_by_name($stid, ":compensation", $compensation);

if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo "Error inserting into Review table: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}
oci_free_statement($stid);

// Update DataRequest Status
$query = "UPDATE DataRequest SET Status = :status
WHERE DataRequestID = (SELECT DataRequestID FROM TransactionIDDataRequestID WHERE TransactionID = :transactionID)";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":status", $status);
oci_bind_by_name($stid, ":transactionID", $transactionID);

if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo "Error updating DataRequest table: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}
oci_free_statement($stid);


if ($action === 'accept_with_compensation' || $action === 'accept') { 
    $query = "UPDATE TransactionIDAmount 
              SET Amount = :compensation 
              WHERE TransactionID = :transactionID";

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":compensation", $compensation);
    oci_bind_by_name($stid, ":transactionID", $transactionID);

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        echo "Error updating TransactionIDAmount table: " . htmlentities($e['message'], ENT_QUOTES);
        exit;
    }
    oci_free_statement($stid);
} 

if (!oci_commit($conn)) {
    $e = oci_error($conn);
    echo "Error committing transaction: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

echo ucfirst($action) . " successful! User removed.";
oci_close($conn);
?>
