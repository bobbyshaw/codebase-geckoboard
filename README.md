# Codebase Integration with Geckoboard

* Author : Tom Robertshaw

## Description

Retrieve information from [Codebase](http://www.codebasehq.com) and construct feed url for [Geckoboard](geckoboard.com).

## Functionality 

Visit commits/$account/$username for commit statistics from all projects, repositories and branches.  The Codebase API currently returns the latest 20 commits from each.

Visit commits/$project/$account/$username for commits statistics on particular project (N.B. use permalink).

## Creating Geckoboard Custom Widget

* Go to your Geckoboard dashboard and click "Add Widget".
* Select the "Custom Widgets" tab.
* Choose the "RAG Column & Numbers" widget.
* Enter URL data feed.  e.g. BASE_URL/commits/project/account/username
* Copy and past API key from Codebase account.
* Select Widget type "Custom".
* Either feed format will work.
* Choose reload time (probably does not need to be that often! Recommend 60 mins)
* Label it.
* Woo hoo, we're done.
