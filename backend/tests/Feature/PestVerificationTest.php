<?php

test('pest is working', function () {
    expect(true)->toBeTrue();
});

it('can access the application', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});
