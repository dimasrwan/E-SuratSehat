@extends('layouts.app')

@section('content')
<div>
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 tracking-tight">
                @php
                    $cleanYearDash = preg_replace('/[^0-9]/', '', (string)($selectedTahun ?? 'all'));
                @endphp
                @if(($selectedTahun ?? 'all') === 'all' || empty($cleanYearDash))
                    Dashboard (Semua Tahun)
                @else
                    Dashboard Maba {{ $cleanYearDash }}
                @endif
            </h2>
            <p class="text-sm text-gray-500">Selamat datang kembali, <span class="font-semibold text-emerald-700">{{ Auth::user()->name }}</span> ({{ ucfirst(Auth::user()->role) }})</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ url()->current() }}" method="GET" class="w-48">
                @php
                    $yearOptions = ['all' => 'Semua Tahun'];
                    foreach ($availableYears as $yr) {
                        $yearOptions[(string)$yr] = 'Maba ' . $yr;
                    }
                @endphp
                <x-form-select name="tahun_masuk" :value="$selectedTahun ?? (string)date('Y')" placeholder="Pilih Tahun Maba" :options="$yearOptions" onchange="this.closest('form').submit()" />
            </form>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2.5 bg-amber-600 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide shadow-sm hover:bg-amber-700 transition">
                Manajemen User ({{ $totalUser ?? 0 }})
            </a>
            @endif
            <a href="{{ route('pemeriksaan.create', ['tahun_masuk' => ($selectedTahun !== 'all' ? $selectedTahun : date('Y'))]) }}" class="inline-flex items-center px-5 py-2.5 bg-emerald-700 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide shadow-sm hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 transition ease-in-out duration-150">
                + Input Data Pemeriksaan
            </a>
        </div>
    </div>

    <!-- 4 Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Card 1 -->
        <div class="bg-gradient-to-br from-white to-emerald-50/30 rounded-2xl shadow-sm border border-emerald-100 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-600 group-hover:w-2 transition-all"></div>
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm font-semibold text-gray-500 mb-1">Total Pemeriksaan</div>
                    <div class="text-4xl font-extrabold text-gray-800">{{ $totalPemeriksaan }}</div>
                </div>
                <div class="p-3 bg-emerald-100 rounded-lg text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
        </div>
        
        <!-- Card 2 -->
        <div class="bg-gradient-to-br from-white to-teal-50/30 rounded-2xl shadow-sm border border-teal-100 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-teal-500 group-hover:w-2 transition-all"></div>
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm font-semibold text-gray-500 mb-1">PDF Dibuat</div>
                    <div class="text-4xl font-extrabold text-teal-600">{{ $pdfDibuat }}</div>
                </div>
                <div class="p-3 bg-teal-100 rounded-lg text-teal-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-gradient-to-br from-white to-blue-50/30 rounded-2xl shadow-sm border border-blue-100 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500 group-hover:w-2 transition-all"></div>
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm font-semibold text-gray-500 mb-1">Email Terkirim</div>
                    <div class="text-4xl font-extrabold text-blue-600">{{ $emailTerkirim }}</div>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-gradient-to-br from-white to-amber-50/30 rounded-2xl shadow-sm border border-amber-100 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-500 group-hover:w-2 transition-all"></div>
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm font-semibold text-gray-500 mb-1">Email Gagal</div>
                    <div class="text-4xl font-extrabold text-amber-500">{{ $emailGagal }}</div>
                </div>
                <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Data Table -->
    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-100">
        <div class="px-6 py-5 border-b border-gray-100 bg-white">
            <h3 class="text-lg font-bold text-gray-800">Data Pemeriksaan Terbaru</h3>
        </div>
        <div class="p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-emerald-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">NIK</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Nama Mahasiswa</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Tanggal Pemeriksaan</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Status Email</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($pemeriksaanTerbaru as $index => $data)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $data->nik ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $data->nama ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $data->created_at ? \Carbon\Carbon::parse($data->created_at)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($data->status_pengiriman == 'Terkirim')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                            Terkirim
                                        </span>
                                    @elseif($data->status_pengiriman == 'Gagal')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Gagal
                                        </span>
                                    @elseif($data->status_pengiriman == 'Proses')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Proses
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Menunggu
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 whitespace-nowrap text-sm text-center text-gray-500 italic">
                                    Belum ada data pemeriksaan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
