<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AudioManagementPage extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-speaker-wave';
    protected static string $view = 'filament.pages.audio-management';
    protected static ?string $title = 'Manajemen Audio';
    protected static ?string $navigationLabel = 'Manajemen Audio';
    protected static ?int $navigationSort = 10;

    public $audioUrl = '';
    public $audioName = '';
    public $audioDescription = '';
    public $audioType = 'announcement';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('audioUrl')
                    ->label('URL Audio Eksternal')
                    ->placeholder('https://example.com/audio.mp3')
                    ->helperText('Masukkan URL audio dari link eksternal')
                    ->url(),
                
                TextInput::make('audioName')
                    ->label('Nama Audio')
                    ->placeholder('Audio Pemanggilan Antrian')
                    ->required(),
                
                Textarea::make('audioDescription')
                    ->label('Deskripsi')
                    ->placeholder('Deskripsi audio...')
                    ->rows(3),
                
                Select::make('audioType')
                    ->label('Tipe Audio')
                    ->options([
                        'announcement' => 'Audio Pemanggilan',
                        'background' => 'Audio Background',
                        'notification' => 'Audio Notifikasi',
                    ])
                    ->default('announcement')
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function getActions(): array
    {
        return [
            Action::make('testAudio')
                ->label('Test Audio')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action('testAudio'),
            
            Action::make('saveAudio')
                ->label('Simpan Audio')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('saveAudio'),
            
            Action::make('uploadAudio')
                ->label('Upload File Audio')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('info')
                ->form([
                    FileUpload::make('audioFile')
                        ->label('File Audio')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/ogg'])
                        ->maxSize(10240) // 10MB
                        ->required(),
                    
                    TextInput::make('fileName')
                        ->label('Nama File')
                        ->placeholder('audio_pemanggilan.mp3')
                        ->required(),
                ])
                ->action('uploadAudio'),
        ];
    }

    public function testAudio(): void
    {
        if (empty($this->audioUrl)) {
            Notification::make()
                ->title('Error')
                ->body('URL audio tidak boleh kosong')
                ->danger()
                ->send();
            return;
        }

        // Test audio dengan membuat audio element
        $this->dispatch('test-audio', url: $this->audioUrl);
        
        Notification::make()
            ->title('Test Audio')
            ->body('Audio sedang di-test, periksa console browser')
            ->info()
            ->send();
    }

    public function saveAudio(): void
    {
        $this->validate([
            'audioUrl' => 'required|url',
            'audioName' => 'required|string|max:255',
        ]);

        // Simpan konfigurasi audio ke database atau config
        // Untuk sekarang, simpan ke session atau cache
        session([
            'audio_config' => [
                'url' => $this->audioUrl,
                'name' => $this->audioName,
                'description' => $this->audioDescription,
                'type' => $this->audioType,
                'updated_at' => now(),
            ]
        ]);

        Notification::make()
            ->title('Berhasil')
            ->body('Konfigurasi audio berhasil disimpan')
            ->success()
            ->send();
    }

    public function uploadAudio(array $data): void
    {
        $file = $data['audioFile'];
        $fileName = $data['fileName'];
        
        // Simpan file ke storage
        $path = $file->storeAs('audio', $fileName, 'public');
        
        // Simpan konfigurasi
        session([
            'audio_config' => [
                'url' => Storage::url($path),
                'name' => $fileName,
                'description' => 'Uploaded audio file',
                'type' => 'announcement',
                'updated_at' => now(),
            ]
        ]);

        Notification::make()
            ->title('Berhasil')
            ->body('File audio berhasil diupload')
            ->success()
            ->send();
    }

    public function getViewData(): array
    {
        $audioConfig = session('audio_config', []);
        
        return [
            'audioConfig' => $audioConfig,
            'currentAudioUrl' => $audioConfig['url'] ?? asset('sounds/opening.mp3'),
        ];
    }
}
