<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            width: 70px;
            height: auto;
        }

        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        .invoice-info {
            text-align: right;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 12px;
            color: #555;
        }

        /* Info section */
        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin: 2px 0;
        }

        /* Table */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th {
            background: #f3f4f6;
            text-align: left;
            font-weight: bold;
            padding: 8px;
            border-bottom: 1px solid #ccc;
        }

        .table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .total-row {
            background: #f9fafb;
            font-weight: bold;
        }

        /* Section title */
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            color: white;
        }

        .badge.pending {
            background-color: #f59e0b;
        }

        .badge.partially_paid {
            background-color: #f59e0b;
        }

        .badge.approved {
            background-color: #3b82f6;
        }

        .badge.completed {
            background-color: #16a34a;
        }

        .badge.unpaid {
            background-color: #ef4444;
        }

        .badge.paid {
            background-color: #10b981;
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
        <div class="invoice-info">
            <div class="title">Invoice #{{ $invoice->id }}</div>
            <div class="subtitle">Issued on {{ $invoice->created_at->format('F d, Y') }}</div>
        </div>
    </div>

    <!-- Patient & Appointment Info -->
    <div class="info">
        <p><strong>Patient:</strong>
            {{ $invoice->appointment->patient->first_name }} {{ $invoice->appointment->patient->last_name }}
        </p>
        <p>
            <strong>Status:</strong>
            <span class="badge {{ $invoice->status->value }}">
                {{ str_replace('_', ' ', ucfirst($invoice->status->value)) }}
            </span>
        </p>
        <p><strong>Service:</strong> {{ $invoice->appointment->type->label() }}</p>
        <p><strong>Scheduled at:</strong> {{ $invoice->appointment->scheduled_at->format('F d, Y h:i A') }}</p>
    </div>

    <!-- Invoice Items -->
    <div class="section-title">Invoice Items</div>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th class="right">Quantity</th>
                <th class="right">Unit Price</th>
                <th class="right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->invoiceItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td class="right" colspan="4">Total</td>
                <td class="right">{{ number_format($invoice->total, 2) }}</td>
            </tr>
            <tr>
                <td class="right" colspan="4">Total Paid</td>
                <td class="right">{{ number_format($invoice->total_paid, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="right" colspan="4">Outstanding Balance</td>
                <td class="right">{{ number_format($invoice->balance, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Payments -->
    <div class="section-title">Payments</div>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('F d, Y h:i A') }}</td>
                    <td class="right">{{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #999;">No payments recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ now()->timezone('Asia/Manila')->format('F d, Y h:i A') }}</p>
    </div>
</body>

</html>
