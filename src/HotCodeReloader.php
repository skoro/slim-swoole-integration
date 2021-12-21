<?php

namespace Slim\Swoole;

use Swoole\Server;

class HotCodeReloader
{
    private int $timerId;
    private bool $running = false;

    public function __construct(
        private FileWatcher $fileWatcher,
        private Server $server,
        private int $msDelay = 1000
    ) { }

    public function checkAndReload(): void
    {
        if ($this->hasChanges()) {
            $this->reload();
        }
    }

    protected function hasChanges(): bool
    {
        return count($this->fileWatcher->readChanges()) > 0;
    }

    protected function reload(): bool
    {
        return $this->server->reload();
    }

    public function start(): void
    {
        if (! $this->running) {
            $this->timerId = $this->server->tick($this->msDelay, [$this, 'checkAndReload']);
            $this->running = true;
        }
    }

    public function stop(): void
    {
        if ($this->running) {
            $this->server->clearTimer($this->timerId);
            $this->running = false;
        }
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}