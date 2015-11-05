<?php

namespace DRI\SugarCRM\Bootstrap;

/**
 * @author Emil Kilhage
 */
class Bootstrap
{
    /**
     * @var array
     */
    private static $possibleSubDirs = array (
        'docroot',
        'src',
    );

    /**
     * @param string $dir
     */
    public static function addPossibleSubDir($dir)
    {
        self::$possibleSubDirs[] = $dir;
    }

    /**
     * @param null|string $path
     * @throws \Exception
     */
    public static function boot($path = null)
    {
        self::ensureSugarPath($path);
        self::bootSugar();
        self::initDatabase();
        self::pauseTracker();
        self::disableLogging();
        self::silenceLicenseCheck();
    }

    /**
     * @param null|string $path
     * @throws \Exception
     */
    public static function ensureSugarPath($path = null)
    {
        if (!self::isSugarPath()) {
            $path = self::findSugarPath($path);

            chdir($path);
        }
    }

    /**
     * @param null|string $path
     * @return string
     * @throws \Exception
     */
    public static function findSugarPath($path = null)
    {
        if ($path === null) {
            $path = getcwd();
        }

        foreach (self::$possibleSubDirs as $possibleSubDir) {
            $sugarVersionPath = "$path/$possibleSubDir/sugar_version.php";
            if (file_exists($sugarVersionPath)) {
                $path = "$path/{$possibleSubDir}";
                break;
            }
        }

        if (!file_exists("$path/sugar_version.php")) {
            throw new \Exception('Unable to find sugar base path');
        }

        return $path;
    }

    /**
     * @return bool
     */
    public static function isSugarPath()
    {
        $path = getcwd();
        return file_exists("$path/sugar_version.php");
    }

    /**
     *
     */
    public static function bootSugar($path = null)
    {
        if (!defined('sugarEntry')) {
            define('sugarEntry', true);
        }

        global $sugar_config;
        global $sugar_flavor;
        global $locale;
        global $db;
        global $beanList;
        global $beanFiles;
        global $moduleList;
        global $modInvisList;
        global $adminOnlyList;
        global $modules_exempt_from_availability_check;

        global $app_list_strings;
        global $app_strings;
        global $mod_strings;

        require_once 'include/entryPoint.php';

        // Scope is messed up due to requiring files within a function
        // We need to explicitly assign these variables to $GLOBALS
        foreach (get_defined_vars() as $key => $val) {
            $GLOBALS[$key] = $val;
        }
    }

    /**
     *
     */
    public static function silenceLicenseCheck()
    {
        $_SESSION['VALIDATION_EXPIRES_IN'] = 'valid';
    }

    /**
     *
     */
    public static function initDatabase()
    {
        $GLOBALS['db'] = \DBManagerFactory::getInstance();
    }

    /**
     *
     */
    public static function pauseTracker()
    {
        \TrackerManager::getInstance()->pause();
    }

    /**
     *
     */
    public static function disableLogging()
    {
        $GLOBALS['log'] = \LoggerManager::getLogger();
        $GLOBALS['log']->setLevel('fatal');
    }
}
