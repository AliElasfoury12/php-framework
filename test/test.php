<?php 

use core\base\_Array;

require_once __DIR__."/../vendor/autoload.php";

function matchRoute($pattern, $url) {
    // Normalize both: remove leading/trailing slashes
    $pattern = trim($pattern, '/');
    $url = trim($url, '/');

    // Split into parts
    $patternParts = explode('/', $pattern);
    $urlParts = explode('/', $url);

    // If part count doesn't match, it's not a match
    if (count($patternParts) !== count($urlParts)) {
        return false;
    }

    $params = [];

    // Compare each part
    foreach ($patternParts as $i => $part) {
        if (preg_match('/^{(\w+)}$/', $part, $matches)) {
            // It's a dynamic part: store the value
            $params[$matches[1]] = $urlParts[$i];
        } elseif ($part !== $urlParts[$i]) {
            // Static part does not match
            return false;
        }
    }

    return $params;
}

// Test
$pattern = 'posts/{id}/user/{name}';
$url = 'posts/6/user/ali';

$result = matchRoute($pattern, $url);

if ($result) {
    echo "Matched!\n";
    print_r($result); // Outputs: Array ( [id] => 6 [name] => ali )
} else {
    echo "No match.\n";
}