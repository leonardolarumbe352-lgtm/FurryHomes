<?php

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="furryhomes-report.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['Month', 'Intakes', 'Adoptions']);
foreach (analytics_report() as $record) {
    fputcsv($output, $record);
}
fclose($output);
