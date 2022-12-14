<?php

namespace Feeds\Response;

use Feeds\App;
use Feeds\Request\Request;

App::check();

abstract class Action
{
    /**
     * Array of headers.
     *
     * @var Header[]
     */
    private array $headers = array();

    /**
     * Add default headers.
     *
     * @param int $status               HTTP status code.
     * @return void
     */
    protected function __construct(
        private readonly int $status
    ) {
        $this->add_header("X-Software", "bfren/ccf");
    }

    /**
     * Add a header to the response.
     *
     * @param string $key               Header key.
     * @param string $value             Header value.
     * @return void
     */
    protected function add_header(string $key, string $value)
    {
        $this->headers[$key] = new Header($key, $value);
    }

    /**
     * Add debug headers if $debug is set in Request.
     *
     * @return bool                     True if the debug headers have been added.
     */
    protected function add_debug_headers(): bool
    {
        if (Request::$debug) {
            $this->add_header("Content-Type", "text/plain");
            return true;
        }

        return false;
    }

    /**
     * Send headers to the response.
     *
     * @return void
     */
    public function send_headers()
    {
        foreach ($this->headers as $header) {
            header(sprintf("%s: %s", $header->key, $header->value), $this->status);
        }
    }

    /**
     * Execute action.
     *
     * @return void
     */
    public abstract function execute(): void;
}
