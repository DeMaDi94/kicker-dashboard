<?php

declare(strict_types=1);

use App\Domain\News\NewsAccess;

describe('NEWS-03 · the author and any admin change or delete a news post', function () {
    it('lets the author', function () {
        expect(NewsAccess::mayChange(authorId: 4, userId: 4, mayManageNews: false))->toBeTrue();
    });

    it('lets an admin change another’s post', function () {
        expect(NewsAccess::mayChange(authorId: 4, userId: 9, mayManageNews: true))->toBeTrue();
    });

    it('refuses another user', function () {
        expect(NewsAccess::mayChange(authorId: 4, userId: 9, mayManageNews: false))->toBeFalse();
    });
});
