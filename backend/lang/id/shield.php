<?php

return [
    'challenge' => [
        'title' => 'Verifikasi Keamanan Shield',
        'message' => 'Harap tunggu sementara kami memverifikasi bahwa Anda bukan robot. Pengecekan ini membantu melindungi sistem dari serangan otomatis.',
        'retry' => 'Coba Lagi',
        'steps' => [
            'analyze' => 'Analisis Koneksi',
            'solve' => 'Memproses Tantangan',
            'verify' => 'Verifikasi Respons',
        ],
        'status' => [
            'initializing' => 'Memulai pengecekan keamanan...',
            'analyzing' => 'Menganalisis sidik jari browser...',
            'verifying' => 'Membuat bukti kerja (PoW)...',
            'finalizing' => 'Mengirimkan hasil verifikasi...',
            'verified' => 'Verifikasi berhasil! Mengalihkan...',
            'failed' => 'Verifikasi gagal. Silakan coba lagi.',
        ],
    ],
];
