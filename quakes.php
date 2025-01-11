<?php

/**
 *   @package QuakesGR
 *   @author Alexandros Anastasiadis
 *   @category Webscraper that fetching latest earthquakes in Greece.
 *   @link https://github.com/tuxanasgr/quakesgr
 *   
 */


/**
 *  Put your headers here according to
 *      your server configuration
 */

header("Content-type: application/json; charset=utf-8");
header('Access-Control-Allow-Origin: *');




/**
 *    WEB SCRAPER STARTS HERE 
 */



$quakesList = [];

$baseURL  = "https://www.seismos.gr";
$scrapURL = $baseURL . "/seismoi-lista";


$html = file_get_contents($scrapURL);
$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

if ($html === false) {
    die("Failed to retrieve the webpage.");
}

$dom = new DOMDocument;

libxml_use_internal_errors(true);

if (!$dom->loadHTML($html)) {
    die("Failed to load HTML content.");
}

$xpath = new DOMXPath($dom);

$query = "//div[contains(@class, 'list-group')]//a[contains(@class, 'list-group-item')]";
$items = $xpath->query($query);


if ($items->length > 0) {

    foreach ($items as $item) {

        $nodes = [
            'title' => $xpath->query('.//h4', $item),
            'magnitude' => $xpath->query('.//span', $item),
            'timeago' => $xpath->query('.//p', $item),
        ];


        // explode titles string
        $titleParts = [
            'title' => explode(" - ", $nodes['title']->item(0)->textContent)[1],
            'date' => explode(" ", $nodes['title']->item(0)->textContent)[0],
            'time' => explode(" ", $nodes['title']->item(0)->textContent)[1],
        ];


        $quakesList[] = [
            'title' => $titleParts['title'],
            'link' =>  $baseURL . $item->getAttribute('href'),
            'magnitude' =>  $nodes['magnitude']->item(0)->textContent,
            'timeago' => $nodes['before']->item(0)->textContent,
            'date' =>  $titleParts['date'],
            'time' => $titleParts['time']
        ];
    }
}


/**
 *   WEB SCRAPER ENDS HERE
 */




$response = [
    'success' => true,
    'data' => [
        'count' => count($quakesList),
        'quakes_list' => $quakesList
    ]
];


echo json_encode($response);
