<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Audio Service
    |--------------------------------------------------------------------------
    |
    | This option controls the default audio service that will be used
    | to generate audio for announcements. Available options:
    | - default: Use local audio files
    | - google: Google Text-to-Speech API
    | - elevenlabs: ElevenLabs API
    | - azure: Azure Cognitive Services
    | - custom: Custom external URL
    |
    */
    'default_service' => env('AUDIO_DEFAULT_SERVICE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Google Text-to-Speech Configuration
    |--------------------------------------------------------------------------
    */
    'google' => [
        'api_key' => env('GOOGLE_TTS_API_KEY'),
        'voice' => [
            'language_code' => 'id-ID',
            'name' => 'id-ID-Wavenet-A',
            'ssml_gender' => 'FEMALE'
        ],
        'audio_config' => [
            'audio_encoding' => 'MP3'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | ElevenLabs Configuration
    |--------------------------------------------------------------------------
    */
    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
        'voice_id' => env('ELEVENLABS_VOICE_ID', 'pNInz6obpgDQGcFmaJgB'),
        'model_id' => 'eleven_multilingual_v2',
        'voice_settings' => [
            'stability' => 0.5,
            'similarity_boost' => 0.5
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Azure Cognitive Services Configuration
    |--------------------------------------------------------------------------
    */
    'azure' => [
        'api_key' => env('AZURE_TTS_API_KEY'),
        'region' => env('AZURE_TTS_REGION', 'eastus'),
        'voice' => [
            'name' => 'id-ID-GadisNeural',
            'language' => 'id-ID'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | ResponsiveVoice Configuration
    |--------------------------------------------------------------------------
    */
    'responsivevoice' => [
        'api_key' => env('RESPONSIVEVOICE_API_KEY'),
        'voice' => env('RESPONSIVEVOICE_VOICE', 'Indonesian Female'),
        'rate' => env('RESPONSIVEVOICE_RATE', 0.8),
        'pitch' => env('RESPONSIVEVOICE_PITCH', 1),
        'volume' => env('RESPONSIVEVOICE_VOLUME', 1),
        'script_url' => 'https://code.responsivevoice.org/responsivevoice.js'
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Audio URL Configuration
    |--------------------------------------------------------------------------
    */
    'custom' => [
        'url' => env('CUSTOM_AUDIO_URL'),
        'placeholders' => [
            '{text}' => 'Full announcement text',
            '{queueNumber}' => 'Queue number only',
            '{serviceName}' => 'Service name only',
            '{counterName}' => 'Counter name only',
            '{zona}' => 'Zone name only'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Audio File Management
    |--------------------------------------------------------------------------
    */
    'file_management' => [
        'storage_disk' => 'public',
        'storage_path' => 'audio',
        'cleanup_days' => 7, // Delete audio files older than 7 days
        'max_file_size' => 10240, // 10MB in KB
        'allowed_formats' => ['mp3', 'wav', 'ogg', 'm4a']
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    */
    'fallback' => [
        'enabled' => true,
        'url' => 'sounds/opening.mp3',
        'use_speech_synthesis' => true
    ]
];
