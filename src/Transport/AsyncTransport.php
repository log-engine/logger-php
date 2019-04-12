<?php

namespace LogEngine\Transport;


use LogEngine\Exceptions\LogEngineException;

/**
 * This transport collects log data until the end of processing.
 * It sends data executing shell curl and sending it to background (Asynchronous mode).
 */
class AsyncTransport extends AbstractApiTransport
{
    /**
     * CURL command path.
     *
     * @var string
     */
    protected $curlPath = 'curl';

    /**
     * String template for curl error message.
     *
     * @var string
     */
    const ERROR_CURL = 'Command returned an error. [Command: "%s"] [Return code: %d] [Message: "%s"]';

    /**
     * String template for curl success message.
     *
     * @var string
     */
    const SUCCESS_CURL = 'Command sent. [Command: "%s"]';

    /**
     * AsyncTransport constructor.
     *
     * @param null|string $url
     * @param null|string $apiKey
     * @param array $options
     * @throws LogEngineException
     */
    public function __construct($url = null, $apiKey = null, array $options = array())
    {
        parent::__construct($url, $apiKey, $options);

        if (!function_exists('exec')) {
            throw new LogEngineException("PHP function 'exec' is not available, is it disabled for security reasons?");
        }

        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            throw new LogEngineException('Exec transport is not supposed to work on Windows OS');
        }
    }

    /**
     * List of available transport's options with validation regex.
     *
     * ['param-name' => 'regex']
     *
     * @return mixed
     */
    protected function getAllowedOptions()
    {
        return array_merge(parent::getAllowedOptions(), array(
            'curlPath' => '/.+/',
        ));
    }

    /**
     * Send a portion of the load to the remote service.
     *
     * @param string $data
     * @return mixed
     */
    public function send($data)
    {
        $cmd = "$this->curlPath -X POST";

        foreach ($this->getApiHeaders() as $name => $value) {
            $cmd .= " --header \"$name: $value\"";
        }

        $escapedData = $this->escapeArg($data);

        $cmd .= " --data '$escapedData' '".$this->config->getUrl()."' --max-time 5";
        if ($this->proxy) {
            $cmd .= " --proxy '$this->proxy'";
        }

        // return immediately while curl will run in the background
        $cmd .= ' > /dev/null 2>&1 &';

        $output = array();
        $r = exec($cmd, $output, $result);

        if ($result !== 0) {
            // curl returned some error
            error_log(date('Y-m-d H:i:s')." - [Error] [".get_class($this)."] $result ");
        }
    }

    /**
     * Escape character to use in the CLI.
     *
     * @param $string
     * @return mixed
     */
    protected function escapeArg($string)
    {
        // http://stackoverflow.com/a/1250279/871861
        return str_replace("'", "'\"'\"'", $string);
    }
}