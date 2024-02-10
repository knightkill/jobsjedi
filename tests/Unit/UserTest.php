<?php

use App\Models\User;

test('that admin role exists', function () {
    expect(\Spatie\Permission\Models\Role::where('name', 'admin')->exists())->toBeTrue();
});

test('that atleast one admin exists', function () {
    expect(User::with('roles')
        ->whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->count()
    )->toBeGreaterThan(0);
});
