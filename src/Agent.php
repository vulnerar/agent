<?php

namespace Vulnerar\Agent;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use React\Http\Browser;
use React\Http\HttpServer;
use React\Socket\SocketServer;
use React\Stream\WritableStreamInterface;

final class Agent
{
    public function __construct(
        protected RecordsBuffer $buffer,
        protected Browser $browser,
        protected ?WritableStreamInterface $output
    ) {
        //
    }

    protected function ingest(array $records): void
    {
        $host = config('vulnerar.host');
        $token = config('vulnerar.token');

        $this->browser->post("https://{$host}/api/agent/ingest", [
            'Content-Type' => 'application/json',
            'User-Agent' => 'vulnerar/agent',
            'Authorization' => "Bearer {$token}",
        ], json_encode([
            'events' => $records,
        ]))->then(function (ResponseInterface $response) {
            $this->info('Ingest successful');
        }, function (Exception $e) {
            $this->error($e->getMessage());
        });
    }

    private function info(string $message): void
    {
        $this->output?->write(date('Y-m-d H:i:s') . ' [INFO] ' . $message . \PHP_EOL);
    }

    private function error(string $message): void
    {
        $this->output?->write(date('Y-m-d H:i:s') . ' [ERROR] ' . $message . \PHP_EOL);
    }

    public function run(int $port): void
    {
        $httpServer = new HttpServer(function (RequestInterface $request) {
            $record = json_decode($request->getBody()->getContents(), true);

            if (! is_array($record)) {
                return;
            }

            $this->buffer->write($record);

            if ($this->buffer->full) {
                $this->ingest($this->buffer->pull());
            }
        });
        $socket = new SocketServer("127.0.0.1:$port");

        Loop::addPeriodicTimer(30, function () {
            if ($this->buffer->count() === 0) {
                return;
            }

            $this->ingest($this->buffer->pull());
        });

        $this->info('Vulnerar agent listening on 127.0.0.1:'.$port);

        $httpServer->listen($socket);
    }
}