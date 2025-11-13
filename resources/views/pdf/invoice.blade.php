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
            margin: 0;
            padding: 0;
        }

        /* Header */
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

        .invoice-info {
            text-align: left;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .invoice-subtitle {
            font-size: 12px;
            color: #555;
        }

        /* Info section */
        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin: 4px 0;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            color: white;
            text-transform: capitalize;
        }

        .badge.pending,
        .badge.partially_paid {
            background-color: #f59e0b;
        }

        .badge.approved,
        .badge.completed {
            background-color: #16a34a;
        }

        .badge.unpaid {
            background-color: #ef4444;
        }

        .badge.paid {
            background-color: #10b981;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        .totals-table td {
            border: none;
            padding: 4px 0;
        }

        .totals-table tr.total {
            font-weight: bold;
            border-top: 2px solid #ccc;
        }

        .totals-table tr.discount td {
            color: #16a34a;
        }

        .totals-table tr.balance td {
            color: #ef4444;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
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
        <div class="invoice-info">
            <div class="invoice-title">Invoice #{{ $invoice->id }}</div>
            <div class="invoice-subtitle">Issued on {{ $invoice->created_at->format('F d, Y') }}</div>
            <div class="invoice-subtitle">Generated on {{ now()->timezone('Asia/Manila')->format('F d, Y h:i A') }}</div>
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
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th class="right">Qty</th>
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
        </tbody>
    </table>

    <!-- Payments -->
    <div class="section-title" style="clear: both;">Payments</div>
    <table>
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

        <!-- Totals -->
    <table class="totals-table" style="margin-top: 20px; width: 50%; float: right;">
        <tr>
            <td>Subtotal:</td>
            <td class="right">{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if ($invoice->discount_amount > 0)
            <tr class="discount">
                <td>Discount:</td>
                <td class="right">-{{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td>Subtotal after Discount:</td>
            <td class="right">{{ number_format($invoice->subtotal_after_discount, 2) }}</td>
        </tr>
        <tr>
            <td>VAT:</td>
            <td class="right">{{ number_format($invoice->vat_amount, 2) }}</td>
        </tr>
        <tr class="total">
            <td>Total:</td>
            <td class="right">{{ number_format($invoice->total, 2) }}</td>
        </tr>
        <tr>
            <td>Total Paid:</td>
            <td class="right">{{ number_format($invoice->total_paid, 2) }}</td>
        </tr>
        <tr class="balance">
            <td>Outstanding Balance:</td>
            <td class="right">{{ number_format($invoice->balance, 2) }}</td>
        </tr>
    </table>
</body>

</html>
