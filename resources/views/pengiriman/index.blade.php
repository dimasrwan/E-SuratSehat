@extends('layouts.app')

@section('content')
<div>
    <div class="mb-8 flex items-center justify-between">
        <h2 class="text-3xl font-bold text-gray-800 tracking-tight">Pengiriman Surat Keterangan Sehat</h2>
        <div class="flex gap-3">
            <form action="{{ route('pengiriman.retryFailed') }}" method="POST" onsubmit="event.preventDefault(); if(confirm('Kirim ulang semua email yang gagal?')) submitAjaxForm(this, 'btn-kirim-ulang', 'Mengirim...');">
                @csrf
                <button type="submit" id="btn-kirim-ulang" class="inline-flex items-center px-5 py-2.5 bg-amber-500 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition ease-in-out duration-150">
                    <svg class="icon-loading hidden animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="btn-text">Kirim Ulang Gagal</span>
                </button>
            </form>
            <button type="button" id="btn-kirim-terpilih" onclick="submitBulkSend(this)" class="inline-flex items-center px-5 py-2.5 bg-emerald-700 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide shadow-sm hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 transition ease-in-out duration-150">
                <svg class="icon-loading hidden animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="btn-text">Kirim Terpilih</span>
            </button>
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

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <ul class="list-disc list-inside text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-100">
        <div class="p-0 bg-white">
            <form id="bulk-send-form" action="{{ route('pengiriman.bulkSend') }}" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-emerald-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Nama Mahasiswa</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Waktu Pengiriman</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($pengiriman as $data)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(!in_array($data->status_pengiriman, ['Terkirim', 'Dalam antrean', 'Mengirim']))
                                            <input type="checkbox" name="pemeriksaan_ids[]" value="{{ $data->id }}" class="row-checkbox rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                                        @else
                                            <input type="checkbox" disabled class="rounded border-gray-300 text-gray-300 shadow-sm">
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $data->nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $data->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $data->waktu_pengiriman ? \Carbon\Carbon::parse($data->waktu_pengiriman)->format('d/m/Y H:i:s') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($data->status_pengiriman == 'Terkirim')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">Terkirim</span>
                                        @elseif($data->status_pengiriman == 'Gagal')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Gagal</span>
                                        @elseif($data->status_pengiriman == 'Mengirim')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800 animate-pulse">Mengirim</span>
                                        @elseif($data->status_pengiriman == 'Dalam antrean')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-sky-100 text-sky-800">Dalam antrean</span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Belum dikirim</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(!in_array($data->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                                            <button type="submit" formaction="{{ route('pemeriksaan.sendEmail', $data->id) }}" onclick="event.preventDefault(); submitAjaxForm(this.closest('form'), this.id, 'Memproses...', this.getAttribute('formaction'))" id="btn-individu-{{ $data->id }}" class="inline-flex items-center text-emerald-700 hover:text-white hover:bg-emerald-700 border border-emerald-700 bg-emerald-50 px-3 py-1 rounded transition">
                                                <svg class="icon-loading hidden animate-spin h-5 w-5 mr-1.5 text-emerald-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                                <span class="btn-text font-bold">{{ $data->status_pengiriman == 'Terkirim' ? 'Kirim Ulang' : 'Kirim Individu' }}</span>
                                            </button>
                                        @else
                                            <span class="text-sky-600 italic text-xs font-semibold">Diproses...</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 whitespace-nowrap text-sm text-center text-gray-500 italic">
                                        Belum ada data dengan email yang tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            
            <div class="p-6 border-t border-gray-100">
                {{ $pengiriman->links() }}
            </div>
        </div>
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
