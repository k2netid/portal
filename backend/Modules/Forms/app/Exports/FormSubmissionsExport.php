<?php

namespace Modules\Forms\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Forms\Models\FormSubmission;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @implements WithMapping<FormSubmission>
 */
class FormSubmissionsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /**
     * @param  Builder<FormSubmission>  $query
     * @param  array<int, string>  $fieldKeys
     */
    public function __construct(protected Builder $query, protected array $fieldKeys) {}

    /**
     * @return Builder<FormSubmission>
     */
    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return array_merge([
            'ID',
            'Submitted At',
            'IP Address',
            'Status',
        ], $this->fieldKeys);
    }

    /**
     * @param  FormSubmission  $submission
     * @return array<int, mixed>
     */
    public function map($submission): array
    {
        $row = [
            $submission->id,
            $submission->created_at ? $submission->created_at->format('Y-m-d H:i:s') : '-',
            $submission->ip_address,
            ucfirst((string) $submission->status),
        ];

        foreach ($this->fieldKeys as $key) {
            /** @var mixed $value */
            $value = $submission->data[$key] ?? '';
            if (is_array($value)) {
                /** @var array<int, string> $parts */
                $parts = [];
                foreach ($value as $item) {
                    if (is_scalar($item)) {
                        $parts[] = (string) $item;
                    }
                }
                $row[] = implode(', ', $parts);
            } else {
                $row[] = $value;
            }
        }

        return $row;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'], // Indigo-600
                ],
            ],
        ];
    }
}
