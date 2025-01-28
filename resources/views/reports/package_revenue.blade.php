<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Revenue Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            background-color: #007BFF;
            color: white;
            padding: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
        }
        .container {
            padding: 20px;
        }
        .section-title {
            font-size: 18px;
            margin: 20px 0 10px;
            color: #333;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f4f4f4;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Package Revenue Report</h1>
        <p>Generated on: {{ now()->format('d M Y') }}</p>
    </div>

    <div class="container">
        <!-- User Statistics -->
        <h2 class="section-title">User Statistics</h2>
        <table class="table">
            <tbody>
                <tr>
                    <th>Total Users</th>
                    <td>{{ $total_users }}</td>
                </tr>
                <tr>
                    <th>New Registrations (Last 7 Days)</th>
                    <td>{{ $new_registrations }}</td>
                </tr>
                <tr>
                    <th>Subscribed Users</th>
                    <td>{{ $subscribed_users }}</td>
                </tr>
                <tr>
                    <th>Pending Verifications</th>
                    <td>{{ $pending_verifications }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Yearly Package Revenue -->
        <h2 class="section-title">Yearly Package Revenue</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Revenue (USD)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($yearly_package_revenue as $month => $revenue)
                    <tr>
                        <td>{{ $month }}</td>
                        <td>${{ number_format($revenue, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Revenue Per Package -->
        <h2 class="section-title">Total Revenue Per Package</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Package Name</th>
                    <th>Total Revenue (USD)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($total_revenue_per_package as $package)
                    <tr>
                        <td>{{ $package['name'] }}</td>
                        <td>${{ number_format($package['total_revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Weekly Package Revenue -->
        <h2 class="section-title">Weekly Package Revenue</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Week</th>
                    <th>Revenue (USD)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($weekly_package_revenue as $week => $revenue)
                    <tr>
                        <td>Week {{ $week }}</td>
                        <td>${{ number_format($revenue, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Revenue By Date Range -->
        @if (!empty($revenue_by_date))
        <h2 class="section-title">Revenue By Date Range</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Package Name</th>
                    <th>Total Revenue (USD)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($revenue_by_date as $revenue)
                    <tr>
                        <td>{{ $revenue['name'] }}</td>
                        <td>${{ number_format($revenue['total_amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Summary -->
        <h2 class="section-title">Summary</h2>
        <table class="table">
            <tbody>
                <tr>
                    <th>Total Revenue</th>
                    <td>${{ number_format($total_revenue, 2) }}</td>
                </tr>
                <tr>
                    <th>Highest Weekly Revenue</th>
                    <td>${{ number_format($weekly_package_revenue_max, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Report generated by your system.
    </div>
</body>
</html>
