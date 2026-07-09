<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Karyawan (NIK)</th>
            <th>Nama Nasabah</th>
            <th>Status Lead</th>
            <th>No WhatsApp</th>
            <th>Sosmed</th>
        </tr>
    </thead>
    <tbody>
        @foreach($nasabahs as $index => $nasabah)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($nasabah->tanggal)->translatedFormat('d M Y') }}</td>
                <td>{{ $nasabah->karyawan ? $nasabah->karyawan->nama_karyawan : '-' }} ({{ $nasabah->nik }})</td>
                <td>{{ $nasabah->nama }}</td>
                <td>
                    @if($nasabah->status_lead == 'hot')
                        Hot
                    @elseif($nasabah->status_lead == 'warm')
                        Warm
                    @else
                        Cold
                    @endif
                </td>
                <td>{{ $nasabah->no_whatsapp ?? '-' }}</td>
                <td>{{ $nasabah->akun_sosial_media ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
