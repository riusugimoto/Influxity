<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$username = "ora_nanrey";
$password = "a22597249";
$connection_string = "dbhost.students.cs.ubc.ca:1522/stu";

// Connect to Oracle database
$conn = oci_connect($username, $password, $connection_string);

if (!$conn) {
    $e = oci_error();
    echo json_encode(["error" => "Connection failed: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

// Fetch data from all relevant tables
$tables = ['Users', 'UserEmailPassword','UserEmailPassword', 'IndividualUser', 'IndividualUserName',  'CorporateUser', 'CompanyNameIndustry',  'Company', 'CompanyNameSize','DataRequest',  'DataBelongCategory', 'Activity',   'UserActivityType', 'TransparencyReport', 'ActivityTimestamp','TransactionIDUserID', 'TransactionIDDataRequestID', 'TransactionIDAmount', 'TransactionIDTimestamp', 'TransactionIDCurrency', 'TransactionIDExchangeRate','CurrencyExchangeRate', 'Review'];
$data = [];


// For each table in the $tables array
foreach ($tables as $table) {
    $query = "SELECT * FROM $table";
    $stid = oci_parse($conn, $query);
    oci_execute($stid);
    $rows = [];
    while ($row = oci_fetch_assoc($stid)) {
        $rows[] = $row;
    }
    oci_free_statement($stid);
    $data[$table] = $rows;
}

oci_close($conn);

echo json_encode($data);
?>
