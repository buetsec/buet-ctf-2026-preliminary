<?php

namespace App\Services;

class SystemDiagnostics
{
    public function getSystemStatus(): array
    {
        return [
            'cpu_usage' => rand(25, 45) . '%',
            'memory_usage' => rand(50, 70) . '%',
            'disk_usage' => '67%',
            'network_latency' => rand(12, 35) . 'ms',
            'active_connections' => rand(150, 300),
            'queued_commands' => rand(0, 15),
            'last_sync' => now()->subMinutes(rand(1, 5))->toIso8601String(),
            'build_hash' => 'a7f3c2e1',
            'version' => '3.2.1-stable',
        ];
    }

    public function getRegisteredNodes(): array
    {
        return [
            [
                'alias' => 'node-alpha-01',
                'id' => 'f8e7d6c5-b4a3-9281-7060-504030201000',
                'region' => 'us-east-1',
                'status' => 'operational',
                'load' => rand(20, 40) . '%',
            ],
            [
                'alias' => 'node-beta-02',
                'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'region' => 'eu-west-1',
                'status' => 'operational',
                'load' => rand(30, 60) . '%',
            ],
            [
                'alias' => 'node-gamma-03',
                'id' => 'deadbeef-cafe-babe-dead-beefcafebabe',
                'region' => 'ap-south-1',
                'status' => 'maintenance',
                'load' => '0%',
            ],
        ];
    }

    public function getNodeTelemetry(string $nodeId): array
    {
        return [
            'node_id' => $nodeId,
            'metrics' => [
                'requests_per_second' => rand(100, 500),
                'avg_response_time' => rand(5, 25) . 'ms',
                'error_rate' => '0.' . rand(1, 9) . '%',
                'bandwidth_in' => rand(50, 200) . ' MB/s',
                'bandwidth_out' => rand(100, 400) . ' MB/s',
            ],
            'health' => [
                'cpu' => rand(20, 50),
                'memory' => rand(40, 70),
                'disk_io' => rand(10, 30),
            ],
        ];
    }
}
