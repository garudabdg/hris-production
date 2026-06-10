<?php
$lines = file('/var/www/hris.didimax.online/storage/logs/laravel.log');
$errors = [];
foreach($lines as $line) {
    if (strpos($line, 'TokenMismatchException') !== false || strpos($line, '419') !== false || strpos($line, 'Sesi') !== false) {
        $errors[] = $line;
    }
}
$last_errors = array_slice($errors, -15);
foreach($last_errors as $e) {
    echo htmlspecialchars(substr($e, 0, 500)) . "<br>";
}
