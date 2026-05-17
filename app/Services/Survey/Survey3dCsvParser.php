<?php

namespace App\Services\Survey;

class Survey3dCsvParser
{
    /**
     * @return array{type: string, points: array<int, array{id: string, xb: float, yb: float, zb: float}>}
     */
    public function parse(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (count($lines) < 2) {
            throw new \InvalidArgumentException('Fail CSV kosong atau tidak sah.');
        }

        $header = array_map('trim', str_getcsv(array_shift($lines)));
        $xi = $this->columnIndex($header, ['Xb', 'XB', 'X']);
        $yi = $this->columnIndex($header, ['Yb', 'YB', 'Y']);
        $zi = $this->columnIndex($header, ['Zb', 'ZB', 'Z']);

        if ($xi === null || $yi === null || $zi === null) {
            throw new \InvalidArgumentException('Lajur Xb, Yb, Zb diperlukan.');
        }

        $points = [];
        $i = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cols = str_getcsv($line);
            if (count($cols) <= max($xi, $yi, $zi)) {
                continue;
            }
            $i++;
            $points[] = [
                'id' => 'P' . $i,
                'xb' => (float) $cols[$xi],
                'yb' => (float) $cols[$yi],
                'zb' => (float) $cols[$zi],
            ];
        }

        if (empty($points)) {
            throw new \InvalidArgumentException('Tiada titik dalam fail CSV.');
        }

        return ['type' => '3d', 'points' => $points];
    }

    /** @param  array<int, string>  $header */
    private function columnIndex(array $header, array $names): ?int
    {
        foreach ($names as $name) {
            $idx = array_search($name, $header, true);
            if ($idx !== false) {
                return (int) $idx;
            }
        }

        return null;
    }
}
