<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class TicketExport implements FromArray, WithHeadings
{
    protected $records;
    protected $columns;

    public function __construct($records, $columns)
    {
        $this->records = $records;
        $this->columns = $columns;
    }

    public function array(): array
    {
        return $this->records->map(function ($record) {
            return collect($this->columns)->map(function ($column) use ($record) {
                $value = data_get($record, $column);
                
                // Handle date formatting for Y-m-d only
                if (in_array($column, ['created_at', 'updated_at', 'expected_transfer_date', 'latestComment.transfer_date'])) {
                    try {
                        if ($value instanceof \Carbon\Carbon) {
                            return $value->format('Y-m-d');
                        }
                        if (is_string($value) && !empty($value)) {
                            return Carbon::parse($value)->format('Y-m-d');
                        }
                        return null;
                    } catch (\Exception $e) {
                        return $value;
                    }
                }
                
                return $value;
            })->toArray();
        })->toArray();
    }

    public function headings(): array
    {
        $headingMap = [
            'nomor_pengajuan' => 'Nomor Pengajuan',
            'owner.name' => 'Pemilik',
            'owner.region.name' => 'BM',
            'owner.email' => 'Email',
            'title' => 'Judul',
            'status' => 'Status',
            'created_at' => 'Tanggal Diajukan',
            'updated_at' => 'Tanggal Diperbarui',
            'total_project' => 'Total Proyek',
            'total_budget' => 'Total Anggaran',
            'expected_transfer_date' => 'Tanggal Transfer (Diharapkan)',
            'content' => 'Konten',
            'latestComment.transfer_date' => 'Tanggal Transfer (Aktual)',
        ];

        return array_map(function ($column) use ($headingMap) {
            return $headingMap[$column] ?? $column;
        }, $this->columns);
    }
}