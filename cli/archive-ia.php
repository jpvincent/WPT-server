<?php
$debug = true;
chdir('..');

include('common.inc');
require_once('./lib/S3.php');
require_once('./testStatus.inc');
require_once('har.inc.php');
set_time_limit(36000);

$crawls = array();

// bail if we are already running
$lock = Lock("Archive IA", false, 36000);
if (!isset($lock)) {
  echo "Archive process is already running\n";
  exit(0);
}

$UTC = new DateTimeZone('UTC');
$now = time();
$kept = 0;
$archiveCount = 0;
$deleted = 0;
$logFile = __DIR__ . '/archive-' . date("m") . '.log';

// Get the state of any current testing
if (!GetTestState()) {
  exit(0);
}

echo "\n**************************************************************************\n";
echo "                             Archiving\n";
echo "**************************************************************************\n\n";
logMsg("*** Archiving Tests", $logFile, true);

delTree('./results/archive');
if (!is_dir('./results/archive'))
    mkdir('./results/archive', 0777, true);
if (!is_dir('./results/archive/tmp'))
    mkdir('./results/archive/tmp', 0777, true);
if (!is_dir('./results/archive/har'))
    mkdir('./results/archive/har', 0777, true);
    
$bucket_base = 'httparchive_20';
$download_path = "http://www.archive.org/download/";

/*
*   Archive any tests that have not already been archived
*   We will also keep track of all of the tests that are 
*   known to have been archived separately so we don't thrash
*/  
$endDate = (int)gmdate('ymd');
$years = scandir('./results');
foreach( $years as $year )
{
    $yearDir = "./results/$year";
    if (is_numeric($year) && is_dir($yearDir) && $year != '.' && $year != '..') {
        $months = scandir($yearDir);
        foreach( $months as $month ) {
            $monthDir = "$yearDir/$month";
            if (is_dir($monthDir) && $month != '.' && $month != '..' && !is_file("$monthDir/archive.dat")) {
                $days = scandir($monthDir);
                foreach( $days as $day ) {
                    $dayDir = "$monthDir/$day";
                    if( is_dir($dayDir) && $day != '.' && $day != '..' && !is_file("$dayDir/archive.dat")) {
                        $groups = scandir($dayDir);
                        foreach ($groups as $group) {
                            $groupDir = "$dayDir/$group";
                            if( is_dir($groupDir) && $group != '.' && $group != '..' && !is_file("$groupDir/archive.dat")) {
                                $archiveCount = 0;
                                // force tests that have been around for more than 15 days to be considered complete
                                $date = DateTime::createFromFormat('ymd', "$year$month$day", $UTC);
                                $daytime = $date->getTimestamp();
                                $elapsed = max($now - $daytime, 0) / 86400;
                                if ($elapsed >= 1) {
                                    if (CheckGroup($groupDir, "$year$month{$day}_$group", $elapsed)) {
                                        $zipFile = "$year$month{$day}_$group.zip";
                                        $bucket = "$bucket_base{$year}_{$month}_{$day}_$group";
                                        if ($archiveCount && CombineArchives("./results/archive/", $zipFile, $bucket)) {
                                            file_put_contents("$groupDir/archive.dat", "$download_path$bucket/$zipFile");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// Indicate the HAR upload is finished if there are no pending tests
FinishHars();

echo "\n**************************************************************************\n";
echo "                        Archiving SQL Dumps\n";
echo "**************************************************************************\n\n";
logMsg("*** Archiving SQL Dumps", $logFile, true);
$updated = false;
$baseDir = '/var/www/httparchive.dev/downloads';
if (is_file("$baseDir/archived.json")) {
    $downloads = json_decode(file_get_contents("$baseDir/archived.json"), true);
}
if (!isset($downloads) || !is_array($downloads)) {
    $downloads = array();
    $updated = true;
}
foreach( scandir($baseDir) as $filename ){
    $matches = array();
    if (is_file("$baseDir/$filename") && 
        preg_match('/(?:httparchive_)(?:mobile_)?(.*)_[a-z\.]+/i', $filename, $matches) &&
        count($matches) > 1) {
        $modified = filemtime("$baseDir/$filename");
        $size = filesize("$baseDir/$filename");
        $bucket = "httparchive_downloads_" . $matches[1];
        if (array_key_exists($filename, $downloads) &&
            $downloads[$filename]['size'] != $size) {
            if (array_key_exists('bucket', $downloads[$filename]))
              DeleteArchive($filename, $downloads[$filename]['bucket']);
            else
              DeleteArchive($filename, 'httparchive_downloads');
            unset($downloads[$filename]);
        }
        if (!array_key_exists($filename, $downloads) &&
            (!$modified || ($modified < $now && $now - $modified > 86400))) {
            if (ArchiveFile("$baseDir/$filename", $bucket)) {
                $downloads[$filename] = 
                    array('url' => "{$download_path}$bucket/$filename", 
                    'size' => $size,
                    'modified' => $modified,
                    'verified' => false,
                    'bucket' => $bucket);
                $updated = true;
            }
        }
    }
}
if ($updated) {
    file_put_contents("$baseDir/archived.json", json_encode($downloads));
}

echo "\n**************************************************************************\n";
echo "                             Pruning\n";
echo "**************************************************************************\n\n";
foreach( $years as $year )
{
    $yearDir = "./results/$year";
    if (is_numeric($year) && is_dir($yearDir) && $year != '.' && $year != '..') {
        PruneDirectory($yearDir);
    }
}

echo "\n**************************************************************************\n";
echo "                             Pruning SQL dumps\n";
echo "**************************************************************************\n\n";
$updated = false;
foreach($downloads as $filename => &$download){
    if (!$download['verified']) {
        echo "$filename - Checking {$download['url']}...";
        if (URLExists($download['url'])) {
            echo "exists\n";
            $download['verified'] = true;
            $updated = true;
        } else {
            echo "missing\n";
        }
    }
    if ($download['verified'] && is_file("$baseDir/$filename")) {
        echo "$filename is in archive, deleting...\n";
        unlink("$baseDir/$filename");
    }
}
if ($updated) {
    file_put_contents("$baseDir/archived.json", json_encode($downloads));
}

echo "\nDone\n\n";
logMsg("*** Archiving pass complete", $logFile, true);
Unlock($lock);

/**
* Combine all of the individual tests into a larger zip
*/
function CombineArchives($destPath, $destFile, $bucket) {
    global $archiveCount;
    global $logFile;
    
    $zipFile = "$destPath$destFile";
    $ret = false;
    if ($archiveCount) {
        echo "\nCombining tests into $zipFile";
        file_put_contents($zipFile, ' ');
        $zipFile = realpath($zipFile);
        if (is_file($zipFile))
            unlink($zipFile);
        $command = 'zip -rDmj ' . $zipFile . ' ' . realpath('./results/archive/tmp');
        echo "\nExecuting $command\n";
        system($command, $zipResult);
        if ($zipResult == 0)
            $ret = true;
        
        // delete the temp files
        // this has to be done in a separate pass because they may not be written
        // to the archive until the zip is closed
        $files = scandir('./results/archive/tmp');
        foreach( $files as $file ) {
            $filePath = "./results/archive/tmp/$file";
            if( is_file($filePath) )
                @unlink($filePath);
        }
       
        // upload the zip file to the proper bucket
        if ($ret) {
            echo "\rCombined tests into $zipFile\n";
            $ret = ArchiveFile($zipFile, $bucket);
        } else {
            echo "\rFailed to Combine tests into $zipFile\n";
        }

        if (is_file($zipFile))
            unlink($zipFile);
    }
    
    delTree('./results/archive');
    if (!is_dir('./results/archive'))
        mkdir('./results/archive', 0777, true);
    if (!is_dir('./results/archive/tmp'))
        mkdir('./results/archive/tmp', 0777, true);
    if (!is_dir('./results/archive/har'))
        mkdir('./results/archive/har', 0777, true);

    $archiveCount = 0;
    return $ret;
}

function ArchiveFile($file, $bucket) {
    global $logFile;
    $ret = true;
    if (is_file($file)) {
        $size = filesize($file);
        if ($size) {
            $url = "http://s3.us.archive.org/$bucket/" . basename($file);
            echo 'Uploading ' . number_format($size / (1024 * 1024 * 1024), 3) . "GB to $url...\n";
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $url);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($c, CURLOPT_NOPROGRESS, false);
            curl_setopt($c, CURLOPT_HTTPHEADER, array(  'x-amz-auto-make-bucket: 1', 
                                                        'x-archive-queue-derive: 0', 
                                                        'x-archive-meta-collection: httparchive',
                                                        'authorization: LOW UZHEaSWYs3z5GQKx:IvWbccS08iaiWZvq'));
            curl_setopt($c, CURLOPT_PUT, true);
            curl_setopt($c, CURLOPT_INFILESIZE, $size);
            $fp = fopen($file, "r");
            curl_setopt($c, CURLOPT_INFILE, $fp);
            if (curl_exec($c) === false) {
                $ret = false;
                echo "Upload Failed!\n";
            } else {
                echo "Upload Succeeded\n";
            }
            curl_close($c);
            fclose($fp); 
        }
    }
    if ($ret)
      logMsg("S3: Uploading $file to $bucket OK", $logFile, true);
    else
      logMsg("S3: Uploading $file to $bucket FAILED", $logFile, true);
    return $ret;
}

/**
* Delete an archived item in case it exists already
*/
function DeleteArchive($file, $bucket) {
    $c = curl_init();
    $url = "http://s3.us.archive.org/$bucket/$file";
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($c, CURLOPT_NOPROGRESS, false);
    curl_setopt($c, CURLOPT_HTTPHEADER, array(  'x-archive-meta-collection: httparchive',
                                                'authorization: LOW UZHEaSWYs3z5GQKx:IvWbccS08iaiWZvq'));
    curl_setopt($c, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_exec($c);
    curl_close($c);
}

/**
* Recursively check within a given day
* 
* @param mixed $dir
* @param mixed $baseID
* @param mixed $archived
*/
function CheckGroup($dir, $baseID, $elapsed) {
  $archived = false;
  global $pendingTests;
  global $finishedTests;
  global $logFile;
  if (array_key_exists($baseID, $pendingTests) && $pendingTests[$baseID] > 0) {
    echo "\r$dir has {$pendingTests[$baseID]} pending tests                           \n";
    logMsg("$dir has {$pendingTests[$baseID]} pending tests", $logFile, true);
  } else {
    echo "\rChecking: $dir (started $elapsed days ago)\n";
    $tests = scandir($dir);
    $now = time();
    
    // see if all of the tests are complete
    $complete = true;
    if ($elapsed < 15) {
      foreach( $tests as $test ) {
        if( $test != '.' && $test != '..' ) {
          $testinfo = GetTestInfo("{$baseID}_$test");
          if ($testinfo) {
            if (!array_key_exists('completed', $testinfo) || !$testinfo['completed']) {
              $elapsedMinutes = max($testinfo['completed'] - $now, 0) / 60;
              if($elapsedMinutes < 60) {
                echo "$dir/$test - Not Complete\n";
                $complete = false;
                break;
              } else {
                // make SURE it isn't complete yet
                $status = GetTestStatus("{$baseID}_$test");
                if ($status['statusCode'] < 200) {
                  echo "$dir/$test - Not Complete\n";
                  $complete = false;
                  break;
                }
              }
            }
          }
        }
      }
    }
    
    if ($complete) {
      $count = count($tests);
      logMsg("$dir is complete, Archiving $count tests...", $logFile, true);
      echo "\r$dir is complete, Archiving                                  \n";
      foreach( $tests as $test ) {
        if( $test != '.' && $test != '..' ) {
          CheckTest("$dir/$test", "{$baseID}_$test");
        }
      }
      
      // upload HAR files for all of the known tests
      if (isset($finishedTests[$baseID]) && count($finishedTests[$baseID])) {
        UploadHARs($finishedTests[$baseID]);
      }
      $archived = true;
    } else {
      logMsg("$dir is NOT complete", $logFile, true);
      echo "\r$dir is NOT complete                                         \n";
    }
  }

  return $archived;
}

/**
* Upload HAR files for the provided list of test IDs
* 
* @param mixed $tests
*/
function UploadHARs($tests) {
  global $logFile;
  global $year;
  global $month;
  global $day;
  global $crawls;
  $count = count($tests);
  logMsg("HAR: Uploading $count Tests...", $logFile, true);
  foreach($tests as $index => $test) {
    $id = $test['id'];
    $crawl = $test['crawl'];
    if (isset($crawls[$crawl])) {
      $name = $crawls[$crawl]['name'];
      $testPath = './' . GetTestPath($id);
      $har = GenerateHAR($id, $testPath, ['bodies' => 1, 'run' => 'median', 'cached' => 0, 'pretty' => 1]);
      if (isset($har) && strlen($har)) {
        gz_file_put_contents("./results/archive/har/$id.har", $har);
        unset($har);
        $file = "./results/archive/har/$id.har.gz";
        if (is_file($file)) {
          $file = realpath($file);
          $remoteFile = "$name/$id.har.gz";
          $bucket = 'httparchive';
          if (gsUpload($file, $bucket, $remoteFile)) {
            logMsg("HAR: Uploaded $id to $bucket/$remoteFile", $logFile, true);
          } else {
            logMsg("HAR Error: Uploading $id to $bucket/$remoteFile", $logFile, true);
          }
          unlink($file);
        } else {
          logMsg("HAR file missing for $id", $logFile, true);
        }
      } else {
        logMsg("Error generating HAR for $id", $logFile, true);
      }
    } else {
      logMsg("Crawl name not available for crawl $crawl (test $id)", $logFile, true);
    }
  }
}

/**
* Upload the given file to Google Storage
* 
* @param mixed $file
* @param mixed $remoteFile
*/
function gsUpload($file, $bucket, $remoteFile) {
  $ret = false;
  $key = 'GOOGT4X7CFTWS2VWN2HT';
  $secret = 'SEWZTyKZH6dNbjbT2CHg5Q5pUh5Y5+iinj0yBFB4';
  $server = 'storage.googleapis.com';
  $s3 = new S3($key, $secret, false, $server);
  $metaHeaders = array();
  $requestHeaders = array();
  if ($s3->putObject($s3->inputFile($file, false), $bucket, $remoteFile, S3::ACL_PUBLIC_READ, $metaHeaders, $requestHeaders))
    $ret = true;
  return $ret;
}

function FinishHars() {
  return;
  global $logFile;
  global $crawls;
  $done = true;
  foreach ($crawls as $crawl) {
    if (!$crawl['finished'])
      $done = false;
  }
  if ($done) {
    $dir = './results/har';
    if (!is_dir($dir))
      mkdir($dir, 0777, true);
    foreach ($crawls as $crawl) {
      if (isset($crawl['name']) && strlen($crawl['name'])) {
        $name = $crawl['name'];
        $marker = "$dir/$name.done";
        if ($crawl['finished'] && !is_file($marker)) {
          logMsg("Marking crawl $crawl as done", $logFile, true);
          file_put_contents($marker, "");
          $file = realpath($marker);
          $remoteFile = "$name/done.txt";
          $bucket = 'httparchive';
          gsUpload($file, $bucket, $remoteFile);
        }
      }
    }
  }
}

/**
* Check the given log file for all tests that match
* 
* @param mixed $logFile
* @param mixed $match
*/
function CheckTest($testPath, $id)
{
    global $settings;
    global $archiveCount;
    global $logFile;
    $ret = true;

    echo "\rArchived:$archiveCount, Archiving:" . str_pad($id,45);
    
    // zip up the test
    $count = 0;
    $testZip = "./results/archive/tmp/$id.zip";
    if (is_file($testZip))
        unlink($testZip);
    $zip = new ZipArchive();
    if ($zip->open($testZip, ZIPARCHIVE::CREATE) === true) {
        $files = scandir($testPath);
        foreach ($files as $file) {
            $filePath = "$testPath/$file";
            if (is_file($filePath)) {
                if (strpos($file, '_doc.jpg') === false && strpos($file, '_render.jpg') === false) {
                    $count++;
                    $zip->addFile($filePath, $file);
                }
            } elseif ($file != '.' && $file != '..' && is_dir($filePath)) {
                $zip->addEmptyDir($file);
                $dirFiles = scandir($filePath);
                foreach ($dirFiles as $dirFile) {
                    $dirFilePath = "$filePath/$dirFile";
                    if (is_file($dirFilePath)) {
                        $count++;
                        if( !$zip->addFile($dirFilePath, "$file/$dirFile"))
                            $ret = false;
                    }
                }
            }
        }
        $zip->close();

        // add it to the archive zip
        if ($count && $ret) {
            $archiveCount++;
        } else {
          logMsg("Error zipping test $id", $logFile, true);
        }
    } else {
        $ret = false;
    }
    
    return $ret;
}

/**
* Recursively scan for archived directories
*/
function PruneDirectory($dir, $depth = 0) {
    echo "\rChecking $dir                 ";
    $files = scandir($dir);
    $children = array();
    foreach( $files as $file) {
        if ($file != '.' && $file != '..' && is_dir("$dir/$file")) {
            $children[] = $file;
        }
    }
    if (count($children)) {
        $archiveFile = "$dir/archive.dat";
        if (is_file($archiveFile)) {
            $archive = file_get_contents($archiveFile);
            if (ArchiveExists($archive, $archiveFile)) {
                echo "\rArchive for $dir exists, deleting children...\n";
                foreach( $children as $child ) {
                    delTree("$dir/$child");
                }
            } else {
                echo "\rArchive for $dir is missing - $archive\n";
            }
        } else {
            if ($depth < 3) {
                foreach( $children as $child ) {
                    PruneDirectory("$dir/$child", $depth+1);

                }
            }
        }
    }
    // prune empty directories on the way back up
    @rmdir($dir);
}

/**
* See if the given archive exists
*/
function ArchiveExists($url, $archiveFile) {
    $exists = false;
    
    if (is_file("$archiveFile.valid")) {
        $exists = true;
    } else {
        $exists = URLExists($url);
        if ($exists) {
            file_put_contents("$archiveFile.valid", '');
        }
    }

    return $exists;
}

function URLExists($url) {
    $exists = false;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    if ($result !== false) {
        $split = strpos($result, '200 OK');
        if ($split !== false) {
            $result = substr($result, $split);
            if (preg_match('/Content-Length:([ 0-9]*)/i', $result, $matches)) {
                $size = (int)trim($matches[1]);
                if ($size > 4096) {
                    $exists = true;
                }
            }
        }
    }
    curl_close($curl);
    return $exists;
}

function GetTestState() {
  $ret = false;
  $gMysqlServer = "localhost";
  $gMysqlDb = "httparchive";
  $gMysqlUsername = "root";
  $gMysqlPassword = "HTTP@rchive";
  $statusTables = array("statusdev", "statusmobile");
  global $pendingTests;
  global $finishedTests;
  global $crawls;

  // get counts for test groups that are in some form of processing
  $pendingTests = array();
  $finishedTests = array();

  echo "Checking Pending Tests...\n";
  
  $db = mysql_connect($gMysqlServer, $gMysqlUsername, $gMysqlPassword);
  if ( mysql_select_db($gMysqlDb) ) {
    foreach ($statusTables as $table) {
      $result = mysql_query("SELECT wptid,status,crawlid FROM $table;", $db);
      if ($result !== false) {
        $ret = true;
        while ($row = mysql_fetch_assoc($result)) {
          $id = $row['wptid'];
          $status = $row['status'];
          $crawl = $row['crawlid'];
          if (preg_match('/^([A-Z0-9]+_[A-Z0-9]+)_[A-Z0-9]+$/', $id, $matches)) {
            $group = $matches[1];
            if ($status < 4) {
              if (!isset($pendingTests[$group]))
                $pendingTests[$group] = 0;
              $pendingTests[$group]++;
            }
            // Keep track of "completed" tests (with no processing error)
            if ($status == 4) {
              if (!isset($finishedTests[$group]))
                $finishedTests[$group] = array();
              $finishedTests[$group][] = array('id' => $id, 'crawl' => $crawl);
            }
            if (!isset($crawls[$crawl]))
              $crawls[$crawl] = array('id' => $crawl);
          }
        }        
      }
    }
    if (count($crawls)) {
      foreach($crawls as $crawl => &$crawl_data) {
        $result = mysql_query("SELECT label,location,finishedDateTime FROM crawls WHERE crawlid=$crawl;", $db);
        if ($result !== false) {
          if ($row = mysql_fetch_assoc($result)) {
            $type = $row['location'];
            if ($type == 'IE8')
              $type = 'desktop';
            elseif ($type == 'iphone4')
              $type = 'mobile';
            $crawl_data['name'] = str_replace(' ', '_', $type) . '-' . str_replace(' ', '_', $row['label']);
            $crawl_data['finished'] = isset($row['finishedDateTime']) ? true : false;
          }
        }
        echo "\n";
      }
    }
    mysql_close($db);
  }
  ksort($pendingTests);

  echo "Current crawls:\n";  
  if (count($crawls)) {
    foreach($crawls as $crawl => &$crawl_data) {
      echo "  {$crawl_data['id']}: {$crawl_data['name']} - ";
      echo $crawl_data['finished'] ? "FINISHED\n" : "Running\n";
    }
  } else {
    echo "  NONE\n";  
  }
  
  if ($ret) {
    echo "\nPending Tests in each group:\n";
    foreach($pendingTests as $group => $count) {
      echo "  $group: $count\n";
    }
    echo "\nFinished Tests in each group:\n";
    foreach($finishedTests as $group => $tests) {
      $count = count($tests);
      echo "  $group: $count\n";
    }
  } else {
    echo "\nFailed to query pending tests\n";
  }
  
  return $ret;
}
?>
