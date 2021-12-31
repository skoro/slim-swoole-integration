<?php

namespace Slim\Swoole\FileWatchers;

use Slim\Swoole\Exceptions\FileWatcherException;
use Slim\Swoole\FileWatcher;
use RuntimeException;

class InotifyWatcher implements FileWatcher
{
    /** @var resource */
    private $inotify;

    /** @var array<int, string>  */
    private array $pathToWd = [];

    public function __construct()
    {
        if (! function_exists('inotify_init')) {
            throw new FileWatcherException('Please install "inotify" extension.');
        }

        $resource = inotify_init();
        if (! is_resource($resource)) {
            throw new RuntimeException('Cannot initialize inotify extension.');
        }

        if (! stream_set_blocking($resource, false)) {
            throw new RuntimeException('Cannot set non-blocking mode for inotify resource.');
        }

        $this->inotify = $resource;
    }

    public function addFilePath(string $path): void
    {
        $paths = is_dir($path) ? $this->getPathFiles($path) : array($path);

        foreach ($paths as $checkPoint) {
            $wd = inotify_add_watch($this->inotify, $checkPoint, IN_MODIFY);
            $this->pathToWd[$wd] = $checkPoint;
        }
    }

    /**
     * @return string[]
     */
    private function getPathFiles(string $path): array
    {
        if (($files = scandir($path)) === false) {
            return [];
        }

        $paths = [$path];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filename = $path . '/' . $file;
            if (is_dir($filename)) {
                $paths = array_merge($paths, $this->getPathFiles($filename));
            }
        }

        return $paths;
    }

    /**
     * @return string[] The list of changed files.
     */
    public function readChanges(): array
    {
        $changed = [];
        $events = inotify_read($this->inotify);
        if (is_array($events)) {
            foreach ($events as $event) {
                $wd = $event['wd'] ?? null;
                if ($wd && isset($this->pathToWd[$wd])) {
                    $changed[] = $this->pathToWd[$wd];
                }
            }
        }
        return $changed;
    }
}