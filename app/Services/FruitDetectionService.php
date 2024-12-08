<?php

namespace App\Services;

use App\Contracts\FruitDetectionServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Http\UploadedFile;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Google\Cloud\Storage\StorageClient;

class FruitDetectionService implements FruitDetectionServiceInterface
{
    private $storagePath = 'cc';
    private $bucketName = 'kantong01';
    private $pythonServiceUrl = 'https://model-app-504129612552.asia-southeast2.run.app';
    private $httpClient;
    private $storage;
    private $bucket;

    public function __construct()
    {
        try {
            $this->storage = new StorageClient([
                'keyFilePath' => base_path('storage/app/gcs/service-account.json'),
                'projectId' => 'freshmate-441608'
            ]);
            
            $this->bucket = $this->storage->bucket($this->bucketName);

            $this->httpClient = new Client([
                'base_uri' => $this->pythonServiceUrl,
                'timeout'  => 30,
                'verify' => false
            ]);
        } catch (Exception $e) {
            Log::error('Gagal inisialisasi Google Cloud Storage: ' . $e->getMessage());
            throw new Exception('Gagal menginisialisasi layanan penyimpanan');
        }
    }

    private function calculateDynamicConfidence(array $response): float 
    {
        $ripeness = $response['ripeness'] ?? '';
        $originalConfidence = (float)($response['confidence'] ?? 0);
        $ripeness_probs = $response['ripeness_probabilities'] ?? null;

        // Get base confidence from model probabilities
        $baseConfidence = $originalConfidence;
        if ($ripeness_probs) {
            $baseConfidence = (float)($ripeness_probs[$ripeness] ?? $originalConfidence);
        }

        // Add small random variation
        $randomFactor = (mt_rand(-10, 10) / 100); // -0.10 to 0.10

        switch ($ripeness) {
            case 'rotten':
                // For rotten: 0.01-0.19
                $minConf = 0.01;
                $maxConf = 0.19;
                $adjustedConf = $minConf + ($baseConfidence * ($maxConf - $minConf));
                return max($minConf, min($maxConf, $adjustedConf + ($adjustedConf * $randomFactor)));

            case 'unripe':
                // For unripe: 0.20-0.69
                $minConf = 0.20;
                $maxConf = 0.69;
                $adjustedConf = $minConf + ($baseConfidence * ($maxConf - $minConf));
                return max($minConf, min($maxConf, $adjustedConf + ($adjustedConf * $randomFactor)));

            case 'ripe':
                // For ripe: 0.70-1.00
                $minConf = 0.70;
                $maxConf = 1.00;
                $adjustedConf = $minConf + ($baseConfidence * ($maxConf - $minConf));
                return max($minConf, min($maxConf, $adjustedConf + ($adjustedConf * $randomFactor)));

            default:
                return $baseConfidence;
        }
    }

    private function saveImage(UploadedFile $imageFile): string
    {
        try {
            $filename = date('Y-m-d_His_') . Str::random(10) . '.' . $imageFile->getClientOriginalExtension();
            $fullPath = $this->storagePath . '/' . $filename;

            $object = $this->bucket->upload(
                fopen($imageFile->getRealPath(), 'r'),
                [
                    'name' => $fullPath,
                    'metadata' => [
                        'contentType' => $imageFile->getMimeType(),
                    ]
                ]
            );

            Log::info('Berhasil upload gambar ke: ' . $fullPath);
            return $fullPath;

        } catch (Exception $e) {
            Log::error('Gagal menyimpan gambar: ' . $e->getMessage());
            throw new Exception('Gagal mengunggah gambar: ' . $e->getMessage());
        }
    }

    public function detectRipeness($imageFile): array
    {
        try {
            if (!$this->checkPythonService()) {
                throw new Exception('Layanan ML tidak tersedia');
            }

            $savedImagePath = $this->saveImage($imageFile);
            Log::info('Gambar disimpan di: ' . $savedImagePath);

            $response = $this->sendToPythonService($imageFile);
            Log::info('Menerima prediksi dari layanan Python', ['response' => $response]);

            // Cek jika hanya ada message (kasus gambar tidak valid/bukan buah)
            if (isset($response['message']) && !isset($response['confidence'])) {
                return [
                    'message' => $response['message']
                ];
            }

            // Calculate dynamic confidence
            $dynamicConfidence = $this->calculateDynamicConfidence($response);
            
            // Format to 2 decimal places
            $formattedConfidence = number_format($dynamicConfidence, 2, '.', '');

            Log::info('Confidence details', [
                'original' => $response['confidence'],
                'dynamic' => $dynamicConfidence,
                'formatted' => $formattedConfidence,
                'ripeness' => $response['ripeness'],
                'probabilities' => $response['ripeness_probabilities'] ?? null
            ]);

            return [
                'message' => $response['message'],
                'fruit_type' => $response['fruit_type'],
                'ripeness' => $response['ripeness'],
                'confidence' => (float)$formattedConfidence,
                'timestamp' => now()->toIso8601String()
            ];

        } catch (Exception $e) {
            if (isset($savedImagePath)) {
                try {
                    $object = $this->bucket->object($savedImagePath);
                    if ($object->exists()) {
                        $object->delete();
                    }
                } catch (Exception $deleteError) {
                    Log::error('Gagal menghapus file error: ' . $deleteError->getMessage());
                }
            }
            Log::error('Deteksi kematangan gagal: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private function checkPythonService(): bool
    {
        try {
            $response = $this->httpClient->get('/health');
            $status = json_decode($response->getBody(), true);
            return $status['status'] === 'healthy';
        } catch (GuzzleException $e) {
            Log::error('Pengecekan layanan Python ML gagal: ' . $e->getMessage());
            return false;
        }
    }

    private function sendToPythonService(UploadedFile $imageFile): array
    {
        try {
            $response = $this->httpClient->post('/predict', [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($imageFile->path(), 'r'),
                        'filename' => $imageFile->getClientOriginalName()
                    ]
                ]
            ]);

            $result = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Gagal mengurai respons dari layanan Python');
            }

            return $result;

        } catch (GuzzleException $e) {
            Log::error('Gagal berkomunikasi dengan layanan Python: ' . $e->getMessage());
            throw new Exception('Gagal memproses gambar: Layanan ML tidak tersedia');
        }
    }

    public function getDetectionHistory(): array
    {
        return [];
    }

    public function getSupportedFruits(): array
    {
        try {
            $response = $this->httpClient->get('/supported-fruits');
            $result = json_decode($response->getBody(), true);
            return $result['fruits'] ?? [];
        } catch (GuzzleException $e) {
            Log::error('Gagal mendapatkan daftar buah yang didukung: ' . $e->getMessage());
            throw new Exception('Gagal mendapatkan daftar buah yang didukung');
        }
    }
}