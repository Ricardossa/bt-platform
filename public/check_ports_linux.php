<?php
echo "--- SITES ENABLED IN APACHE ---\n";
echo shell_exec("ls -l /etc/apache2/sites-enabled/");
echo "\n--- PORT 80 CONFIG ---\n";
echo shell_exec("grep -r \"VirtualHost \*:80\" /etc/apache2/sites-enabled/");
