<?php 
/**
 * Simple script that that uses Jenkins' API to get a list of jobs and their status from a given jenkins instance. 
 * The status for each is stored in an sqlite database along with the time for when it was checked.
 * 
 * Script assumes that no authentication is needed to reach the Jenkins URL
 * 
 * Script uses a third party utility for making and printing tables on console
 * 
 * @author Bayode Aderinola <bayodesegun@gmail.com>
 */

// Import third party utility for making and printing tables on console
require_once 'Table.php';									

// Sorry, we're gonna prompt you for the url until you input something. Kindly input a valid URL, please :)
$jenkinsInstanceUrl = null;
while (!$jenkinsInstanceUrl) {
	if (PHP_OS == 'WINNT') {
	  echo '$ Please enter Jenkins instance URL: ';
	  $jenkinsInstanceUrl = stream_get_line(STDIN, 1024, PHP_EOL);
	} else {
	  $jenkinsInstanceUrl = readline('$ Please enter Jenkins instance URL: ');
	}
}

// "Calculate" the instance API URL from the instance URL, in JSON format
$jenkinsInstanceApiUrl = $jenkinsInstanceUrl . "/api/json";
echo "Thank you! Your Jenkins instance API URL (json format): $jenkinsInstanceApiUrl. Reading URL...\n";

// "get" the instance API URL, which contains the jobs information. Not using curl here as it's a simple GET operation.
$apiPage = @file_get_contents($jenkinsInstanceApiUrl);
$timeChecked = null;		
$apiPageObject = null;

if ($apiPage === FALSE) {
	echo "Error while reading API URL - please check that correct URL is given and try again.";
	exit;
}
else {
	echo "Successfully read URL.\n";
	$apiPageObject = json_decode ($apiPage);
	$timeChecked = date('Y-m-d H:i:s');						
}

// extract jobs from the api page
$jobs = $apiPageObject->jobs;

// Create db connection, create the jobs table and save jobs data in table
$jobsDb = new \PDO('sqlite:jobs.sqlite3');

$jobsDb->exec("CREATE TABLE IF NOT EXISTS jobs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT, 
                    name TEXT, 
                    status TEXT, 
                    time_checked DATETIME)");


echo "Saving jobs data from URL in database...\n";

foreach ($jobs as $job) {
	$jobsDb->prepare("INSERT INTO jobs (name, status, time_checked) values (:name, :status, :time)")
			->execute([
			'name' => $job->name, 
			'status' => $job->color, 
			'time' => $timeChecked 			
	]);
}

// Print out the saved table for user to see!
echo "Successfully saved jobs data in database 'jobs.sqlite3' on table jobs. Data on table:\n";
$table = $jobsDb->query("SELECT * from jobs");

$consoleTable = new Console_Table();
$consoleTable->setHeaders(array('id', 'Name', 'Status', 'Time Checked'));

foreach ($table as $row) {
	$consoleTable->addRow(array($row['id'], $row['name'], $row['status'], $row['time_checked']));
}

echo $consoleTable->getTable();

// close db connection
$jobsDb = null;


