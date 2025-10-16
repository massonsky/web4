<?php
require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');
echo "Echo GET test (project5)\n";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'NOT SET') . "\n";

echo "\nRaw $_GET:\n";
foreach ($_GET as $k => $v) {
    echo "$k = $v\n";
}

// Fallback parse
$parsed = [];
if (isset($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $parsed);
}

echo "\nParsed from QUERY_STRING:\n";
foreach ($parsed as $k => $v) {
    echo "$k = $v\n";
}

echo "\nAction (GET): '" . ($_GET['action'] ?? '') . "'\n";
echo "Action (parsed): '" . ($parsed['action'] ?? '') . "'\n";

echo "\nisAjaxRequest(): " . (isAjaxRequest() ? 'true' : 'false') . "\n";
?>