<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1 class="text-center">Payment Report</h1>
    <table>
        <thead>
            <tr>
                <th>Trx ID</th>
                <th>Payment For</th>
                <th>User Name</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pdfData as $row)
                <tr>
                    <td>{{ $row['trxId'] }}</td>
                    <td>{{ $row['payment_for'] }}</td>
                    <td>{{ $row['user_name'] }}</td>
                    <td class="text-right">{{ number_format($row['amount'], 2) }}</td>
                    <td>{{ $row['method'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['date'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="text-right">Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
</body>
</html>
