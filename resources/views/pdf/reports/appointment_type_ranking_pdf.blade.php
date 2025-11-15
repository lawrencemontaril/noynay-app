<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Appointment Type Ranking Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .header {
            border-bottom: 2px solid #e5e7eb;
            padding: 15px 0;
            margin-bottom: 25px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            width: 80px;
            height: auto;
        }

        .clinic-name {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }

        .report-info {
            margin-top: 5px;
            font-size: 12px;
            color: #555;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th,
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        td.right {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            <img src="file://{{ public_path('noynay_logo.png') }}" alt="Clinic Logo" class="logo">
            <div class="clinic-name">Noynay Medical Center</div>
        </div>
        <div class="report-info">
            <div><strong>Report:</strong> Appointment Type Ranking</div>
            <div><strong>Generated on:</strong> {{ $generated_at }}</div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="section-title">Ranking Summary</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Rank</th>
                <th>Label</th>
                <th>Total Appointments</th>
                <th class="right" style="width: 15%;">Revenue Generated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ranking as $item)
                <tr>
                    <td>{{ $item['rank'] }}</td>
                    <td>{{ $item['label'] }}</td>
                    <td>{{ $item['total'] }}</td>
                    <td class="right">{{ $item['revenue'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#999; padding:20px;">
                        No appointment data available.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Noynay Medical Center &middot; Generated automatically by the system
    </div>
</body>

</html>
