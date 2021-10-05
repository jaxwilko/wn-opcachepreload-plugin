<?php

namespace JaxWilko\OpcachePreload\Classes;

use FilesystemIterator as FI;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RegexIterator;

class Preloader
{
    const OUTPUT_NONE = 0;
    const OUTPUT_OKAY = 1;
    const OUTPUT_WARN = 2;
    const OUTPUT_ERR  = 3;

    protected static $self = null;

    protected $basePath = null;

    protected $paths = [];
    protected $ignore = [];

    protected $iteratorFlags = FI::KEY_AS_PATHNAME | FI::CURRENT_AS_FILEINFO | FI::SKIP_DOTS;

    protected $options = [];

    public static function instance(): Preloader
    {
        if (!static::$self) {
            static::$self = new static();
        }

        return static::$self;
    }

    public function setBasePath(string $path): Preloader
    {
        $this->basePath = rtrim($path, '/') . '/';

        return $this;
    }

    public function setPaths($path): Preloader
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->setPaths($p);
            }
            return $this;
        }

        $this->paths[] = ($this->basePath ?? '') . $path;

        return $this;
    }

    public function ignore($path): Preloader
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->ignore($p);
            }

            return $this;
        }

        $this->ignore[] = $path;

        return $this;
    }

    public function options(array $options = []): Preloader
    {
        $this->options = $options;

        return $this;
    }

    public function run()
    {
        if ($this->options['dry']) {
            $this->setErrorHandler();
        }

        foreach ($this->paths as $path) {
            $iterator = new \RegexIterator(
                $this->getDirectoryIterator($path),
                '/.+(\.php$)/i',
                RegexIterator::GET_MATCH
            );

            foreach ($iterator as $filePath => $fileInfo) {
                foreach ($this->ignore as $ignore) {
                    if (preg_match($ignore, $filePath)) {
                        $this->output('Ignored', static::OUTPUT_WARN, $filePath);
                        continue 2;
                    }
                }
                $this->output('Loading', static::OUTPUT_OKAY, $filePath);
                if (!$this->options['dry']) {
                    try {
                        if (function_exists('opcache_compile_file')) {
                            opcache_compile_file($filePath);
                            continue;
                        }
                        include $filePath;
                    } catch (\Throwable $e) {
                        if ($this->options['errors']) {
                            $this->output($e->getMessage(), static::OUTPUT_ERR, $filePath);
                        }
                    }
                }
            }
        }
    }

    protected function getDirectoryIterator(string $path): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, $this->iteratorFlags),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    protected function output($message, int $type = self::OUTPUT_NONE, ?string $file = null): void
    {
        if (!$this->options['verbose']) {
            return;
        }

        if ($file && $this->basePath) {
            $file = str_replace($this->basePath, '', $file);
        }

        echo "\e" . $this->getAsciiCode($type) . "[Preloader] " . $message . ($file ? ' ' . $file : '') . "\e[0m\n";
    }

    protected function getAsciiCode(int $type): string
    {
        switch ($type) {
            case static::OUTPUT_OKAY:
                return '[1;32m';
            case static::OUTPUT_WARN:
                return '[1;33m';
            case static::OUTPUT_ERR:
                return '[0;31m';
            case static::OUTPUT_NONE:
            default:
                return '[1;37m';
        }
    }

    protected function setErrorHandler()
    {
        // set error handler to handle opcache errors
        set_error_handler(function ($severity, $message, $filename, $lineno) {
            if (error_reporting() === 0) {
                return;
            }
            if (error_reporting() & $severity) {
                throw new \ErrorException($message, 0, $severity, $filename, $lineno);
            }
        });
    }
}
