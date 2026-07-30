<?php
header('Content-Type: text/plain');
echo "--- APACHE ACCESS LOG (Last 20) ---\n";
echo shell_exec("tail -n 20 /var/log/apache2/access.log");
echo "\n--- APACHE ERROR LOG (Last 20) ---\n";
echo shell_exec("tail -n 20 /var/log/apache2/error.log");
