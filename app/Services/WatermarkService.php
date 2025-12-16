<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Rectangle;
use Intervention\Image\Encoders\JpegEncoder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Pegawai;
use App\Models\Satpelkes;

class WatermarkService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Proses foto dan tambahkan watermark
     */
    public function processFoto(
        string $fotoBase64,
        int $presensiId,
        int $pegawaiId,
        float $latitude,
        float $longitude,
        float $accuracy,
        ?int $satpelkesId,
        ?float $jarak,
        string $status
    ): array {
        // Validasi dan decode base64
        if (empty($fotoBase64)) {
            throw new \Exception('Foto base64 tidak boleh kosong');
        }
        
        // Hapus data URI prefix jika ada
        $base64String = preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64);
        
        // Validasi base64 string
        if (empty($base64String)) {
            throw new \Exception('Data base64 tidak valid. Pastikan foto sudah diambil dengan benar.');
        }
        
        // Decode base64 dengan strict mode
        $imageData = base64_decode($base64String, true);
        
        // Validasi hasil decode
        if ($imageData === false || empty($imageData)) {
            throw new \Exception('Gagal mendekode foto base64. Format tidak valid. Pastikan foto sudah diambil dengan benar.');
        }
        
        // Validasi bahwa hasil decode adalah gambar yang valid
        $imageInfo = @getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            throw new \Exception('Data yang di-decode bukan merupakan gambar yang valid. Pastikan format foto benar (JPG/PNG).');
        }
        
        // Simpan foto original
        $filename = 'original_' . $presensiId . '_' . time() . '.jpg';
        $pathOriginal = 'absensi/foto/' . $filename;
        
        try {
            Storage::disk('public')->put($pathOriginal, $imageData);
        } catch (\Exception $e) {
            throw new \Exception('Gagal menyimpan foto original: ' . $e->getMessage());
        }

        // Load image untuk watermark dengan error handling
        try {
            $image = $this->imageManager->read($imageData);
        } catch (\Exception $e) {
            // Jika gagal memproses gambar, gunakan foto original saja
            \Illuminate\Support\Facades\Log::warning('Gagal memproses gambar untuk watermark', [
                'presensi_id' => $presensiId,
                'error' => $e->getMessage()
            ]);
            
            // Return foto original saja jika watermark gagal
            return [
                'foto_asli' => $pathOriginal,
                'foto_watermark' => $pathOriginal, // Gunakan foto original sebagai fallback
            ];
        }

        // Ambil data pegawai dan satpelkes
        $pegawai = Pegawai::find($pegawaiId);
        if (!$pegawai) {
            throw new \Exception('Pegawai tidak ditemukan');
        }
        
        $satpelkes = $satpelkesId ? Satpelkes::find($satpelkesId) : null;

        // Generate text watermark
        $watermarkText = $this->generateWatermarkText(
            $pegawai,
            $satpelkes,
            $latitude,
            $longitude,
            $accuracy,
            $jarak,
            $status
        );

        // Apply watermark dengan error handling
        try {
            $image = $this->applyWatermark($image, $watermarkText);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal apply watermark', [
                'presensi_id' => $presensiId,
                'error' => $e->getMessage()
            ]);
            
            // Jika watermark gagal, gunakan foto original
            return [
                'foto_asli' => $pathOriginal,
                'foto_watermark' => $pathOriginal, // Gunakan foto original sebagai fallback
            ];
        }

        // Simpan foto dengan watermark
        $filenameWatermark = 'watermark_' . $presensiId . '_' . time() . '.jpg';
        $pathWatermark = 'absensi/foto/' . $filenameWatermark;
        
        try {
            // Untuk Intervention Image v3, gunakan encode() dengan JpegEncoder
            $encoder = new JpegEncoder(85);
            $encodedImage = $image->encode($encoder);
            // encode() mengembalikan objek, perlu toString() untuk mendapatkan binary data
            $imageData = $encodedImage->toString();
            Storage::disk('public')->put($pathWatermark, $imageData);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal menyimpan foto watermark', [
                'presensi_id' => $presensiId,
                'error' => $e->getMessage()
            ]);
            
            // Jika gagal menyimpan watermark, gunakan foto original
            return [
                'foto_asli' => $pathOriginal,
                'foto_watermark' => $pathOriginal, // Gunakan foto original sebagai fallback
            ];
        }

        return [
            'foto_asli' => $pathOriginal,
            'foto_watermark' => $pathWatermark,
        ];
    }

    /**
     * Generate text watermark
     */
    protected function generateWatermarkText(
        Pegawai $pegawai,
        ?Satpelkes $satpelkes,
        float $latitude,
        float $longitude,
        float $accuracy,
        ?float $jarak,
        string $status
    ): string {
        $now = Carbon::now();
        
        $text = "Nama: {$pegawai->nama}\n";
        $text .= "Waktu: " . $now->format('d-m-Y H:i:s') . "\n";
        $text .= "Lokasi: {$latitude}, {$longitude}\n";
        $text .= "Akurasi: " . number_format($accuracy, 2) . " meter\n";
        
        if ($satpelkes) {
            $text .= "Satpelkes: {$satpelkes->nama_satpelkes}\n";
            if ($jarak !== null) {
                $text .= "Jarak ke Satpelkes: " . number_format($jarak, 2) . " meter\n";
            }
        }
        
        $text .= "Status: " . str_replace('_', ' ', $status);

        return $text;
    }

    /**
     * Apply watermark ke gambar
     */
    protected function applyWatermark($image, string $text)
    {
        try {
            $width = $image->width();
            $height = $image->height();

            // Calculate text position (bottom right)
            $fontSize = 14;
            $padding = 10;
            $textBoxWidth = 280;
            $textBoxHeight = 140;
            $x = max(0, $width - $textBoxWidth - $padding); // Pastikan tidak negatif
            $y = max(0, $height - $textBoxHeight - $padding); // Pastikan tidak negatif

            // Draw semi-transparent background rectangle
            // Untuk Intervention Image v3, gunakan fill dengan crop untuk membuat background box
            $boxX = (int)$x;
            $boxY = (int)$y;
            $boxW = min((int)$textBoxWidth, $width - $boxX); // Pastikan tidak melebihi batas gambar
            $boxH = min((int)$textBoxHeight, $height - $boxY); // Pastikan tidak melebihi batas gambar
            
            if ($boxW > 0 && $boxH > 0) {
                try {
                    // Buat layer baru untuk background box
                    $boxImage = $this->imageManager->create($boxW, $boxH);
                    $boxImage->fill('rgba(0, 0, 0, 180)'); // Alpha value: 0-255, 180 = ~0.7 opacity
                    
                    // Place box ke image utama
                    $image->place($boxImage, 'top-left', $boxX, $boxY);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal membuat background box watermark', [
                        'error' => $e->getMessage()
                    ]);
                    // Lanjutkan tanpa background box
                }
            }

            // Draw text
            $lines = explode("\n", $text);
            $lineHeight = 18;
            $currentY = $y + 15; // Start dari dalam box dengan padding

            foreach ($lines as $line) {
                if (!empty(trim($line)) && $currentY < $height) {
                    try {
                        $image->text($line, $x + 10, $currentY, function ($font) use ($fontSize) {
                            // Gunakan font default jika tidak ada font file
                            try {
                                // Coba gunakan font sistem (Windows/Linux)
                                if (file_exists('C:/Windows/Fonts/arial.ttf')) {
                                    $font->file('C:/Windows/Fonts/arial.ttf');
                                } elseif (file_exists('/usr/share/fonts/truetype/arial.ttf')) {
                                    $font->file('/usr/share/fonts/truetype/arial.ttf');
                                }
                            } catch (\Exception $e) {
                                // Fallback ke default font
                            }
                            $font->size($fontSize);
                            $font->color('rgb(255, 255, 255)');
                            $font->align('left');
                            $font->valign('top');
                        });
                    } catch (\Exception $e) {
                        // Skip text jika gagal (misalnya font tidak tersedia)
                        \Illuminate\Support\Facades\Log::warning('Gagal menambahkan text watermark', [
                            'line' => $line,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                $currentY += $lineHeight;
            }

            return $image;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error dalam applyWatermark', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Gagal menambahkan watermark: ' . $e->getMessage());
        }
    }
}

