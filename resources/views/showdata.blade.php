@extends('layout.layout')
@section('title', 'ข้อมูลสลิปเงินเดือน')

@section('desktop-content')
    <h3 class="text-center px-2">รายการสลิปเงินเดือน</h3>
    <div class="container p-5">

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
                        <th>ผู้สร้าง</th>
                        <th>ผู้อนุมัติ</th>
                        <th>ยอดรับ</th>
                        <th>ค่าใช้จ่าย</th>
                        <th>เดือน</th>
                        <th>ดูรายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slips as $slip)
                        <tr>
                            <td>{{ $slip->creator->prefix ?? '' }} {{ $slip->creator->name ?? 'N/A' }}</td>
                            <td>{{ $slip->approver->prefix ?? '' }} {{ $slip->approver->name ?? 'N/A' }}</td>
                            <td>{{ number_format($slip->total_receipt, 2) }}</td>
                            <td>{{ number_format($slip->total_expenses, 2) }}</td>
                            <td>{{ $slip->monthly ?? '-' }}</td>
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">ไม่มีสลิป</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3 d-flex justify-content-center">
            {{ $slips->links() }}
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.view-file').click(function() {
                let slipId = $(this).data('id');
                window.open('/slip/' + slipId + '/pdf', '_blank');
            });
        });
    </script>
@endsection
