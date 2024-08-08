<?php

function db_log_in() {
    //extracted database connection details to a separate file for easier authentication changes.
        return oci_connect("ora_nanrey", "a22597249", "dbhost.students.cs.ubc.ca:1522/stu");
}

?>