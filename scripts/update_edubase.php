<?php

set_time_limit(0); // This is a script and the Internet connection might be limited
ini_set('default_socket_timeout', 5 * 60); // Set the download timeout to 5 minutes

$today           = date('Ymd');
$baseDownloadUrl = 'http://www.education.gov.uk/edubase/';
$downloadList    = array(
    'all-data'                   => array(
        'edubasealldata%s.csv',
        'links_edubasealldata%s.csv',
    ),
    'state-funded'               => array(
        'edubaseallstatefunded%s.csv',
        'links_edubaseallstatefunded%s.csv',
    ),
    'academies-and-free-schools' => array(
        'edubaseallacademiesandfree%s.csv',
        'links_edubaseallacademiesandfree%s.csv',
    ),
);

foreach ($downloadList as $downloadFolder => $urls) {
    foreach ($urls as $url) {
        $fileUrl = $baseDownloadUrl . sprintf($url, $today);
        echo sprintf('Downloading %s ...%s', $fileUrl, PHP_EOL);
        $contents = file_get_contents($fileUrl);
        file_put_contents(sprintf('%s/../edubase/%s/%s', __DIR__, $downloadFolder, sprintf($url, '')), $contents);
    }
}

file_put_contents(sprintf('%s/../edubase/last-updated.txt', __DIR__), date('d-m-Y H:i:s') . PHP_EOL);
