<?php

if (! function_exists('pcntl_fork')) {
    fwrite(STDERR, "Pail requires the pcntl extension (Linux/macOS/WSL).\n");
    fwrite(STDERR, "On Windows, tail logs with:\n");
    fwrite(STDERR, "  Get-Content storage/logs/laravel.log -Wait -Tail 50\n");
    exit(1);
}

passthru('php artisan pail --timeout=0', $exitCode);

exit($exitCode);
