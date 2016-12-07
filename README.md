# simple_console_app
A simple script that that uses Jenkins' API to get a list of jobs and their status from a given Jenkins instance. 
The status for each is stored in an sqlite database along with the time for when it was checked.

* Script assumes that no authentication is needed to reach the Jenkins URL
* Script uses a third party utility, Console Table, for making and printing tables on console
