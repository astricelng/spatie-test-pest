<?php

use function Pest\Laravel\get;
use App\Models\Redirect;
use App\Http\Middleware\RedirectMiddleware;
use \Symfony\Component\HttpFoundation\Response;

it('will perform the right redirects in isolation', function () {
    $response = (new RedirectMiddleware())->handle(
        createRequest('get', '/'),
        fn () => new Response()
    );

    expect($response->isRedirect())->toBeFalse();

    Redirect::factory()->create([
        'from' => '/',
        'to' => '/new-homepage'
    ]);

    $response = (new RedirectMiddleware())->handle(
        createRequest('get', '/'),
        fn () => new Response()
    );

    expect($response->isRedirect(url('/new-homepage')))->toBeTrue();
});


it('will perform the right redirects', function () {
    // create a route just for test
    Route::get('my-test-route', fn () => 'ok')->middleware(RedirectMiddleware::class);

    get('my-test-route')->assertStatus(200);

    Redirect::factory()->create([
        'from' => '/my-test-route',
        'to' => '/new-homepage'
    ]);

    get('my-test-route')->assertRedirect('/new-homepage');
});
