@extends('layout.layout')
@section('title', 'สลิปเงินเดือนที่ฉันสร้าง')

@section('desktop-content')
    <h3 class="text-center px-2">รายการสลิปเงินเดือนที่ฉันสร้าง</h3>
    <div class="container p-5">

        {{-- ปุ่มเพิ่มสลิป --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlipModal">
                    <i class="bi bi-plus-circle"></i> เพิ่มสลิป
                </button>
            </div>
        </div>

        {{-- ฟิลเตอร์ / ค้นหา --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex align-items-center">
                    <span class="me-1">แสดง</span>
                    <select name="perPage" class="form-select form-select-sm me-2" style="width:auto;"
                        onchange="this.form.submit()">
                        <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        <option value="-1" {{ request('perPage') == -1 ? 'selected' : '' }}>ทั้งหมด</option>
                    </select>
                    <span class="me-1">รายการ</span>
                </form>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                <form method="GET" class="d-flex">
                    <span class="me-1">ค้นหา:</span>
                    <input type="search" name="search" class="form-control form-control-sm me-2"
                        placeholder="ค้นหาผู้สร้างหรือผู้อนุมัติ..." value="{{ request('search') }}" style="width:auto;">
                </form>
            </div>
        </div>

        {{-- ตารางข้อมูล --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>เจ้าของสลิป</th>
                        <th>ผู้สร้าง</th>
                        <th>รายรับ (บาท)</th>
                        <th>รายจ่าย (บาท)</th>
                        <th>สุทธิ (บาท)</th>
                        <th>เดือน</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slips as $slip)
                        <tr>
                            <td>{{ $slip->owner->prefix ?? '' }} {{ $slip->owner->name ?? 'N/A' }}</td>
                            <td>{{ $slip->approver->name ?? '-' }}</td>
                            <td>{{ number_format($slip->total_receipt, 2) }}</td>
                            <td>{{ number_format($slip->total_expenses, 2) }}</td>
                            <td>{{ number_format($slip->total_receipt - $slip->total_expenses, 2) }}</td>
                            <td>{{ $slip->monthly ? \Carbon\Carbon::parse($slip->monthly)->format('m/Y') : '-' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'รอตรวจสอบ' => 'warning',
                                        'เสร็จสิ้น' => 'success',
                                        'ไม่ผ่าน' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$slip->status] ?? 'secondary' }}">
                                    {{ $slip->status }}
                                </span>
                            </td>
                            <td>
                                @if ($slip->status === 'อนุมัติแล้ว')
                                    <a href="{{ url('/slip/' . $slip->id . '/pdf') }}" class="btn btn-danger btn-sm"
                                        target="_blank">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </a>
                                @else
                                    <a href="javascript:void(0)" class="btn btn-danger btn-sm disabled"
                                        style="pointer-events: none; opacity: 0.6;">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </a>
                                @endif
                                <button class="btn btn-primary btn-sm reply-btn" data-id="{{ $slip->id }}">
                                    <i class="bi bi-search"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">ไม่มีสลิปในระบบ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3 d-flex justify-content-center">
            {{ $slips->links() }}
        </div>


        {{-- Modal: เพิ่มสลิป --}}
        <div class="modal fade" id="addSlipModal" tabindex="-1" aria-labelledby="addSlipLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('slips.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addSlipLabel">เพิ่มสลิปใหม่</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            {{-- ผู้ใช้ --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="owner_id" class="form-label">เลือกผู้ใช้</label>
                                    <select name="owner_id" class="form-select" required>
                                        <option value="">-- เลือกผู้ใช้ --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="monthly" class="form-label">เดือน</label>
                                    <input type="month" name="monthly" id="monthly" class="form-control" required>
                                </div>
                            </div>


                            <hr>
                            <h5>รายรับ</h5>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label>เงินเดือน</label>
                                    <input type="number" class="form-control receipt-input" name="receipt[เงินเดือน]"
                                        value="">
                                </div>
                                <div class="col-md-3">
                                    <label>ค่าครองชีพชั่วคราว</label>
                                    <input type="number" class="form-control receipt-input"
                                        name="receipt[ค่าครองชีพชั่วคราว]" value="">
                                </div>
                                <div class="col-md-3">
                                    <label>เงินเพิ่ม</label>
                                    <input type="number" class="form-control receipt-input" name="receipt[เงินเพิ่ม]"
                                        value="">
                                </div>
                                <div class="col-md-3">
                                    <label>เงินเดือน (ตกเบิก)</label>
                                    <input type="number" class="form-control receipt-input"
                                        name="receipt[เงินเดือน (ตกเบิก)]" value="">
                                </div>
                            </div>

                            <hr>
                            <h5>รายจ่าย</h5>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label>สหกรณ์ฯ</label>
                                    <input type="number" class="form-control expense-input" name="expenses[สหกรณ์ฯ]"
                                        value="">
                                </div>
                                <div class="col-md-3">
                                    <label>กยศ</label>
                                    <input type="number" class="form-control expense-input" name="expenses[กยศ]"
                                        value="">
                                </div>
                                <div class="col-md-3">
                                    <label>กรุงไทย</label>
                                    <input type="number" class="form-control expense-input" name="expenses[กรุงไทย]"
                                        value="">
                                </div>
                                <div class="col-md-3">
                                    <label>ออมสิน</label>
                                    <input type="number" class="form-control expense-input" name="expenses[ออมสิน]"
                                        value="">
                                </div>
                            </div>

                            <hr>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <strong>รวมรายรับ:</strong> <span id="total-receipt">0</span> บาท
                                </div>
                                <div class="col-md-4">
                                    <strong>รวมรายจ่าย:</strong> <span id="total-expenses">0</span> บาท
                                </div>
                                <div class="col-md-4">
                                    <strong>สุทธิ:</strong> <span id="total-net">0</span> บาท
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ต้องมี jQuery และ Select2 --}}
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        {{-- Script คำนวณ --}}
        <script>
            function calcTotals() {
                let totalReceipt = 0;
                document.querySelectorAll('.receipt-input').forEach(i => totalReceipt += parseFloat(i.value || 0));

                let totalExpenses = 0;
                document.querySelectorAll('.expense-input').forEach(i => totalExpenses += parseFloat(i.value || 0));

                document.getElementById('total-receipt').innerText = totalReceipt.toLocaleString();
                document.getElementById('total-expenses').innerText = totalExpenses.toLocaleString();
                document.getElementById('total-net').innerText = (totalReceipt - totalExpenses).toLocaleString();
            }

            document.querySelectorAll('.receipt-input, .expense-input').forEach(input => {
                input.addEventListener('input', calcTotals);
            });
        </script>
        <script>
        $(document).ready(function() {
            $('.view-file').click(function() {
                let slipId = $(this).data('id');
                window.open('/slip/' + slipId + '/pdf', '_blank');
            });
        });
    </script>
    @endsection
