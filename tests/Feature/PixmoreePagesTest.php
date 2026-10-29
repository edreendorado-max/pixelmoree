<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ArtworkMarketValue;
use Carbon\CarbonImmutable;
use Database\Seeders\ArtworkFixtureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PixmoreePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Pixmoree');
        $this->get('/about')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_me_page_combines_profile_and_collection_sections(): void
    {
        $user = User::create([
            'name' => 'Me Page User',
            'email' => 'me-page@example.test',
            'password' => 'secret-password',
        ]);

        foreach (['/user/me', '/user/dashboard', '/user/collection'] as $path) {
            $this->actingAs($user)
                ->get($path)
                ->assertOk()
                ->assertSee('data-member-panel-content="me"', false)
                ->assertSee('data-collection-tab="uploads"', false)
                ->assertSee('data-collection-tab="owned"', false)
                ->assertSee('data-collection-tab="favorites"', false);
        }
    }

    public function test_default_seeding_does_not_create_demo_accounts_or_artwork(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('artworks', 0);
    }

    public function test_pixel_coin_wallet_requires_authentication_and_records_demo_topups(): void
    {
        $this->getJson('/api/pixel-coins')->assertUnauthorized();

        $user = User::create([
            'name' => 'Wallet User',
            'email' => 'wallet@example.test',
            'password' => 'secret-password',
        ]);

        $this->actingAs($user)
            ->getJson('/api/pixel-coins')
            ->assertOk()
            ->assertJsonPath('balance', 0)
            ->assertJsonPath('demo_only', true)
            ->assertJsonCount(0, 'transactions');

        $this->postJson('/api/pixel-coins/top-ups', ['amount' => 1000])
            ->assertCreated()
            ->assertJsonPath('balance', 1000)
            ->assertJsonPath('transaction.type', 'demo_top_up')
            ->assertJsonPath('transaction.amount', 1000);

        $this->getJson('/api/pixel-coins')
            ->assertOk()
            ->assertJsonPath('balance', 1000)
            ->assertJsonPath('transactions.0.balance_after', 1000);

        $this->get('/user/pixel-coin')
            ->assertOk()
            ->assertSee('Pixel Coin wallet')
            ->assertSee('data-pixel-topup="500"', false)
            ->assertSee('member-pixel-coin-link');

        $this->postJson('/api/pixel-coins/top-ups', ['amount' => 100])
            ->assertUnprocessable();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'pixel_coin_balance' => 1000]);
        $this->assertDatabaseCount('pixel_coin_transactions', 1);
    }

    public function test_earnings_page_summarizes_only_recorded_purchases_of_the_signed_in_users_artwork(): void
    {
        $this->get('/user/earnings')->assertRedirect('/login');

        $seller = User::create([
            'name' => 'Earnings Seller',
            'email' => 'seller@example.test',
            'password' => 'secret-password',
        ]);
        $otherSeller = User::create([
            'name' => 'Other Seller',
            'email' => 'other-seller@example.test',
            'password' => 'secret-password',
        ]);
        $buyer = User::create([
            'name' => 'Earnings Buyer',
            'email' => 'earnings-buyer@example.test',
            'password' => 'secret-password',
        ]);
        $now = now();

        foreach ([
            ['id' => 'seller-artwork', 'title' => 'Seller Artwork', 'user_id' => $seller->id],
            ['id' => 'other-artwork', 'title' => 'Other Artwork', 'user_id' => $otherSeller->id],
        ] as $artwork) {
            DB::table('artworks')->insert($artwork + [
                'creator' => 'Creator',
                'category' => 'Digital Art',
                'price' => 1,
                'image' => $artwork['id'] . '.png',
                'description' => 'Test artwork',
                'status' => 'Listed',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('pixel_coin_transactions')->insert([
            [
                'user_id' => $buyer->id,
                'artwork_id' => 'seller-artwork',
                'type' => 'purchase',
                'amount' => 1000,
                'balance_after' => 0,
                'description' => 'Demo purchase: Seller Artwork',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $buyer->id,
                'artwork_id' => 'other-artwork',
                'type' => 'purchase',
                'amount' => 2500,
                'balance_after' => 0,
                'description' => 'Demo purchase: Other Artwork',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->actingAs($seller)
            ->get('/user/earnings')
            ->assertOk()
            ->assertSee('Earnings balance')
            ->assertSee('1,000 PIXEL COIN')
            ->assertSee('Seller Artwork')
            ->assertDontSee('Other Artwork</td>')
            ->assertSee('data-earnings-total="1000"', false)
            ->assertSee('It is not credited to your wallet');
    }

    public function test_pixel_coin_purchases_use_server_artwork_prices_and_prevent_duplicate_charges(): void
    {
        $this->seed(ArtworkFixtureSeeder::class);
        $buyer = User::create([
            'name' => 'Wallet Buyer',
            'email' => 'buyer@example.test',
            'password' => 'secret-password',
        ]);

        $this->actingAs($buyer)->postJson('/api/pixel-coins/top-ups', ['amount' => 1000])->assertCreated();
        $this->postJson('/api/pixel-coins/purchases', ['artwork_id' => 'neon-wanderer'])
            ->assertCreated()
            ->assertJsonPath('balance', 140)
            ->assertJsonPath('transaction.type', 'purchase')
            ->assertJsonPath('transaction.amount', 860);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-market-value="0.90"', false);
        $this->assertDatabaseHas('artworks', ['id' => 'neon-wanderer', 'price' => '0.86']);

        $this->get('/week12/artworks/neon-wanderer')
            ->assertOk()
            ->assertSee('0.90 ETH');

        $this->postJson('/api/pixel-coins/purchases', ['artwork_id' => 'neon-wanderer'])
            ->assertStatus(409);

        $this->assertDatabaseHas('users', ['id' => $buyer->id, 'pixel_coin_balance' => 140]);
        $this->assertDatabaseCount('pixel_coin_transactions', 2);
    }

    public function test_market_estimate_rises_with_recent_sales_and_decays_after_inactivity(): void
    {
        $now = CarbonImmutable::parse('2026-10-29 12:00:00');

        $rising = ArtworkMarketValue::estimate(
            100,
            $now->subDays(10),
            3,
            $now->subDays(3),
            $now,
        );
        $this->assertSame(115.0, $rising['value']);
        $this->assertSame(15.0, $rising['change_percent']);

        $falling = ArtworkMarketValue::estimate(
            100,
            $now->subDays(100),
            0,
            null,
            $now,
        );
        $this->assertSame(90.0, $falling['value']);
        $this->assertSame(-10.0, $falling['change_percent']);

        $capped = ArtworkMarketValue::estimate(
            100,
            $now->subDays(1),
            20,
            $now,
            $now,
        );
        $this->assertSame(125.0, $capped['value']);
        $this->assertSame(25.0, $capped['change_percent']);
    }

    public function test_about_developer_profiles_can_store_quotes_sections_and_safe_social_urls(): void
    {
        $admin = User::create([
            'name' => 'Content Admin',
            'email' => 'content-admin@example.test',
            'password' => 'secret-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)->post('/admin/content/about', [
            'content' => [
                'team' => [
                    'members' => [
                        0 => [
                            'section' => 'Website engineering',
                            'quote' => 'Creativity grows when ideas are shared.',
                            'socials' => ['instagram' => 'https://instagram.com/pixmoree'],
                        ],
                    ],
                ],
            ],
        ])->assertRedirect('/admin/content/about');

        $this->get('/about')
            ->assertOk()
            ->assertSee('Website engineering')
            ->assertSee('Creativity grows when ideas are shared.')
            ->assertSee('https://instagram.com/pixmoree', false);

        $this->post('/admin/content/about', [
            'content' => [
                'team' => [
                    'members' => [
                        0 => ['socials' => ['instagram' => 'javascript:alert(1)']],
                    ],
                ],
            ],
        ])->assertSessionHasErrors('content.team.members.0.socials.instagram');
    }

    public function test_week12_marketplace_and_admin_screens_render_seeded_artwork(): void
    {
        $this->seed(ArtworkFixtureSeeder::class);
        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('artworks', 7);
        $this->assertSame(4, DB::table('artworks')->distinct()->count('user_id'));
        $this->assertSame(7, DB::table('artworks')->distinct()->count('image_hash'));
        foreach (DB::table('artworks')->pluck('image') as $image) {
            $this->assertFileExists(public_path('assets/' . $image));
        }
        $admin = User::create([
            'name' => 'Beta Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->get('/week12')->assertOk()->assertSee('Ethereal Life');
        $this->get('/week12/artworks/neon-wanderer')->assertOk()->assertSee('Ethereal Life');
        $home = $this->get('/');
        $home->assertOk()->assertSee('Trending Creators');
        $this->assertSame(7, substr_count($home->getContent(), 'class="col-6 col-sm-3 art-column"'));
        $this->assertSame(4, substr_count($home->getContent(), 'class="creator-mini-card"'));
        $this->get('/about')
            ->assertOk()
            ->assertSee('The people behind Pixmoree.')
            ->assertSee('assets/creators/noah.jpg')
            ->assertSee('assets/creators/kenneth.png');
        $this->actingAs($admin);
        $this->get('/week12/admin')->assertOk()->assertSee('Dashboard overview')->assertSee('ADMIN STUDIO');
        $this->get('/week12/admin/artworks')->assertOk()->assertSee('Artwork management')->assertSee('ADMIN STUDIO');
    }

    public function test_admin_can_edit_public_page_copy_and_about_artwork(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.test',
            'password' => 'secret-password',
        ]);
        $admin = User::create([
            'name' => 'Content Admin',
            'email' => 'content-admin@example.test',
            'password' => 'secret-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($user)
            ->get('/admin/content/about')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/content/about')
            ->assertOk()
            ->assertSee('Edit About page')
            ->assertSee('Mission artwork');

        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lFwAAAAASUVORK5CYII=');
        $this->assertNotFalse($imageContent);

        $this->post('/admin/content/about', [
            'content' => [
                'hero' => ['heading' => 'A refreshed Pixmoree story'],
                'stories' => [1 => ['heading' => 'A refreshed mission']],
                'team' => ['members' => [0 => [
                    'name' => 'Lead Developer',
                    'role' => 'Project lead',
                    'bio' => 'Building the Pixmoree experience.',
                ]]],
            ],
            'images' => [
                'stories' => [1 => ['image' => UploadedFile::fake()->createWithContent('mission.png', $imageContent)]],
                'team' => ['members' => [0 => ['image' => UploadedFile::fake()->createWithContent('developer.png', $imageContent)]]],
            ],
        ])->assertRedirect('/admin/content/about');

        $aboutPage = DB::table('site_pages')->where('slug', 'about')->first();
        $this->assertNotNull($aboutPage);
        $aboutContent = json_decode($aboutPage->content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('A refreshed Pixmoree story', $aboutContent['hero']['heading']);
        $this->assertSame('A refreshed mission', $aboutContent['stories'][1]['heading']);
        $this->assertSame('Lead Developer', $aboutContent['team']['members'][0]['name']);
        $uploadedPath = str_replace('storage/', '', $aboutContent['stories'][1]['image']);
        Storage::disk('public')->assertExists($uploadedPath);
        $developerImagePath = str_replace('storage/', '', $aboutContent['team']['members'][0]['image']);
        Storage::disk('public')->assertExists($developerImagePath);

        $this->get('/about')
            ->assertOk()
            ->assertSee('A refreshed Pixmoree story')
            ->assertSee('A refreshed mission')
            ->assertSee('Lead Developer')
            ->assertSee('Building the Pixmoree experience.');

        $this->post('/admin/content/home', [
            'content' => [
                'hero_heading' => 'A refreshed home page',
                'trending' => ['heading' => 'Fresh trends'],
            ],
            'images' => ['hero_background' => UploadedFile::fake()->createWithContent('home-hero.png', $imageContent)],
        ])->assertRedirect('/admin/content/home');
        $homeContent = json_decode(DB::table('site_pages')->where('slug', 'home')->first()->content, true, flags: JSON_THROW_ON_ERROR);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $homeContent['hero_background']));
        $this->get('/')->assertOk()->assertSee('A refreshed home page')->assertSee('Fresh trends');

        $this->post('/admin/content/marketplace', [
            'content' => ['hero' => ['heading_before' => 'Discover a refreshed']],
            'images' => ['hero' => ['image' => UploadedFile::fake()->createWithContent('marketplace-hero.png', $imageContent)]],
        ])->assertRedirect('/admin/content/marketplace');
        $this->get('/week12')->assertOk()->assertSee('Discover a refreshed');
        $this->get('/admin/content/login')->assertOk()->assertSee('Log-in page copy');
        $this->post('/admin/content/login', [
            'content' => ['heading' => 'Sign in to Pixmoree'],
        ])->assertRedirect('/admin/content/login');
        $this->get('/login')->assertOk()->assertSee('Sign in to Pixmoree');
        $this->get('/admin/content/register')->assertOk()->assertSee('Registration page copy');
        $this->post('/admin/content/register', [
            'content' => ['heading' => 'Join Pixmoree'],
        ])->assertRedirect('/admin/content/register');
        $this->get('/register')->assertOk()->assertSee('Join Pixmoree');
    }

    public function test_admin_can_create_update_and_delete_an_artwork(): void
    {
        $this->seed(ArtworkFixtureSeeder::class);
        $admin = User::create([
            'name' => 'Beta Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin);

        $this->post('/week12/admin/artworks', [
            'title' => 'Test Artwork',
            'creator' => 'test.creator',
            'category' => 'Space',
            'price' => '2.50',
            'image' => 'anime-style-character-space.jpg',
            'description' => 'A test listing for the Laravel feature suite.',
        ])->assertRedirect();

        $artwork = DB::table('artworks')->where('title', 'Test Artwork')->first();
        $this->assertNotNull($artwork);
        $this->assertSame('Review', $artwork->status);

        $this->post("/week12/admin/artworks/{$artwork->id}/status", ['status' => 'Listed'])
            ->assertRedirect('/week12/admin/artworks');
        $this->assertDatabaseHas('artworks', ['id' => $artwork->id, 'status' => 'Listed']);

        $this->post("/week12/admin/artworks/{$artwork->id}/delete")
            ->assertRedirect('/week12/admin/artworks');
        $this->assertDatabaseMissing('artworks', ['id' => $artwork->id]);
    }

    public function test_creator_artwork_uploads_are_persistent_and_identical_images_are_rejected(): void
    {
        Storage::fake('public');
        $this->seed(ArtworkFixtureSeeder::class);
        $creator = User::query()->where('email', 'noah.creator@pixmoree.test')->firstOrFail();
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lFwAAAAASUVORK5CYII=');
        $this->assertNotFalse($imageContent);

        $listing = [
            'title' => 'Noah Original',
            'description' => 'A newly uploaded original illustration.',
            'category' => 'Illustration',
            'price' => '0.50',
            'originality' => '1',
            'image' => UploadedFile::fake()->createWithContent('noah-original.png', $imageContent),
        ];

        $this->actingAs($creator)
            ->post('/user/artworks', $listing)
            ->assertRedirect('/user/create')
            ->assertSessionHasNoErrors();

        $saved = DB::table('artworks')->where('title', 'Noah Original')->first();
        $this->assertNotNull($saved);
        $this->assertSame($creator->id, $saved->user_id);
        $this->assertSame('Listed', $saved->status);
        $this->assertNotNull($saved->image_hash);
        Storage::disk('public')->assertExists(substr($saved->image, strlen('storage/')));
        $this->get('/')->assertOk()->assertSee('Noah Original');
        $this->get('/week12')->assertOk()->assertSee('8 listings loaded from the database.');

        $listing['image'] = UploadedFile::fake()->createWithContent('same-original.png', $imageContent);
        $this->post('/user/artworks', $listing)->assertSessionHasErrors('image');
        $this->assertDatabaseCount('artworks', 8);

        $this->patch('/user/artworks/' . $saved->id, [
            'title' => 'Noah Updated Original',
            'description' => 'The creator updated this listing.',
            'category' => 'Digital Art',
            'price' => '0.75',
        ])->assertRedirect('/user/nft-management');
        $this->assertDatabaseHas('artworks', [
            'id' => $saved->id,
            'title' => 'Noah Updated Original',
            'price' => '0.75',
        ]);

        $otherUser = User::create([
            'name' => 'Another creator',
            'email' => 'another@example.test',
            'password' => 'secret-password',
        ]);
        $this->actingAs($otherUser)
            ->delete('/user/artworks/' . $saved->id)
            ->assertNotFound();

        $this->actingAs($creator)
            ->delete('/user/artworks/' . $saved->id)
            ->assertRedirect('/user/nft-management');
        $this->assertDatabaseMissing('artworks', ['id' => $saved->id]);
        Storage::disk('public')->assertMissing(substr($saved->image, strlen('storage/')));
    }

    public function test_trending_creators_are_capped_at_twelve_live_accounts(): void
    {
        $this->seed(ArtworkFixtureSeeder::class);
        for ($index = 1; $index <= 10; $index++) {
            $creator = User::create([
                'name' => 'extra-creator-' . $index,
                'email' => 'extra-creator-' . $index . '@example.test',
                'password' => 'secret-password',
            ]);
            $creator->artworks()->create([
                'id' => 'extra-artwork-' . $index,
                'title' => 'Extra artwork ' . $index,
                'creator' => $creator->name,
                'category' => 'Digital Art',
                'price' => 1,
                'image' => 'uploads/extra-' . $index . '.png',
                'description' => 'An extra creator artwork.',
                'status' => 'Listed',
            ]);
        }

        $home = $this->get('/')->assertOk();
        $this->assertSame(12, substr_count($home->getContent(), 'class="creator-mini-card"'));
        $this->assertSame(17, substr_count($home->getContent(), 'class="col-6 col-sm-3 art-column"'));
    }

    public function test_creator_profile_changes_do_not_change_the_developer_team(): void
    {
        Storage::fake('public');
        $this->seed(ArtworkFixtureSeeder::class);
        $creator = User::query()->where('email', 'noah.creator@pixmoree.test')->firstOrFail();
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lFwAAAAASUVORK5CYII=');
        $this->assertNotFalse($imageContent);

        $this->actingAs($creator)->post('/user/profile', [
            'name' => 'Noah Updated',
            'email' => $creator->email,
            'phone' => '',
            'bio' => 'I make digital illustrations.',
            'avatar' => UploadedFile::fake()->createWithContent('noah-avatar.png', $imageContent),
        ])->assertRedirect('/user/profile-settings');

        $creator->refresh();
        $this->assertSame('Noah Updated', $creator->name);
        $this->assertSame('I make digital illustrations.', $creator->bio);
        Storage::disk('public')->assertExists(substr($creator->avatar_path, strlen('storage/')));
        $this->get('/about')
            ->assertOk()
            ->assertSee('Meet the developers who designed and built the Pixmoree website.')
            ->assertSee('assets/creators/noah.jpg')
            ->assertSee('Noah')
            ->assertDontSee('Noah Updated')
            ->assertDontSee('I make digital illustrations.');
    }
}
