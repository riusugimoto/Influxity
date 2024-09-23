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

session_start(); // Start the session

if (!isset($_SESSION['userID']) && !isset($_SESSION['company_id'])) {
    echo json_encode(['error' => "Please log in again."]);
    exit;
}

if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
    // Fetch reviewed user data for the current company
    $query = "SELECT tuu.TransactionID, tuu.UserID, tuu.DataText, dr.DataPurpose, dc.CategoryName, 
                     dr.Compensation AS RequestedCompensation, r.Status, r.Compensation AS OfferedCompensation
              FROM TransactionIDUserID tuu 
              JOIN TransactionIDDataRequestID tddr ON tuu.TransactionID = tddr.TransactionID 
              JOIN DataRequest dr ON tddr.DataRequestID = dr.DataRequestID 
              JOIN DataCategory dc ON dr.CategoryID = dc.CategoryID
              JOIN Review r ON tuu.TransactionID = r.TransactionID
              WHERE dr.CompanyID = :company_id"; // Filter by company_id

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":company_id", $company_id);
    oci_execute($stid);

    $reviewed_user_data = [];
    while ($row = oci_fetch_assoc($stid)) {
        $reviewed_user_data[] = $row;
    }
    oci_free_statement($stid);
    oci_close($conn);
    
    echo json_encode([
        'transactions' => $reviewed_user_data,
    ]);
} else {
    $userID = $_SESSION['userID'];

    // Fetch data for analytics, filtered by the current user's ID and the current company
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
              WHERE tuu.UserID = :userID AND dr.CompanyID = :company_id"; // Filter by UserID and company_id

    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":userID", $userID);
    oci_bind_by_name($stid, ":company_id", $company_id);

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
        'transactions' => $transactions
    ]);
}
?>
