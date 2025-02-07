<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Package Revenue Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
        }
        h1, h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Package Revenue Report</h1>
    <h2>Generated on: {{ now()->format('Y-m-d') }}</h2>

    <h3>Summary</h3>
    <ul>
        <li>Total Users: {{ $total_users }}</li>
        <li>New Registrations (Last 7 Days): {{ $new_registrations }}</li>
        <li>Subscribed Users: {{ $subscribed_users }}</li>
        <li>Pending Verifications: {{ $pending_verifications }}</li>
        <li>Total Revenue: ${{ number_format($total_revenue, 2) }}</li>
    </ul>

    <h3>Revenue by Package</h3>
    <table>
        <tr>
            <th>Package Name</th>
            <th>Total Revenue</th>
        </tr>
        @foreach($total_revenue_per_package as $package)
        <tr>
            <td>{{ $package['name'] }}</td>
            <td>${{ number_format($package['total_revenue'], 2) }}</td>
        </tr>
        @endforeach
    </table>

    <h3>Revenue by Date Range</h3>
    <table>
        <tr>
            <th>Package Name</th>
            <th>Total Amount</th>
        </tr>
        @foreach($revenue_by_date as $package)
        <tr>
            <td>{{ $package['name'] }}</td>
            <td>${{ number_format($package['total_amount'], 2) }}</td>
        </tr>
        @endforeach
    </table>

    <h3>Yearly Package Revenue</h3>
    <table>
        <tr>
            <th>Package Name</th>
            <th>Total Revenue (Yearly)</th>
        </tr>
        @foreach($yearly_package_revenue as $package)
        <tr>
            <td>{{ $package['name'] }}</td>
            <td>${{ number_format($package['total_revenue_yearly'], 2) }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>
