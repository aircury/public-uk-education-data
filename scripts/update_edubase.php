<?php

set_time_limit(0); // This is a script and the Internet connection might be limited

$today           = date('Ymd');
$baseDownloadUrl = 'http://www.education.gov.uk/edubase/';
$downloadList    = [
    'all-data'                   => [
        'edubasealldata%s.csv',
        'links_edubasealldata%s.csv',
    ],
    'state-funded'               => [
        'edubaseallstatefunded%s.csv',
        'links_edubaseallstatefunded%s.csv',
    ],
    'academies-and-free-schools' => [
        'edubaseallacademiesandfree%s.csv',
        'links_edubaseallacademiesandfree%s.csv',
        'grouplinks_edubaseallacademiesandfree%s.csv',
    ],
    'childrens-centres'          => [
        'edubaseallchildrencentre%s.csv',
    ],
    'governance'                 => [
        'governancematdata%s.csv',
        'governanceacaddata%s.csv',
        'governanceladata%s.csv',
    ],
];

foreach ($downloadList as $downloadFolder => $urls) {
    foreach ($urls as $url) {
        $fileUrl = $baseDownloadUrl . sprintf($url, $today);
        $target  = sprintf('%s/../edubase/%s/%s', __DIR__, $downloadFolder, sprintf($url, ''));

        echo sprintf('Downloading %s ...%s', $fileUrl, PHP_EOL);

        $targetFile = fopen($target, 'w');
        $ch         = curl_init();

        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL              => $fileUrl,
                CURLOPT_RETURNTRANSFER   => true,
                CURLOPT_NOPROGRESS       => false,
                CURLOPT_PROGRESSFUNCTION => 'progressCallback',
                CURLOPT_FILE             => $targetFile,
                CURLOPT_TIMEOUT          => 10 * 60 // Set the download timeout to 5 minutes
            ]
        );

        curl_exec($ch);

        curl_close($ch);

        fclose($targetFile);
    }
}

file_put_contents(sprintf('%s/../edubase/last-updated.txt', __DIR__), date('d-m-Y H:i:s') . PHP_EOL);

function progressCallback($resource, $download_size, $downloaded_size, $upload_size, $uploaded_size)
{
    static $timeStarted = 0;
    static $lastElapsed = 0;

    if (0 === $timeStarted) {
        $timeStarted = microtime(true);
    }

    $elapsed = microtime(true) - $timeStarted;

    if ($elapsed - $lastElapsed > 5) {
        if ($download_size == 0) {
            $progress = 0;
        } else {
            $progress = round($downloaded_size * 100 / $download_size);
        }

        echo sprintf(
            '    %2s%% - Elapsed: %.0fs - Remaining: %.0fs' . "\n",
            $progress,
            $elapsed,
            0 == $progress ? 0 : (100 * (microtime(true) - $timeStarted) / $progress) - $elapsed
        );

        $lastElapsed = $elapsed;
    }
}
