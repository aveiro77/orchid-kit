<?php

namespace App\Console\Commands;

use App\Models\Opportunity;
use Illuminate\Console\Command;

class CloseExpiredOpportunities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opportunities:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis mengubah status opportunity yang tanggal_expired-nya sudah lewat menjadi closed.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = Opportunity::where('status', 'published')
            ->whereNotNull('tanggal_expired')
            ->whereDate('tanggal_expired', '<', now()->today())
            ->update(['status' => 'closed']);

        $this->info("Berhasil menutup {$count} peluang yang sudah expired.");

        return Command::SUCCESS;
    }
}
