class Presensi {
  final String? id;
  final String nik;
  final String tanggal;
  final String? jamIn;
  final String? jamOut;
  final String? fotoIn;
  final String? fotoOut;
  final String? lokasiIn;
  final String? lokasiOut;
  final String? status;
  final String? keterangan;

  Presensi({
    this.id,
    required this.nik,
    required this.tanggal,
    this.jamIn,
    this.jamOut,
    this.fotoIn,
    this.fotoOut,
    this.lokasiIn,
    this.lokasiOut,
    this.status,
    this.keterangan,
  });

  factory Presensi.fromJson(Map<String, dynamic> json) {
    return Presensi(
      id: json['id']?.toString(),
      nik: json['nik'] ?? '',
      tanggal: json['tanggal'] ?? '',
      jamIn: json['jam_in'],
      jamOut: json['jam_out'],
      fotoIn: json['foto_in'],
      fotoOut: json['foto_out'],
      lokasiIn: json['lokasi_in'],
      lokasiOut: json['lokasi_out'],
      status: json['status'],
      keterangan: json['keterangan'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'nik': nik,
      'tanggal': tanggal,
      'jam_in': jamIn,
      'jam_out': jamOut,
      'foto_in': fotoIn,
      'foto_out': fotoOut,
      'lokasi_in': lokasiIn,
      'lokasi_out': lokasiOut,
      'status': status,
      'keterangan': keterangan,
    };
  }
}
