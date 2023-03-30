<?php

use App\Actions\SyncExternalPostAction;
use App\Models\ExternalPost;
use App\Support\Rss\RssEntry;
use App\Support\Rss\RssRepository;
use Carbon\CarbonImmutable;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('will sync an external feed to the database', function () {

    // WITH HTTP FAKE
    Http::fake([
      //'https://example.com/feed' => Http::response(getFeed())   //just one call
      'https://example.com/feed' => Http::sequence()
      ->push(getFeed('test-a'))
      ->push(getFeed('test-b'))
   ]);

    $syncExternalPostsAction = app(SyncExternalPostAction::class);
    $syncExternalPostsAction('https://example.com/feed');

    assertDatabaseHas(ExternalPost::class, [
       'url' => 'https://example.com',
       'title' => 'test-a',
    ]);

    assertDatabaseMissing(ExternalPost::class, [
       'url' => 'https://example.com',
       'title' => 'test-b',
    ]);

    $syncExternalPostsAction('https://example.com/feed');

    assertDatabaseHas(ExternalPost::class, [
       'url' => 'https://example.com',
       'title' => 'test-b',
    ]);

    assertDatabaseMissing(ExternalPost::class, [
       'url' => 'https://example.com',
       'title' => 'test-a',
    ]);

    // WITH MOCK
   /* $rssRepository = mock(RssRepository::class)
        ->expect(fetch: function () {
            return collect([
               new RssEntry(
                   'https://test.com',
                   'test',
                   CarbonImmutable::make('2021-01-01'),
               )
            ]);
        });

    $syncExternalPostsAction = new SyncExternalPostAction($rssRepository);
    $syncExternalPostsAction('https://example.com/feed');

    assertDatabaseHas(ExternalPost::class, [
       'url' => 'https://test.com',
       'title' => 'test',
    ]);*/
});
