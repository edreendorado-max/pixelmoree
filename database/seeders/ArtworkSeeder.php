<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtworkSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $artworks = [
            [
                'id' => 'neon-wanderer',
                'title' => 'Neon Wanderer',
                'creator' => 'pixel.dreams',
                'category' => 'Space',
                'price' => 0.86,
                'image' => 'anime-style-character-space.jpg',
                'description' => 'A fictional traveler drifts through a violet galaxy in this demo collectible.',
                'status' => 'Listed',
            ],
            [
                'id' => 'moonlit-guardian',
                'title' => 'Moonlit Guardian',
                'creator' => 'neko.studio',
                'category' => 'Characters',
                'price' => 1.24,
                'image' => 'cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png',
                'description' => 'A fictional guardian watches over a quiet moonlit world.',
                'status' => 'Listed',
            ],
            [
                'id' => 'starlight-drift',
                'title' => 'Starlight Drift',
                'creator' => 'cosmic.art',
                'category' => 'Space',
                'price' => 1.52,
                'image' => 'anime-style-character-space (2).jpg',
                'description' => 'An imagined journey across a glowing nebula. This is sample artwork.',
                'status' => 'Review',
            ],
        ];

        foreach ($artworks as &$artwork) {
            $artwork['created_at'] = $now;
            $artwork['updated_at'] = $now;
        }
        unset($artwork);

        DB::table('artworks')->upsert(
            $artworks,
            ['id'],
            ['title', 'creator', 'category', 'price', 'image', 'description', 'status', 'updated_at']
        );
    }
}
