<?php

namespace App\Exports;

use App\Models\ItTicket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export data IT Ticket ke Excel
 * Mendukung filter: search, status, prioritas, kategori, kode_cabang
 */
class ItTicketExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $filters;
    protected $isTicketHandler;
    protected $user;
    protected $rowNumber = 0;

    public function __construct(array $filters, bool $isTicketHandler, $user)
    {
        $this->filters = $filters;
        $this->isTicketHandler = $isTicketHandler;
        $this->user = $user;
    }

    /**
     * Query data tiket dengan filter yang sama seperti halaman index
     */
    public function collection()
    {
        $query = ItTicket::with(['pemohon.userkaryawan.karyawan.departemen', 'assignedTo', 'resolvedBy', 'cabang']);

        // Scope akses: IT Staff / Super Admin lihat semua, user biasa lihat miliknya
        if (!$this->isTicketHandler) {
            $userCabangs = $this->user->getCabangCodes();
            $query->where(function ($q) use ($userCabangs) {
                $q->where('pemohon_id', $this->user->id);
                if (!empty($userCabangs)) {
                    $q->orWhereIn('kode_cabang', $userCabangs);
                }
            });
        }

        // Terapkan filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nomor_tiket', 'like', '%' . $search . '%')
                  ->orWhere('judul', 'like', '%' . $search . '%');
            });
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['prioritas'])) {
            $query->where('prioritas', $this->filters['prioritas']);
        }
        if (!empty($this->filters['kategori'])) {
            $query->where('kategori', $this->filters['kategori']);
        }
        if (!empty($this->filters['kode_cabang'])) {
            $query->where('kode_cabang', $this->filters['kode_cabang']);
        }

        return $query
            ->orderByRaw("FIELD(status,'open','in_progress','pending','resolved','closed')")
            ->orderByRaw("FIELD(prioritas,'critical','high','medium','low')")
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Header kolom Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'Nomor Tiket',
            'Judul',
            'Kategori',
            'Prioritas',
            'Klasifikasi Data',
            'Dampak',
            'Cabang',
            'Lokasi',
            'Pemohon',
            'Departemen',
            'Assigned To',
            'Status',
            'Tanggal Dibuat',
            'Target SLA',
            'Tanggal Resolved',
            'Resolved By',
            'Catatan Resolusi',
        ];
    }

    /**
     * Mapping setiap baris tiket ke kolom Excel
     */
    public function map($ticket): array
    {
        $this->rowNumber++;

        // Ambil departemen pemohon
        $dept = '-';
        if ($ticket->pemohon && $ticket->pemohon->userkaryawan && $ticket->pemohon->userkaryawan->karyawan) {
            $karyawan = $ticket->pemohon->userkaryawan->karyawan;
            $dept = optional($karyawan->departemen)->nama_dept ?? '-';
        }

        // Label status yang readable
        $statusLabels = [
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'pending'     => 'Pending',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
        ];

        return [
            $this->rowNumber,
            $ticket->nomor_tiket,
            $ticket->judul,
            ucfirst($ticket->kategori),
            ucfirst($ticket->prioritas),
            ucfirst($ticket->klasifikasi_data ?? '-'),
            ucfirst($ticket->dampak ?? '-'),
            optional($ticket->cabang)->nama_cabang ?? '-',
            $ticket->lokasi ?? '-',
            optional($ticket->pemohon)->name ?? '-',
            $dept,
            optional($ticket->assignedTo)->name ?? '-',
            $statusLabels[$ticket->status] ?? $ticket->status,
            $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '-',
            $ticket->tanggal_target ? $ticket->tanggal_target->format('d/m/Y') : '-',
            $ticket->resolved_at ? $ticket->resolved_at->format('d/m/Y H:i') : '-',
            optional($ticket->resolvedBy)->name ?? '-',
            $ticket->catatan_resolusi ?? '-',
        ];
    }

    /**
     * Judul sheet Excel
     */
    public function title(): string
    {
        return 'IT Tickets';
    }

    /**
     * Styling header dan border tabel
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Border untuk semua data
        $sheet->getStyle('A1:R' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        return [
            // Styling header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF024A75'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    /**
     * Lebar kolom kustom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,    // No
            'B' => 20,   // Nomor Tiket
            'C' => 40,   // Judul
            'D' => 14,   // Kategori
            'E' => 12,   // Prioritas
            'F' => 16,   // Klasifikasi Data
            'G' => 14,   // Dampak
            'H' => 18,   // Cabang
            'I' => 20,   // Lokasi
            'J' => 20,   // Pemohon
            'K' => 18,   // Departemen
            'L' => 20,   // Assigned To
            'M' => 14,   // Status
            'N' => 18,   // Tanggal Dibuat
            'O' => 14,   // Target SLA
            'P' => 18,   // Tanggal Resolved
            'Q' => 20,   // Resolved By
            'R' => 40,   // Catatan Resolusi
        ];
    }
}
