<?php

namespace App\Http\Controllers;

use App\Models\Pelanggar;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\DetailPelanggaran;

class PelanggarController extends Controller
{
    public function index(): View
    {
        //get data DB
        $id_pelanggars = DB::table('pelanggars')->pluck('id_siswa')->toArray();

        $pelanggars = DB::table('pelanggars')
            ->join('siswas', 'pelanggars.id_siswa', '=', 'siswas.id')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select(
                'pelanggars.*',
                'siswas.image',
                'siswas.nis',
                'siswas.tingkatan',
                'siswas.jurusan',
                'siswas.kelas',
                'siswas.hp',
                'users.name',
                'users.email'
            )->whereIn('siswas.id', $id_pelanggars)
            ->latest()->paginate(10);

        if (request('cari')) {
            $pelanggars = $this->searchPelanggar(request('cari'), $id_pelanggars);
        }

        return view('admin.pelanggar.index', compact('pelanggars'));
    }

    public function searchPelanggar(string $cari, $id)
    {

        $pelanggars = DB::table('pelanggars')
            ->join('siswas', 'pelanggars.id_siswa', '=', 'siswas.id')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select(
                'pelanggars.*',
                'siswas.image',
                'siswas.nis',
                'siswas.tingkatan',
                'siswas.jurusan',
                'siswas.kelas',
                'siswas.hp',
                'users.name',
                'users.email'
            )->whereIn('siswas.id', $id)
            ->latest()
            ->where('users.name', 'like', '%' . $cari . '%')
            ->orWhere('siswas.nis', 'like', '%' . $cari . '%')
            ->paginate(10);

        return $pelanggars;
    }

    public function create(): View
    {
        $id_pelanggars = DB::table('pelanggars')->pluck('id_siswa')->toArray();

        //get data db
        $siswas = DB::table('siswas')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select(
                'siswas.*',
                'users.name',
                'users.email'
            )->whereNotIn('siswas.id', $id_pelanggars)
            ->latest()
            ->paginate(10);

        if (request('cari')) {
            $siswas = $this->searchSiswa(request('cari'), $id_pelanggars);
        }

        return view('admin.pelanggar.create', compact('siswas'));
    }

    public function searchSiswa(string $cari, $id)
    {

        $siswas = DB::table('siswas')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select(
                'siswas.*',
                'users.name',
                'users.email'
            )->whereNotIn('siswas.id', $id)
            ->latest()
            ->where('users.name', 'like', '%' . $cari . '%')
            ->orWhere('siswas.nis', 'like', '%' . $cari . '%')
            ->paginate(10);

        return $siswas;
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'id_siswa'      => 'required'
        ]);

        Pelanggar::create([
            'id_siswa' => $request->id_siswa,
            'poin_pelanggar' => 0,
            'status_pelanggar' => 0,
            'status' => 0
        ]);

        $idPelanggar = Pelanggar::where('id_siswa', $request-id)->value('id');

        return redirect()->route('pelanggar.show', $idPelanggar);
    }

    public function show(string $id)
    {
        //get data db
        $pelanggar = DB::table(pelanggars)
            ->join('siswas', 'pelanggars.id_siswa', '=', 'siswas.id')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select(
                'pelanggars.*',
                'siswas.image',
                'siswas.nis',
                'siswas.tingkatan',
                'siswas.jurusan',
                'siswas.kelas',
                'siswas.hp',
                'siswas.status',
                'users.name',
                'users.email'
            )
            ->where('pelanggars.id', $id)
            ->first();

        $pelanggarans = DB::table('pelanggarans')->latest()->paginate(10);

        if (request('cari')) {
            $pelanggarans = $this->searchPelanggaran(request('cari'));
        }

        $idUser = Auth::id();

        return view('admin.pelanggar.show', compact('pelanggar', 'pelanggarans', 'idUser'));
    }

    public function searchPelanggaran(string $cari)
    {
        $pelanggarans = DB::table('pelanggarans')->where(DB::raw('lower(jenis'), 'like', '%' . strtolower($cari) . '%')->paginate(10);
        return $pelanggarans;
    }

    public function storePelanggaran(Request $request)
    {   // validate form
        $validated = $request->validate([
            'id_pelanggar' => 'required',
            'id_user' => 'required',
            'id_pelanggaran' => 'required'                              
        ]);

        //create post
        DetailPelanggaran::create([
            'id_pelanggar' => $request->id_pelanggar,
            'id_user' => $request->id_user,
            'id_pelanggaran' => $request->id_pelanggaran,
            'status' => 0
        ]);

        $this->updatePoin($request->id_pelanggaran, $request->id_pelanggar);

        //redirect to index
        return redirect()->route('detailPelanggar.show', $request->id_pelanggar)->with(['success' => 'Data Berhasil Disimpan!']);
    }

    function updatePoin(string $id_pelanggaran, string $id_pelanggar)
    {
        $poin = this->calculatePoin($id_pelanggaran, $id_pelanggar);

        $datas = Pelanggar::findOrFail($id_pelanggar);

        //update post
        $datas->update([
            'poin_pelanggar' => $poin
        ]);

        $this->updateStatus($datas, $poin);
    }

    function calculatePoin(string $id_pelanggaran, string $id_pelanggar)
    {
        $poin_pelanggaran = DB::table('pelanggarans')->where('id', $id_pelanggaran)->value('poin');
        $poin_pelanggar = DB::table('pelanggars')->where('id', $id_pelanggar)->value('poin_pelanggar');
        $poin = $poin_pelanggar + $poin_pelanggaran;

        return $poin;
    }

    function updateStatus($datas, string $poin)
    {
        if (poin >= 0 && $poin <15) {
            //update post
            $katagoriPelanggar = 0;

            $datas->update([
                'status_pelanggar' => $katagoriPelanggar,
                'status' => 0
            ]);
        } elseif ($poin >= 15 && $poin < 20) {
            //update post
            $katagoriPelanggar = 1;

            if ($katagoriPelanggar > $datas->status_pelanggar && $datas->status = 0) {
                $datas->update([
                    'status_pelanggar' => $katagoriPelanggar,
                    'status' => 1
                ]);
            }
        } elseif ($poin >= 20 && $poin < 30) {
            $katagoriPelanggar = 2;

            if ($katagoriPelanggar > $datas->status_pelanggar && $datas->status = 2) {
                $datas->update([
                    'status_pelanggar' => $katagoriPelanggar,
                    'status' => 1
                ]);
            }
        } elseif ($poin >= 30 && $poin < 40) {
            $katagoriPelanggar = 3;

            if ($katagoriPelanggar > $datas->status_pelanggar && $datas->status = 2) {
                $datas->update([
                    'status_pelanggar' => $katagoriPelanggar,
                    'status' => 1
                ]);
            }
        } elseif ($poin >= 40 && $poin < 50) {
            $katagoriPelanggar = 4;

            if ($katagoriPelanggar > $datas->status_pelanggar && $datas->status = 2) {
                $datas->update([
                    'status_pelanggar' => $katagoriPelanggar,
                    'status' => 1
                ]);
            }
        } elseif ($poin >= 50 && $poin < 100) {
            $katagoriPelanggar = 5;

            if ($katagoriPelanggar > $datas->status_pelanggar && $datas->status = 2) {
                $datas->update([
                    'status_pelanggar' => $katagoriPelanggar,
                    'status' => 1
                ]);
            }
        } elseif ($poin >= 100) {
            $katagoriPelanggar = 6;

            if ($katagoriPelanggar > $datas->status_pelanggar && $datas->status = 2) {
                $datas->update([
                    'status_pelanggar' => $katagoriPelanggar,
                    'status' => 1
                ]);
            }
        }
    }

    public function statusTindak($id)
    {
        $datas = Pelanggar::findOrFail($id);

        $pelanggar = DB::table('pelanggars')
            ->join('siswas', 'pelanggars.id_siswa', '=', 'siswas.id')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select(
                'users.name'
            )
            ->where('pelanggars.id', $id)
            ->first();

        $datas->update([
            'status' => 2
        ]);

        return redirect()->route('pelanggar.index')->with(['success' => $pelanggar->name . ' Telah Ditindak!']);
    }

    // hapus data
    public function destroy($id): RedirectResponse
    {
        //delete pelanggar
        $this-destroyPelanggaran($id);

        //get post by id
        $post = Pelanggar::findOrFail($id);

        //delete post
        $post->delete();

        //redirect to index
        return redirect()->route('pelanggar.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function destroyPelanggaran(string $id)
    {
        //get id user
        $pelanggaran = DB::table('detail_pelanggarans')->where('id_pelanggar', $id);

        //delete post
        $pelanggaran->delete();
    }
}
