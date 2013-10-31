<?php
/* SingleResourceGenerator
 *
 * MIT licence
 */

namespace LukasGrebe\Tools;

class SingleResourceGenerator
{
    static function init($cacheSeconds, $sourceDir = '.')
    {
        //Browser Caching
        header('Cache-Control: public, max-age=' . 3600*12);
        header("Connection: Keep-alive");
        

        // 2. check client cache freshness
        if (isset($headers['If-Modified-Since'])) 
        {
          $clientCache = strtotime($headers['If-Modified-Since']);
        }else{
          $clientCache = 0;
        }

        $lastModified = self::latestFileModification($sourceDir);
        if($lastModified > $clientCache)
        {
          //cache invalid
          header('Last-Modified: '.gmdate("D, d M Y H:i:s", $lastModified)." GMT", true, 200);
        }else{
          //valid cache. goodby
          header('Last-Modified: '.gmdate("D, d M Y H:i:s", $lastModified)." GMT", true, 304);  
          exit();
        }

        //No or invalid cache. Build a single gziped Response with all ressources to maximize mobile network TCP connection
        ob_start("ob_gzhandler");

        //Shut it down.
        register_shutdown_function(function()
            {
                //Send Response.
                if(ob_get_length()) 
                { 
                    header("Content-Length: ".ob_get_length()); 
                    ob_end_flush(); 
                    exit(); 
                }
            }
        );
    } 

    static private function latestFileModification($sourceDir)
    {
      $fileTimes = array_map("filemtime", iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator('.'))));
      arsort($fileTimes);
      return reset($fileTimes);
    }

    static function dataUri($resource, $dataUriMime = false)
    {
        if($dataUriMime !== false)
        {
            $dataUriMime = 'data:' . $dataUriMime . ';base64,';
        }
        return $dataUriMime . base64_encode(file_get_contents($resource));
    }

}
