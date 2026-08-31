<?php

$minPhp = '8.1.0';
$requiredExtensions = [
    'ctype',
    'curl',
    'dom',
    'fileinfo',
    'gd',
    'iconv',
    'intl',
    'json',
    'libxml',
    'mbstring',
    'mysqli',
    'openssl',
    'simplexml',
    'xml',
    'xmlreader',
    'xmlwriter',
    'zip',
    'zlib',
];

$errors = [];

printf("PHP version: %s\n", PHP_VERSION);
if (version_compare(PHP_VERSION, $minPhp, '<')) {
    $errors[] = "PHP {$minPhp}+ diperlukan.";
}

foreach ($requiredExtensions as $extension) {
    $loaded = extension_loaded($extension);
    printf("%-12s %s\n", $extension, $loaded ? 'OK' : 'MISSING');
    if (!$loaded) {
        $errors[] = "PHP extension '{$extension}' belum aktif.";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "\nRequirement check FAILED:\n- " . implode("\n- ", $errors) . "\n");
    exit(1);
}

echo "\nRequirement check PASS.\n";
