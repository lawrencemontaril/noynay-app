<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Most Loyal Patients Report</title>
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

        th, td {
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
            <div><strong>Report:</strong> Most Loyal Patients</div>
            <div><strong>Generated on:</strong> {{ $generated_at }}</div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="section-title">Top Patients by Loyalty Score</div>
    <table>
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th style="width:22%;">Patient</th>
                <th style="width:8%;">Visits</th>
                <th style="width:8%;">Visits/yr</th>
                <th style="width:12%;">Last Visit</th>
                <th style="width:6%;">Tenure</th>
                <th style="width:6%;">Services</th>
                <th style="width:10%;">Spend (₱)</th>
                <th style="width:8%;">Avg Days</th>
                <th style="width:7%;">Status Score</th>
                <th style="width:7%;">Loyalty Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mostLoyalPatients as $idx => $p)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $p['name'] }}</td>
                    <td class="right">{{ $p['total_appointments'] }}</td>
                    <td class="right">{{ $p['visits_per_year'] }}</td>
                    <td class="right">{{ $p['last_visit'] ? \Carbon\Carbon::parse($p['last_visit'])->format('F d, Y') : '—' }}</td>
                    <td class="right">{{ $p['tenure_years'] }}</td>
                    <td class="right">{{ $p['distinct_services'] }}</td>
                    <td class="right">{{ number_format($p['total_spend'] ?? 0, 2) }}</td>
                    <td class="right">{{ $p['avg_days_between_visits'] ?? '—' }}</td>
                    <td class="right">{{ $p['status_score'] }}</td>
                    <td class="right">{{ $p['loyalty_score'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center; color:#999; padding:20px;">No patient data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Noynay Medical Center &middot; Automatically generated report
    </div>
</body>
</html>
