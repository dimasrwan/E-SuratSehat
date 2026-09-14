@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <!-- Header & Action Controls -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 py-1">
        <div>
            @php
                $cleanYearPeng = preg_replace('/[^0-9]/', '', (string)($selectedTahun ?? 'all'));
            @endphp
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                @if(($selectedTahun ?? 'all') === 'all' || empty($cleanYearPeng))
                    Pengiriman Surat Keterangan Sehat
                @else
                    Pengiriman Surat Maba {{ $cleanYearPeng }}
                @endif
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">Daftar pengiriman email surat kesehatan per angkatan mahasiswa baru.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- 1. Tahun Maba Selector -->
            <form action="{{ route('pengiriman.index') }}" method="GET" class="w-36 sm:w-44">
                @php
                    $yearOptions = ['all' => 'Semua Tahun'];
                    foreach ($availableYears as $yr) {
                        $yearOptions[(string)$yr] = 'Maba ' . $yr;
                    }
                @endphp
                <x-form-select name="tahun_masuk" :value="$selectedTahun ?? (string)$activeYear" placeholder="Pilih Tahun Maba" :options="$yearOptions" onchange="this.closest('form').submit()" />
            </form>

            <!-- 2. Kirim Ulang Gagal (Secondary Warning Button) -->
            <form action="{{ route('pengiriman.retryFailed') }}" method="POST" onsubmit="event.preventDefault(); if(confirm('Kirim ulang semua email yang gagal?')) submitAjaxForm(this, 'btn-kirim-ulang', 'Mengirim...');">
                @csrf
                <button type="submit" id="btn-kirim-ulang" class="h-[42px] px-3.5 bg-white hover:bg-slate-50 border border-amber-300 text-amber-800 font-medium text-xs rounded-lg shadow-2xs transition duration-150 flex items-center justify-center gap-1.5">
                    <svg class="icon-loading hidden animate-spin h-4 w-4 text-amber-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span class="btn-text">Kirim Ulang Gagal</span>
                </button>
            </form>

            <!-- 3. Kirim Terpilih (Primary Action Button) -->
            <button type="button" id="btn-kirim-terpilih" onclick="submitBulkSend(this)" class="h-[42px] px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-medium text-xs rounded-lg shadow-2xs transition duration-150 flex items-center justify-center gap-1.5">
                <svg class="icon-loading hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span class="btn-text">Kirim Terpilih</span>
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200/80 rounded-lg text-xs font-medium text-emerald-800 flex items-center gap-2.5">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-3.5 bg-rose-50 border border-rose-200/80 rounded-lg text-xs font-medium text-rose-800 flex items-center gap-2.5">
            <svg class="h-4 w-4 text-rose-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Table Outer Container -->
    <div class="bg-white rounded-lg border border-slate-200/80 shadow-2xs overflow-hidden">
        <form id="bulk-send-form" action="{{ route('pengiriman.bulkSend') }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[10px] font-semibold tracking-wider">
                        <tr>
                            <th class="py-2.5 px-4 w-10 text-center">
                                <input type="checkbox" id="select-all" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/20">
                            </th>
                            <th class="py-2.5 px-4">Nama Mahasiswa</th>
                            <th class="py-2.5 px-4">Email</th>
                            <th class="py-2.5 px-4">Waktu Pengiriman</th>
                            <th class="py-2.5 px-4">Status</th>
                            <th class="py-2.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengiriman as $data)
                            <tr class="hover:bg-slate-50/60 transition duration-150">
                                <td class="py-3 px-4 text-center">
                                    @if(!in_array($data->status_pengiriman, ['Terkirim', 'Dalam antrean', 'Mengirim']))
                                        <input type="checkbox" name="pemeriksaan_ids[]" value="{{ $data->id }}" class="row-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/20">
                                    @else
                                        <input type="checkbox" disabled class="rounded border-slate-200 text-slate-300 cursor-not-allowed">
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900 text-xs">{{ $data->nama }}</td>
                                <td class="py-3 px-4 text-slate-600 text-xs font-mono">{{ $data->email }}</td>
                                <td class="py-3 px-4 text-slate-500 text-xs">
                                    {{ $data->waktu_pengiriman ? \Carbon\Carbon::parse($data->waktu_pengiriman)->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    @if($data->status_pengiriman == 'Terkirim')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            ● Terkirim
                                        </span>
                                    @elseif($data->status_pengiriman == 'Gagal')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">
                                            ● Gagal
                                        </span>
                                    @elseif($data->status_pengiriman == 'Mengirim')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60 animate-pulse">
                                            ● Mengirim
                                        </span>
                                    @elseif($data->status_pengiriman == 'Dalam antrean')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200/60">
                                            ● Dalam antrean
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                                            ● Belum dikirim
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-center">
                                    @if(!in_array($data->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                                        <button type="submit" formaction="{{ route('pemeriksaan.sendEmail', $data->id) }}" onclick="event.preventDefault(); submitAjaxForm(this.closest('form'), this.id, 'Memproses...', this.getAttribute('formaction'))" id="btn-individu-{{ $data->id }}" class="h-7 px-2.5 bg-white hover:bg-slate-50 border {{ $data->status_pengiriman == 'Gagal' ? 'border-amber-300 text-amber-800' : 'border-slate-300 text-slate-700' }} font-medium text-[11px] rounded transition duration-150 inline-flex items-center justify-center">
                                            <svg class="icon-loading hidden animate-spin h-3.5 w-3.5 mr-1 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                            <span class="btn-text font-medium">{{ $data->status_pengiriman == 'Terkirim' ? 'Kirim Ulang' : ($data->status_pengiriman == 'Gagal' ? 'Kirim Ulang' : 'Kirim Individu') }}</span>
                                        </button>
                                    @else
                                        <span class="text-sky-600 italic text-[11px] font-medium">Diproses...</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-xs text-slate-400 font-normal">
                                    Belum ada data dengan email yang tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        
        @if($pengiriman && $pengiriman->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
                {{ $pengiriman->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    document.getElementById('select-all').addEventListener('change', function(e) {
        let checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = e.target.checked;
        });
    });

    function showLoadingInline(btnId, loadingText) {
        const btn = document.getElementById(btnId);
        if (!btn || btn.disabled || btn.classList.contains('cursor-not-allowed')) return false;

        btn.disabled = true;
        btn.style.pointerEvents = 'none';
        btn.classList.add('cursor-not-allowed');
        btn.style.filter = 'brightness(0.85)';
        
        const textSpan = btn.querySelector('.btn-text');
        if(textSpan) textSpan.innerText = loadingText || 'Memproses...';
        
        const iconLoading = btn.querySelector('.icon-loading');
        if(iconLoading) iconLoading.classList.remove('hidden');

        return true;
    }

    function showLoadingInlineRow(btn) {
        if (btn.disabled || btn.classList.contains('cursor-not-allowed')) return false;
        
        btn.disabled = true;
        btn.style.pointerEvents = 'none';
        btn.classList.add('cursor-not-allowed');
        btn.style.filter = 'brightness(0.85)';
        
        const textSpan = btn.querySelector('.btn-text');
        if(textSpan) textSpan.innerText = 'Mengirim...';
        
        const iconLoading = btn.querySelector('.icon-loading');
        if(iconLoading) iconLoading.classList.remove('hidden');
        
        return true;
    }

    function submitAjaxForm(form, btnId, loadingText, customAction = null) {
        if (btnId && !showLoadingInline(btnId, loadingText)) return;
        
        const action = customAction || form.action;
        const formData = new FormData(form);
        
        fetch(action, {
            method: form.method || 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            keepalive: true
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            window.location.reload();
        })
        .catch(err => {
            alert('Terjadi kesalahan. Silakan coba lagi.');
            window.location.reload();
        });
    }

    function submitBulkSend(btn) {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            alert('Silakan pilih minimal satu data untuk dikirim!');
            return;
        }
        submitAjaxForm(document.getElementById('bulk-send-form'), btn.id, 'Mengirim...');
    }
</script>
@endsection
