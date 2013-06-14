<?php
if(!class_exists("BGLog")) {

require_once("Logger.php");

class BGLog {
    private static $sharedInstance	= null;
    private static $key     		= null;
    private static $type    		= null;
    private static $logger  		= null;

    public function sharedInstance($key="", $type="web") {
        if(self::$sharedInstance == null) { self::$sharedInstance = new BGlog($type); }

        if($key!="" && self::$key != $key)  { self::$key = $key; }
        if($type!=null && self::$type != $type) { self::$logger = Logger::getLogger(self::$type=$type); }

        return self::$sharedInstance;
    }

    private function __construct($type) {
        Logger::configure("/Users/dhkim/Sites/gboard/application/third_party/log4php/log4php.properties");
        self::$logger = Logger::getLogger(self::$type=$type);
    }

    public function trace($log){ self::$logger->trace("[".self::$key."] $log"); }
    public function debug($log){ self::$logger->debug("[".self::$key."] $log"); }
    public function info($log) { self::$logger->info("[".self::$key."] $log"); }
    public function warn($log) { self::$logger->warn("[".self::$key."] $log"); }
    public function error($log){ self::$logger->error("[".self::$key."] $log"); }
    public function fatal($log){ self::$logger->fatal("[".self::$key."] $log"); }
}

}
?>
