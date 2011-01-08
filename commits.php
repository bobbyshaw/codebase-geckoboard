<?php

 /*
  * Retrieve latest commits from codebase and output
  * json/xml ready for geckoboard widget.
  */

require_once('codebase.class.php'); 
require_once('response.class.php');

// Check that we have everything we need
if (!isset($_POST['format']) || !isset($_SERVER['PHP_AUTH_USER'])
        || !isset($_GET['account']) || !isset($_GET['username'])) {
    Header("HTTP/1.1 404 Page not found");
    exit(1);
}

$account = strtolower(trim($_GET['account']));
$username = strtolower(trim($_GET['username']));
$api = $_SERVER['PHP_AUTH_USER'];

// Init Codebase object with settings
$codebase = new Codebase($api, $account, $username);

// Get all projects
$projects = $codebase->getProjects();

$stats = array();

// For each project get repositories
foreach($projects as $project) {
    $repositories = $codebase->getRepositories($project->permalink);

    // For each respository, get 20 most recent commits
    foreach($repositories as $repository) {
        $commits = $codebase->getCommits($project->permalink, 
            $repository->permalink, $repository->{'last-commit-ref'});

        // For each commit, get committer name and count
        foreach ($commits as $commit) {
            if(isset($stats["{$commit->{'committer-name'}}"])) {
                $stats["{$commit->{'committer-name'}}"]++;
            } else {
                $stats["{$commit->{'committer-name'}}"] = 1;
            }
        }
    }
}

// Stats in descending order
arsort($stats);

// Convert to geckoboard RAG column and number format.
$stats = convertToGecko($stats);

$format = isset($_POST['format']) ? (int)$_POST['format'] : 0;
$format = ($format == 1) ? 'xml' : 'json';
$response_obj = new Response();
$response_obj->setFormat($format);

$response = $response_obj->getResponse($stats);
echo $response;


/*
 * Takes SimpleXML codebase array and converts to format
 * appropriate for geckoboard
 */
function convertToGecko($stats) {
    $gecko = array();

    foreach($stats as $key => $value) {
        $gecko[] = array( "value"  => $value, "text" => $key );
    }

    return array('item' => $gecko);
}
