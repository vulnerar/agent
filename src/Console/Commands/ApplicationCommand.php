<?php

namespace Vulnerar\Agent\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\Jobs\IngestEvents;
use const DIRECTORY_SEPARATOR;

class ApplicationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vulnerar:application';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect application information.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $event = new Event(
            'app.info',
            [
                'app_url' => config('app.url'),
                'laravel_version' => app()->version(),
                'php_version' => phpversion(),
                'environment' => app()->environment(),
                'os' => [
                    'name' => $this->getOsName(),
                    'user' => $this->getOsUser(),
                ],
            ]
        );
        IngestEvents::dispatch($event);
    }

    private function getOsName(): string
    {
        $os = implode(' ', [php_uname('s'), php_uname('r')]);

        // Detect if running on Unix system (Linux, macOS, etc.)
        if (DIRECTORY_SEPARATOR === '/') {
            $result = Process::run(['lsb_release', '--description', '--short', '2>/dev/null']);

            if ($result->successful()) {
                $os = trim((string) $result->output());
            } elseif(file_exists('/etc/os-release')) {
                $osRelease = file_get_contents('/etc/os-release');

                if ($name = Str::of($osRelease)->match('/^PRETTY_NAME="?([^"]*)"?$/m')) {
                    $os = $name;
                }
            }
        }
        return $os;
    }

    /**
     * @todo support Windows and other non-POSIX systems
     */
    private function getOsUser(): array
    {
        $uid = function_exists('posix_getuid')
            ? posix_getuid() : null;
        $gid = function_exists('posix_getgid')
            ? posix_getgid() : null;
        $user = function_exists('posix_getpwuid') && is_int($uid)
            ? (posix_getpwuid($uid)['name'] ?? null) : null;
        $group = function_exists('posix_getgrgid') && is_int($gid)
            ? (posix_getgrgid($gid)['name'] ?? null) : null;

        return [
            'uid' => $uid,
            'gid' => $gid,
            'user' => $user,
            'group' => $group,
        ];
    }
}