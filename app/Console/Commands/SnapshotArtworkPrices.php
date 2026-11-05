<?php

namespace App\Console\Commands;

use App\Support\ArtworkPriceDashboard;
use Illuminate\Console\Command;

class SnapshotArtworkPrices extends Command
{
    protected $signature = 'artworks:prices:snapshot';

    protected $description = 'Capture daily market estimates for listed artwork';

    public function handle(ArtworkPriceDashboard $dashboard): int
    {
        $count = $dashboard->captureDailySnapshot();

        $this->info("Captured daily prices for {$count} listed artworks.");

        return self::SUCCESS;
    }
}
