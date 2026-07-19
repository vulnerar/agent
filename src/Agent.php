<?php

namespace Vulnerar\Agent;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use React\Http\Browser;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Http\Middleware\LimitConcurrentRequestsMiddleware;
use React\Http\Middleware\RequestBodyBufferMiddleware;
use React\Http\Middleware\RequestBodyParserMiddleware;
use React\Http\Middleware\StreamingRequestMiddleware;
use React\Socket\SocketServer;
use React\Stream\WritableResourceStream;
use Vulnerar\Agent\Console\Commands\ApplicationCommand;

final class Agent
{
    public function __construct(
        protected RecordsBuffer $buffer,
        protected Browser $browser,
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
        $this->output()?->write(date('Y-m-d H:i:s') . ' [INFO] ' . $message . \PHP_EOL);
    }

    private function error(string $message): void
    {
        $this->output()?->write(date('Y-m-d H:i:s') . ' [ERROR] ' . $message . \PHP_EOL);
    }

    public function run(int $port): void
    {
        $httpServer = new HttpServer(
            new StreamingRequestMiddleware(),
            new LimitConcurrentRequestsMiddleware(100),
            new RequestBodyBufferMiddleware(20 * 1024 * 1024),
            new RequestBodyParserMiddleware(),
            function (RequestInterface $request) {
                $record = json_decode($request->getBody()->getContents(), true);

                if (!is_array($record)) {
                    return Response::plaintext('OK');
                }

                $this->buffer->write($record);

                if ($this->buffer->full) {
                    $this->ingest($this->buffer->pull());
                }
                return Response::plaintext('OK');
            });
        $socket = new SocketServer("127.0.0.1:$port");

        Loop::addPeriodicTimer(30, function () {
            if ($this->buffer->count() === 0) {
                return;
            }

            $this->ingest($this->buffer->pull());
        });

        Loop::addTimer(1, function () {
            Artisan::call(ApplicationCommand::class);
        });

        Loop::addTimer(5, function () {
            if ($this->buffer->count() === 0) {
                return;
            }

            $this->ingest($this->buffer->pull());
        });

        $this->info('Vulnerar agent listening on 127.0.0.1:'.$port);

        $httpServer->listen($socket);
    }

    private function output(): ?WritableResourceStream
    {
        return Loop::get() && defined('STDOUT')
            ? new WritableResourceStream(\STDOUT)
            : null;
    }
}