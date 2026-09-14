<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FakultasProdiController extends Controller
{
    /**
     * Display a listing of Fakultas & Program Studi.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $fakultas_id = $request->input('fakultas_id', 'all');
        $status = $request->input('status', 'all');

        $query = Fakultas::query()->orderBy('sort_order', 'asc');

        if (!empty($fakultas_id) && $fakultas_id !== 'all') {
            $query->where('id', (int)$fakultas_id);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where(function ($q) {
                $q->where('is_active', false)
                  ->orWhereHas('programStudi', function ($pq) {
                      $pq->where('is_active', false);
                  });
            });
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('kode', 'LIKE', "%{$search}%")
                  ->orWhereHas('programStudi', function ($pq) use ($search) {
                      $pq->where('nama', 'LIKE', "%{$search}%");
                  });
            });
        }

        $query->with(['programStudi' => function ($pq) use ($search, $status) {
            if ($status === 'active') {
                $pq->where('is_active', true);
            } elseif ($status === 'inactive') {
                $pq->where('is_active', false);
            }

            if (!empty($search)) {
                $pq->where(function ($subPq) use ($search) {
                    $subPq->where('nama', 'LIKE', "%{$search}%")
                          ->orWhereHas('fakultas', function ($fq) use ($search) {
                              $fq->where('nama', 'LIKE', "%{$search}%")
                                ->orWhere('kode', 'LIKE', "%{$search}%");
                          });
                });
            }

            $pq->orderBy('sort_order', 'asc');
        }]);

        $fakultas = $query->get();

        $masterFakultasOptions = ['all' => 'Semua Fakultas'] + Fakultas::orderBy('sort_order', 'asc')->pluck('nama', 'id')->toArray();

        return view('admin.fakultas_prodi.index', compact('fakultas', 'search', 'fakultas_id', 'status', 'masterFakultasOptions'));
    }

    /**
     * Store a newly created Fakultas.
     */
    public function storeFakultas(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:20', 'unique:fakultas,kode'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama Fakultas wajib diisi.',
            'kode.required' => 'Kode Fakultas wajib diisi.',
            'kode.unique' => 'Kode Fakultas sudah digunakan.',
        ]);

        Fakultas::create([
            'nama' => trim($validated['nama']),
            'kode' => strtoupper(trim($validated['kode'])),
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : true,
        ]);

        return redirect()->route('admin.fakultas-prodi.index')
            ->with('success', 'Fakultas berhasil ditambahkan.');
    }

    /**
     * Update the specified Fakultas.
     */
    public function updateFakultas(Request $request, Fakultas $fakultas)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:20', Rule::unique('fakultas', 'kode')->ignore($fakultas->id)],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama Fakultas wajib diisi.',
            'kode.required' => 'Kode Fakultas wajib diisi.',
            'kode.unique' => 'Kode Fakultas sudah digunakan oleh fakultas lain.',
        ]);

        $fakultas->update([
            'nama' => trim($validated['nama']),
            'kode' => strtoupper(trim($validated['kode'])),
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : false,
        ]);

        return redirect()->route('admin.fakultas-prodi.index')
            ->with('success', 'Fakultas berhasil diperbarui.');
    }

    /**
     * Remove the specified Fakultas if unused and has no program studi.
     */
    public function destroyFakultas(Fakultas $fakultas)
    {
        // Protection 1: Must not have child program studi
        if ($fakultas->programStudi()->count() > 0) {
            return redirect()->route('admin.fakultas-prodi.index')
                ->with('error', 'Fakultas tidak dapat dihapus karena masih memiliki program studi.');
        }

        // Protection 2: Check operational data usage
        $usedInMaba = MabaData::where('fakultas', $fakultas->nama)->exists();
        $usedInPemeriksaan = Pemeriksaan::where('fakultas', $fakultas->nama)->exists();

        if ($usedInMaba || $usedInPemeriksaan) {
            return redirect()->route('admin.fakultas-prodi.index')
                ->with('error', 'Fakultas sudah digunakan oleh data operasional dan tidak dapat dihapus. Nonaktifkan fakultas jika sudah tidak digunakan.');
        }

        $fakultas->delete();

        return redirect()->route('admin.fakultas-prodi.index')
            ->with('success', 'Fakultas berhasil dihapus.');
    }

    /**
     * Store a newly created Program Studi.
     */
    public function storeProdi(Request $request, Fakultas $fakultas)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('program_studi', 'nama')->where(function ($query) use ($fakultas) {
                    return $query->where('fakultas_id', $fakultas->id);
                }),
            ],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama Program Studi wajib diisi.',
            'nama.unique' => 'Program Studi dengan nama tersebut sudah ada pada Fakultas ini.',
        ]);

        ProgramStudi::create([
            'fakultas_id' => $fakultas->id,
            'nama' => trim($validated['nama']),
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : true,
        ]);

        return redirect()->route('admin.fakultas-prodi.index')
            ->with('success', 'Program Studi berhasil ditambahkan.');
    }

    /**
     * Update the specified Program Studi.
     */
    public function updateProdi(Request $request, ProgramStudi $programStudi)
    {
        $validated = $request->validate([
            'fakultas_id' => ['required', 'exists:fakultas,id'],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('program_studi', 'nama')
                    ->where(function ($query) use ($request) {
                        return $query->where('fakultas_id', $request->input('fakultas_id'));
                    })
                    ->ignore($programStudi->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'fakultas_id.required' => 'Fakultas wajib dipilih.',
            'fakultas_id.exists' => 'Fakultas yang dipilih tidak valid.',
            'nama.required' => 'Nama Program Studi wajib diisi.',
            'nama.unique' => 'Program Studi dengan nama tersebut sudah ada pada Fakultas yang dipilih.',
        ]);

        $programStudi->update([
            'fakultas_id' => $validated['fakultas_id'],
            'nama' => trim($validated['nama']),
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : false,
        ]);

        return redirect()->route('admin.fakultas-prodi.index')
            ->with('success', 'Program Studi berhasil diperbarui.');
    }

    /**
     * Remove the specified Program Studi if unused.
     */
    public function destroyProdi(ProgramStudi $programStudi)
    {
        $usedInMaba = MabaData::where('program_studi', $programStudi->nama)
            ->orWhere('program_studi_biro', $programStudi->nama)
            ->exists();

        $usedInPemeriksaan = false;
        // Check if any pemeriksaan points to mabaData with this prodi or direct column
        if (!$usedInMaba) {
            $usedInPemeriksaan = Pemeriksaan::where('pekerjaan', 'LIKE', "%{$programStudi->nama}%")->exists();
        }

        if ($usedInMaba || $usedInPemeriksaan) {
            return redirect()->route('admin.fakultas-prodi.index')
                ->with('error', 'Program studi sudah digunakan oleh data operasional dan tidak dapat dihapus. Nonaktifkan program studi jika sudah tidak digunakan.');
        }

        $programStudi->delete();

        return redirect()->route('admin.fakultas-prodi.index')
            ->with('success', 'Program Studi berhasil dihapus.');
    }

    /**
     * Get JSON prodi options by fakultas.
     */
    public function getProdiByFakultas(Request $request, Fakultas $fakultas)
    {
        $prodiList = $fakultas->programStudi()
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'nama', 'is_active']);

        return response()->json([
            'success' => true,
            'data' => $prodiList,
        ]);
    }
}
