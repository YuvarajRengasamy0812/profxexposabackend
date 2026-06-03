@extends('dashboard.layouts.master')
@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .page-header {
            display: flex;
            align-items: center;
            margin: 1.5rem 0rem;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 0;
            position: relative;
            min-height: 50px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 8px 24px #e5e4e6;
            border: 1px solid transparent;
            border-radius: 5px;
        }

        .page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            position: relative;
            margin-block-end: 0.2rem;
        }

        .btn-primary {
            /* background-color: rgb(var(--primary-rgb)) !important; */
            background-color: rgb(17 130 64) !important;
            border-color: rgb(17 130 64) !important;
            color: #fff !important;
        }

        .btn {
            font-size: 0.85rem;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            box-shadow: none;
            font-weight: 500;
        }

        .btn-secondary {
            background-color: rgb(212, 63, 141) !important;
            color: rgb(255, 255, 255) !important;
            border-color: rgb(212, 63, 141; ) !important;
        }

        .input-group {
            position: relative;
            display: flex;

            align-items: stretch;
            width: 100%;
        }

        .form-control {
            color: #1a2638;
            background-color: #fff;
            font-size: 0.8125rem;
            font-weight: 400;
            line-height: 1.7;
            border-color: #e2e6f1;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
        }

        .modal-content {
            color: #1a2638;
            background-color: #fff;
            border: 1px solid #eff2ff;
            border-radius: 0.3rem;
        }

        .modal-header .btn-close {
            font-size: 0.625rem;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
        }

        .modal-header::after {
            content: none !important;
        }

        .modal-header .material-icons {
            font-size: 24px;
            color: #333;

        }


        /* Table ui */
        /* Wrapper */
        .ref-table-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        }

        /* Controls */
        .ref-table-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .ref-table-controls input,
        .ref-table-controls select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            outline: none;
        }

        .ref-table-controls input:focus,
        .ref-table-controls select:focus {
            border-color: #c19d38;
        }

        /* Table */
        .ref-table thead {
            background: #c19d38;
            color: #fff;
        }

        .ref-table th {
            text-transform: uppercase;
            font-size: 13px;
        }

        .ref-table tbody tr {
            transition: all .3s ease;
        }

        .ref-table tbody tr:hover {
            background: rgba(233, 30, 99, .08);
            transform: scale(1.01);
        }

        /* Buttons hover */
        .ref-table .btn {
            transition: all .25s ease;
        }

        .ref-table .btn:hover {
            transform: translateY(-2px);
        }

        /* Pagination */
        .ref-pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 6px;
        }

        .ref-pagination button {
            border: none;
            background: #f1f1f1;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: .3s;
        }

        .ref-pagination button.active,
        .ref-pagination button:hover {
            background: #c19d38;
            color: #fff;
        }

        /* Horizontal scroll container */
        .ref-table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #c19d38 #f5f5f5;
        }

        /* Custom scrollbar (Webkit) */
        .ref-table-scroll::-webkit-scrollbar {
            height: 8px;
        }

        .ref-table-scroll::-webkit-scrollbar-track {
            background: #f5f5f5;
            border-radius: 10px;
        }

        .ref-table-scroll::-webkit-scrollbar-thumb {
            background: #c19d38;
            border-radius: 10px;
        }

        /* Prevent table from squeezing */
        .ref-table {
            min-width: 900px;
        }

        /* Mobile tweaks */
        @media (max-width: 768px) {
            .ref-table-controls {
                flex-direction: column;
                gap: 10px;
            }

            .ref-table-controls input,
            .ref-table-controls select {
                width: 100%;
            }

            .ref-pagination {
                flex-wrap: wrap;
            }
        }
    </style>
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="page-title">Coupon Codes</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#couponModal">Add New</button>
        </div>


        <div class="ref-table-wrapper">
            <div class="ref-table-controls">
                <input type="text" id="ref-search" placeholder="Search coupons...">
                <select id="ref-rows">
                    <option value="5">5 rows</option>
                    <option value="10" selected>10 rows</option>
                    <option value="25">25 rows</option>
                </select>
            </div>
            <div class="ref-table-scroll">
                <table class="table table-bordered ref-table">
                    <thead>
                        <tr>

                            <th>SNo</th>
                            <th>Name</th>
                            <th>Percentage</th>
                            <th>Code</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @php $serial = 1; @endphp
                        @foreach($coupons as $coupon)
                            <tr>
                                <td>{{ $serial++ }}</td>
                                <td>{{ $coupon->coupon_name }}</td>
                                <td>{{ $coupon->percentage }}%</td>
                                <td>{{ $coupon->coupon_code }}</td>
                                <td>{{ $coupon->created_at->format('Y-m-d') }}</td>
                                <td>{{ $coupon->updated_at->format('Y-m-d') }}</td>
                                <td>
                                    <button class="btn btn-primary edit-btn" data-toggle="modal" data-target="#editcouponmodal"
                                        data-id="{{ $coupon->coupon_id }}" data-name="{{ $coupon->coupon_name }}"
                                        data-percentage="{{ $coupon->percentage }}" data-code="{{ $coupon->coupon_code }}"
                                        data-status="{{ $coupon->status }}">
                                        <span class="material-icons">edit</span>
                                    </button>

                                    <!-- Delete button triggers modal -->
                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                        data-target="#deleteCoupon{{ $coupon->coupon_id }}">
                                        <span class="material-icons">delete</span>
                                    </button>
                                     


                                    <!-- Delete Modal -->
                                    <div id="deleteCoupon{{ $coupon->coupon_id }}" class="modal fade" data-backdrop="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Confirm Delete</h5>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <p>Are you sure to delete <strong>{{ $coupon->coupon_name }}</strong>?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">No</button>
                                                    <form method="POST"
                                                        action="{{ route('coupondelete', $coupon->coupon_id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /Delete Modal -->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="ref-pagination" id="ref-pagination"></div>
        </div>
    </div>
    {{-- SweetAlert for Session Messages (Create) --}}
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
    {{-- Create Modal --}}
    <div class="modal fade" id="couponModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Create Coupon</h5>
                    <button type="button" class="btn" data-dismiss="modal">
                        <span class="material-icons">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('couponadd') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="coupon_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Percentage</label>
                            <input type="number" name="percentage" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Coupon Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="coupon_code" id="coupon_code_input"
                                    required="">
                                <button type="button" class="btn btn-secondary" id="generate_code_btn">Generate</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Edit Coupon Modal -->


    <div class="modal fade" id="editcouponmodal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit Coupon</h5>
                    <button type="button" class="btn" data-dismiss="modal">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                 <form method="POST" id="editForm" >
                @csrf
                <div class="modal-body">

                    <input type="hidden" id="coupon_id">

                    <div class="form-group">
                        <label>Coupon Name</label>
                        <input type="text" name="coupon_name" id="coupon_name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Percentage</label>
                        <input type="number" name="percentage" id="percentage" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Coupon Code</label>
                        <input type="text" id="coupon_code" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Auto show modal on page load -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editForm = document.getElementById('editForm');

            // Handle Edit button click
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.dataset.id;
                    document.getElementById('coupon_name').value = this.dataset.name;
                    document.getElementById('percentage').value = this.dataset.percentage;
                    document.getElementById('coupon_code').value = this.dataset.code;
                    document.getElementById('status').value = this.dataset.status;

                    // Set form action dynamically
                    editForm.action = "{{ url('admin/couponupdate') }}/" + id;
                });
            });

            // Generate random coupon code
            $('#generate_code_btn').click(function () {
                let code = '';
                const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                const length = 8;
                for (let i = 0; i < length; i++) {
                    code += characters.charAt(Math.floor(Math.random() * characters.length));
                }
                $('#coupon_code_input').val(code);
            });
        });
    </script>


    <!-- Table -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const table = document.querySelector(".ref-table tbody");
            const rows = Array.from(table.querySelectorAll("tr"));
            const searchInput = document.getElementById("ref-search");
            const rowsSelect = document.getElementById("ref-rows");
            const pagination = document.getElementById("ref-pagination");

            let currentPage = 1;
            let rowsPerPage = parseInt(rowsSelect.value);

            function displayRows(filteredRows) {
                table.innerHTML = "";
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                filteredRows.slice(start, end).forEach(row => table.appendChild(row));
            }

            function setupPagination(filteredRows) {
                pagination.innerHTML = "";
                const pageCount = Math.ceil(filteredRows.length / rowsPerPage);

                for (let i = 1; i <= pageCount; i++) {
                    const btn = document.createElement("button");
                    btn.textContent = i;
                    if (i === currentPage) btn.classList.add("active");

                    btn.addEventListener("click", () => {
                        currentPage = i;
                        displayRows(filteredRows);
                        setupPagination(filteredRows);
                    });

                    pagination.appendChild(btn);
                }
            }

            function filterTable() {
                const query = searchInput.value.toLowerCase();
                const filteredRows = rows.filter(row =>
                    row.textContent.toLowerCase().includes(query)
                );
                currentPage = 1;
                displayRows(filteredRows);
                setupPagination(filteredRows);
            }

            rowsSelect.addEventListener("change", () => {
                rowsPerPage = parseInt(rowsSelect.value);
                filterTable();
            });

            searchInput.addEventListener("keyup", filterTable);

            filterTable();
        });
    </script>


@endsection