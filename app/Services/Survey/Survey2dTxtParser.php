<?php

namespace App\Services\Survey;

class Survey2dTxtParser
{
    /**
     * @return array{type: string, days: array<int, int>, records: array<int, array<string, mixed>>}
     */
    public function parse(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (count($lines) < 2) {
            throw new \InvalidArgumentException('Fail TXT kosong atau tidak sah.');
        }

        $header = array_map('trim', str_getcsv(array_shift($lines)));
        $map = [
            'day'    => $this->columnIndex($header, ['DAY']),
            'point'  => $this->columnIndex($header, ['POINT']),
            'xb'     => $this->columnIndex($header, ['Xb', 'XB']),
            'yb'     => $this->columnIndex($header, ['Yb', 'YB']),
            'zb'     => $this->columnIndex($header, ['Zb', 'ZB']),
            'dxy_mm' => $this->columnIndex($header, ['DXY(mm)', 'DXY']),
            'dz_mm'  => $this->columnIndex($header, ['DZ(mm)', 'DZ']),
        ];

        if ($map['day'] === null || $map['point'] === null || $map['xb'] === null || $map['yb'] === null) {
            throw new \InvalidArgumentException('Lajur DAY, POINT, Xb, Yb diperlukan.');
        }

        $records = [];
        $days = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cols = str_getcsv($line);
            $day = (int) $cols[$map['day']];
            $days[$day] = $day;

            $records[] = [
                'day'    => $day,
                'point'  => (string) $cols[$map['point']],
                'xb'     => (float) $cols[$map['xb']],
                'yb'     => (float) $cols[$map['yb']],
                'zb'     => $map['zb'] !== null ? (float) $cols[$map['zb']] : null,
                'dxy_mm' => $map['dxy_mm'] !== null ? (float) $cols[$map['dxy_mm']] : 0,
                'dz_mm'  => $map['dz_mm'] !== null ? (float) $cols[$map['dz_mm']] : 0,
            ];
        }

        if (empty($records)) {
            throw new \InvalidArgumentException('Tiada rekod dalam fail TXT.');
        }

        sort($days);

        return [
            'type'    => '2d',
            'days'    => array_values($days),
            'records' => $records,
        ];
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
