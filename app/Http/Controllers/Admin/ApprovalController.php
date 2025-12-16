<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function pending(Request $request)
    {
        $query = Presensi::where('status', 'OUT_ZONE_PENDING')
            ->with(['pegawai', 'satpelkes']);

        if ($request->search) {
            $query->whereHas('pegawai', function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%");
            });
        }

        $presensi = $query->latest('waktu_absen')->paginate(10);

        return view('admin.approval.pending', compact('presensi'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        try {
            DB::select('CALL sp_approve_presensi(?, ?, ?, ?)', [
                $id,
                auth('web')->id(),
                'approve',
                $request->catatan,
            ]);

            return redirect()->route('approval.pending')->with('success', 'Presensi berhasil di-approve.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        try {
            DB::select('CALL sp_approve_presensi(?, ?, ?, ?)', [
                $id,
                auth('web')->id(),
                'reject',
                $request->catatan,
            ]);

            return redirect()->route('approval.pending')->with('success', 'Presensi berhasil di-reject.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}

