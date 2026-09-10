@extends('layouts.app')

@section('content')
<div>
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h2 class="text-3xl font-bold text-gray-800 tracking-tight">Data Pemeriksaan</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('pemeriksaan.exportExcel', request()->all()) }}" class="inline-flex items-center px-5 py-2.5 bg-white border border-emerald-600 rounded-lg font-semibold text-sm text-emerald-700 tracking-wide shadow-sm hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export ke Excel
            </a>
            <a href="{{ route('pemeriksaan.exportPdf', request()->all()) }}" class="inline-flex items-center px-5 py-2.5 bg-white border border-amber-600 rounded-lg font-semibold text-sm text-amber-700 tracking-wide shadow-sm hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export ke PDF
            </a>
            <a href="{{ route('pemeriksaan.create') }}" class="inline-flex items-center px-5 py-2.5 bg-emerald-700 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide shadow-sm hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 transition ease-in-out duration-150">
                + Tambah Data Baru
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-md shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-100">
        <div class="p-6 bg-white border-b border-gray-100">
            <!-- Filter Form -->
            <form action="{{ route('pemeriksaan.index') }}" method="GET" class="mb-6">
                <!-- Search & Actions Row -->
                <div class="flex flex-col md:flex-row gap-3 mb-4">
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Nama atau NIK..." class="pl-10 border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 rounded-lg shadow-sm block w-full py-2.5">
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('advanced-filters').classList.toggle('hidden')" class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 tracking-wide shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filter Lanjutan
                        </button>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gray-800 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide shadow-sm hover:bg-gray-700 transition">
                            Cari
                        </button>
                        @if(request()->hasAny(['search', 'tanggal_awal', 'tanggal_akhir', 'kesimpulan', 'fakultas', 'jenis_kelamin', 'status_email', 'riwayat_medis', 'nomor_surat']))
                            <a href="{{ route('pemeriksaan.index') }}" class="inline-flex items-center px-4 py-2.5 bg-gray-100 border border-transparent rounded-lg font-semibold text-sm text-gray-600 tracking-wide hover:bg-gray-200 transition tooltip" title="Reset Semua Filter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div id="advanced-filters" class="{{ request()->hasAny(['tanggal', 'kesimpulan', 'fakultas', 'jenis_kelamin', 'status_email', 'riwayat_medis', 'nomor_surat']) ? '' : 'hidden' }} p-4 bg-gray-50 rounded-lg border border-gray-200 mt-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Surat</label>
                            <input type="text" name="nomor_surat" value="{{ $nomor_surat ?? '' }}" placeholder="Contoh: 0130" class="border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 rounded-lg shadow-sm block w-full p-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Per Tanggal</label>
                            <input type="date" name="tanggal" value="{{ $tanggal ?? '' }}" class="border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 rounded-lg shadow-sm block w-full p-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kesimpulan</label>
                            <select name="kesimpulan" class="border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 rounded-lg shadow-sm block w-full p-2.5 text-sm">
                                <option value="">Semua</option>
                                <option value="SEHAT DAN TIDAK BUTA WARNA" {{ ($kesimpulan ?? '') == 'SEHAT DAN TIDAK BUTA WARNA' ? 'selected' : '' }}>Sehat & Tidak Buta Warna</option>
                                <option value="SEHAT DAN BUTA WARNA" {{ ($kesimpulan ?? '') == 'SEHAT DAN BUTA WARNA' ? 'selected' : '' }}>Sehat & Buta Warna</option>
                                <option value="SEHAT DAN BUTA WARNA PARSIAL" {{ ($kesimpulan ?? '') == 'SEHAT DAN BUTA WARNA PARSIAL' ? 'selected' : '' }}>Sehat & Buta Warna Parsial</option>
                                <option value="SEHAT" {{ ($kesimpulan ?? '') == 'SEHAT' ? 'selected' : '' }}>Sehat</option>
                                <option value="TIDAK SEHAT" {{ ($kesimpulan ?? '') == 'TIDAK SEHAT' ? 'selected' : '' }}>Tidak Sehat</option>
                                <option value="TIDAK SEHAT DAN BUTA WARNA PARSIAL" {{ ($kesimpulan ?? '') == 'TIDAK SEHAT DAN BUTA WARNA PARSIAL' ? 'selected' : '' }}>Tidak Sehat & Buta Warna Parsial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Fakultas / Pekerjaan</label>
                            <select name="fakultas" class="border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 rounded-lg shadow-sm block w-full p-2.5 text-sm">
                                <option value="">Semua Fakultas</option>
                                <option value="Fakultas Tarbiyah dan Keguruan" {{ ($fakultas ?? '') == 'Fakultas Tarbiyah dan Keguruan' ? 'selected' : '' }}>Fakultas Tarbiyah dan Keguruan</option>
                                <option value="Fakultas Syariah dan Hukum" {{ ($fakultas ?? '') == 'Fakultas Syariah dan Hukum' ? 'selected' : '' }}>Fakultas Syariah dan Hukum</option>
                                <option value="Fakultas Dakwah dan Komunikasi" {{ ($fakultas ?? '') == 'Fakultas Dakwah dan Komunikasi' ? 'selected' : '' }}>Fakultas Dakwah dan Komunikasi</option>
                                <option value="Fakultas Ushuluddin dan Filsafat" {{ ($fakultas ?? '') == 'Fakultas Ushuluddin dan Filsafat' ? 'selected' : '' }}>Fakultas Ushuluddin dan Filsafat</option>
                                <option value="Fakultas Adab dan Humaniora" {{ ($fakultas ?? '') == 'Fakultas Adab dan Humaniora' ? 'selected' : '' }}>Fakultas Adab dan Humaniora</option>
                                <option value="Fakultas Ekonomi dan Bisnis Islam" {{ ($fakultas ?? '') == 'Fakultas Ekonomi dan Bisnis Islam' ? 'selected' : '' }}>Fakultas Ekonomi dan Bisnis Islam</option>
                                <option value="Fakultas Sains dan Teknologi" {{ ($fakultas ?? '') == 'Fakultas Sains dan Teknologi' ? 'selected' : '' }}>Fakultas Sains dan Teknologi</option>
                                <option value="Fakultas Psikologi" {{ ($fakultas ?? '') == 'Fakultas Psikologi' ? 'selected' : '' }}>Fakultas Psikologi</option>
                                <option value="Fakultas Ilmu Sosial dan Ilmu Pemerintahan" {{ ($fakultas ?? '') == 'Fakultas Ilmu Sosial dan Ilmu Pemerintahan' ? 'selected' : '' }}>Fakultas Ilmu Sosial dan Ilmu Pemerintahan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Riwayat Medis</label>
                            <select name="riwayat_medis" class="border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 rounded-lg shadow-sm block w-full p-2.5 text-sm">
                                <option value="">Semua</option>
                                <option value="Ada" {{ ($riwayat_medis ?? '') == 'Ada' ? 'selected' : '' }}>Ada Riwayat</option>
                                <option value="Tidak Ada" {{ ($riwayat_medis ?? '') == 'Tidak Ada' ? 'selected' : '' }}>Tidak Ada</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">L/P</label>
                                <select name="jenis_kelamin" class="border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 rounded-lg shadow-sm block w-full p-2.5 text-sm">
                                    <option value="">Semua</option>
                                    <option value="Laki-laki" {{ ($jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>L</option>
                                    <option value="Perempuan" {{ ($jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>P</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Email</label>
                                <select name="status_email" class="border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 rounded-lg shadow-sm block w-full p-2.5 text-sm">
                                    <option value="">Semua</option>
                                    <option value="Terkirim" {{ ($status_email ?? '') == 'Terkirim' ? 'selected' : '' }}>Terkirim</option>
                                    <option value="Gagal" {{ ($status_email ?? '') == 'Gagal' ? 'selected' : '' }}>Gagal</option>
                                    <option value="Proses" {{ ($status_email ?? '') == 'Proses' ? 'selected' : '' }}>Proses</option>
                                    <option value="Belum dikirim" {{ ($status_email ?? '') == 'Belum dikirim' ? 'selected' : '' }}>Belum dikirim</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-emerald-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">NIK</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Tanggal Pemeriksaan</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Kesimpulan</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($pemeriksaans as $pemeriksaan)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ($pemeriksaans->currentPage() - 1) * $pemeriksaans->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $pemeriksaan->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pemeriksaan->nik ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pemeriksaan->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">{{ $pemeriksaan->kesimpulan ?? 'SEHAT' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                                    <a href="{{ route('pemeriksaan.show', $pemeriksaan->id) }}" class="text-emerald-700 hover:text-white hover:bg-emerald-700 border border-emerald-700 bg-emerald-50 px-3 py-1 rounded transition">Detail</a>
                                    <a href="{{ route('pemeriksaan.edit', $pemeriksaan->id) }}" class="text-amber-600 hover:text-white hover:bg-amber-600 border border-amber-600 bg-amber-50 px-3 py-1 rounded transition">Edit</a>
                                    <form action="{{ route('pemeriksaan.destroy', $pemeriksaan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-white hover:bg-red-600 border border-red-600 bg-red-50 px-3 py-1 rounded transition">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 whitespace-nowrap text-sm text-center text-gray-500 italic">
                                    Belum ada data pemeriksaan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6">
                {{ $pemeriksaans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
