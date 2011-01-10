# Codebase Integration with Geckoboard

Retrieve information from [Codebase](http://www.codebasehq.com) and construct feed url for [Geckoboard](geckoboard.com). Caches list of repositories with a TTL of 1 hour to try and cut down on the requests made.

* Author : Tom Robertshaw (<http://tomrobertshaw.net>)

## Configuration

Written as a service, there is no config file and all data should be handed through feed url together with API key specified when creating a widget in Geckoboard.

## Global Commit Statistics

Visit **commits/$account/$username** for commit statistics from all projects, repositories and branches.  The Codebase API currently returns the latest 20 commits from each.

![Top 3 committers from all projects](http://tomrobertshaw.net/codebase_geckoboard/images/codebase-commits.png)

## Project Commit Statistics

Visit **commits/$project/$account/$username** for commits statistics on particular project (N.B. use permalink).  Again, this limits to 20 most recent commits.

![Top 3 committers from specific project](http://tomrobertshaw.net/codebase_geckoboard/images/codebase-project-commits.png)

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


## I just want to play

This service is currently hosted at http://tomrobertshaw.net/codebase_geckoboard/ if you want to test it out.
