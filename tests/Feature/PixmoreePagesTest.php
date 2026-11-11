<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Support\ArtworkPriceDashboard;
use App\Support\ArtworkMarketValue;
use Carbon\CarbonImmutable;
use Database\Seeders\ArtworkFixtureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $this->get('/store')->assertRedirect('/login');
        $this->get('/week12')->assertRedirect('/store');
    }

    public function test_store_lists_only_listed_artworks_and_prompts_unverified_users_on_purchase(): void
    {
        config(['payments.enabled' => true]);
        $user = User::create([
            'name' => 'Store Buyer',
            'email' => 'store-buyer@example.test',
            'password' => 'secret-password',
        ]);
        DB::table('artworks')->insert([
            [
                'id' => 'store-listed-artwork',
                'user_id' => $user->id,
                'title' => 'Store Listed Artwork',
                'creator' => $user->name,
                'category' => 'Digital Art',
                'price' => 1.25,
                'image' => 'store-listed.png',
                'description' => 'A listed piece.',
                'status' => 'Listed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'store-draft-artwork',
                'user_id' => $user->id,
                'title' => 'Store Draft Artwork',
                'creator' => $user->name,
                'category' => 'Digital Art',
                'price' => 2.50,
                'image' => 'store-draft.png',
                'description' => 'A draft piece.',
                'status' => 'Draft',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->get('/store')->assertRedirect('/login');
        $this->get('/week12')->assertRedirect('/store');

        $this->withSession(['email_verification_user_id' => $user->id])
            ->actingAs($user)
            ->get('/store')
            ->assertOk()
            ->assertSee('Store Listed Artwork')
            ->assertDontSee('Store Draft Artwork')
            ->assertSee('Your artwork')
            ->assertDontSee('href="' . route('user.checkout', ['artworkId' => 'store-listed-artwork']) . '"', false);

        $this->get(route('user.checkout', ['artworkId' => 'store-listed-artwork']))
            ->assertRedirect(route('verification.notice'));

        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Verify your email before buying NFTs.')
            ->assertSee($user->email);
        $this->assertDatabaseCount('payment_orders', 0);
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

    public function test_member_home_recommends_listed_artwork_and_discloses_unavailable_price_analytics(): void
    {
        $user = User::create([
            'name' => 'For You Creator',
            'email' => 'for-you-creator@example.test',
            'password' => 'secret-password',
        ]);
        DB::table('artworks')->insert([
            'id' => 'for-you-artwork',
            'user_id' => $user->id,
            'title' => 'For You Artwork',
            'creator' => $user->name,
            'category' => 'Digital Art',
            'price' => 1.25,
            'image' => 'for-you-artwork.png',
            'description' => 'A recommended listing.',
            'status' => 'Listed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/user')
            ->assertOk()
            ->assertSee('For You')
            ->assertSee('For You Artwork')
            ->assertSee('based on your likes, purchases, and artwork views')
            ->assertSee('Price analytics')
            ->assertSee('Price analytics are hidden during beta')
            ->assertDontSee('Top 10 price increases')
            ->assertDontSee('Daily price history is being collected');
    }

    public function test_for_you_recommendations_refresh_from_the_users_latest_interests(): void
    {
        $user = User::create([
            'name' => 'Interested User',
            'email' => 'interested-user@example.test',
            'password' => 'secret-password',
        ]);
        $creator = User::create([
            'name' => 'Recommendation Creator',
            'email' => 'recommendation-creator@example.test',
            'password' => 'secret-password',
        ]);

        foreach ([
            ['id' => 'photo-interest', 'title' => 'Photo Interest', 'category' => 'Photography'],
            ['id' => 'photo-pick', 'title' => 'Photography Pick', 'category' => 'Photography'],
            ['id' => 'sculpture-interest', 'title' => 'Sculpture Interest', 'category' => '3D Art'],
            ['id' => 'sculpture-pick', 'title' => 'Sculpture Pick', 'category' => '3D Art'],
        ] as $item) {
            DB::table('artworks')->insert([
                'id' => $item['id'],
                'user_id' => $creator->id,
                'title' => $item['title'],
                'creator' => $creator->name,
                'category' => $item['category'],
                'price' => 1.00,
                'item_count' => 10,
                'image' => $item['id'] . '.png',
                'description' => 'A recommendation listing.',
                'status' => 'Listed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('artwork_likes')->insert([
            'user_id' => $user->id,
            'artwork_id' => 'photo-interest',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $firstResponse = $this->actingAs($user)->get('/user')->assertOk();
        $firstFeed = substr($firstResponse->getContent(), strpos($firstResponse->getContent(), 'id="memberForYouItems"'));
        $this->assertLessThan(strpos($firstFeed, 'Sculpture Pick'), strpos($firstFeed, 'Photography Pick'));

        DB::table('artwork_likes')->where('user_id', $user->id)->delete();
        DB::table('artwork_likes')->insert([
            'user_id' => $user->id,
            'artwork_id' => 'sculpture-interest',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $secondResponse = $this->get('/user')->assertOk();
        $secondFeed = substr($secondResponse->getContent(), strpos($secondResponse->getContent(), 'id="memberForYouItems"'));
        $this->assertLessThan(strpos($secondFeed, 'Photography Pick'), strpos($secondFeed, 'Sculpture Pick'));
    }

    public function test_member_home_does_not_present_estimate_snapshots_as_market_data(): void
    {
        $user = User::create([
            'name' => 'Market Creator',
            'email' => 'market-creator@example.test',
            'password' => 'secret-password',
        ]);
        $yesterday = today()->subDay()->toDateString();

        foreach ([
            ['id' => 'daily-price-gainer', 'title' => 'Daily Price Gainer', 'price' => 2.00, 'previous' => 1.00],
            ['id' => 'daily-price-decliner', 'title' => 'Daily Price Decliner', 'price' => 0.50, 'previous' => 1.00],
        ] as $item) {
            DB::table('artworks')->insert([
                'id' => $item['id'],
                'user_id' => $user->id,
                'title' => $item['title'],
                'creator' => $user->name,
                'category' => 'Digital Art',
                'price' => $item['price'],
                'item_count' => 10,
                'image' => $item['id'] . '.png',
                'description' => 'A daily market listing.',
                'status' => 'Listed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('artwork_price_snapshots')->insert([
                'artwork_id' => $item['id'],
                'snapshot_date' => $yesterday,
                'price' => $item['previous'],
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);
        }

        $this->actingAs($user)
            ->get('/user')
            ->assertOk()
            ->assertSee('For You')
            ->assertSee('Price analytics are hidden during beta')
            ->assertDontSee('Top 10 price increases')
            ->assertDontSee('+100.00%')
            ->assertDontSee('-50.00%');
    }

    public function test_daily_price_movers_are_limited_to_the_top_ten_in_each_direction(): void
    {
        $user = User::create([
            'name' => 'Top Movers Creator',
            'email' => 'top-movers@example.test',
            'password' => 'secret-password',
        ]);
        $yesterday = today()->subDay()->toDateString();
        $artworkIds = [];

        for ($rank = 1; $rank <= 11; $rank++) {
            foreach ([
                ['direction' => 'gainer', 'price' => 1 + $rank],
                ['direction' => 'decliner', 'price' => 1 - $rank / 100],
            ] as $movement) {
                $id = $movement['direction'] . '-' . str_pad((string) $rank, 2, '0', STR_PAD_LEFT);
                $artworkIds[] = $id;
                DB::table('artworks')->insert([
                    'id' => $id,
                    'user_id' => $user->id,
                    'title' => $id,
                    'creator' => $user->name,
                    'category' => 'Digital Art',
                    'price' => $movement['price'],
                    'item_count' => 10,
                    'image' => $id . '.png',
                    'description' => 'A daily market listing.',
                    'status' => 'Listed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('artwork_price_snapshots')->insert([
                    'artwork_id' => $id,
                    'snapshot_date' => $yesterday,
                    'price' => 1,
                    'created_at' => now()->subDay(),
                    'updated_at' => now()->subDay(),
                ]);
            }
        }

        $artworks = Artwork::query()->whereIn('id', $artworkIds)->get();
        foreach ($artworks as $artwork) {
            $artwork->setAttribute('creator_sold_count', 0);
            $artwork->setAttribute('likes_count', 0);
            $artwork->setAttribute('views_count', 0);
            $artwork->setAttribute('recent_market_sales_count', 0);
            $artwork->setAttribute('last_market_sale_at', null);
        }

        $movers = (new ArtworkPriceDashboard())->dailyMovers($artworks);

        $this->assertCount(10, $movers['gainers']);
        $this->assertCount(10, $movers['decliners']);
        $this->assertSame('gainer-11', $movers['gainers']->first()->id);
        $this->assertSame('decliner-11', $movers['decliners']->first()->id);
        $this->assertFalse($movers['gainers']->contains('id', 'gainer-01'));
        $this->assertFalse($movers['decliners']->contains('id', 'decliner-01'));
    }

    public function test_default_seeding_does_not_create_demo_accounts_or_artwork(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('artworks', 0);
    }

    public function test_legacy_wallet_routes_are_removed_and_the_old_page_redirects(): void
    {
        $user = User::create([
            'name' => 'Wallet User',
            'email' => 'wallet@example.test',
            'password' => 'secret-password',
        ]);

        $this->getJson('/api/pixel-coins')->assertNotFound();
        $this->postJson('/api/pixel-coins/top-ups', ['amount' => 1000])->assertNotFound();
        $this->actingAs($user)->get('/user/pixel-coin')->assertRedirect(route('user.purchases'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'pixel_coin_balance' => 0]);
        $this->assertDatabaseCount('pixel_coin_transactions', 0);
    }

    public function test_artwork_views_are_deduplicated_and_likes_require_authentication(): void
    {
        $artworkId = 'engagement-nft';
        DB::table('artworks')->insert([
            'id' => $artworkId,
            'title' => 'Engagement NFT',
            'creator' => 'Engagement Creator',
            'category' => 'Digital Art',
            'price' => 1,
            'image' => 'engagement-nft.png',
            'description' => 'A test artwork for engagement metrics.',
            'status' => 'Listed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->putJson("/api/artworks/{$artworkId}/like")->assertUnauthorized();
        $user = User::create([
            'name' => 'Artwork Liker',
            'email' => 'artwork-liker@example.test',
            'password' => 'secret-password',
        ]);

        $this->actingAs($user)->postJson("/api/artworks/{$artworkId}/views")
            ->assertOk()
            ->assertJsonPath('views', 1)
            ->assertJsonPath('likes', 0);
        $this->postJson("/api/artworks/{$artworkId}/views")
            ->assertOk()
            ->assertJsonPath('views', 1);

        $this->putJson("/api/artworks/{$artworkId}/like")
            ->assertOk()
            ->assertJsonPath('liked', true)
            ->assertJsonPath('likes', 1);
        $this->putJson("/api/artworks/{$artworkId}/like")
            ->assertOk()
            ->assertJsonPath('likes', 1);
        $this->deleteJson("/api/artworks/{$artworkId}/like")
            ->assertOk()
            ->assertJsonPath('liked', false)
            ->assertJsonPath('likes', 0);
    }

    public function test_creator_views_likes_and_follows_are_persistent_and_creator_only(): void
    {
        $creator = User::create([
            'name' => 'Engagement Creator',
            'email' => 'engagement-creator@example.test',
            'password' => 'secret-password',
        ]);
        $creator->forceFill(['role' => 'user'])->save();
        $follower = User::create([
            'name' => 'Creator Follower',
            'email' => 'creator-follower@example.test',
            'password' => 'secret-password',
        ]);
        $follower->forceFill(['role' => 'user'])->save();

        $this->actingAs($follower)->postJson("/api/creators/{$creator->id}/views")
            ->assertOk()
            ->assertJsonPath('views', 1)
            ->assertJsonPath('followers', 0);
        $this->postJson("/api/creators/{$creator->id}/views")
            ->assertOk()
            ->assertJsonPath('views', 1);

        $this->putJson("/api/creators/{$creator->id}/like")
            ->assertOk()
            ->assertJsonPath('liked', true)
            ->assertJsonPath('likes', 1);
        $this->putJson("/api/creators/{$creator->id}/follow")
            ->assertOk()
            ->assertJsonPath('following', true)
            ->assertJsonPath('followers', 1);
        $this->deleteJson("/api/creators/{$creator->id}/follow")
            ->assertOk()
            ->assertJsonPath('following', false)
            ->assertJsonPath('followers', 0);

        $this->actingAs($creator)->putJson("/api/creators/{$creator->id}/like")->assertUnprocessable();
        $this->putJson("/api/creators/{$creator->id}/follow")->assertUnprocessable();
        $this->assertDatabaseMissing('creator_follows', ['user_id' => $creator->id, 'creator_id' => $creator->id]);

        $this->get("/creators/{$creator->id}")
            ->assertOk()
            ->assertSee('Engagement Creator')
            ->assertSee('Followers');
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

        DB::table('payment_orders')->insert([
            [
                'purchase_id' => (string) Str::uuid(),
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'artwork_id' => 'seller-artwork',
                'type' => 'nft_purchase',
                'amount_minor' => 24500,
                'payment_method' => 'gcash',
                'status' => 'approved',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'purchase_id' => (string) Str::uuid(),
                'buyer_id' => $buyer->id,
                'seller_id' => $otherSeller->id,
                'artwork_id' => 'other-artwork',
                'type' => 'nft_purchase',
                'amount_minor' => 25000,
                'payment_method' => 'gcash',
                'status' => 'approved',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'purchase_id' => (string) Str::uuid(),
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'artwork_id' => 'seller-artwork',
                'type' => 'nft_purchase',
                'amount_minor' => 5000,
                'payment_method' => 'gcash',
                'status' => 'pending_review',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->actingAs($seller)
            ->get('/user/earnings')
            ->assertOk()
            ->assertSee('Creator earnings')
            ->assertSee('PHP 245.00')
            ->assertSee('Seller Artwork')
            ->assertDontSee('Other Artwork</td>')
            ->assertSee('data-earnings-total="24500"', false)
            ->assertSee('Payments are made directly to creators.');
    }

    public function test_store_has_no_wallet_or_top_up_ui(): void
    {
        $this->seed(ArtworkFixtureSeeder::class);
        $buyer = User::create([
            'name' => 'Wallet Buyer',
            'email' => 'buyer@example.test',
            'password' => 'secret-password',
        ]);

        $this->actingAs($buyer)->get('/store')
            ->assertOk()
            ->assertSee('NFT Store')
            ->assertDontSee('Pixel Coin')
            ->assertDontSee('wallet');
        $this->assertDatabaseHas('artworks', ['id' => 'neon-wanderer', 'price' => '0.86']);
        $this->getJson('/api/pixel-coins/purchases')->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $buyer->id, 'pixel_coin_balance' => 0]);
        $this->assertDatabaseCount('pixel_coin_transactions', 0);
    }

    public function test_market_estimate_combines_popularity_creator_reputation_supply_and_inactivity(): void
    {
        $now = CarbonImmutable::parse('2026-10-29 12:00:00');

        $baseline = ArtworkMarketValue::estimate(100, $now, 0, null, $now, itemCount: 10);
        $popular = ArtworkMarketValue::estimate(100, $now, 4, $now, $now, 80, 500, 0, 0, 0, 10);
        $famousCreator = ArtworkMarketValue::estimate(100, $now, 0, null, $now, 0, 0, 100, 1000, 50, 10);
        $scarce = ArtworkMarketValue::estimate(100, $now, 0, null, $now, itemCount: 1);
        $abundant = ArtworkMarketValue::estimate(100, $now, 0, null, $now, itemCount: 100);

        $this->assertSame(100.0, $baseline['value']);
        $this->assertGreaterThan($baseline['value'], $popular['value']);
        $this->assertGreaterThan($baseline['value'], $famousCreator['value']);
        $this->assertGreaterThan($abundant['value'], $scarce['value']);
        $this->assertSame(1, $scarce['item_count']);

        $falling = ArtworkMarketValue::estimate(
            100,
            $now->subDays(100),
            0,
            null,
            $now,
            itemCount: 10,
        );
        $this->assertSame(90.0, $falling['value']);
        $this->assertSame(-10.0, $falling['change_percent']);

        $capped = ArtworkMarketValue::estimate(
            100,
            $now->subDays(1),
            20,
            $now,
            $now,
            200,
            2000,
            200,
            2000,
            100,
            1,
        );
        $this->assertSame(140.0, $capped['value']);
        $this->assertSame(40.0, $capped['change_percent']);
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

    public function test_store_and_admin_screens_render_seeded_artwork(): void
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

        $this->get('/week12')->assertRedirect('/store');
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
        $this->assertSame('A refreshed home page', $homeContent['hero_heading']);
        $this->assertSame('Fresh trends', $homeContent['trending']['heading']);

        $this->post('/admin/content/marketplace', [
            'content' => ['hero' => ['heading_before' => 'Discover a refreshed']],
            'images' => ['hero' => ['image' => UploadedFile::fake()->createWithContent('marketplace-hero.png', $imageContent)]],
        ])->assertRedirect('/admin/content/marketplace');
        $this->get('/week12')->assertRedirect('/store');
        $this->assertDatabaseHas('site_pages', ['slug' => 'marketplace']);
        $this->get('/admin/content/login')->assertOk()->assertSee('Log-in page copy');
        $this->post('/admin/content/login', [
            'content' => ['heading' => 'Sign in to Pixmoree'],
        ])->assertRedirect('/admin/content/login');
        $this->get('/admin/content/register')->assertOk()->assertSee('Registration page copy');
        $this->post('/admin/content/register', [
            'content' => ['heading' => 'Join Pixmoree'],
        ])->assertRedirect('/admin/content/register');
        Auth::logout();
        $this->get('/login')->assertOk()->assertSee('Sign in to Pixmoree');
        $this->get('/register')->assertOk()->assertSee('Join Pixmoree');
    }

    public function test_admin_can_moderate_and_delete_creator_artwork(): void
    {
        $admin = User::create([
            'name' => 'Beta Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();
        $creator = User::create([
            'name' => 'Test Creator',
            'email' => 'test-creator@example.test',
            'password' => 'secret-password',
        ]);
        $artwork = Artwork::create([
            'id' => 'creator-test-artwork',
            'user_id' => $creator->id,
            'title' => 'Test Artwork',
            'creator' => $creator->name,
            'category' => 'Space',
            'price' => '2.50',
            'image' => 'nfts/ethereal-life.png',
            'description' => 'A creator listing for the Laravel feature suite.',
            'status' => 'Review',
        ]);
        $this->actingAs($admin);

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
        $this->get('/user')->assertOk()->assertSee('Noah Original');
        $this->get('/store')
            ->assertOk()
            ->assertSee('Noah Original')
            ->assertSee('class="store-card"', false);

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
