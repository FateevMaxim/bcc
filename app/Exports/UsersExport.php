<?php

namespace App\Exports;

use App\Models\TrackList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{

    use Importable;
    private $date;
    private $city;
    private $status;

    public function __construct(string|null $date, string $city, string $status)
    {
        $this->date = $date;
        $this->city = $city;
        $this->status = $status;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = TrackList::query()
            ->select('id', 'track_code', 'status', 'city');
        if ($this->date != null){
            $query->whereDate('to_client', $this->date);
        }
        if ($this->city != 'Выберите город'){
            $query->where('city', 'LIKE', $this->city);
        }
        if ($this->status != 'Выберите статус'){
            $query->where('status', 'LIKE', $this->status);
        }

        $data = $query->with('user')->get();

        return $data;
    }

    /**
     * @param $data
     * @return array
     */
    public function map($data): array
    {
        return [
            $data->id,
            $data->track_code,
            $data->status,
            $data->city,
            $data->user->name ?? '',
            $data->user->login ?? '',
        ];
    }
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
        ];
    }
    public function headings(): array
    {
        return [
            '#',
            'Трек код',
            'Статус',
            'Город',
            'Имя',
            'Телефон',
        ];
    }
}
