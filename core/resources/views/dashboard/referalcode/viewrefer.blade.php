

@if(isset($EStatus) && $EStatus=="edit" && isset($EditCoupon))
<!-- Edit Coupon Modal -->
<div class="modal fade show" id="editCouponModal" style="display:block;" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Coupon</h5>
                <a href="{{ route('couponlist') }}" class="btn-close" aria-label="Close"></a>
            </div>
            <form method="POST" action="{{ route('couponupdate', $EditCoupon->coupon_id) }}">
                @csrf
                <div class="modal-body">

                    <!-- Coupon Name -->
                    <div class="mb-3">
                        <label class="form-label">Coupon Name</label>
                        <input type="text" name="coupon_name" class="form-control" value="{{ $EditCoupon->coupon_name }}" required>
                    </div>

                    <!-- Percentage -->
                    <div class="mb-3">
                        <label class="form-label">Percentage</label>
                        <input type="number" name="percentage" class="form-control" value="{{ $EditCoupon->percentage }}" min="1" max="100" required>
                    </div>

                    <!-- Coupon Code (Read Only) -->
                    <div class="mb-3">
                        <label class="form-label">Coupon Code</label>
                        <input type="text" class="form-control" value="{{ $EditCoupon->coupon_code }}" readonly>
                        <small class="text-muted">Coupon code cannot be changed.</small>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1" {{ $EditCoupon->status==1 ? 'selected':'' }}>Active</option>
                            <option value="0" {{ $EditCoupon->status==0 ? 'selected':'' }}>Inactive</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <a href="{{ route('couponlist') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Auto show modal on page load -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('editCouponModal'));
        modal.show();
    });
</script>
@endif


