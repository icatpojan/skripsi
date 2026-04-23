<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sampah - Print</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 20px;
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }

            table {
                page-break-inside: avoid;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-section h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-button:hover {
            background: #0056b3;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .waste-type-icon {
            margin-right: 5px;
        }

    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>

    <div class="header">
        <h1>LAPORAN SAMPAH</h1>
        <p>Aplikasi Pelaporan Sampah dengan Leaflet</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <h3>Informasi Laporan</h3>
        <p>Total Data: {{ $reports->count() }} laporan</p>
        <p>Status:
            @if(request('status'))
            {{ ucfirst(request('status')) }}
            @else
            Semua Status
            @endif
        </p>
        @if(request('search'))
        <p>Pencarian: "{{ request('search') }}"</p>
        @endif
        @if(request('date_start') || request('date_end'))
        <p>Rentang Tanggal:
            @if(request('date_start') && request('date_end'))
            {{ \Carbon\Carbon::parse(request('date_start'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('date_end'))->format('d/m/Y') }}
            @elseif(request('date_start'))
            {{ \Carbon\Carbon::parse(request('date_start'))->format('d/m/Y') }}
            @elseif(request('date_end'))
            {{ \Carbon\Carbon::parse(request('date_end'))->format('d/m/Y') }}
            @endif
        </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Pelapor</th>
                <th>Judul</th>
                <th>Jenis Sampah</th>
                <th>Status</th>
                <th>Alamat</th>
                <th>Catatan Admin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $report->user->name }}</td>
                <td>{{ $report->title ?: 'Laporan Sampah' }}</td>
                <td>
                    @if($report->wasteType)
                    <i class="{{ $report->wasteType->icon }} waste-type-icon" style="color: {{ $report->wasteType->color }};"></i>
                    {{ $report->wasteType->name }}
                    @else
                    Tidak ditentukan
                    @endif
                </td>
                <td>
                    <span class="status-badge {{ $report->status_badge_class }}">
                        {{ $report->status_text }}
                    </span>
                </td>
                <td>{{ $report->address ?: 'Tidak ada alamat' }}</td>
                <td>{{ $report->admin_notes ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">
                    Tidak ada data laporan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Aplikasi Pelaporan Sampah dengan Leaflet</p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };

    </script>
</body>
</html>
