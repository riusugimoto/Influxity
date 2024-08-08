<?php
include "db_auth.php";

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Connect to Oracle database
global $conn;
$conn = db_log_in();
if (!$conn) {
    $e = oci_error();
    echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'viewAllData':
                viewAllData();
                break;
            case 'searchByKey':
                if (isset($_GET['key'])) {
                    searchByKey($_GET['key']);
                }
                break;
            case 'searchCompleteData':
                if (isset($_GET['type'])) {
                    searchCompleteData($_GET['type']);
                }
                break;
            case 'viewTotalRequestsPerCategory':
                viewTotalRequestsPerCategory();
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
    } else {
        echo json_encode(['error' => 'No action specified']);
    }
}

function viewAllData() {
    global $conn;
    $tables = ['Users', 'UserEmailPassword', 'IndividualUser', 'IndividualUserName', 'CorporateUser', 'CompanyNameIndustry', 'Company', 'CompanyNameSize', 'DataRequest', 'DataBelongCategory', 'Activity', 'UserActivityType', 'TransparencyReport', 'ActivityTimestamp', 'TransactionIDUserID', 'TransactionIDDataRequestID', 'TransactionIDAmount', 'TransactionIDTimestamp', 'TransactionIDCurrency', 'TransactionIDExchangeRate', 'CurrencyExchangeRate', 'Review'];
    $data = [];

    // For each table in the $tables array
    foreach ($tables as $table) {
        $query = "SELECT * FROM $table";
        $stid = oci_parse($conn, $query);
        if (!$stid) {
            $e = oci_error($conn);
            echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
            oci_close($conn);
            exit;
        }
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
            oci_close($conn);
            exit;
        }
        $rows = [];
        while ($row = oci_fetch_assoc($stid)) {
            $rows[] = $row;
        }
        oci_free_statement($stid);
        $data[$table] = $rows;
    }
    echo json_encode($data);
}

function searchByKey($key) {
    global $conn;
    $key = $_GET['key'];
    
    $tables = [
        'Users' => ['UserID'],
        'UserEmailPassword' => ['Email'],
        'IndividualUser' => ['UserID'],
        'IndividualUserName' => ['FirstName', 'LastName'],
        'CorporateUser' => ['UserID'],
        'CompanyNameIndustry' => ['CompanyName'],
        'CompanyNameSize' => ['CompanyName'],
        'Activity' => ['ActivityID'],
        'ActivityTimestamp' => ['ActivityID'],
        'UserActivityType' => ['UserID', 'Timestamp'],
        'TransparencyReport' => ['ReportID'],
        'Company' => ['CompanyID'],
        'DataRequest' => ['DataRequestID'],
        'DataBelongCategory' => ['CategoryID', 'DataRequestID'],
        'TransactionIDUserID' => ['TransactionID'],
        'TransactionIDDataRequestID' => ['TransactionID'],
        'TransactionIDAmount' => ['TransactionID'],
        'TransactionIDTimestamp' => ['TransactionID'],
        'TransactionIDCurrency' => ['TransactionID'],
        'TransactionIDExchangeRate' => ['TransactionID'],
        'CurrencyExchangeRate' => ['Currency'],
        'Review' => ['ReviewID']
    ];

    $data = [];
    foreach ($tables as $table => $keys) {
        if (in_array($key, $keys)) {
            $query = "SELECT * FROM $table WHERE $key IS NOT NULL";
            $stid = oci_parse($conn, $query);
            if (!$stid) {
                $e = oci_error($conn);
                echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
                oci_close($conn);
                exit;
            }
            if (!oci_execute($stid)) {
                $e = oci_error($stid);
                echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
                oci_close($conn);
                exit;
            }
            $rows = [];
            while ($row = oci_fetch_assoc($stid)) {
                $rows[] = $row;
            }
            oci_free_statement($stid);
            $data[$table] = $rows;
        }
    }
    echo json_encode($data);
}

function searchCompleteData($type) {
    global $conn;
    $query = '';
    if ($type === 'usersCompleteData') {
        $query = "SELECT UserID
            FROM Users u
            WHERE NOT EXISTS (
                SELECT dc.CategoryID
                FROM DataCategory dc
                MINUS
                SELECT dr.CategoryID
                FROM DataRequest dr
                JOIN TransactionIDDataRequestID tdr ON dr.DataRequestID = tdr.DataRequestID
                JOIN TransactionIDUserID tuu ON tdr.TransactionID = tuu.TransactionID
                WHERE tuu.UserID = u.UserID)";
    } else if ($type === 'companiesAllCategories') {
        $query = "SELECT c.Name AS CompanyName
            FROM Company c
            JOIN DataRequest dr ON c.CompanyID = dr.CompanyID
            GROUP BY c.CompanyID, c.Name
            HAVING COUNT(DISTINCT dr.CategoryID) = (SELECT COUNT(*) FROM DataCategory)";
    }
    
    $stid = oci_parse($conn, $query);
    if (!$stid) {
        $e = oci_error($conn);
        echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
        oci_close($conn);
        exit;
    }
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
        oci_close($conn);
        exit;
    }
    $rows = [];
    while ($row = oci_fetch_assoc($stid)) {
        $rows[] = $row;
    }
    oci_free_statement($stid);
    echo json_encode($rows);  
}

function viewTotalRequestsPerCategory() {
    global $conn;
    $query = "SELECT CategoryID, COUNT(*) AS TotalRequests
              FROM DataRequest
              GROUP BY CategoryID";

    $stid = oci_parse($conn, $query);
    if (!$stid) {
        $e = oci_error($conn);
        echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
        oci_close($conn);
        exit;
    }
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        echo json_encode(['error' => htmlentities($e['message'], ENT_QUOTES)]);
        oci_close($conn);
        exit;
    }

    $rows = [];
    while ($row = oci_fetch_assoc($stid)) {
        $rows[] = $row;
    }
    oci_free_statement($stid);
    echo json_encode($rows);
}

oci_close($conn);
?>
