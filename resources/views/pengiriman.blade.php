@extends('layouts.app')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6 bg-white border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Pengiriman Surat</h2>
        <p class="text-gray-600 mb-6">Halaman ini digunakan untuk mengelola pembuatan PDF Surat Keterangan Sehat dan pengirimannya ke email mahasiswa.</p>
        
        <div class="p-8 border-2 border-dashed border-gray-300 rounded-lg text-center bg-gray-50">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tabel Data Pengiriman Placeholder</h3>
            <p class="mt-1 text-sm text-gray-500">Daftar mahasiswa beserta status pengiriman PDF akan ditampilkan di sini.</p>
        </div>
    </div>
</div>
@endsection
