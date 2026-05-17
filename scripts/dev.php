<?php

/**
 * Start local dev processes. Skips Laravel Pail when pcntl is unavailable (e.g. Windows).
 */

$commands = [
    'php artisan serve',
    'php artisan queue:listen --tries=1 --timeout=0',
    'npm run dev',
];

$names = ['server', 'queue', 'vite'];
$colors = '#93c5fd,#c4b5fd,#fdba74';

if (function_exists('pcntl_fork')) {
    array_splice($commands, 2, 0, ['php artisan pail --timeout=0']);
    array_splice($names, 2, 0, ['logs']);
    $colors = '#93c5fd,#c4b5fd,#fb7185,#fdba74';
}

$parts = ['npx', 'concurrently', '-c', $colors];

foreach ($commands as $command) {
    $parts[] = $command;
}

$parts[] = '--names=' . implode(',', $names);
$parts[] = '--kill-others';

$cmd = implode(' ', array_map(static fn (string $part): string => str_contains($part, ' ') ? '"' . $part . '"' : $part, $parts));

passthru($cmd, $exitCode);

exit($exitCode);
