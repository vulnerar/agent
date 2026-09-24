<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Workbench\App\Models\User;
use Illuminate\Support\Facades\Auth;

pest()->use(RefreshDatabase::class);

it('ingests http.request events', function () {
    Http::fake();

    $response = $this->get(route('request.show', ['id' => 1]), [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer secret-token',
    ])->assertSuccessful();

    Http::assertSent(function (Request $request) use ($response): bool {
        return $request['type'] === 'http.request'
            && $request['data']['request']['method'] === 'GET'
            && $request['data']['request']['url'] === route('request.show', ['id' => 1])
            && $request['data']['request']['size'] === 0
            && $request['data']['request']['headers']['accept'] == ['application/json']
            && $request['data']['request']['headers']['authorization'] == ['Bearer [12 bytes redacted]']
            && $request['data']['request']['files'] === []
            && $request['data']['route']['name'] === 'request.show'
            && $request['data']['route']['path'] === '/request/{id}'
            && $request['data']['response']['status'] === 200
            && $request['data']['response']['size'] === strlen($response->getContent())
            && $request['user'] === null
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests http.request events (authenticated)', function () {
    Http::fake();

    $user = User::factory()->create();

    Auth::login($user);

    $response = $this->get(route('request.show', ['id' => 1]), [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer secret-token',
    ])->assertSuccessful();

    Http::assertSent(function (Request $request) use ($response, $user): bool {
        return $request['type'] === 'http.request'
            && $request['data']['request']['method'] === 'GET'
            && $request['data']['request']['url'] === route('request.show', ['id' => 1])
            && $request['data']['request']['size'] === 0
            && $request['data']['request']['headers']['accept'] == ['application/json']
            && $request['data']['request']['headers']['authorization'] == ['Bearer [12 bytes redacted]']
            && $request['data']['request']['files'] === []
            && $request['data']['route']['name'] === 'request.show'
            && $request['data']['route']['path'] === '/request/{id}'
            && $request['data']['response']['status'] === 200
            && $request['data']['response']['size'] === strlen($response->getContent())
            && $request['user']['id'] === (string) $user->id
            && $request['user']['type'] === get_class($user)
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests http.request events with uploaded files', function () {
    Http::fake();

    $file = UploadedFile::fake()
        ->createWithContent('shell.php', '<?php echo "Hello World"; ?>');

    $response = $this->post(route('request.store'), [
        'file' => $file,
    ])->assertSuccessful();

    Http::assertSent(function (Request $request) use ($response, $file): bool {
        return $request['type'] === 'http.request'
            && $request['data']['request']['method'] === 'POST'
            && $request['data']['request']['url'] === route('request.store')
            && $request['data']['request']['files'] === [
                [
                    'original_name' => 'shell.php',
                    'extension' => 'php',
                    'mime_type' => 'application/x-php',
                    'size' => (int) $file->getSize(),
                    'sha1' => sha1($file->getContent()),
                    'md5' => md5($file->getContent()),
                ]
            ]
            && $request['data']['route']['name'] === 'request.store'
            && $request['data']['route']['path'] === '/request'
            && $request['data']['response']['status'] === 200
            && $request['data']['response']['size'] === strlen($response->getContent())
            && $request['user'] === null
            && $request['ip_address'] === '127.0.0.1';
    });
});