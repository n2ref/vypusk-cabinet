<?php
namespace Cabinet;


/**
 * Class Threads
 */
class Threads {

    protected $php_path     = '';
    protected $host         = '';
    protected $cache        = '';
    protected $file_execute = '';
    protected $plugin       = '';
    protected $method       = '';
    protected $arguments    = [];


    /**
     * @param  string $php_path
     * @param  string $host
     * @param  string $cache
     * @throws \Exception
     */
    public function __construct($php_path, $host, $cache = '/tmp') {

        if ( ! function_exists('exec')) {
            throw new \Exception("function 'exec' not found");
        }

        $this->host         = $host;
        $this->cache        = $cache;
        $this->file_execute = DOC_ROOT . 'index.php';

        if ($php_path) {
            $this->php_path = $php_path;
        } else {
            $system_php_path = exec('which php');
            if ( ! empty($system_php_path)) {
                $this->php_path = $system_php_path;
            } else {
                throw new \Exception('php not found');
            }
        }
    }


    /**
     * @param string $plugin
     */
    public function setPlugin($plugin) {
        $this->plugin = $plugin;
    }


    /**
     * @param string $method
     */
    public function setMethod($method) {
        $this->method = $method;
    }


    /**
     *
     */
    public function setArguments() {
        $this->arguments = func_get_args();
    }


    /**
     * @return bool
     */
    public function start() {

        if ( ! empty($this->file_execute)) {
            $arguments = '';
            if ( ! empty($this->arguments)) {
                foreach ($this->arguments as $argument) {
                    $arguments .= '--argument ' . escapeshellarg($argument);
                }
            }
            $out_file = realpath($this->cache) . '/cabinet_' . $this->plugin . '_' . $this->method . '.out';
            $cmd      = sprintf(
                '%s %s --plugin %s --method %s --section %s %s > %s 2>&1 & echo $!',
                $this->php_path,
                $this->file_execute,
                $this->plugin,
                $this->method,
                $this->host,
                $arguments,
                $out_file
            );
            exec($cmd);

            return true;

        } else {
            return false;
        }
    }
}