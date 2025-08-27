<?php

use Streeboga\Genesis\Utils\UsersUtils;

it('builds gravatar url', function () {
    $url = UsersUtils::avatarUrl('User@Example.com', 100);
    expect($url)->toContain('gravatar.com');
    expect($url)->toContain('s=100');
});

it('merges preferences deeply', function () {
    $existing = ['theme' => ['color' => 'blue', 'font' => 'sans'], 'notifications' => ['email' => true]];
    $incoming = ['theme' => ['color' => 'red'], 'notifications' => ['sms' => false]];

    $merged = UsersUtils::mergePreferences($existing, $incoming);

    expect($merged['theme']['color'])->toBe('red');
    expect($merged['theme']['font'])->toBe('sans');
    expect($merged['notifications']['email'])->toBeTrue();
    expect($merged['notifications']['sms'])->toBeFalse();
});

it('generates project link', function () {
    $link = UsersUtils::linkToProject(5, 10);
    expect($link)->toBe('/projects/5/users/10');
});


