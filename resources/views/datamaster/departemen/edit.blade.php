<form action="{{ route('departemen.update', Crypt::encrypt($departemen->kode_dept)) }}" method="POST" id="formDepartemen">
   @csrf
   @method('PUT')
   <x-input-with-icon label="Kode Departemen" name="kode_dept" icon="ti ti-barcode" value="{{ $departemen->kode_dept }}" maxlength="3" placeholder="Maksimal 3 karakter" />
   <x-input-with-icon label="Nama Departemen" name="nama_dept" icon="ti ti-building" value="{{ $departemen->nama_dept }}" maxlength="30" placeholder="Maksimal 30 karakter" required />
   <div class="form-group">
      <label for="sub_departemen" class="form-label">Sub Departemen (Opsional)</label>
      <div class="input-group">
         <span class="input-group-text"><i class="ti ti-list"></i></span>
         <textarea class="form-control" name="sub_departemen" id="sub_departemen" placeholder="Pisahkan dengan koma, contoh: IT, HR, Finance" rows="3">{{ is_array($departemen->sub_departemen) ? implode(', ', $departemen->sub_departemen) : '' }}</textarea>
      </div>
      <small style="color: #666;"><i class="ti ti-info-circle"></i> Sub departemen digunakan untuk departemen BU (Business Unit). Pisahkan setiap sub departemen dengan koma.</small>
   </div>
   <div class="form-group mb-3">
      <button type="submit" class="btn btn-primary w-100"><i class="ti ti-send me-1"></i> Submit</button>
   </div>
</form>
<script src="{{ asset('assets/js/pages/departemen.js') }}"></script>
