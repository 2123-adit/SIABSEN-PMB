<?php
// resources/views/admin/holidays/create.blade.php - Update bagian checkbox
?>
@extends('layouts.admin')

@section('title', 'Tambah Hari Libur - SIABSEN PMB')
@section('page-title', 'Tambah Hari Libur')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-plus me-2"></i>
                    Form Tambah Hari Libur
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.holidays.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
                                       value="{{ old('tanggal') }}" required>
                                @error('tanggal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jenis Libur <span class="text-danger">*</span></label>
                                <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="nasional" {{ old('jenis') == 'nasional' ? 'selected' : '' }}>Hari Libur Nasional</option>
                                    <option value="cuti_bersama" {{ old('jenis') == 'cuti_bersama' ? 'selected' : '' }}>Cuti Bersama</option>
                                    <option value="khusus" {{ old('jenis') == 'khusus' ? 'selected' : '' }}>Libur Khusus</option>
                                </select>
                                @error('jenis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Libur <span class="text-danger">*</span></label>
                        <input type="text" name="nama_libur" class="form-control @error('nama_libur') is-invalid @enderror" 
                               value="{{ old('nama_libur') }}" placeholder="Contoh: Hari Kemerdekaan RI" required>
                        @error('nama_libur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                  rows="3" placeholder="Deskripsi tambahan tentang hari libur">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- UPDATE: DEFAULT NONAKTIF -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   id="is_active" {{ old('is_active', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Status Aktif
                            </label>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Default: Nonaktif. Centang untuk mengaktifkan hari libur ini.
                        </small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.holidays.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection