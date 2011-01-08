<?php

/*
 * Communicate with Codebase API to get info on projects.
 */
class Codebase {

    private $_apiurl = "http://api3.codebasehq.com";
    private $_apikey;
    private $_account;
    private $_username;
  
    function Codebase($apikey, $account, $username) {
        $this->_apikey = $apikey;
        $this->_account = $account;
        $this->_username = $username;
    }

    function getProjects() {
        $url = "projects";
        return $this->makeRequest($url);
    }
   

    function getRepositories($project) {
        $url = "$project/repositories";
        return $this->makeRequest($url);
    } 

    function getCommits($project, $repository, $ref) {
        $url = "$project/$repository/commits/$ref";
        return $this->makeRequest($url);
    }

    function makeRequest($url) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL,"$this->_apiurl/$url");
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($c, CURLOPT_USERPWD, "$this->_account/$this->_username:$this->_apikey");
        curl_setopt($c, CURLOPT_HTTPHEADER, array("Accept: application/xml", "Content-type: application/xml"));
        $result = curl_exec($c);
        curl_close($c);
        return simplexml_load_string($result);
    }

}
