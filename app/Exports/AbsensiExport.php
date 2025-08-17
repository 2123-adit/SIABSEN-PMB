<?php
// app/Exports/AbsensiExport.php - MATRIX FORMAT V3

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AbsensiExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $absensis;
    protected $filters;
    protected $dateRange;
    protected $lateAttendanceData = []; // Store positions of late attendance
    protected $alfaAttendanceData = []; // Store positions of alfa attendance

    public function __construct($absensis, $filters)
    {
        $this->absensis = $absensis;
        $this->filters = $filters;
        $this->dateRange = CarbonPeriod::create($filters['tanggal_mulai'], $filters['tanggal_selesai']);
    }

    public function array(): array
    {
        // Group absensi by user
        $grouped = $this->absensis->groupBy('user.id');
        $data = [];
        $rowIndex = 2; // Starting from row 2 (after header)

        $no = 1; // Initialize row number
        foreach ($grouped as $userId => $records) {
            $user = $records->first()->user;
            $recordsByDate = $records->keyBy(fn($a) => $a->tanggal->format('Y-m-d'));
            
            $row = [
                $no++, // Add row number
                $user->name,
                $user->jabatan->nama_jabatan ?? '-'
            ];

            // Add attendance data for each date
            $colIndex = 'D'; // Starting from column D (after No, Name and Position)
            foreach ($this->dateRange as $date) {
                $record = $recordsByDate[$date->format('Y-m-d')] ?? null;
                
                if ($record) {
                    if ($record->status_kehadiran === 'hadir') {
                        $jamMasuk = $record->jam_masuk ? Carbon::parse($record->jam_masuk)->format('H:i') : '-';
                        $jamPulang = $record->jam_pulang ? Carbon::parse($record->jam_pulang)->format('H:i') : '-';
                        $menitTerlambat = $record->menit_terlambat ?? 0;
                        
                        // Format sama seperti PDF: Status, Jam, Menit terlambat
                        $cellValue = "HADIR\n{$jamMasuk} - {$jamPulang}\n{$menitTerlambat} menit";
                        
                        // Store late attendance positions for styling
                        if ($menitTerlambat > 0) {
                            $this->lateAttendanceData[] = $colIndex . $rowIndex;
                        }
                    } else {
                        $cellValue = strtoupper($record->status_kehadiran);
                    }
                } else {
                    // Check if this date is a working day for this user's position
                    $isWorkingDay = true;
                    if ($user->jabatan && method_exists($user->jabatan, 'isWorkingDay')) {
                        $isWorkingDay = $user->jabatan->isWorkingDay($date);
                    } else {
                        // Default: Senin-Jumat (not weekend)
                        $isWorkingDay = !in_array($date->dayOfWeek, [0, 6]); // 0=Sunday, 6=Saturday
                    }
                    
                    if ($isWorkingDay) {
                        $cellValue = 'ALFA';
                        // Store alfa attendance positions for styling
                        $this->alfaAttendanceData[] = $colIndex . $rowIndex;
                    } else {
                        $cellValue = '-'; // Empty for non-working days
                    }
                }
                
                $row[] = $cellValue;
                $colIndex++; // Move to next column
            }

            $data[] = $row;
            $rowIndex++; // Move to next row
        }

        return $data;
    }

    public function headings(): array
    {
        $headers = [
            'No',
            'Nama Karyawan',
            'Jabatan'
        ];

        // Add date headers
        foreach ($this->dateRange as $date) {
            $headers[] = $date->format('d/m');
        }

        return $headers;
    }

    public function title(): string
    {
        $periode = Carbon::parse($this->filters['tanggal_mulai'])->format('d/m/Y') . ' - ' . 
                   Carbon::parse($this->filters['tanggal_selesai'])->format('d/m/Y');
        return "Laporan Absensi {$periode}";
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2C3E50']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            // No, Name and Position columns (A, B & C)
            'A2:C' . $lastRow => [
                'font' => [
                    'size' => 10,
                    'bold' => true
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            // Date columns (D onwards)
            'D2:' . $lastColumn . $lastRow => [
                'font' => [
                    'size' => 9
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]
        ];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // No
            'B' => 25,  // Nama Karyawan
            'C' => 20,  // Jabatan
        ];

        // Add width for date columns - make them narrower since they're just dates
        $columnIndex = 'D'; // Start from column D (after No, Name, Position)
        foreach ($this->dateRange as $date) {
            $widths[$columnIndex] = 18; // Width for date columns
            $columnIndex++;
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Apply special styling to late attendance cells
                foreach ($this->lateAttendanceData as $cellAddress) {
                    $event->sheet->getStyle($cellAddress)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFE6E6'] // Light red background for late
                        ],
                        'font' => [
                            'color' => ['rgb' => 'D63031'] // Red text for late
                        ]
                    ]);
                }
                
                // Apply special styling to alfa attendance cells
                foreach ($this->alfaAttendanceData as $cellAddress) {
                    $event->sheet->getStyle($cellAddress)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFCCCC'] // Light red background for alfa
                        ],
                        'font' => [
                            'color' => ['rgb' => 'B71C1C'], // Dark red text for alfa
                            'bold' => true
                        ]
                    ]);
                }
                
                // Set row height to accommodate multi-line text
                $lastRow = $event->sheet->getHighestRow();
                for ($i = 2; $i <= $lastRow; $i++) {
                    $event->sheet->getRowDimension($i)->setRowHeight(45);
                }
                
                // Set header row height
                $event->sheet->getRowDimension(1)->setRowHeight(25);
            }
        ];
    }
}