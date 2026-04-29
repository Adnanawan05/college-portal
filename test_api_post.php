<?php
$url = 'http://localhost/smartschool-final/api/students.php';
$data = array('action' => 'delete_student', 'id' => '10');

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "Error making request";
} else {
    echo "Response:\n";
    echo $result;
}
?>
