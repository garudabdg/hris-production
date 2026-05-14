class User {
  final String nik;
  final String namaKaryawan;
  final String? email;
  final String? jabatan;
  final String? departemen;
  final String? cabang;
  final String? fotoUrl;
  final String? nomorHp;

  User({
    required this.nik,
    required this.namaKaryawan,
    this.email,
    this.jabatan,
    this.departemen,
    this.cabang,
    this.fotoUrl,
    this.nomorHp,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      nik: json['nik'] ?? '',
      namaKaryawan: json['nama_karyawan'] ?? json['name'] ?? '',
      email: json['email'],
      jabatan: json['nama_jabatan'],
      departemen: json['nama_dept'],
      cabang: json['nama_cabang'],
      fotoUrl: json['foto'],
      nomorHp: json['no_hp'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'nik': nik,
      'nama_karyawan': namaKaryawan,
      'email': email,
      'nama_jabatan': jabatan,
      'nama_dept': departemen,
      'nama_cabang': cabang,
      'foto': fotoUrl,
      'no_hp': nomorHp,
    };
  }
}
