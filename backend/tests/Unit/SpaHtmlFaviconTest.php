<?php

use App\Support\SpaHtmlFavicon;

it('replaces the static engine favicon link without integrity', function () {
    $html = '<!DOCTYPE html><html><head>'
        .'<link rel="icon" href="/favicon.ico" integrity="sha384-abc" crossorigin="anonymous">'
        .'<title>K2NET</title></head><body></body></html>';

    $out = SpaHtmlFavicon::inject($html, '/storage/k2net-icon.png');

    expect($out)->toContain('<link rel="icon" href="/storage/k2net-icon.png">')
        ->and($out)->not->toContain('integrity=')
        ->and($out)->not->toContain('href="/favicon.ico" integrity');
});

it('inserts an icon link when the shell has none', function () {
    $html = '<!DOCTYPE html><html><head><title>K2NET</title></head><body></body></html>';

    $out = SpaHtmlFavicon::inject($html, '/storage/k2net-icon.png');

    expect($out)->toContain('<link rel="icon" href="/storage/k2net-icon.png">')
        ->and($out)->toContain('</head>');
});

it('treats /favicon.ico as the generic engine icon', function () {
    expect(SpaHtmlFavicon::isGenericEngineIcon('/favicon.ico'))->toBeTrue()
        ->and(SpaHtmlFavicon::isGenericEngineIcon('https://staging.k2net.id/favicon.ico'))->toBeTrue()
        ->and(SpaHtmlFavicon::isGenericEngineIcon('/storage/k2net-icon.png'))->toBeFalse();
});
