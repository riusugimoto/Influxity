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
// Start the session
session_start();

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    echo json_encode(['error' => 'Company is not logged in.']);
    exit;
}

$company_id = $_SESSION['company_id'];

// Fetch data requests for the company
$query = "SELECT dr.*, dc.CategoryName, dr.DataRequestID 
          FROM DataRequest dr 
          JOIN DataCategory dc ON dr.CategoryID = dc.CategoryID 
          WHERE dr.CompanyID = :company_id";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":company_id", $company_id);
oci_execute($stid);

$data_requests = [];
while ($row = oci_fetch_assoc($stid)) {
    $data_requests[] = $row;
}
oci_free_statement($stid);

// Fetch granted user data (excluding reviewed transactions)
$query = "SELECT tuu.TransactionID, tuu.UserID, tuu.DataText, dr.DataPurpose, dc.CategoryName, dr.Compensation AS RequestedCompensation
          FROM TransactionIDUserID tuu 
          JOIN TransactionIDDataRequestID tddr ON tuu.TransactionID = tddr.TransactionID 
          JOIN DataRequest dr ON tddr.DataRequestID = dr.DataRequestID 
          JOIN DataCategory dc ON dr.CategoryID = dc.CategoryID
          WHERE dr.CompanyID = :company_id
          AND NOT EXISTS (
              SELECT 1
              FROM Review r
              WHERE r.TransactionID = tuu.TransactionID
          )";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":company_id", $company_id);
oci_execute($stid);

$granted_user_data = [];
while ($row = oci_fetch_assoc($stid)) {
    $granted_user_data[] = $row;
}
oci_free_statement($stid);

// Fetch compensations
$query = "SELECT tia.*, tic.Currency, tit.Timestamp 
          FROM TransactionIDAmount tia 
          JOIN TransactionIDCurrency tic ON tia.TransactionID = tic.TransactionID 
          JOIN TransactionIDTimestamp tit ON tia.TransactionID = tit.TransactionID 
          WHERE tia.TransactionID IN (
              SELECT tuu.TransactionID 
              FROM TransactionIDUserID tuu 
              JOIN TransactionIDDataRequestID tddr ON tuu.TransactionID = tddr.TransactionID 
              JOIN DataRequest dr ON tddr.DataRequestID = dr.DataRequestID 
              WHERE dr.CompanyID = :company_id
          )";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":company_id", $company_id);
oci_execute($stid);

$compensations = [];
while ($row = oci_fetch_assoc($stid)) {
    $compensations[] = $row;
}

$query = "SELECT SUM(r.Compensation) AS TotalCompensation
          FROM Review r
          JOIN TransactionIDUserID tuu ON r.TransactionID = tuu.TransactionID
          JOIN TransactionIDDataRequestID tddr ON tuu.TransactionID = tddr.TransactionID
          JOIN DataRequest dr ON tddr.DataRequestID = dr.DataRequestID
          WHERE dr.CompanyID = :company_id";

$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ":company_id", $company_id);
oci_execute($stid);

$total_compensation = oci_fetch_assoc($stid)['TOTALCOMPENSATION'];


if (!oci_commit($conn)) {
    $e = oci_error($conn);
    echo "Error committing transaction: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

oci_free_statement($stid);

oci_close($conn);

echo json_encode([
    'data_requests' => $data_requests,
    'transactions' => $granted_user_data,
    'compensations' => $compensations,
    'total_compensation' => $total_compensation 
]);
?>
