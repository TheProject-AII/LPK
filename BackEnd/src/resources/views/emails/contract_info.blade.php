<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <p>Hello,</p>
    <p>Here is your installment schedule for Contract No: <strong>{{ $contractNo }}</strong></p>

    <div class="info-section">
        <p><strong>Contract No:</strong> {{ $contractNo }}</p>
        <p><strong>Student Name:</strong> {{ $student_name }}</p>
        <p><strong>LPK Name:</strong> {{ $lpk_name }}</p>
        <p><strong>Training Start:</strong> {{ $training_start }}</p>
        <p><strong>Training End:</strong> {{ $training_end }}</p>
        <p><strong>First Date Start Working:</strong> {{ $first_date_start_working }}</p>
        <p><strong>First Date End Working:</strong> {{ $first_date_end_working }}</p>
        <p><strong>Working Days:</strong> {{ $working_day }} Days</p>
        <p><strong>First Salary:</strong>Rp. {{ $first_salary }}</p>
        <p><strong>Grace Period:</strong> {{ $grace_period }}</p>
    </div>

    <table>
        <tr>
            <th>Due Date</th>
            <th>Interest</th>
            <th>Provision Fee</th>
            <th>Process Fee</th>
            <th>Principle</th>
            <th>Membership</th>
            <th>Total Before Tax</th>
            <th>Total Income Tax</th>
            <th>Total After Tax</th>
        </tr>
        @foreach ($installments as $installment)
            <tr>
                <td>{{ $installment['due_date'] }}</td>
                <td>Rp. {{ $installment['interest'] }}</td>
                <td>Rp. {{ $installment['provision_fee'] }}</td>
                <td>Rp. {{ $installment['process_fee'] }}</td>
                <td>Rp. {{ $installment['principle'] }}</td>
                <td>Rp. {{ $installment['membership'] }}</td>
                <td>Rp. {{ $installment['total_before_tax'] }}</td>
                <td>Rp. {{ $installment['total_income_tax'] }}</td>
                <td>Rp. {{ $installment['total_after_tax'] }}</td>
            </tr>
        @endforeach
    </table>

    <p>Best regards,</p>
    <p>Teman Mitra Usaha</p>
</body>
</html>
