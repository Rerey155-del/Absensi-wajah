<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;

class AbsensiController extends Controller
{
    public function store(Request $request)
    {
        $gambarPath = null;

        if ($request->gambar) {
            $data = explode(',', $request->gambar);
            $decoded = base64_decode($data[1]);

            $filename = 'absen_' . time() . '.png';
            $folder = public_path('absen');

            // Buat folder jika belum ada
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }

            $path = $folder . '/' . $filename;
            file_put_contents($path, $decoded);

            // Simpan path relatif untuk akses melalui asset()
            $gambarPath = $filename;
        }

        $absen = Absensi::create([
            'nama' => $request->nama,
            'waktu_absen' => now(),
            'gambar' => $gambarPath
        ]);

        return response()->json([
            'nama' => $absen->nama,
            'waktu_absen' => $absen->waktu_absen,
            'gambar' => asset($absen->gambar)
        ]);
    }
}
