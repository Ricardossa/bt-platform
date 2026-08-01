<?php
header('Content-Type: text/plain');
echo "--- SITES AVAILABLE ---\n";
echo shell_exec('ls -l /etc/apache2/sites-available/');
echo "\n--- SITES ENABLED ---\n";
echo shell_exec('ls -l /etc/apache2/sites-enabled/');
echo "\n--- CURRENT 000-DEFAULT.CONF ---\n";
echo shell_exec('cat /etc/apache2/sites-available/000-default.conf');
echo "\n--- APACHE STATUS ---\n";
echo shell_exec('systemctl status apache2');
