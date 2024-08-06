<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$username = "ora_nanrey";
$password = "a22597249";
$connection_string = "dbhost.students.cs.ubc.ca:1522/stu";

// Connect to Oracle database
$conn = oci_connect($username, $password, $connection_string);

if (!$conn) {
    $e = oci_error();
    echo "Connection failed: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Get the key from the query parameter
$key = $_GET['key'];

// Define the tables and their primary keys
$tables = [
    'Users' => ['UserID'],
    'UserEmailUsername' => ['Email'],
    'UserEmailPassword' => ['Email'],
    'IndividualUser' => ['UserID'],
    'IndividualUserName' => ['FirstName', 'LastName', 'DateOfBirth'],
    'CorporateUser' => ['UserID'],
    'CompanyNameIndustry' => ['CompanyName'],
    'CompanyNameSize' => ['CompanyName'],
    'Activity' => ['ActivityID'],
    'ActivityTimestamp' => ['ActivityID'],
    'UserActivityType' => ['UserID', 'Timestamp'],
    //'UserActivityDetails' => ['UserID', 'Timestamp'],
    'TransparencyReport' => ['ReportID'],
    'ReportGeneratedOn' => ['ReportID'],
    //'UserGeneratedReportDetails' => ['UserID', 'GeneratedOn'],
    'Company' => ['CompanyID'],
    //'DataCategory' => ['CategoryID'],
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


// Array to hold the data
$data = [];

foreach ($tables as $table => $keys) {
    if (in_array($key, $keys)) {
        $query = "SELECT * FROM $table WHERE $key IS NOT NULL";
        $stid = oci_parse($conn, $query);
        oci_execute($stid);
        $rows = [];
        while ($row = oci_fetch_assoc($stid)) {
            $rows[] = $row;
        }
        oci_free_statement($stid);
        $data[$table] = $rows;
    }
}

oci_close($conn);

echo json_encode($data);
?>
