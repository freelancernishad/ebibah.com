<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class ServerStatusController extends Controller
{
    public function status(): JsonResponse
    {
        $status = [
            'app' => [
                'version' => config('app.version'),
                'environment' => app()->environment(),
                'debug' => config('app.debug'),
            ],
            'database' => $this->checkDatabaseConnection(),
            'server' => $this->getServerStatus(),
        ];

        return response()->json($status);
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getServerStatus(): array
    {
        $cpuUsage = $this->getCpuUsage();
        $memoryUsage = $this->getMemoryUsage();
        $diskUsage = $this->getDiskUsage();

        return [
            'cpu_usage' => $cpuUsage,
            'memory_usage' => $memoryUsage,
            'disk_usage' => $diskUsage,
        ];
    }

    private function getCpuUsage(): ?string
    {
        $process = new Process(['top', '-bn1']);
        $process->run();

        if ($process->isSuccessful()) {
            preg_match('/%Cpu\(s\):\s+(\d+\.\d+)\s+us/', $process->getOutput(), $matches);
            return $matches[1] ?? null;
        }

        return null;
    }

    private function getMemoryUsage(): ?string
    {
        $process = new Process(['free', '-m']);
        $process->run();

        if ($process->isSuccessful()) {
            preg_match('/Mem:\s+(\d+)\s+(\d+)\s+/', $process->getOutput(), $matches);
            if (count($matches) > 2) {
                $totalMemory = $matches[1];
                $usedMemory = $matches[2];
                return round(($usedMemory / $totalMemory) * 100, 2) . '%';
            }
        }

        return null;
    }

    private function getDiskUsage(): string
    {
        return round(disk_free_space("/") / disk_total_space("/") * 100, 2) . '%';
    }
}
