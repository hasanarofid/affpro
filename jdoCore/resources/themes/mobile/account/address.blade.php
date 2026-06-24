@extends('theme::account.layout')
@section('title', 'Buku Alamat')

@section('account_content')
<div class="card-body p-0">
 <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
 <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt-fill me-2 text-primary"></i>Buku Alamat</h5>
 <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
 data-bs-target="#addAddressModal">
 <i class="bi bi-plus-lg me-1"></i> Tambah Alamat
 </button>
 </div>

 <div class="p-4 pb-5">
 @forelse($addresses as $address)
 <div class="card mb-3 {{ $address->is_main ? 'border-primary' : '' }}"
 style="border-radius:10px; {{ $address->is_main ? 'background-color: var(--primary); /* Opacity trick using rgb */ background-color: rgba(var(--primary-rgb, 79, 70, 229), 0.03);' : '' }}">
 <div class="card-body p-3">
 <div class="d-flex justify-content-between align-items-start mb-2">
 <div>
 <span class="fw-bold text-dark">{{ $address->recipient_name }}</span>
 @if($address->title)
 <span class="badge bg-light text-dark ms-2 border">{{ $address->title }}</span>
 @endif
 @if($address->is_main)
 <span class="badge bg-primary ms-1">Utama</span>
 @endif
 <div class="text-muted small mt-1"><i class="bi bi-telephone me-1"></i>{{ $address->phone }}
 </div>
 </div>
 <div class="dropdown">
 <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
 <i class="bi bi-three-dots-vertical"></i>
 </button>
 <ul class="dropdown-menu dropdown-menu-end shadow-sm"
 style="border-radius:10px; border:none;">
 @if(!$address->is_main)
 <li>
 <form action="{{ route('account.addresses.setDefault', $address->id) }}"
 method="POST">
 @csrf @method('PUT')
 <button class="dropdown-item small text-primary"><i
 class="bi bi-check2-circle me-2"></i>Jadikan Utama</button>
 </form>
 </li>
 @endif
 <li><a class="dropdown-item small" href="#" data-bs-toggle="modal"
 data-bs-target="#editAddressModal{{ $address->id }}"><i
 class="bi bi-pencil me-2"></i>Ubah</a></li>
 <li>
 <hr class="dropdown-divider">
 </li>
 <li>
 <form action="{{ route('account.addresses.destroy', $address->id) }}" method="POST"
 onsubmit="return confirm('Hapus alamat ini?')">
 @csrf @method('DELETE')
 <button class="dropdown-item small text-danger"><i
 class="bi bi-trash me-2"></i>Hapus</button>
 </form>
 </li>
 </ul>
 </div>
 </div>
 <p class="text-muted small mb-0 mt-2" style="line-height: 1.5">
 {{ $address->address_line }}<br>
 @if($address->city || $address->province)
 {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
 @endif
 </p>
 </div>
 </div>

 <!-- Edit Modal -->
 <div class="modal fade" id="editAddressModal{{ $address->id }}" tabindex="-1">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content" style="border-radius:16px; border:none;">
 <form action="{{ route('account.addresses.update', $address->id) }}" method="POST">
 @csrf @method('PUT')
 <div class="modal-header border-bottom-0 pt-4 px-4">
 <h6 class="modal-title fw-bold">Ubah Alamat</h6>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body px-4">
 <div class="row g-3" x-data="locationSearch('{{ $address->province }}', '{{ $address->province_id }}', '{{ $address->city }}', '{{ $address->city_id }}', '{{ $address->postal_code }}')">
 <div class="col-12">
 <label class="form-label small fw-medium">Label (Rumah, Kantor, dll)</label>
 <input type="text" name="title" class="form-control"
 value="{{ $address->title }}" style="border-radius:10px">
 </div>
 <div>
 <label class="form-label small fw-medium">Nama Penerima *</label>
 <input type="text" name="recipient_name" class="form-control"
 value="{{ $address->recipient_name }}" required style="border-radius:10px">
 </div>
 <div>
 <label class="form-label small fw-medium">No. Telepon *</label>
 <input type="text" name="phone" class="form-control"
 value="{{ $address->phone }}" required style="border-radius:10px">
 </div>
 <div class="col-12">
 <label class="form-label small fw-medium">Kecamatan/Kota (RajaOngkir) *</label>
 <div class="position-relative">
 <input type="text" x-model="search" @input.debounce.500ms="fetchLocations" @focus="showDropdown = true" @click.away="showDropdown = false" class="form-control" placeholder="Ketik minimal 3 huruf..." required style="border-radius:10px">
 
 <div x-show="loading" class="position-absolute end-0 top-50 translate-middle-y me-3">
 <span class="spinner-border spinner-border-sm text-primary"></span>
 </div>

 <div x-show="showDropdown && results.length > 0" class="position-absolute w-100 bg-white border mt-1 shadow-sm" style="max-height: 200px; overflow-y: auto; border-radius: 10px; z-index: 1050; display: none;" x-transition>
 <template x-for="item in results" :key="item.id">
 <div @click="selectLocation(item)" class="p-2 border-bottom" style="cursor: pointer; font-size: 0.85rem;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
 <i class="bi bi-geo-alt text-muted me-2"></i><span x-text="item.label || item.name || (item.city_name + ', ' + item.province)"></span>
 </div>
 </template>
 </div>
 </div>
 
 <input type="hidden" name="province" x-model="province">
 <input type="hidden" name="province_id" x-model="province_id">
 <input type="hidden" name="city" x-model="city">
 <input type="hidden" name="city_id" x-model="city_id">
 </div>
 <div>
 <label class="form-label small fw-medium">Kode Pos</label>
 <input type="text" name="postal_code" x-model="postal_code" class="form-control"
 value="{{ $address->postal_code }}" style="border-radius:10px">
 </div>
 <div class="col-12">
 <label class="form-label small fw-medium">Alamat Lengkap *</label>
 <textarea name="address_line" class="form-control" rows="3" required
 style="border-radius:10px">{{ $address->address_line }}</textarea>
 </div>
 </div>
 </div>
 <div class="modal-footer border-top-0 pb-4 px-4">
 <button type="button" class="btn btn-light" data-bs-dismiss="modal"
 style="border-radius:10px">Batal</button>
 <button type="submit" class="btn btn-primary" style="border-radius:10px">Simpan
 Perubahan</button>
 </div>
 </form>
 </div>
 </div>
 </div>
 @empty
 <div class="text-center py-5">
 <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3"
 style="width:80px; height:80px;">
 <i class="bi bi-geo-alt" style="font-size:2.5rem;color:#cbd5e1"></i>
 </div>
 <h6 class="fw-bold text-dark">Belum ada alamat</h6>
 <p class="text-muted small mb-4">Anda belum menambahkan alamat pengiriman apapun.</p>
 </div>
 @endforelse
 </div>
</div>

<!-- Add Modal -->
 <div class="modal fade" id="addAddressModal" tabindex="-1">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content" style="border-radius:16px; border:none;">
 <form action="{{ route('account.addresses.store') }}" method="POST">
 @csrf
 <div class="modal-header border-bottom-0 pt-4 px-4">
 <h6 class="modal-title fw-bold">Tambah Alamat Baru</h6>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body px-4">
 <div class="row g-3" x-data="locationSearch('{{ old('province') }}', '{{ old('province_id') }}', '{{ old('city') }}', '{{ old('city_id') }}', '{{ old('postal_code') }}')">
 <div class="col-12">
 <label class="form-label small fw-medium">Label (Rumah, Kantor, dll)</label>
 <input type="text" name="title" class="form-control" style="border-radius:10px">
 </div>
 <div>
 <label class="form-label small fw-medium">Nama Penerima *</label>
 <input type="text" name="recipient_name" class="form-control" required
 style="border-radius:10px">
 </div>
 <div>
 <label class="form-label small fw-medium">No. Telepon *</label>
 <input type="text" name="phone" class="form-control" required style="border-radius:10px">
 </div>
 <div class="col-12">
 <label class="form-label small fw-medium">Kecamatan/Kota (RajaOngkir) *</label>
 <div class="position-relative">
 <input type="text" x-model="search" @input.debounce.500ms="fetchLocations" @focus="showDropdown = true" @click.away="showDropdown = false" class="form-control" placeholder="Ketik minimal 3 huruf..." required style="border-radius:10px">
 
 <div x-show="loading" class="position-absolute end-0 top-50 translate-middle-y me-3">
 <span class="spinner-border spinner-border-sm text-primary"></span>
 </div>

 <div x-show="showDropdown && results.length > 0" class="position-absolute w-100 bg-white border mt-1 shadow-sm" style="max-height: 200px; overflow-y: auto; border-radius: 10px; z-index: 1050; display: none;" x-transition>
 <template x-for="item in results" :key="item.id">
 <div @click="selectLocation(item)" class="p-2 border-bottom" style="cursor: pointer; font-size: 0.85rem;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
 <i class="bi bi-geo-alt text-muted me-2"></i><span x-text="item.label || item.name || (item.city_name + ', ' + item.province)"></span>
 </div>
 </template>
 </div>
 </div>
 
 <input type="hidden" name="province" x-model="province">
 <input type="hidden" name="province_id" x-model="province_id">
 <input type="hidden" name="city" x-model="city">
 <input type="hidden" name="city_id" x-model="city_id">
 </div>
 <div>
 <label class="form-label small fw-medium">Kode Pos</label>
 <input type="text" name="postal_code" x-model="postal_code" class="form-control" style="border-radius:10px">
 </div>
 <div class="col-12">
 <label class="form-label small fw-medium">Alamat Lengkap *</label>
 <textarea name="address_line" class="form-control" rows="3" required
 style="border-radius:10px"></textarea>
 </div>
 </div>
 </div>
 <div class="modal-footer border-top-0 pb-4 px-4">
 <button type="button" class="btn btn-light" data-bs-dismiss="modal"
 style="border-radius:10px">Batal</button>
 <button type="submit" class="btn btn-primary" style="border-radius:10px">Simpan Alamat</button>
 </div>
 </form>
 </div>
 </div>
 </div>
@endsection

@push('scripts')
<script>
 document.addEventListener('alpine:init', () => {
 Alpine.data('locationSearch', (initProvince, initProvId, initCity, initCityId, initPostalCode) => ({
 search: initCity ? (initCity + ', ' + initProvince) : '',
 province: initProvince || '',
 province_id: initProvId || '',
 city: initCity || '',
 city_id: initCityId || '',
 postal_code: initPostalCode || '',
 
 loading: false,
 results: [],
 showDropdown: false,

 async fetchLocations() {
 if (this.search.length < 3) {
 this.results = [];
 return;
 }
 
 this.loading = true;
 
 try {
 const response = await fetch(`{{ route('shipping.destinations') }}?search=${encodeURIComponent(this.search)}`);
 const data = await response.json();
 
 if (data && data.data) {
 this.results = data.data;
 this.showDropdown = true;
 } else {
 this.results = [];
 }
 } catch (error) {
 console.error('Error fetching locations:', error);
 this.results = [];
 } finally {
 this.loading = false;
 }
 },

 selectLocation(item) {
 // If using Komerce API format
 if (item.id && item.city_name && item.province_name) {
 this.city_id = item.id; 
 this.province_id = item.province_id || '';
 
 // The text that will be saved in "city" input (simpan teksnya saja persis 1 label string)
 this.city = item.label || (item.district_name ? (item.district_name + ', ' + item.city_name) : item.city_name);
 
 this.province = item.province_name;
 this.postal_code = item.zip_code || this.postal_code;
 this.search = item.label || (this.city + ', ' + this.province);
 } 
 // Fallback for standard Rajaongkir PRO/Basic
 else {
 this.city_id = item.city_id || item.id || '';
 this.province_id = item.province_id || '';
 
 // simpan string
 this.city = item.label || item.city_name || item.name || '';
 
 this.province = item.province || '';
 this.postal_code = item.postal_code || this.postal_code;
 this.search = item.label || (this.city + ', ' + this.province);
 }
 
 this.showDropdown = false;
 this.results = [];
 }
 }));
 });
</script>
@endpush