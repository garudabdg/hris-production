<!DOCTYPE html>
<html>
<head>
    <title>Informasi Akun Admin Baru</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Halo {{ $name }},</h2>
    <p>Akun admin Anda telah berhasil dibuat. Berikut adalah detail login Anda:</p>
    
    <table style="margin-bottom: 20px;">
        <tr>
            <td><strong>Email Login</strong></td>
            <td>: {{ $emailStr }}</td>
        </tr>
        <tr>
            <td><strong>Password</strong></td>
            <td>: {{ $passwordStr }}</td>
        </tr>
    </table>

    <p>Silakan login menggunakan URL berikut:</p>
    <p><a href="{{ $loginUrl }}" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">Login ke Dashboard</a></p>
    
    <p>Atau copy paste URL berikut ke browser Anda: <br>
    <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>

    <p>Kami menyarankan Anda untuk segera mengubah password setelah berhasil login pertama kali demi keamanan akun Anda.</p>

    <br>
    <p>Terima kasih,</p>
    <p><strong>Tim HRIS</strong></p>
</body>
</html>
