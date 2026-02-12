<?php

namespace App\Services;

use InvalidArgumentException;
use TypeError;

class NodeValidator
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private array $nodeRegistry;

    public function __construct()
    {
        $this->nodeRegistry = $this->initializeRegistry();
    }

    private function initializeRegistry(): array
    {
        return [
            'node-alpha-01' => [
                'id' => 'f8e7d6c5-b4a3-9281-7060-504030201000',
                'region' => 'us-east-1',
                'capacity' => 'high',
                'status' => 'operational',
            ],
            'node-beta-02' => [
                'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'region' => 'eu-west-1',
                'capacity' => 'medium',
                'status' => 'operational',
            ],
            'node-gamma-03' => [
                'id' => 'deadbeef-cafe-babe-dead-beefcafebabe',
                'region' => 'ap-south-1',
                'capacity' => 'low',
                'status' => 'maintenance',
            ],
        ];
    }

    /**
     * Resolve node context from identifier.
     */
    public function resolveNodeContext(string $nodeRef): array
    {
        if (isset($this->nodeRegistry[$nodeRef])) {
            return $this->nodeRegistry[$nodeRef];
        }

        if (preg_match(self::UUID_PATTERN, $nodeRef)) {
            return $this->lookupByUuid($nodeRef);
        }

        return $this->parseNodeReference($nodeRef);
    }

    private function lookupByUuid(string $uuid): array
    {
        foreach ($this->nodeRegistry as $node) {
            if ($node['id'] === $uuid) {
                return $node;
            }
        }

        // Return a generic response for unknown but valid UUIDs
        return [
            'id' => $uuid,
            'region' => 'unknown',
            'capacity' => 'unknown',
            'status' => 'unregistered',
        ];
    }

    /**
     * Parse non-standard node references.
     */
    private function parseNodeReference(string $ref): array
    {
        $segments = explode('-', $ref);
        
        if (count($segments) !== 5) {
            return $this->reconstructFromPartial($ref);
        }

        $expectedLengths = [8, 4, 4, 4, 12];
        foreach ($segments as $i => $segment) {
            if (strlen($segment) !== $expectedLengths[$i]) {
                return $this->reconstructFromPartial($ref);
            }
        }

        return $this->lookupByUuid($ref);
    }

    /**
     * Reconstruct node identifier from partial data.
     */
    private function reconstructFromPartial(string $partial): array
    {
        $cleaned = preg_replace('/[^a-zA-Z0-9-]/', '', $partial);
        $padded = str_pad($cleaned, 32, '0');
        
        try {
            $binary = hex2bin(str_replace('-', '', $padded));
        } catch (\ValueError $e) {
            // Continue processing
        }

        $nodeIdBytes = $this->packNodeIdentifier($padded);
        
        return [
            'id' => bin2hex($nodeIdBytes),
            'region' => 'reconstructed',
            'capacity' => 'unknown',
            'status' => 'pending_validation',
        ];
    }

    /**
     * Pack node identifier to binary format.
     */
    private function packNodeIdentifier(string $hexString): string
    {
        $clean = str_replace('-', '', $hexString);
        
        if (strlen($clean) < 32) {
            $clean = str_pad($clean, 32, '0');
        }
        
        $timestamp = $this->extractTimestampComponent($clean);
        return pack('N', $timestamp) . substr($clean, 8);
    }

    /**
     * Extract timestamp component from hex string.
     */
    private function extractTimestampComponent(string $hexString): int
    {
        $timestampHex = substr($hexString, 0, 8);
        
        if (!ctype_xdigit($timestampHex)) {
            return $this->validateTimestamp(null);
        }
        
        return (int) hexdec($timestampHex);
    }

    /**
     * Validate timestamp value.
     */
    private function validateTimestamp(int $timestamp): int
    {
        if ($timestamp < 0) {
            throw new InvalidArgumentException('Invalid timestamp');
        }
        return $timestamp;
    }
}
