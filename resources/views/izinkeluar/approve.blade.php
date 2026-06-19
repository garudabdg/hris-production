<form action="{{ route('izinkeluar.storeapprove', Crypt::encrypt($izinkeluar->kode_izin_keluar)) }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-12">
            <table class="table">
                <tr>
                    <th>Kode Izin</th>
                    <td>{{ $izinkeluar->kode_izin_keluar }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>{{ date('d-m-Y', strtotime($izinkeluar->tanggal)) }}</td>
                </tr>
                <tr>
                    <th>Karyawan</th>
                    <td>{{ $izinkeluar->nama_karyawan }}</td>
                </tr>
                <tr>
                    <th>Jabatan</th>
                    <td>{{ $izinkeluar->nama_jabatan }}</td>
                </tr>
                <tr>
                    <th>Departemen</th>
                    <td>{{ $izinkeluar->nama_dept }}</td>
                </tr>
                <tr>
                    <th>Cabang</th>
                    <td>{{ $izinkeluar->nama_cabang }}</td>
                </tr>
                <tr>
                    <th>Jam Keluar</th>
                    <td>{{ date('H:i', strtotime($izinkeluar->jam_keluar)) }}</td>
                </tr>
                <tr>
                    <th>Jam Kembali</th>
                    <td>{{ $izinkeluar->jam_kembali ? date('H:i', strtotime($izinkeluar->jam_kembali)) : 'Selesai / Tidak Kembali' }}</td>
                </tr>
                <tr>
                    <th>Keperluan</th>
                    <td>{{ $izinkeluar->keperluan }}</td>
                </tr>
                <tr>
                    <th>Pilih Driver (Opsional)</th>
                    <td>
                        <select name="driver_nik" class="form-select">
                            <option value="">- Tidak Pakai Driver -</option>
                            @foreach($drivers as $dr)
                                <option value="{{ $dr->nik }}">{{ $dr->nama_karyawan }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Pilih Kendaraan (Opsional)</th>
                    <td>
                        <select name="kode_asset_kendaraan" class="form-select">
                            <option value="">- Tidak Pakai Kendaraan Perusahaan -</option>
                            @foreach($vehicles as $vh)
                                <option value="{{ $vh->kode_asset }}">{{ $vh->nama_asset }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <div class="d-flex gap-2">
                <button type="submit" name="approve" value="1" class="btn btn-success w-50">
                    <i class="ti ti-check me-2"></i> Setujui
                </button>
                <button type="submit" name="reject" value="1" class="btn btn-danger w-50">
                    <i class="ti ti-x me-2"></i> Tolak
                </button>
            </div>
        </div>
    </div>
</form>
