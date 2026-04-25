<form action="{{ route('departemen.store') }}" method="POST" id="formDepartemen">
   @csrf
   <x-input-with-icon label="Kode Departemen" name="kode_dept" icon="ti ti-barcode" maxlength="3" placeholder="Maksimal 3 karakter" required />
   <x-input-with-icon label="Nama Departemen" name="nama_dept" icon="ti ti-building" maxlength="30" placeholder="Maksimal 30 karakter" required />
   <x-input-with-icon label="Sub Departemen (Opsional)" name="sub_departemen" icon="ti ti-list" placeholder="Pisahkan dengan koma, contoh: IT, HR, Finance" />
   <div class="form-group mb-3" style="font-size: 12px; color: #666; margin-top: -10px; margin-bottom: 15px;">
      <i class="ti ti-info-circle"></i> Sub departemen digunakan untuk departemen BU (Business Unit). Pisahkan setiap sub departemen dengan koma.
   </div>
   <div class="form-group mb-3">
      <button type="submit" class="btn btn-primary w-100"><i class="ti ti-send me-1"></i> Submit</button>
   </div>
</form>
<script src="{{ asset('assets/js/pages/departemen.js') }}"></script>
