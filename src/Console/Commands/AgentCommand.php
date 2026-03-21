<?php

namespace Vulnerar\Agent\Console\Commands;

use Illuminate\Console\Command;
use Vulnerar\Agent\Agent;

final class AgentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vulnerar:agent {--port=2709}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the vulnerar agent';

    /**
     * Execute the console command.
     */
    public function handle(Agent $agent): void
    {
        $port = (int) $this->option('port');

        $agent->run($port);
    }
}