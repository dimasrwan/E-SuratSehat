@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li><a href="/" class="hover:text-emerald-700">Dashboard</a></li>
                    <li><span class="text-slate-400 mx-1">/</span></li>
                    <li><a href="{{ route('admin.import.index') }}" class="hover:text-emerald-700">Import Data Biro</a></li>
                    <li><span class="text-slate-400 mx-1">/</span></li>
                    <li class="font-medium text-slate-700">Riwayat Import</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Riwayat Import Data Biro</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar seluruh aktivitas import batch data Biro Maba oleh Admin.</p>
        </div>
        <div>
            <a href="{{ route('admin.import.index') }}" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Import Baru
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">ID / Waktu</th>
                        <th class="px-6 py-3.5">Nama File</th>
                        <th class="px-6 py-3.5">Target Angkatan</th>
                        <th class="px-6 py-3.5">Admin</th>
                        <th class="px-6 py-3.5">Total Baris</th>
                        <th class="px-6 py-3.5">Hasil Execution</th>
                        <th class="px-6 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                            <div class="font-bold text-slate-800">Batch #{{ $batch->id }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $batch->created_at->format('d M Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-800 whitespace-nowrap">{{ $batch->original_filename }}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-700">Maba {{ $batch->tahunMaba->tahun ?? '-' }}</td>
                        <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $batch->user->name ?? '-' }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800 whitespace-nowrap">{{ number_format($batch->total_rows) }}</td>
                        <td class="px-6 py-4 text-slate-600 whitespace-nowrap space-y-0.5">
                            <div>Ditambahkan: <strong class="text-emerald-700">{{ number_format($batch->inserted_rows) }}</strong></div>
                            <div>Jadwal Updated: <strong class="text-amber-700">{{ number_format($batch->updated_rows) }}</strong></div>
                            <div>Dilewati/Skip: <span class="text-slate-500">{{ number_format($batch->skipped_rows) }}</span></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($batch->status === 'COMPLETED')
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-semibold text-[11px]">COMPLETED</span>
                            @elseif($batch->status === 'PREVIEW')
                                <a href="{{ route('admin.import.preview', $batch->id) }}" class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full font-semibold text-[11px] hover:bg-amber-100">PREVIEW (Lanjutkan)</a>
                            @elseif($batch->status === 'CANCELLED')
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 border border-slate-200 rounded-full font-medium text-[11px]">CANCELLED</span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-full font-semibold text-[11px]">{{ $batch->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400 italic">Belum ada riwayat import data Biro.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $batches->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
