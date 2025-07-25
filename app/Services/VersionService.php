<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Log;

class VersionService
{
    public function getVersion()
    {
        $version = config('app.version');
        if (empty($version)) {
            $git = $this->getCurrentGitCommit();
            $version = $git ? substr($git, 0, 5) : 'unknown';
        }

        return $version;
    }

    public function getUserAgent(): string
    {
        $version = $this->getVersion();

        return sprintf(
            '%s/%s (%s; bot; contact: %s)',
            config('app.name'),
            $version,
            config('app.url'),
            config('app.legal.email')
        );
    }

    public function getCurrentGitCommit(): ?string
    {
        $head = $this->getGitHead();
        if (! $head) {
            return null;
        }

        $gitCommit = null;
        try {
            $gitCommit = file_get_contents(base_path().'/.git/'.$this->getGitHead());
        } catch (Exception $exception) {
            // if .git/HEAD is detached, we can still try to read the commit hash
            try {
                $hash = $this->getGitHead();
                $gitLog = file_get_contents(base_path().'/.git/logs/HEAD');

                // check if the log contains the commit hash
                if (str_contains($gitLog, $hash)) {
                    return $hash;
                }

            } catch (Exception $exception) {
                Log::error('Failed to read git logs', [
                    'exception' => $exception,
                ]);
            }

            report($exception);
        }

        return $gitCommit;
    }

    public function getGitHead(): ?string
    {
        if ($head = file_get_contents(base_path().'/.git/HEAD')) {
            return substr($head, 5, -1);
        }

        return null;
    }
}
