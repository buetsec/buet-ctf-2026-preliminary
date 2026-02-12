<?php

namespace App\Services;

use Illuminate\Support\Str;

class ImplantRegistry
{
    private array $implants;

    public function __construct()
    {
        $this->implants = $this->baseImplants();
    }

    private function baseImplants(): array
    {
        return [
            [
                'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'hostname' => 'CORP-WS-0142',
                'os' => 'Windows 10 Pro 21H2',
                'ip' => '10.0.15.142',
                'status' => 'active',
                'implant_version' => '3.2.1',
                'beacon_floor_s' => 12,
                'beacon_ceil_s' => 180,
            ],
            [
                'id' => 'b2c3d4e5-f6a7-8901-bcde-f23456789012',
                'hostname' => 'DEV-SERVER-03',
                'os' => 'Ubuntu 22.04 LTS',
                'ip' => '10.0.20.103',
                'status' => 'active',
                'implant_version' => '3.2.1',
                'beacon_floor_s' => 25,
                'beacon_ceil_s' => 300,
            ],
            [
                'id' => 'c3d4e5f6-a7b8-9012-cdef-345678901234',
                'hostname' => 'EXEC-LAPTOP-CEO',
                'os' => 'macOS 14.2',
                'ip' => '10.0.5.15',
                'status' => 'active',
                'implant_version' => '3.2.0',
                'beacon_floor_s' => 6,
                'beacon_ceil_s' => 120,
            ],
            [
                'id' => 'd4e5f6a7-b8c9-0123-defa-456789012345',
                'hostname' => 'DB-PROD-01',
                'os' => 'CentOS Stream 9',
                'ip' => '10.0.30.50',
                'status' => 'dormant',
                'implant_version' => '3.1.9',
                'beacon_floor_s' => 900,
                'beacon_ceil_s' => 5400,
            ],
            [
                'id' => 'e5f6a7b8-c9d0-1234-efab-567890123456',
                'hostname' => 'HR-WS-0087',
                'os' => 'Windows 11 Enterprise',
                'ip' => '10.0.12.87',
                'status' => 'offline',
                'implant_version' => '3.2.1',
                'beacon_floor_s' => 3600,
                'beacon_ceil_s' => 14400,
            ],
        ];
    }

    public function getActiveImplants(): array
    {
        $out = [];
        foreach ($this->implants as $implant) {
            $out[] = $this->hydrateImplant($implant);
        }
        return $out;
    }

    public function findImplant(string $id): ?array
    {
        foreach ($this->implants as $implant) {
            if ($implant['id'] === $id) {
                return $this->hydrateImplant($implant);
            }
        }
        return null;
    }

    public function getTelemetryHistory(string $id): array
    {
        $bucket = $this->timeBucket(5);
        $rows = [];
        for ($i = 0; $i < 12; $i++) {
            $t = now()->subSeconds(($i * 15) + ($bucket % 7));
            $rows[] = [
                'timestamp' => $t->toIso8601String(),
                'cpu' => $this->prandInt($id.'|cpu|'.$i, 9, 68, 5),
                'memory' => $this->prandInt($id.'|mem|'.$i, 22, 86, 5),
                'network_in' => $this->prandInt($id.'|nin|'.$i, 80, 920, 5),
                'network_out' => $this->prandInt($id.'|nout|'.$i, 40, 460, 5),
            ];
        }
        return $rows;
    }

    public function verifyIntegrity(string $id): array
    {
        return [
            'checksum_valid' => true,
            'signature_verified' => true,
            'last_verification' => now()->subHours(1)->toIso8601String(),
            'next_scheduled' => now()->addHours(5)->toIso8601String(),
        ];
    }

    public function getSystemStats(): array
    {
        $implants = $this->getActiveImplants();
        $bucket = $this->timeBucket(3);
        $active = collect($implants)->where('status', 'active')->count();
        $dormant = collect($implants)->where('status', 'dormant')->count();
        $offline = collect($implants)->where('status', 'offline')->count();

        // "Total exfil" slowly increases over time (fake but plausible).
        $baseTb = 2.4;
        $deltaTb = (time() % 86400) / 86400 * 0.12; // up to +0.12 TB/day
        $totalTb = $baseTb + $deltaTb;

        // Uptime is static-looking but changes occasionally.
        $days = 47 + intdiv(time(), 86400) % 3;
        $hours = (13 + intdiv(time(), 3600)) % 24;
        $mins = (22 + intdiv(time(), 60)) % 60;

        return [
            'total_implants' => count($implants),
            'active' => $active,
            'dormant' => $dormant,
            'offline' => $offline,
            'total_data_exfil' => number_format($totalTb, 2).' TB',
            'uptime' => "{$days}d {$hours}h {$mins}m",
            'c2_health' => ($bucket % 37 === 0) ? 'degraded' : 'nominal',
            'jobs_queued' => $this->prandInt('jobs', 0, 14, 3),
            'packets_per_s' => $this->prandInt('pps', 1200, 9200, 3),
            'agg_cpu' => $this->prandInt('agg_cpu', 18, 74, 3),
            'agg_mem' => $this->prandInt('agg_mem', 34, 88, 3),
            'net_in_mbps' => $this->prandInt('net_in', 40, 420, 3),
            'net_out_mbps' => $this->prandInt('net_out', 20, 240, 3),
        ];
    }

    public function getOperationsLog(): array
    {
        $templates = [
            ['event' => 'BEACON_RX', 'details' => 'Beacon received; no tasking'],
            ['event' => 'TASK_ACK', 'details' => 'Task acknowledged; awaiting completion'],
            ['event' => 'DNS_TUNNEL', 'details' => 'Fallback channel active; jitter within bounds'],
            ['event' => 'DATA_STAGE', 'details' => 'Staging complete; exfil window scheduled'],
            ['event' => 'CRED_ENUM', 'details' => 'Credential material enumerated (local)'],
            ['event' => 'PROC_SPAWN', 'details' => 'Transient process spawned; parent spoofed'],
            ['event' => 'NET_SCAN', 'details' => 'Segment sweep completed; 3 hosts tagged'],
            ['event' => 'INTEGRITY_OK', 'details' => 'Integrity check passed; signature valid'],
        ];

        $implants = $this->getActiveImplants();
        $sources = array_values(array_map(fn ($i) => $i['hostname'], $implants));
        $bucket = $this->timeBucket(4);

        $rows = [];
        for ($i = 0; $i < 10; $i++) {
            $tpl = $templates[$this->prandInt('tpl|'.$i, 0, count($templates) - 1, 4)];
            $src = $sources[$this->prandInt('src|'.$i, 0, max(0, count($sources) - 1), 4)] ?? 'UNKNOWN';
            $rows[] = [
                'timestamp' => now()->subSeconds(($i * 22) + ($bucket % 9))->toIso8601String(),
                'event' => $tpl['event'],
                'source' => $src,
                'details' => $tpl['details'],
            ];
        }

        return $rows;
    }

    private function hydrateImplant(array $implant): array
    {
        $bucket = $this->timeBucket(3);

        $min = (int) ($implant['beacon_floor_s'] ?? 10);
        $max = (int) ($implant['beacon_ceil_s'] ?? 300);
        $since = $this->prandInt($implant['id'].'|beacon', $min, $max, 3);
        $lastBeacon = now()->subSeconds($since)->toIso8601String();

        // Light status drift for realism.
        $status = $implant['status'];
        if ($status !== 'offline') {
            if (($bucket + $this->prandInt($implant['id'].'|flip', 0, 50, 3)) % 41 === 0) {
                $status = 'dormant';
            }
            if (($bucket + $this->prandInt($implant['id'].'|flip2', 0, 50, 3)) % 53 === 0) {
                $status = 'active';
            }
        }

        return [
            'id' => $implant['id'],
            'hostname' => $implant['hostname'],
            'os' => $implant['os'],
            'ip' => $implant['ip'],
            'last_beacon' => $lastBeacon,
            'status' => $status,
            'implant_version' => $implant['implant_version'],
        ];
    }

    private function timeBucket(int $seconds): int
    {
        $seconds = max(1, $seconds);
        return intdiv(time(), $seconds);
    }

    private function prandInt(string $seed, int $min, int $max, int $bucketSeconds): int
    {
        $min = min($min, $max);
        $max = max($min, $max);
        $bucket = $this->timeBucket($bucketSeconds);
        $h = crc32($seed.'|'.$bucket);
        $span = ($max - $min) + 1;
        return $min + ($h % $span);
    }
}
