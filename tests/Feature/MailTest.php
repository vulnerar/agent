<?php

use Illuminate\Support\Facades\Auth;
use Workbench\App\Mail\Welcome;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;

pest()->use(RefreshDatabase::class);

/**
 * Don't use `Mail::fake()` here, because it will prevent the `MessageSent` event from being dispatched.
 * Instead, we use `MAIL_MAILER=array` to prevent the mail from actually being sent.
 */
it('ingests mail events', function () {
    Http::fake();

    $user = User::factory()->create();

    Auth::login($user);

    Mail::to('info@example.com')->send(new Welcome());

    Http::assertSent(function (Request $request) use ($user): bool {
        return $request['type'] === 'mail'
            && $request['data']['mail']['subject'] === 'Welcome'
            && $request['data']['mail']['sender']['name'] === 'Example'
            && $request['data']['mail']['sender']['email'] === 'mail@example.com'
            && $request['data']['mail']['recipients'][0]['name'] === ''
            && $request['data']['mail']['recipients'][0]['email'] === 'info@example.com'
            && $request['user']['id'] === (string) $user->id
            && $request['user']['type'] === get_class($user)
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});
