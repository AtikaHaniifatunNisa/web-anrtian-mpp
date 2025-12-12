<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TestPrintPage extends Page
{
    protected static string $view = 'filament.pages.test-print';
    protected static ?string $title = 'Test Print';
    protected static ?string $navigationLabel = 'Test Print';
    protected static ?string $navigationGroup = 'Testing';
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    
    public static function canAccess(): bool
    {
        return Auth::user()->role === 'admin';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->role === 'admin';
    }

    public function testPrint()
    {
        $this->dispatch('notify', type: 'success', message: 'Test print button clicked!');
        
        // Test dengan service ID 1 (Pengambilan Izin)
        $pdfUrl = route('struk.generate', [
            'service_id' => 1,
            'zona' => 'Zona 1'
        ]);
        
        $this->dispatch('open-pdf', url: $pdfUrl);
    }
}
