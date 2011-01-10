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

$stats = array();

// If cached, we can get repository information without making any requests
if(isCached($account, $username)) {
    $repositories = readCache($account, $username, $api);

    // For each respository, get 20 most recent commits
    foreach($repositories as $repository) {
        $commits = $codebase->getCommits($repository->project,
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
} else {

    $projects = array();
    if(isset($_GET['project'])) {
        // Specify project
        $projects = array($codebase->getProject($_GET['project']));
    } else {
        // Get all projects
        $projects = $codebase->getProjects();
    }

    // Create list of repos which are then cached.
    $all_repositories = new SimpleXMLElement("<repos></repos>");

    // For each project get repositories
    foreach($projects as $project) {
        $repositories = $codebase->getRepositories($project->permalink);

        // For each respository, get most recent commits
        foreach($repositories as $repository) {
            // When reading repos from cache, we need to be able to
            // tell which project a repo is from.
            $repository->addChild("project", $project->permalink);

            // Add repo to list of all repos
            SimpleXMLElement_append($all_repositories, $repository);

            // Get latest 20 commits
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

    // Write to file
    writeCache($all_repositories, $account, $username, $api);
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

/*
 * Write repositories to file to save requests next time
 */
function writeCache($repositories, $account, $username, $api) {
    $filename = getCacheFilename($account, $username);
    //$repositories->asXML($filename);
    file_put_contents($filename,encrypt($repositories->asXML(), $api));
}

/*
 * Check if cached file exists.
 * Cache lasts an hour
 */
function isCached($account, $username) {
    $filename = getCacheFilename($account, $username);
    if (file_exists($filename)) {
        // If over an hour old, don't trust it.
        if (time() - filemtime($filename) > 3600) {
            return false;
        } else {
            return true;
        } 
    }

    return false;
}

/*
 * If cache file exists, return it unserialized
 */
function readCache($account, $username, $api) {
    if (isCached($account, $username)) {
        $filename = getCacheFilename($account, $username);
        return simplexml_load_string(decrypt(file_get_contents($filename), $api));
    }

    return false;
}

/*
 * Create filename based on request
 */
function getCacheFilename($account, $username) {
    $filename = "cache/";
    
    if (isset($_GET['project'])) {
        $filename .= $_GET['project'] . "-";
    }

    $filename .= "$account-$username.txt";  

    return $filename;
}

/*
 * Add one SimpleXML Object as a child of another
 * Thanks to kweij at lsg dot nl on 
 * http://php.net/manual/en/class.simplexmlelement.php
 */
function SimpleXMLElement_append($key, $value) {
   // check class
   if ((get_class($key) == 'SimpleXMLElement')
             && (get_class($value) == 'SimpleXMLElement')) {
       // check if the value is string value / data
       if (trim((string) $value) == '') {
           // add element and attributes
           $element = $key->addChild($value->getName());
           foreach ($value->attributes() as $attKey => $attValue) {
               $element->addAttribute($attKey, $attValue);
           }
           // add children
           foreach ($value->children() as $child) {
               SimpleXMLElement_append($element, $child);
           }
       } else {
           // set the value of this item
           $element = $key->addChild($value->getName(), trim((string) $value));
       }
   } else {
       // throw an error
       throw new Exception('Wrong type: expected SimpleXMLElement');
   }
}

/*
 * Keep cache unreadable from prying eyes that gain access to the server.
 * Encrypt xml string with api key. Probably overkill.
 */
function encrypt($text, $salt) { 
    return mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB,
        mcrypt_create_iv(
            mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
} 

/*
 * Decrypt cache, will only work if correct API key is used
 */
function decrypt($text, $salt) { 
    return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB,
        mcrypt_create_iv(
            mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)); 
} 

