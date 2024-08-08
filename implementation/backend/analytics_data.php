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
    echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}
session_start();
if (!isset($_SESSION['userID'])) {
    echo json_encode(['error' => "Please lg in again."]);
    exit;
}
$userID = $_SESSION['userID'];


// Fetch reviewed user data
$query = "SELECT tuu.TransactionID, tuu.UserID, tuu.DataText, dr.DataPurpose, dc.CategoryName, dr.Compensation AS RequestedCompensation, r.Status, r.Compensation AS OfferedCompensation
          FROM TransactionIDUserID tuu 
          JOIN TransactionIDDataRequestID tddr ON tuu.TransactionID = tddr.TransactionID 
          JOIN DataRequest dr ON tddr.DataRequestID = dr.DataRequestID 
          JOIN DataCategory dc ON dr.CategoryID = dc.CategoryID
          JOIN Review r ON tuu.TransactionID = r.TransactionID"; // Join with Review table

$stid = oci_parse($conn, $query);
oci_execute($stid);

$reviewed_user_data = [];
while ($row = oci_fetch_assoc($stid)) {
    $reviewed_user_data[] = $row;
}
oci_free_statement($stid);

// Fetch data for analytics, filtered by the current user's ID
$query = "SELECT 
          tuu.UserID,
          tuu.DataText, 
          dr.DataPurpose,
          dc.CategoryName,
          dr.Compensation AS RequestedCompensation,
          r.Status,
          r.Compensation AS OfferedCompensation
          FROM TransactionIDUserID tuu 
          JOIN TransactionIDDataRequestID tddr ON tuu.TransactionID = tddr.TransactionID 
          JOIN DataRequest dr ON tddr.DataRequestID = dr.DataRequestID
          JOIN DataCategory dc ON dr.CategoryID = dc.CategoryID
          LEFT JOIN Review r ON tuu.TransactionID = r.TransactionID
          WHERE tuu.UserID = :userID"; // Filter by UserID

$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":userID", $userID);

if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo json_encode(['error' => "Error fetching transaction data: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

$transactions = [];
while ($row = oci_fetch_assoc($stid)) {
    $transactions[] = $row;
}

oci_free_statement($stid);
oci_close($conn);

echo json_encode([
    'transactions' => $reviewed_user_data,
    'transactions' => $transactions
]);
?>