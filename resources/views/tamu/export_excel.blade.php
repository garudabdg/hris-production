<table>
    <tr>
        <td colspan="8" style="font-weight: bold; font-size: 14px;">LAPORAN DATA TAMU</td>
    </tr>
    <tr>
        <td colspan="8" style="font-weight: bold; font-size: 12px;">{{ $cabang ? $cabang->nama_cabang : $generalsetting->nama_perusahaan }}</td>
    </tr>
    <tr>
        <td colspan="8" style="font-style: italic;">{{ $cabang ? $cabang->alamat_cabang : $generalsetting->alamat }}</td>
    </tr>
    <tr>
        <td colspan="8" style="font-style: italic;">Telp: {{ $cabang ? $cabang->telepon_cabang : $generalsetting->telepon }}</td>
    </tr>
    <tr>
        <td colspan="8"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</td>
    </tr>
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: center;">No</th>
            <th style="font-weight: bold; text-align: center;">Nama Tamu</th>
            <th style="font-weight: bold; text-align: center;">No Telpon</th>
            <th style="font-weight: bold; text-align: center;">Plat Nomor</th>
            <th style="font-weight: bold; text-align: center;">Bertemu Dengan</th>
            <th style="font-weight: bold; text-align: center;">Keperluan</th>
            <th style="font-weight: bold; text-align: center;">Jam Masuk</th>
            <th style="font-weight: bold; text-align: center;">Jam Keluar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tamus as $t)
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td>{{ $t->nama_tamu }}</td>
                <td>{{ $t->no_telp }}</td>
                <td>{{ $t->plat_nomor }}</td>
                <td>{{ $t->tujuan }}</td>
                <td>{{ $t->keperluan }}</td>
                <td style="text-align: center;">{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }}</td>
                <td style="text-align: center;">{{ $t->jam_out ? $t->jam_out : '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
