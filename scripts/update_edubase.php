<?php

set_time_limit(0); // This is a script and the Internet connection might be limited

$today = date('Ymd');
$baseDownloadUrl = 'https://ea-edubase-api-prod.azurewebsites.net/edubase/downloads/public/';
$downloadList = [
    'all-data' => [
        'edubasealldata%s.csv',
        'links_edubasealldata%s.csv',
    ],
    'state-funded' => [
        'edubaseallstatefunded%s.csv',
        'links_edubaseallstatefunded%s.csv',
    ],
    'academies-and-free-schools' => [
        'edubaseallacademiesandfree%s.csv',
        'links_edubaseallacademiesandfree%s.csv',
    ],
    'groups' => [
        'grouplinks_edubaseallacademiesandfree%s.csv',
        'allgroupsdata%s.csv',
        'alllinksdata%s.csv',
    ],
    'childrens-centres' => [
        'edubaseallchildrencentre%s.csv',
        'links_edubaseallchildrencentre%s.csv',
    ],
    'governance' => [
        'governancealldata%s.csv',
        'governancematdata%s.csv',
        'governanceacaddata%s.csv',
        'governanceladata%s.csv',
    ],
];

foreach ($downloadList as $downloadFolder => $urls) {
    foreach ($urls as $url) {
        $fileUrl = $baseDownloadUrl . sprintf($url, $today);
        $target = sprintf('%s/../edubase/%s/%s', __DIR__, $downloadFolder, str_replace('/', '_', sprintf($url, '')));
        $timeStarted = microtime(true);
        $lastElapsed = 0;

        echo sprintf('Downloading %s ...%s', $fileUrl, PHP_EOL);

        $targetFile = fopen($target, 'wb');
        $ch = curl_init();

        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $fileUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOPROGRESS => false,
                CURLOPT_PROGRESSFUNCTION => 'progressCallback',
                CURLOPT_FILE => $targetFile,
                CURLOPT_TIMEOUT => 10 * 60 // Set the download timeout to 5 minutes
            ]
        );

        curl_exec($ch);

        curl_close($ch);

        fclose($targetFile);

        if (filesize($target) < 5) {
            throw new \RuntimeException('Something has gone wrong downloading the data from edubase');
        }
    }
}

file_put_contents(sprintf('%s/../edubase/last-updated.txt', __DIR__), date('d-m-Y H:i:s') . PHP_EOL);

function progressCallback($resource, int $download_size, int $downloaded_size, $upload_size, $uploaded_size)
{
    global $timeStarted;
    global $lastElapsed;

    $elapsed = microtime(true) - $timeStarted;

    if ($elapsed - $lastElapsed > 5) {
        if (0 === $download_size) {
            $progress = 0;
        } else {
            $progress = round($downloaded_size * 100 / $download_size);
        }

        echo sprintf(
            '    %2s%% - Elapsed: %.0fs - Remaining: %.0fs' . "\n",
            $progress,
            $elapsed,
            0 === $progress ? 0 : (100 * (microtime(true) - $timeStarted) / $progress) - $elapsed
        );

        $lastElapsed = $elapsed;
    }
}
