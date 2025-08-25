<?php

$username = "LMS";
$password = "new_dev_mahrukh@MLC@2025";
$connection_string = "192.168.88.241/orcl"; 

$conn = oci_connect($username, $password, $connection_string);

if (!$conn) {
    $e = oci_error();
    echo "Connection failed: " . $e['message'];
} else {
    echo "Successfully connected to Oracle Database!";
    oci_close($conn);
}
?>
