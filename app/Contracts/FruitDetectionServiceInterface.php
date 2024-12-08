<?php

namespace App\Contracts;

interface FruitDetectionServiceInterface
{
    public function detectRipeness(string $imageData): array;
    public function getDetectionHistory(): array;
    public function getSupportedFruits(): array;
    // public function getDiseases(): array;
}