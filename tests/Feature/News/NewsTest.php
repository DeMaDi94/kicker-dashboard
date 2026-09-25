<?php

declare(strict_types=1);

use App\Models\NewsItem;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia;

function newsOf(Season $season, User $author, string $text, ?string $at = null): NewsItem
{
    $news = NewsItem::create(['season_id' => $season->id, 'user_id' => $author->id, 'text' => $text]);

    if ($at !== null) {
        $news->forceFill(['created_at' => Carbon::parse($at)])->save();
    }

    return $news;
}

describe('NEWS-01 · a signed-in user writes a season’s news', function () {
    it('stores the text as written, line breaks included', function () {
        $season = Season::factory()->create();
        $user = member();

        $this->actingAs($user)->post(route('news.store', $season), ['text' => "Kasse am Freitag.\nBitte bar."])
            ->assertRedirect(route('seasons.show', $season));

        expect(NewsItem::sole()->only(['season_id', 'user_id', 'text']))
            ->toBe(['season_id' => $season->id, 'user_id' => $user->id, 'text' => "Kasse am Freitag.\nBitte bar."]);
    });

    it('needs a text of at most 2 000 characters (D17)', function () {
        $season = Season::factory()->create();

        $this->actingAs(member())->post(route('news.store', $season), ['text' => ''])->assertInvalid(['text']);
        $this->actingAs(member())->post(route('news.store', $season), ['text' => str_repeat('a', 2001)])->assertInvalid(['text']);
        $this->actingAs(member())->post(route('news.store', $season), ['text' => str_repeat('a', 2000)])->assertValid();
    });

    it('sends a guest to the login', function () {
        $season = Season::factory()->create();

        $this->post(route('news.store', $season), ['text' => 'Hallo'])->assertRedirect(route('login'));

        expect(NewsItem::count())->toBe(0);
    });
});

describe('NEWS-02 · the news on the public season view', function () {
    it('shows them to a guest, newest first, with date and author', function () {
        $season = Season::factory()->create();
        $author = member(['name' => 'Dennis']);
        newsOf($season, $author, 'Alt', '2026-09-01 10:00');
        newsOf($season, $author, 'Neu', '2026-09-20 10:00');

        $this->get(route('seasons.show', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('news.0.text', 'Neu')
                ->where('news.0.authorName', 'Dennis')
                ->where('news.0.postedAt', Carbon::parse('2026-09-20 10:00')->toIso8601String())
                ->where('news.0.edited', false)
                ->where('news.0.mayChange', false)
                ->where('news.1.text', 'Alt'));
    });

    it('shows only the season’s own news', function () {
        $season = Season::factory()->create();
        $other = Season::factory()->create();
        newsOf($other, member(), 'Anderswo');

        $this->get(route('seasons.show', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('news', []));
    });

    it('keeps the author’s name after their account is deleted (D17)', function () {
        $season = Season::factory()->create();
        $author = member(['name' => 'Dennis']);
        newsOf($season, $author, 'Hallo');
        $author->delete();

        $this->get(route('seasons.show', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('news.0.authorName', 'Dennis'));
    });

    it('goes with a deleted season and comes back with it (D17)', function () {
        $season = Season::factory()->create();
        newsOf($season, member(), 'Hallo');

        $season->delete();
        $this->get(route('seasons.show', $season->id))->assertNotFound();

        $season->restore();
        $this->get(route('seasons.show', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('news.0.text', 'Hallo'));
    });
});

describe('NEWS-03 · the author and any admin change or delete a news post', function () {
    it('lets the author change the text, marked as edited (D17)', function () {
        $season = Season::factory()->create();
        $author = member();
        $news = newsOf($season, $author, 'Alt');

        $this->actingAs($author)->put(route('news.update', $news), ['text' => 'Neu'])
            ->assertRedirect(route('seasons.show', $season));

        expect($news->fresh()?->text)->toBe('Neu');
        $this->get(route('seasons.show', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('news.0.edited', true)->where('news.0.mayChange', true));
    });

    it('does not mark a post edited when the text stays the same', function () {
        $author = member();
        $news = newsOf(Season::factory()->create(), $author, 'Gleich');

        $this->actingAs($author)->put(route('news.update', $news), ['text' => 'Gleich']);

        expect($news->fresh()?->edited_at)->toBeNull();
    });

    it('lets an admin change and delete another user’s post', function () {
        $season = Season::factory()->create();
        $news = newsOf($season, member(), 'Alt');
        $admin = admin();

        $this->actingAs($admin)->get(route('seasons.show', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('news.0.mayChange', true));

        $this->actingAs($admin)->put(route('news.update', $news), ['text' => 'Neu'])->assertRedirect();
        expect($news->fresh()?->text)->toBe('Neu');

        $this->actingAs($admin)->delete(route('news.destroy', $news))->assertRedirect(route('seasons.show', $season));
        expect(NewsItem::count())->toBe(0);
    });

    it('lets the author delete their post', function () {
        $author = member();
        $news = newsOf(Season::factory()->create(), $author, 'Weg');

        $this->actingAs($author)->delete(route('news.destroy', $news))->assertRedirect();

        expect(NewsItem::count())->toBe(0);
    });

    it('refuses another plain user, and shows them no way to', function () {
        $season = Season::factory()->create();
        $news = newsOf($season, member(), 'Bleibt');
        $other = member();

        $this->actingAs($other)->get(route('seasons.show', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('news.0.mayChange', false));
        $this->actingAs($other)->put(route('news.update', $news), ['text' => 'Neu'])->assertForbidden();
        $this->actingAs($other)->delete(route('news.destroy', $news))->assertForbidden();

        expect($news->fresh()?->text)->toBe('Bleibt');
    });

    it('holds a changed text to the same rules (NEWS-01, D17)', function () {
        $author = member();
        $news = newsOf(Season::factory()->create(), $author, 'Alt');

        $this->actingAs($author)->put(route('news.update', $news), ['text' => str_repeat('a', 2001)])->assertInvalid(['text']);
    });

    it('sends a guest to the login', function () {
        $news = newsOf(Season::factory()->create(), member(), 'Bleibt');

        $this->put(route('news.update', $news), ['text' => 'Neu'])->assertRedirect(route('login'));
        $this->delete(route('news.destroy', $news))->assertRedirect(route('login'));

        expect(NewsItem::count())->toBe(1);
    });
});
