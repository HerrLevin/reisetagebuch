<?php

namespace App\Services;

use Exception;

class VersionService
{
    public function getVersion() {
        $version = config('app.version');
        if (empty($version)) {
            $git = $this->getCurrentGitCommit();
            $version = $git ? substr($git, 0, 5) : 'unknown';
        }
        return $version;
    }


    public function getUserAgent(): string {
        $version = $this->getVersion();
        return sprintf(
            '%s/%s (%s; bot; contact: %s)',
            config('app.name'),
            $version,
            config('app.url'),
            config('app.legal.email')
        );
    }


    private function getCurrentGitCommit(): bool|string {
        try {
            if ($hash = @file_get_contents(base_path() . '/.git/' . $this->getGitHead())) {
                return $hash;
            }
        } catch (Exception $exception) {
            report($exception);
        }
        return false;
    }

    private function getGitHead(): bool|string {
    if ($head = @file_get_contents(base_path() . '/.git/HEAD')) {
        return substr($head, 5, -1);
    }
    return false;
}
}
