<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArtworkSeeder extends Seeder
{
    public function run(): void
    {
        $creatorProfiles = [
            'edreen.dorado@lspu.edu.ph' => ['name' => 'Edreen Dorado', 'avatar' => 'assets/creators/edreen.jpg'],
            'noah.creator@pixmoree.test' => ['name' => 'noah', 'avatar' => 'assets/creators/noah.jpg'],
            'kenneth.creator@pixmoree.test' => ['name' => 'kenneth', 'avatar' => 'assets/creators/kenneth.png'],
            'jet.creator@pixmoree.test' => ['name' => 'jet', 'avatar' => 'assets/creators/jet.jpg'],
        ];
        $creators = [];

        foreach ($creatorProfiles as $email => $profile) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $profile['name'], 'password' => Str::random(48)]
            );
            $attributes = ['avatar_path' => $profile['avatar']];
            if ($email === 'edreen.dorado@lspu.edu.ph' && $user->name === 'Insannn') {
                $attributes['name'] = $profile['name'];
            }
            $user->forceFill($attributes)->save();
            $creators[$email] = $user;
        }

        $now = now();
        $artworks = [
            [
                'id' => 'neon-wanderer',
                'title' => 'Ethereal Life',
                'owner_email' => 'edreen.dorado@lspu.edu.ph',
                'category' => 'Digital Art',
                'price' => 0.86,
                'image' => 'nfts/ethereal-life.png',
                'description' => 'An original digital artwork contributed to the Pixmoree gallery.',
                'status' => 'Listed',
            ],
            [
                'id' => 'moonlit-guardian',
                'title' => 'Rather Lie',
                'owner_email' => 'noah.creator@pixmoree.test',
                'category' => 'Illustration',
                'price' => 1.24,
                'image' => 'nfts/rather-lie.png',
                'description' => 'An original illustration contributed to the Pixmoree gallery.',
                'status' => 'Listed',
            ],
            [
                'id' => 'starlight-drift',
                'title' => 'Sneaker Study',
                'owner_email' => 'kenneth.creator@pixmoree.test',
                'category' => 'Digital Art',
                'price' => 1.52,
                'image' => 'nfts/sneaker-study.jpg',
                'description' => 'An original digital artwork contributed to the Pixmoree gallery.',
                'status' => 'Listed',
            ],
            [
                'id' => 'neon-character',
                'title' => 'Neon Character',
                'owner_email' => 'jet.creator@pixmoree.test',
                'category' => 'Illustration',
                'price' => 1.15,
                'image' => 'nfts/neon-character.png',
                'description' => 'An original illustration contributed to the Pixmoree gallery.',
                'status' => 'Listed',
            ],
            [
                'id' => 'team-poster',
                'title' => 'Team Poster',
                'owner_email' => 'edreen.dorado@lspu.edu.ph',
                'category' => 'Poster',
                'price' => 0.95,
                'image' => 'nfts/team-poster.png',
                'description' => 'An original poster contributed to the Pixmoree gallery.',
                'status' => 'Listed',
            ],
            [
                'id' => 'music-analytics',
                'title' => 'Music Analytics',
                'owner_email' => 'noah.creator@pixmoree.test',
                'category' => 'Poster',
                'price' => 1.08,
                'image' => 'nfts/music-analytics.png',
                'description' => 'An original poster contributed to the Pixmoree gallery.',
                'status' => 'Listed',
            ],
            [
                'id' => 'monochrome-study',
                'title' => 'Monochrome Study',
                'owner_email' => 'kenneth.creator@pixmoree.test',
                'category' => 'Illustration',
                'price' => 1.32,
                'image' => 'nfts/monochrome-study.png',
                'description' => 'An original illustration contributed to the Pixmoree gallery.',
                'status' => 'Listed',
            ],
        ];

        foreach ($artworks as &$artwork) {
            $owner = $creators[$artwork['owner_email']];
            $artwork['user_id'] = $owner->id;
            $artwork['creator'] = $owner->name;
            $artwork['image_hash'] = hash_file('sha256', public_path('assets/' . $artwork['image']));
            if ($artwork['image_hash'] === false) {
                throw new \RuntimeException('A supplied artwork image could not be verified: ' . $artwork['image']);
            }
            unset($artwork['owner_email']);
            $artwork['created_at'] = $now;
            $artwork['updated_at'] = $now;
        }
        unset($artwork);

        DB::table('artworks')->upsert(
            $artworks,
            ['id'],
            ['title', 'creator', 'user_id', 'category', 'price', 'image', 'image_hash', 'description', 'status', 'updated_at']
        );
    }
}
