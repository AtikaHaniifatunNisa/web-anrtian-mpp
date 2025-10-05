<?php

namespace App\Filament\Pages;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Setting;
use Filament\Pages\Page;

class DashboardKiosk extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static string $view = 'filament.pages.dashboard-kiosk';

    protected static string $layout = 'filament.layouts.base-kiosk';

    protected static ?string $navigationLabel = 'Kiosk Ruang Tunggu';

    protected static ?string $navigationGroup = 'Display Kiosk';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }

    public function getViewData(): array
    {
        return [
            'counters' => Counter::with(['service', 'activeQueue', 'nextQueue'])->get(),

            'setting' => Setting::first() ?? (object)[
                'name' => 'Mall Pelayanan Publik',
                'address' => 'Alamat belum diatur',
                'image' => null,
            ],
        ];
    }

    public function callNextQueue()
    {
        $nextQueues = Queue::where('status', 'waiting')
            ->whereDate('created_at', now()->toDateString())
            ->whereNull('called_at')
            ->get();

        foreach ($nextQueues as $nextQueue) {
            if (!$nextQueue->counter) {
                continue;
            }

            // format angka: A12 => A-12
            $spokenNumber = preg_replace('/([A-Za-z])(\d+)/', '$1-$2', $nextQueue->number);

            $this->dispatch(
                "queue-called",
                "Nomor antrian {$spokenNumber} dipersilakan ke " . $nextQueue->counter->name
            );

            $nextQueue->update(['called_at' => now()]);
        }
    }
}
