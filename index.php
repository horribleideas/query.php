<?php
set_error_handler('error_handler');
ini_set('display_errors', 'off');
error_reporting( E_ALL );

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$output = array();
$conn = pg_connect(pg_connect_string());

$q = pg_query($conn, $_GET['q']);

function return_error($message, $conn) {
  $output = array();
  $output['error'] = $message;

  pg_close($conn);
  http_response_code(406);
  exit(json_encode($output));
}

function error_handler($errno, $errstr, $errfile, $errline) {
  return_error($errstr, $conn);
}

function pg_connect_string() {
  extract(parse_url($_ENV["DATABASE_URL"]));
  return "user=$user password=$pass host=$host dbname=" . substr($path, 1) ." sslmode=require";
}

if (!$q) {
  return_error(pg_last_error($conn), $conn);
}
else {
  while ($row = pg_fetch_object($q)) {
    $output[] = $row;
  }
}

pg_close($conn);
exit(json_encode($output));
