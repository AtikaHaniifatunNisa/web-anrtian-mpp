<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\ExternalAudioService;

class AudioController extends Controller
{
    /**
     * Get audio URL for announcement
     */
    public function getAnnouncementAudio(Request $request): JsonResponse
    {
        $queueNumber = $request->input('queueNumber');
        $serviceName = $request->input('serviceName');
        $counterName = $request->input('counterName');
        $zona = $request->input('zona');
        
        // Default audio URL (bisa diganti dengan link eksternal)
        $defaultAudioUrl = asset('sounds/opening.mp3');
        
        // Jika ada parameter, buat URL dengan parameter
        if ($queueNumber || $serviceName || $counterName || $zona) {
            $audioUrl = $this->generateAudioUrl($queueNumber, $serviceName, $counterName, $zona);
        } else {
            $audioUrl = $defaultAudioUrl;
        }
        
        return response()->json([
            'success' => true,
            'audioUrl' => $audioUrl,
            'queueNumber' => $queueNumber,
            'serviceName' => $serviceName,
            'counterName' => $counterName,
            'zona' => $zona
        ]);
    }
    
    /**
     * Generate audio URL based on parameters
     */
    private function generateAudioUrl($queueNumber, $serviceName, $counterName, $zona): string
    {
        $text = "Nomor antrian {$queueNumber}, layanan {$serviceName}, menuju ke loket {$counterName}, ZONA {$zona}. Terima kasih.";
        
        $audioService = new ExternalAudioService();
        $service = config('services.audio.default_service', 'default');
        
        return $audioService->generateAudioUrl($text, $service);
    }
    
    /**
     * Generate audio using Google Text-to-Speech
     */
    private function generateGoogleTTS(string $text): string
    {
        // Implementasi Google TTS
        // Perlu API key dan setup Google Cloud
        $apiKey = config('services.google.tts_api_key');
        
        if (!$apiKey) {
            return asset('sounds/opening.mp3');
        }
        
        $url = 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . $apiKey;
        
        $data = [
            'input' => ['text' => $text],
            'voice' => [
                'languageCode' => 'id-ID',
                'name' => 'id-ID-Wavenet-A',
                'ssmlGender' => 'FEMALE'
            ],
            'audioConfig' => [
                'audioEncoding' => 'MP3'
            ]
        ];
        
        // Simpan audio ke storage dan return URL
        $filename = 'announcement_' . time() . '.mp3';
        $filepath = 'audio/' . $filename;
        
        // Implementasi HTTP request ke Google TTS
        // ... (implementasi lengkap)
        
        return Storage::url($filepath);
    }
    
    /**
     * Generate audio using ElevenLabs
     */
    private function generateElevenLabsTTS(string $text): string
    {
        // Implementasi ElevenLabs TTS
        $apiKey = config('services.elevenlabs.api_key');
        $voiceId = config('services.elevenlabs.voice_id');
        
        if (!$apiKey || !$voiceId) {
            return asset('sounds/opening.mp3');
        }
        
        $url = "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}";
        
        $data = [
            'text' => $text,
            'model_id' => 'eleven_multilingual_v2',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.5
            ]
        ];
        
        // Implementasi HTTP request ke ElevenLabs
        // ... (implementasi lengkap)
        
        return asset('sounds/opening.mp3');
    }
    
    /**
     * Upload custom audio file
     */
    public function uploadAudio(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg|max:10240', // 10MB max
            'name' => 'required|string|max:255'
        ]);
        
        $file = $request->file('audio');
        $filename = time() . '_' . $file->getClientOriginalName();
        $filepath = $file->storeAs('audio', $filename, 'public');
        
        return response()->json([
            'success' => true,
            'message' => 'Audio berhasil diupload',
            'audioUrl' => Storage::url($filepath),
            'filename' => $filename
        ]);
    }
    
    /**
     * Get list of available audio files
     */
    public function getAudioList(): JsonResponse
    {
        $audioFiles = Storage::files('audio');
        $audioList = [];
        
        foreach ($audioFiles as $file) {
            $audioList[] = [
                'filename' => basename($file),
                'url' => Storage::url($file),
                'size' => Storage::size($file),
                'lastModified' => Storage::lastModified($file)
            ];
        }
        
        return response()->json([
            'success' => true,
            'audioList' => $audioList
        ]);
    }
    
    /**
     * Delete audio file
     */
    public function deleteAudio(Request $request): JsonResponse
    {
        $filename = $request->input('filename');
        
        if (!$filename) {
            return response()->json([
                'success' => false,
                'message' => 'Filename required'
            ], 400);
        }
        
        $filepath = 'audio/' . $filename;
        
        if (Storage::exists($filepath)) {
            Storage::delete($filepath);
            
            return response()->json([
                'success' => true,
                'message' => 'Audio berhasil dihapus'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'File tidak ditemukan'
        ], 404);
    }
}
