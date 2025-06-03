<?php
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=guest_template.csv");

echo "name,email,note,plus_one\n";
echo "John Doe,john@example.com,Vegetarian,1\n";
echo "Jane Smith,jane@example.com,,0\n";
exit;
