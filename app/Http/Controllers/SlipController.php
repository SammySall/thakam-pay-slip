<?php

namespace App\Http\Controllers;

use App\Models\Slip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SlipController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search');

        // ดึงข้อมูล token จาก session
        $tokenData = null;
        if (session('token')) {
            $tokenData = json_decode(
                Crypt::decryptString(session('token')),
                true
            );
        }

        $query = Slip::with(['creator', 'approver'])
        ->where('owner_id', $tokenData['userId'])
        ->orderBy('created_at', 'desc');

        // ถ้าไม่ใช่ approver ให้เห็นเฉพาะของตัวเอง
        if ($tokenData && $tokenData['role'] !== 'approver') {
            $query->where('owner_id', $tokenData['userId']);
        }

        // กรองค้นหาชื่อผู้สร้างหรือผู้อนุมัติ
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('creator', fn($sub) => $sub->where('name', 'like', "%$search%"))
                    ->orWhereHas('approver', fn($sub) => $sub->where('name', 'like', "%$search%"));
            });
        }

        $slips = $perPage == -1 ? $query->get() : $query->paginate($perPage);

        return view('showdata', compact('slips'));
    }

    public function listNewSlip(Request $request)
    {
        $tokenData = session('token') ? json_decode(Crypt::decryptString(session('token')), true) : null;
        if (!$tokenData) return redirect('/login');

        $users = User::all(['id', 'name']);

        // ✅ เรียงจากล่าสุดไปเก่าสุด
        $slips = Slip::where('create_by_id', $tokenData['userId'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('new-slip', compact('slips', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_id' => 'required|exists:users,id',
            'receipt' => 'array',
            'expenses' => 'array',
            'monthly' => 'required|string', // จาก input type="month" เช่น 2025-10
        ]);

        $totalReceipt = array_sum($data['receipt'] ?? []);
        $totalExpenses = array_sum($data['expenses'] ?? []);

        // ✅ แปลง monthly จาก 'YYYY-MM' → 'YYYY-MM-01'
        $monthlyDate = Carbon::createFromFormat('Y-m', $data['monthly'])->startOfMonth();

        Slip::create([
            'owner_id' => $data['owner_id'],
            'create_by_id' => auth()->id(),
            'receipt_details' => json_encode($data['receipt']),
            'expenses_details' => json_encode($data['expenses']),
            'total_receipt' => $totalReceipt,
            'total_expenses' => $totalExpenses,
            'monthly' => $monthlyDate, // เก็บเป็น date
            'status' => 'รอตรวจสอบ',
        ]);

        return redirect()->back()->with('success', 'เพิ่มสลิปสำเร็จ');
    }

    public function listApproveSlip(Request $request)
{
    $tokenData = session('token') ? json_decode(Crypt::decryptString(session('token')), true) : null;
    if (!$tokenData) return redirect('/login');

    $userId = $tokenData['userId'];

    // ดึง Slip ที่รอตรวจสอบ หรือที่ user เป็นคนอนุมัติแล้ว
    $slips = Slip::with(['owner', 'creator'])
                ->where(function ($query) use ($userId) {
                    $query->where('status', 'รอตรวจสอบ')
                          ->orWhere('approve_by_id', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

    return view('approve-slip', compact('slips'));
}


    public function getSlipDetail($id)
{
    $slip = Slip::with(['owner', 'creator'])->findOrFail($id);

    return response()->json([
        'owner' => $slip->owner->name ?? '-',
        'creator' => $slip->creator->name ?? '-',
        'receipt_details' => json_decode($slip->receipt_details, true),
        'expenses_details' => json_decode($slip->expenses_details, true),
        'total_receipt' => $slip->total_receipt,
        'total_expenses' => $slip->total_expenses,
        'net' => $slip->total_receipt - $slip->total_expenses,
        'approve_url' => route('slips.approve', $slip->id),
    ]);
}

public function approveSlip($id)
{
    $slip = Slip::findOrFail($id);
    
    $slip->status = 'อนุมัติแล้ว';
    $slip->approve_by_id = auth()->id(); // เพิ่ม approve_by_id
    $slip->save();

    // ตรวจสอบว่าเป็น Ajax หรือไม่
    if (request()->ajax()) {
        return response()->json(['success' => true]);
    }

    return redirect()->back()->with('success', 'อนุมัติสลิปเรียบร้อยแล้ว');
}

public function generatePdf($id)
{
    $slip = Slip::with(['owner', 'creator'])->findOrFail($id);

    $receipts = json_decode($slip->receipt_details, true) ?? [];
    $expenses = json_decode($slip->expenses_details, true) ?? [];

    // เก็บเป็น array ของ rows สำหรับ table
    $maxRows = max(count($receipts), count($expenses));
    $tableRows = [];
    for ($i = 0; $i < $maxRows; $i++) {
        $rKey = array_keys($receipts)[$i] ?? '';
        $rVal = $rKey ? number_format($receipts[$rKey], 2) . ' บาท' : '';
        $eKey = array_keys($expenses)[$i] ?? '';
        $eVal = $eKey ? number_format($expenses[$eKey], 2) . ' บาท' : '';
        $tableRows[] = [
            'receipt' => $rKey ? "$rKey: $rVal" : '',
            'expense' => $eKey ? "$eKey: $eVal" : '',
        ];
    }

    $fields = [
        'field_1' => $slip->owner->name ?? '-',
        'field_2' => $slip->owner->prefix ?? '-',
        'field_3' => $tableRows, // <-- ส่ง array ไป Blade
        'field_4' => number_format($slip->total_receipt - $slip->total_expenses, 2),
        'field_5' => $slip->owner->position ?? '-',
        'field_6' => $slip->monthly ?? '-',
        'field_7' => $slip->owner->subdistrict ?? '-',
        'field_8' => $slip->owner->district ?? '-',
        'field_9' => $slip->owner->province ?? '-',
        'field_15' => '00',
        'field_20' => $slip->id,
    ];

    $pdf = Pdf::loadView('pdf', compact('fields'));
    return $pdf->stream('slip_' . $slip->id . '.pdf');
}


}
