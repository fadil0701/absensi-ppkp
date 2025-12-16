<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Satpelkes;
use Illuminate\Http\Request;

class SatpelkesController extends Controller
{
    /**
     * List satpelkes
     */
    public function index()
    {
        $satpelkesList = Satpelkes::aktif()->get();

        return response()->json([
            'success' => true,
            'data' => $satpelkesList->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_satpelkes' => $item->nama_satpelkes,
                    'kode_satpelkes' => $item->kode_satpelkes,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'radius_absensi' => $item->radius_absensi,
                    'alamat' => $item->alamat,
                    'is_aktif' => $item->is_aktif,
                ];
            }),
        ]);
    }
}

