@extends('layout.layout')
@section('title', 'สลิปเงินเดือนอนุมัติ')

@section('desktop-content')
    <div class="container p-5">

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
                            <td>{{ $slip->creator->name ?? '-' }}</td>
                            <td>{{ number_format($slip->total_receipt, 2) }}</td>
                            <td>{{ number_format($slip->total_expenses, 2) }}</td>
                            <td>{{ number_format($slip->total_receipt - $slip->total_expenses, 2) }}</td>
                            <td>{{ $slip->monthly ? \Carbon\Carbon::parse($slip->monthly)->format('m/Y') : '-' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'รอตรวจสอบ' => 'warning',
                                        'อนุมัติแล้ว' => 'success',
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
                                <button class="btn btn-primary btn-sm view-detail" data-id="{{ $slip->id }}"
                                    @disabled($slip->status == 'อนุมัติแล้ว')>
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
    </div>

    {{-- JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('.view-detail').click(function(e) {
                e.preventDefault();
                let slipId = $(this).data('id');

                $.ajax({
                    url: '/slip/' + slipId + '/detail',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // สร้างตาราง 2 คอลัมน์
                        let tableHtml = `
                    <table class="table table-bordered table-sm" style="width:100%; text-align:left;">
                        <thead>
                            <tr>
                                <th>รายรับ</th>
                                <th>รายจ่าย</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                        // คำนวณ max length ของรายการ
                        let receiptKeys = Object.keys(data.receipt_details);
                        let expenseKeys = Object.keys(data.expenses_details);
                        let maxLen = Math.max(receiptKeys.length, expenseKeys.length);

                        for (let i = 0; i < maxLen; i++) {
                            let rKey = receiptKeys[i] || '';
                            let rVal = (rKey && data.receipt_details[rKey] != null) ?
                                parseFloat(data.receipt_details[rKey]).toLocaleString() +
                                ' บาท' :
                                '-';

                            let eKey = expenseKeys[i] || '';
                            let eVal = (eKey && data.expenses_details[eKey] != null) ?
                                parseFloat(data.expenses_details[eKey]).toLocaleString() +
                                ' บาท' :
                                '-';

                            tableHtml += `<tr>
                                <td>${rKey ? rKey + ': ' + rVal : '-'}</td>
                                <td>${eKey ? eKey + ': ' + eVal : '-'}</td>
                            </tr>`;
                        }


                        tableHtml += `
                        <tr>
                            <td><strong>รวมรายรับ:</strong> ${parseFloat(data.total_receipt).toLocaleString()} บาท</td>
                            <td><strong>รวมรายจ่าย:</strong> ${parseFloat(data.total_expenses).toLocaleString()} บาท</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align:right;"><strong>สุทธิ:</strong> ${parseFloat(data.net).toLocaleString()} บาท</td>
                        </tr>
                    </tbody>
                    </table>
                `;

                        Swal.fire({
                            title: 'รายละเอียดสลิป',
                            html: `
                        <p><strong>เจ้าของ:</strong> ${data.owner}</p>
                        <p><strong>ผู้สร้าง:</strong> ${data.creator}</p>
                        <hr>
                        ${tableHtml}
                    `,
                            showCancelButton: true,
                            confirmButtonText: 'อนุมัติ',
                            cancelButtonText: 'ปิด',
                            focusConfirm: false,
                            preConfirm: () => {
                                return $.ajax({
                                        url: data.approve_url,
                                        type: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}'
                                        }
                                    })
                                    .then(() => {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'อนุมัติเรียบร้อย',
                                            showConfirmButton: false,
                                            timer: 1500
                                        }).then(() => {
                                            location
                                                .reload(); // รีโหลดหน้าหลัง 1.5 วินาที
                                        });
                                    })
                                    .catch(() => {
                                        Swal.showValidationMessage(
                                            'ไม่สามารถอนุมัติได้');
                                    });
                            }
                        });
                    },
                    error: function() {
                        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดรายละเอียดสลิปได้', 'error');
                    }
                });
            });
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
