@extends('layouts.back-end.common_seller_1')
@section('content')
    <style>
        .custom_radio .addTag {
            top: 12px !important;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('public/website/assets/css/billing.css') }}">
    <main class="main checkoutRespoWrapper">
        <div class="page-content d-none d-md-block">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-6">
                        <div class="left-addressWrapper">
                            <ul class="nav nav-pills" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link pl-0 active" id="pills-chosAdd-tab" data-toggle="pill"
                                        data-target="#pills-chosAdd" type="button" role="tab"
                                        aria-controls="pills-chosAdd" aria-selected="true">Primary Address</button>
                                </li>
                                @php
                                    $address = DB::table('shipping_addresses')
                                        ->where('customer_id', auth()->id())
                                        ->get();
                                @endphp
                                @if (count($address) >= 1)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pills-chngAdd-tab" data-toggle="pill"
                                            data-target="#pills-chngAdd" type="button" role="tab"
                                            aria-controls="pills-chngAdd" aria-selected="false">Change Address</button>
                                    </li>
                                @endif
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link nwAddBtnRespo" id="pills-addNewAdd-tab" data-toggle="pill"
                                        data-target="#pills-addNewAdd" type="button" role="tab"
                                        aria-controls="pills-addNewAdd" aria-selected="false">Add New Address <i
                                            class="fa fa-plus-circle" aria-hidden="true"></i></button>
                                </li>
                            </ul>
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade p-0 show active" id="pills-chosAdd" role="tabpanel"
                                    aria-labelledby="pills-chosAdd-tab">
                                    @php
                                        $address = DB::table('shipping_addresses')
                                            ->where('customer_id', auth()->id())
                                            ->where('is_selected', 1)
                                            ->orderBy('updated_at', 'desc')
                                            ->limit(1)
                                            ->get();
                                        $instant = true;
                                        if (!empty($carts) && count($carts) > 0) {
                                            foreach ($carts as $car) {
                                                $pr = DB::table('products')
                                                    ->where('id', $car->product_id)
                                                    ->value('add_warehouse');
                                                $warehouse = DB::table('warehouse')->where('id', $pr)->value('pincode');
                                                $adres = DB::table('shipping_addresses')
                                                    ->where('customer_id', auth()->id())
                                                    ->where('is_selected', 1)
                                                    ->first();
                                                if (!$adres || !$adres->zip) {
                                                    $instant = false;
                                                    break;
                                                }
                                                if ($warehouse != $adres->zip) {
                                                    $instant = false;
                                                    break;
                                                }
                                            }
                                        }
                                        $user_wallet = DB::table('users')
                                            ->where('id', auth()->id())
                                            ->first();
                                        $balance = $user_wallet ? $user_wallet->wallet_balance : 0;
                                    @endphp

                                    <div class="selAddWrapper">
                                        <div class="custom_radio">
                                            @if (!empty($address))
                                                @foreach ($address as $key => $addres)
                                                    <div class="form-group position-relative">
                                                        <div class="form-check text-right">
                                                            <input type="radio" id="featured-{{ $key }}"
                                                                name="featured" class="address-radio"
                                                                data-address-id="{{ $addres->id }}" checked>
                                                            <label for="featured-{{ $key }}"></label>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                            <p>{{ $addres->contact_person_name }} - {{ $addres->phone }}</p>
                                                            <button type="button" class="btn btn-ic editAddressBtn"
                                                                data-id="{{ $addres->id }}"
                                                                data-name="{{ $addres->contact_person_name }}"
                                                                data-phone="{{ $addres->phone }}"
                                                                data-address="{{ $addres->address }}"
                                                                data-landmark="{{ $addres->landmark }}"
                                                                data-zip="{{ $addres->zip }}"
                                                                data-city="{{ $addres->city }}"
                                                                data-state="{{ $addres->state }}">
                                                                Edit
                                                            </button>
                                                        </div>
                                                        <p>{{ $addres->address }}, {{ $addres->city }} -
                                                            {{ $addres->zip }}, {{ $addres->state }}</p>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>

                                    <div class="modal fade" id="editAddressModal" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form id="editAddressForm" method="POST"
                                                    action="{{ route('addressupdate') }}">
                                                    @csrf
                                                    <input type="hidden" name="id" id="address_id">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Address</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <input type="text" name="contact_person_name"
                                                                    id="edit_name" class="form-control"
                                                                    placeholder="Enter Name" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <input type="text" name="phone" id="edit_phone"
                                                                    class="form-control" placeholder="Phone No." required>
                                                            </div>

                                                            <div class="col-12">
                                                                <textarea name="address" id="edit_address" class="form-control" placeholder="Address" required></textarea>
                                                            </div>

                                                            <div class="col-12">
                                                                <input type="text" name="landmark" id="edit_landmark"
                                                                    class="form-control"
                                                                    placeholder="Landmark (optional)">
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="text" name="zip" id="edit_zip"
                                                                    class="form-control" placeholder="Pincode" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <input type="text" name="city" id="edit_city"
                                                                    class="form-control" placeholder="City" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <input type="text" name="state" id="edit_state"
                                                                    class="form-control" placeholder="State" required>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <script>
                                        $(document).on("click", ".editAddressBtn", function() {
                                            $("#address_id").val($(this).data("id"));
                                            $("#edit_name").val($(this).data("name"));
                                            $("#edit_phone").val($(this).data("phone"));
                                            $("#edit_address").val($(this).data("address"));
                                            $("#edit_landmark").val($(this).data("landmark"));
                                            $("#edit_zip").val($(this).data("zip"));
                                            $("#edit_city").val($(this).data("city"));
                                            $("#edit_state").val($(this).data("state"));
                                            $("#editAddressModal").modal("show");
                                        });
                                    </script>

                                    <script>
                                        let lastShippingCost = 0;

                                        document.addEventListener("DOMContentLoaded", function() {
                                            function updateShippingCost() {
                                                const selectedAddressRadio = document.querySelector(
                                                    'input.address-radio:checked');
                                                if (!selectedAddressRadio) return;
                                                const address_id = selectedAddressRadio.dataset.addressId;
                                                const iscod = document.querySelector('input[name="iscod"]:checked')
                                                    ?.value || 0;
                                                const cartGroups = Array.from(document.querySelectorAll('.cart_group'))
                                                    .map(input => input.value);
                                                const instant = document.querySelector(
                                                    'input[name="instant_delivery"]:checked')?.value || false;

                                                fetch(
                                                        '{{ route('get_shipping_cost') }}', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                            },
                                                            body: JSON.stringify({
                                                                cart_group_id: cartGroups,
                                                                address_id: address_id,
                                                                iscod: iscod,
                                                                instant_delivery: instant
                                                            })
                                                        })
                                                    .then(response => response.json())
                                                    .then(data => {

                                                        if (data.status === "success") {
                                                            const totalAmountSpan = document.getElementById(
                                                                'total-amount');
                                                            let currentTotal = parseFloat(totalAmountSpan
                                                                .textContent.replace('₹', '').trim());

                                                            const shippingHidden = document.getElementById(
                                                                'shippingInput');

                                                            if (data.free_delivery == 1) {

                                                                const originalCost = parseFloat(data.shipping_cost);

                                                                document.getElementById('delivery-charge')
                                                                    .innerHTML =
                                                                    `<span style="color:#3f9339;font-weight:600;">Free</span>
                                                                    &nbsp;<span style="text-decoration:line-through;color:#999;">₹${originalCost.toFixed(2)}</span>`;

                                                                currentTotal = currentTotal - lastShippingCost;
                                                                totalAmountSpan.textContent = "₹" + currentTotal
                                                                    .toFixed(2);

                                                                if (shippingHidden) {
                                                                    shippingHidden.value = 0;
                                                                }

                                                                lastShippingCost = 0;
                                                                return;
                                                            }

                                                            const newShippingCost = parseFloat(data.shipping_cost);

                                                            document.getElementById('delivery-charge').textContent =
                                                                '₹' + newShippingCost.toFixed(2);

                                                            const finalAmount = currentTotal - lastShippingCost +
                                                                newShippingCost;
                                                            totalAmountSpan.textContent = '₹' + finalAmount.toFixed(
                                                                2);

                                                            if (shippingHidden) {
                                                                shippingHidden.value = newShippingCost;
                                                            }

                                                            lastShippingCost = newShippingCost;
                                                        }

                                                    })
                                                    .catch(error => console.error("Error:", error));
                                            }

                                            document.querySelectorAll('input.address-radio').forEach(radio => {
                                                radio.addEventListener('change', updateShippingCost);
                                            });

                                            document.querySelectorAll('input[name="iscod"]').forEach(radio => {
                                                radio.addEventListener('change', updateShippingCost);
                                            });

                                            document.querySelectorAll('input[name="instant_delivery"]').forEach(
                                                radio => {
                                                    radio.addEventListener('change', updateShippingCost);
                                                });

                                            if (document.querySelector('input.address-radio:checked')) {
                                                updateShippingCost();
                                            }
                                        });
                                    </script>
                                    @php
                                        $addresses = DB::table('shipping_addresses')
                                            ->where('customer_id', auth()->id())
                                            ->get();
                                    @endphp
                                    @if ($addresses->isEmpty())
                                        <div class="saveaddress" style="width: 50%; margin: auto;">
                                            <img src="{{ asset('public/website/assets/images/nosavedaddress.webp') }}"
                                                alt="">
                                        </div>
                                    @else
                                        <div class="billAddWrapper">
                                            <h3>Billing Address</h3>
                                            <div class="custom_radio">
                                                <div class="form-group position-relative selected">

                                                    @php
                                                        $address = DB::table('shipping_addresses')
                                                            ->where('customer_id', auth()->id())
                                                            ->first();
                                                    @endphp
                                                    @if ($address)
                                                        <div class="form-check pl-0">
                                                            <input type="radio" id="shipAdd1" name="shipAdd"
                                                                data-address-bill-id="{{ $address->id }}" required>
                                                            <label for="shipAdd1" class="mb-0">Same as Shipping
                                                                Address</label>
                                                        </div>
                                                    @endif

                                                </div>

                                                <div class="form-group position-relative">
                                                    <div class="form-check pl-0">
                                                        <input type="radio" id="shipAdd2" name="shipAdd"
                                                            data-address-bill-id="">
                                                        <label for="shipAdd2" class="mb-0">Use a Different Billing
                                                            Address</label>
                                                    </div>

                                                    @if (!empty($addresses) && count($addresses) > 0)
                                                        <!-- Dropdown Wrapper -->
                                                        <div class="selAddWrappers"
                                                            style="display:none; margin-top:10px;">
                                                            <select id="addressDropdown" class="form-control">
                                                                <option value="">-- Select Address --</option>
                                                                @foreach ($addresses as $key => $addres)
                                                                    <option value="{{ $addres->id }}"
                                                                        data-name="{{ $addres->contact_person_name }}"
                                                                        data-phone="{{ $addres->phone }}"
                                                                        data-full="{{ $addres->address }}, {{ $addres->city }} - {{ $addres->zip }}, {{ $addres->state }}"
                                                                        data-type="Home{{ $addres->is_selected == '1' ? ' (default)' : '' }}">
                                                                        {{ $addres->contact_person_name }} -
                                                                        {{ $addres->city }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <!-- Display Selected Address -->
                                                        <div id="selectedAddress" class="mt-3"
                                                            style="display:none; border:1px solid #ddd; padding:10px; border-radius:5px;">
                                                        </div>

                                                        <!-- Optional: Hidden input to submit ID -->
                                                        <!-- <input type="hidden" name="billing_address_id" id="billingAddressId"> -->
                                                    @endif
                                                </div>
                                                <script>
                                                    document.addEventListener("DOMContentLoaded", function() {
                                                        const shipAdd2 = document.getElementById("shipAdd2");
                                                        const addressWrapper = document.querySelector(".selAddWrappers");
                                                        const selectedAddress = document.getElementById("selectedAddress");

                                                        shipAdd2.addEventListener("change", function() {
                                                            if (this.checked) {
                                                                addressWrapper.style.display = "block";
                                                            } else {
                                                                addressWrapper.style.display = "none";
                                                                selectedAddress.style.display = "none";
                                                            }
                                                        });
                                                    });
                                                </script>
                                                <script>
                                                    let isVisible = false;

                                                    document.getElementById('addressDropdown').addEventListener('change',
                                                        function() {
                                                            const selectedOption = this.options[this.selectedIndex];

                                                            if (selectedOption.value !== "") {
                                                                document.getElementById('shipAdd2')
                                                                    .setAttribute('data-address-bill-id', selectedOption.value);
                                                                const name = selectedOption.getAttribute('data-name');
                                                                const phone = selectedOption.getAttribute('data-phone');
                                                                const full = selectedOption.getAttribute('data-full');
                                                                const type = selectedOption.getAttribute('data-type');

                                                                const displayDiv = document.getElementById('selectedAddress');
                                                                displayDiv.innerHTML = `
                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                            <p><strong>${name}</strong> - ${phone}</p>
                                                        </div>
                                                        <p>${full}</p>
                                                        <span class="addTag addTagCol1">${type}</span>
                                                    `;
                                                                displayDiv.style.display = 'block';
                                                                document.querySelector('.selAddWrappers').style.display =
                                                                    'none';
                                                                isVisible = false;
                                                            }
                                                        });
                                                </script>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-12">
                                            <div class="form-group form-check mt-3 gstinChkBox">
                                                <input type="checkbox" class="form-check-input" id="gstCheck">
                                                <label class="form-check-label" for="gstCheck">
                                                    &nbsp; Use GSTIN details for this order
                                                    <span>( Check this box if you want your business information in the
                                                        bill.)</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div id="gstFields" class="d-none">
                                            <div class="col-12 col-md-9">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" id="company_name"
                                                        placeholder="Company Name">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-9">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" id="gst_no"
                                                        placeholder="GSTIN">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <script>
                                        document.getElementById('gstCheck').addEventListener('change', function() {
                                            document.getElementById('gstFields').classList.toggle('d-none', !this
                                                .checked);
                                        });
                                    </script>
                                    <div class="billAddWrapper paymentWrapper">
                                        <h3>Payment</h3>
                                        <p>All transactions are encrypted and secure</p>
                                        <div class="custom_radio walletsss">
                                            <div class="form-group walletss position-relative d-flex align-items-start"
                                                selected>
                                                <div class="form-check pl-0">
                                                    <input type="radio" id="payWay1" name="iscod" value="0"
                                                        checked><label for="payWay1" class="mb-0">Razorpay Secure (UPI,
                                                        Cards,
                                                        Wallets, Netbanking)</label>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between"
                                                    style="margin-left: 2px;margin-top: -7px;">
                                                    <img src="{{ asset('storage/app/public/images/upi.png') }}"
                                                        class="img-fluid" alt="upi" style="margin-left: 3px;">
                                                    <img src="{{ asset('storage/app/public/images/visa.png') }}"
                                                        class="img-fluid" alt="visa">
                                                    <img src="{{ asset('storage/app/public/images/mastercard.png') }}"
                                                        class="img-fluid" alt="master">
                                                    <img src="{{ asset('storage/app/public/images/rupay.png') }}"
                                                        class="img-fluid" alt="rupay">
                                                    <span style="margin-left: 3px;">+16</span>
                                                </div>
                                            </div>
                                            @php
                                                $hasFreeDelivery = false;
                                                foreach ($carts as $cart) {
                                                    if ($cart->free_delivery == 1) {
                                                        $hasFreeDelivery = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if (!$hasFreeDelivery)
                                                <div class="form-group wallets position-relative">
                                                    <div class="form-check pl-0">
                                                        <input type="radio" id="payWay2" name="iscod"
                                                            value="1"><label for="payWay2" class="mb-0">Cash
                                                            on
                                                            Delivery (COD)</label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <form id="payment-form" class="d-none d-md-block">
                                        @csrf
                                        <input type="hidden" name="address_id" placeholder="Address" value="">
                                        <input type="hidden" name="order_note" value="1">
                                        <input type="hidden" name="transaction_id" value="">
                                        <button type="submit" id="pay-btn" class="btn btnPayNow" style="width:100%;">
                                            <span class="btn-text">Pay Now</span>
                                            <span class="btn-loader" style="display:none;">Processing...</span>
                                        </button>

                                    </form>
                                </div>
                                <div class="tab-pane fade p-0" id="pills-chngAdd" role="tabpanel"
                                    aria-labelledby="pills-chngAdd-tab">
                                    @php
                                        $address = DB::table('shipping_addresses')
                                            ->where('customer_id', auth()->id())
                                            ->get();
                                        $user_wallet = DB::table('users')
                                            ->where('id', auth()->id())
                                            ->first();
                                        $balance = $user_wallet ? $user_wallet->wallet_balance : 0;
                                    @endphp
                                    <div class="selAddWrapper">
                                        <div class="custom_radio">
                                            @if (!empty($address) && count($address) > 0)
                                                @foreach ($address as $key => $addres)
                                                    <div class="form-group position-relative">
                                                        <div class="form-check text-right">
                                                            <input type="radio" id="featured-{{ $key + 1 }}"
                                                                name="featured-{{ $key + 1 }}" class="address"
                                                                data-address-id="{{ $addres->id }}"
                                                                {{ $addres->is_selected == 1 ? 'checked' : '' }}>
                                                            <label for="featured-{{ $key + 1 }}"></label>
                                                        </div>

                                                        <div
                                                            class="d-flex align-items-center justify-content-between mb-1">
                                                            <p class="mt-1">{{ $addres->contact_person_name }} -
                                                                {{ $addres->phone }}
                                                            </p>
                                                        </div>
                                                        <p>{{ $addres->address }}, {{ $addres->city }} -
                                                            {{ $addres->zip }}, {{ $addres->state }}</p>
                                                        <span class="addTag addTagCol1">{{ $addres->address_type }} <span>
                                                                @if ($addres->is_selected == '1')
                                                                    {{ 'default' }}
                                                                @endif
                                                            </span> </span>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade p-0" id="pills-addNewAdd" role="tabpanel"
                                    aria-labelledby="pills-addNewAdd-tab">
                                    <div class="addNewAddWrapper mt-2">
                                        <h3>
                                            <button class="btn locBtn" id="useLocationBtn">Use My
                                                Location<i class="fa fa-map-marker" aria-hidden="true"></i>
                                            </button>
                                        </h3>
                                        <form id="addressForm">
                                            <div class="row">
                                                <!-- Contact Person -->
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control"
                                                            name="contact_person_name" placeholder="Enter Name" required>
                                                        <span id="nameError" class="text-danger"></span>
                                                    </div>
                                                </div>

                                                <!-- Phone -->
                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="contact"
                                                            placeholder="Phone No." required>
                                                        <span id="phoneErrorRR" class="text-danger"></span>
                                                    </div>
                                                </div>

                                                <!-- Address -->
                                                <div class="col-12 col-md-12 mb-2">
                                                    <textarea class="form-control w-100" name="address" cols="20" id="address"></textarea>
                                                    <span id="addressError" class="text-danger"></span>
                                                </div>

                                                <!-- Landmark -->
                                                <div class="col-12 col-md-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="landmark"
                                                            placeholder="Landmark (optional)">
                                                        <span id="landmarkError" class="text-danger"></span>
                                                    </div>
                                                </div>

                                                <!-- Zip -->
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="zip"
                                                            placeholder="Pincode" id="zip" required>
                                                        <span id="pincodeError" class="text-danger"></span>

                                                    </div>
                                                </div>

                                                <!-- City -->
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="city"
                                                            placeholder="City" id="city">
                                                        <span id="cityError" class="text-danger"></span>
                                                    </div>
                                                </div>

                                                <!-- State -->
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="state"
                                                            placeholder="State" id="state">
                                                        <span id="stateError" class="text-danger"></span>
                                                    </div>
                                                </div>

                                                <!-- Address Type -->
                                                <div class="col-12 col-md-12">
                                                    <div class="addTypeWrapper">
                                                        <h5>Address Type</h5>
                                                        <div class="custom_radio">
                                                            <div class="form-check">
                                                                <input type="radio" id="addtype1" name="address_type"
                                                                    value="home" checked>
                                                                <label for="addtype1" class="mb-0">Home</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" id="addtype2" name="address_type"
                                                                    value="work">
                                                                <label for="addtype2" class="mb-0">Work</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" id="addtype3" name="address_type"
                                                                    value="other">
                                                                <label for="addtype3" class="mb-0">Other</label>
                                                            </div>
                                                        </div>
                                                        <span id="addressTypeError" class="text-danger"></span>
                                                    </div>
                                                </div>

                                                <!-- Submit Button -->
                                                <div class="col-12 mt-3">
                                                    <button class="btn btn-primary" type="submit">Add</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                                <script>
                                    const payBtn = document.getElementById("pay-btn");
                                    const btnText = payBtn.querySelector(".btn-text");
                                    const btnLoader = payBtn.querySelector(".btn-loader");

                                    function showBtnLoader() {
                                        btnText.style.display = 'none';
                                        btnLoader.style.display = 'inline-block';
                                        payBtn.disabled = true;
                                    }

                                    function hideBtnLoader() {
                                        btnText.style.display = 'inline-block';
                                        btnLoader.style.display = 'none';
                                        payBtn.disabled = false;
                                    }

                                    payBtn.addEventListener("click", function() {

                                        let selectedShip = document.querySelector('input[name="featured"]:checked');
                                        if (!selectedShip) {
                                            alert("Please select a shipping address.");
                                            return;
                                        }

                                        let billRadio = document.querySelector('input[name="shipAdd"]:checked');
                                        if (!billRadio) {
                                            alert("Please select a billing address option.");
                                            return;
                                        }

                                        if (billRadio.id === "shipAdd2") {
                                            let dropdown = document.getElementById("addressDropdown");
                                            if (!dropdown.value || dropdown.value === "") {
                                                alert("Please select a billing address from dropdown.");
                                                dropdown.focus();
                                                return;
                                            }
                                        }
                                        document.getElementById('payment-form').addEventListener('submit', function(e) {
                                            e.preventDefault();
                                            showBtnLoader();

                                            const formData = new FormData(this);
                                            const data = {};

                                            formData.forEach((value, key) => data[key] = value);

                                            let iscod = document.querySelector('input[name="iscod"]:checked')
                                                .value;

                                            let delivery_charge = document.getElementById('delivery-charge')
                                                .innerText.replace('₹', '')
                                                .trim();
                                            let wallet_amount = document.getElementById('wallet_amount')
                                                .innerText.replace('₹', '')
                                                .trim();
                                            let coupon_charge = document.getElementById('coupon-charge')
                                                .innerText.replace('₹', '')
                                                .trim();
                                            let total_amount = document.getElementById('total-amount').innerText
                                                .replace('₹', '')
                                                .trim();

                                            let selectedShip = document.querySelector(
                                                'input[name="featured"]:checked');
                                            let shipping_address_id = selectedShip ? selectedShip.getAttribute(
                                                    'data-address-id') :
                                                null;

                                            let selectedBill = document.querySelector(
                                                'input[name="shipAdd"]:checked');

                                            let billing_address_id = selectedBill ? selectedBill.getAttribute(
                                                    'data-address-bill-id') :
                                                null;

                                            data['iscod'] = iscod;
                                            data['shipping_fee'] = delivery_charge;
                                            data['wallet_deduct_amount'] = wallet_amount;
                                            data['coupon_discount'] = coupon_charge;
                                            data['amount'] = total_amount;
                                            data['shipping_address_id'] = shipping_address_id;
                                            data['billing_address_id'] = billing_address_id;
                                            data['coupon_code'] = document.getElementById('coupon_codes').value;
                                            data['company_name'] = document.getElementById('company_name')
                                                .value;
                                            data['gst_no'] = document.getElementById('gst_no').value;

                                            fetch("{{ route('create.razorpay.order') }}", {
                                                    method: "POST",
                                                    credentials: "include", // IMPORTANT
                                                    headers: {
                                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                                        "Content-Type": "application/json"
                                                    },
                                                    body: JSON.stringify(data)
                                                })
                                                .then(res => res.json())
                                                .then(res => {
                                                    if (iscod === '1') {
                                                        if (res.order_ids && res.order_ids.length > 0) {
                                                            window.location.href = res.redirect_url;
                                                            return;
                                                        } else {
                                                            alert("Order creation failed");
                                                            hideBtnLoader();
                                                            return;
                                                        }
                                                    }

                                                    if (!res.online_payment || !res.online_payment.id) {
                                                        alert("Failed to create Razorpay order.");
                                                        return;
                                                    }
                                                    let created_cart_group_ids = res.cart_group_ids;
                                                    var options = {
                                                        "key": "{{ config('razor.razor_key') }}",
                                                        "amount": res.amount * 100,
                                                        "currency": "INR",
                                                        "name": "Interior Chowk",
                                                        "description": "Order Payment",
                                                        "order_id": res.online_payment.id,

                                                        "handler": function(response) {
                                                            showBtnLoader();
                                                            fetch("{{ route('payment.razorpay') }}", {
                                                                    method: "POST",
                                                                    credentials: "include", // VERY IMPORTANT
                                                                    headers: {
                                                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                                                        "Content-Type": "application/json"
                                                                    },
                                                                    body: JSON.stringify({
                                                                        razorpay_payment_id: response
                                                                            .razorpay_payment_id,
                                                                        razorpay_order_id: response
                                                                            .razorpay_order_id,
                                                                        razorpay_signature: response
                                                                            .razorpay_signature,
                                                                        cart_group_ids: created_cart_group_ids
                                                                    })
                                                                })
                                                                .then(res => res.json())
                                                                .then(final => {
                                                                    console.log(final);

                                                                    if (final && final
                                                                        .success) {

                                                                        window.location.href =
                                                                            final.redirect_url;
                                                                    } else {
                                                                        alert(
                                                                            "Payment verification failed on server."
                                                                        );
                                                                        hideBtnLoader();
                                                                    }
                                                                });
                                                        },
                                                        "modal": {
                                                            "ondismiss": function() {
                                                                console.log(
                                                                    "Payment popup closed.");
                                                                hideBtnLoader();
                                                                location.reload();
                                                            }
                                                        }
                                                    };
                                                    var rzp1 = new Razorpay(options);
                                                    rzp1.open();
                                                })
                                                .catch(err => alert("Error: " + err.message));
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6">
                        <div class="right-itemWrapper">
                            <ul>
                                <div class="scroll" style="overflow-y: scroll; position: relative; right: 0px;">
                                    @foreach ($carts as $cart)
                                        @php
                                            $images = json_decode($cart->image, true);
                                        @endphp
                                        <li>
                                            <div class="prod-head">
                                                <h5>Product Details</h5>
                                            </div>
                                            <div class="pro-desc">
                                                <img src="{{ 'https://pub-3593718b2c3a49558e703e35d10e7897.r2.dev' . '/' . ltrim($images[0] ?? 'default.jpg', '/') }}"
                                                    class="img-fluid" alt="pro-img">
                                                <div class="ml-3">
                                                    <a href="{{ url('product/' . $cart->slug) }}">
                                                        <h4 class="proHead">
                                                            {{ strlen($cart->name) > 40 ? substr($cart->name, 0, 40) . '...' : $cart->name }}
                                                        </h4>
                                                    </a>
                                                    <h4 class="subProHead">₹ {{ $cart->listed_price }}<span>₹
                                                            {{ $cart->variant_mrp }}</span></h4>
                                                    <label>
                                                        @if ($cart->discount_type == 'percent')
                                                        {{ $cart->discount }}% @else₹ {{ $cart->discount }}
                                                            {{ $cart->discount_type }}
                                                        @endif
                                                        off
                                                    </label>
                                                </div>
                                            </div>
                                            <p class="d-flex align-items-center justify-content-start"><img
                                                    src="{{ asset('storage/app/public/images/Product_Return.png') }}"
                                                    class="img-fluid" alt="pro-return" />
                                                @if ($cart->Return_days != 0)
                                                    {{ $cart->Return_days }} Return Days
                                                @else
                                                    Not Returnable
                                                @endif
                                                <br>
                                                Quantity- {{ $cart->cart_qty }}
                                                <br>
                                                Size- {{ $cart->sizes }}<br>
                                                Variation -
                                                {{ $cart->variation }}
                                                <br>
                                                Color - {{ $cart->color_name }}
                                                <br>
                                                @php
                                                    $cartshipping = DB::table('cart_shippings')
                                                        ->where('cart_group_id', $cart->cart_group_id)
                                                        ->first();

                                                    $deliveryDate = \Carbon\Carbon::now()
                                                        ->addDays($cartshipping->estimated_delivery_days ?? 0)
                                                        ->format('d M Y');
                                                @endphp
                                                Delivery by - {{ $deliveryDate }}
                                            </p>
                                        </li>
                                        <input type="hidden" class="cart_group" value="{{ $cart->cart_group_id }}">
                                    @endforeach
                                </div>
                                @if ($instant === true)
                                    <li>
                                        <div
                                            class="offerCoupWrapper offerCoupWrapperboxShadw bg-white cashWrapper mt-2 d-flex ">
                                            <input type="checkbox" id="instant" name="instant_delivery"
                                                value="{{ $instant }}">
                                            <h6 class="m-2">Instant Delivery — Get it in 4 hours! </h6>

                                        </div>
                                    </li>
                                @endif
                                <li class="offerListContent">
                                    @if ($coupon_discount->count())
                                        @php $firstCoupon = $coupon_discount->first(); @endphp
                                        <div class="offerCoupWrapper">
                                            <div class="offCoupLeftWrap">
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <img src="{{ asset('storage/app/public/images/discount_87.png') }}"
                                                        class="img-fluid" alt="discount">
                                                    <div>
                                                        <h5>{{ $firstCoupon->code }}<span>Best offer for you</span></h5>
                                                        <p class="offParaCnt">{{ $firstCoupon->description }}</p>
                                                    </div>
                                                </div>
                                                <div class="appBtnAndValid">
                                                    @if ($total_listed_price >= $firstCoupon->min_purchase)
                                                        <button type="submit" class="btn btnApply"
                                                            data-code="{{ $firstCoupon->code }}">Apply now</button>
                                                    @else
                                                        <button type="submit" class="btn btnApply" disabled>Apply
                                                            now</button>
                                                        <div><span
                                                                style="color: red; font-size: 12px; margin-left: 5px;">(Min
                                                                purchase
                                                                ₹{{ $firstCoupon->min_purchase }})</span></div>
                                                    @endif
                                                    <p>Valid till :
                                                        {{ \Carbon\Carbon::parse($firstCoupon->expire_date)->format('d M, Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($coupon_discount->count() > 1)
                                        <div class="accordion" id="accordionExample">
                                            <div class="card">
                                                <div class="card-header" id="headingOne">
                                                    <h5 class="mb-0">
                                                        <button class="btn viewOffBtn collapsed" type="button"
                                                            data-toggle="collapse" data-target="#collapseOne"
                                                            aria-expanded="false" aria-controls="collapseOne">
                                                            View More Coupon codes<i class="fa fa-arrow-right"
                                                                aria-hidden="true"></i>
                                                        </button>
                                                    </h5>
                                                </div>
                                                @if ($coupon_discount->count() > 1)
                                                    <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                                                        data-parent="#accordionExample">
                                                        <div class="card-body p-0">
                                                            @foreach ($coupon_discount->skip(1) as $coupon)
                                                                <div class="offerCoupWrapper">
                                                                    <div class="offCoupLeftWrap">
                                                                        <div
                                                                            class="d-flex align-items-start justify-content-between">
                                                                            <img src="{{ asset('storage/app/public/images/discount_87.png') }}"
                                                                                class="img-fluid" alt="discount">
                                                                            <div>
                                                                                <h5>{{ $coupon->code }}<span>Best offer for
                                                                                        you</span></h5>
                                                                                <p style="font-size: 14px">
                                                                                    {{ $coupon->description }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <button type="submit" class="btn btnApply"
                                                                                data-code="{{ $coupon->code }}">Apply
                                                                                now</button>
                                                                            <p style="font-size: 11px">Valid till :
                                                                                {{ \Carbon\Carbon::parse($coupon->expire_date)->format('d M, Y') }}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                    @endif
                                    @endif
                                    <script>
                                        document.querySelectorAll('.btnApply').forEach(function(button) {
                                            button.addEventListener('click', function(e) {
                                                e.preventDefault();
                                                let cod = this.getAttribute('data-code');
                                                let clickedButton = this;

                                                let shipping_fee_raw = document.getElementById('delivery-charge').textContent.trim();
                                                let shipping_fee = shipping_fee_raw.replace(/[^\d.]/g, '');
                                                console.log('Shipping fee:', shipping_fee);

                                                fetch('{{ route('applys') }}', {
                                                        method: 'POST',
                                                        headers: {
                                                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                                            "Content-Type": "application/json"
                                                        },
                                                        body: JSON.stringify({
                                                            code: cod,
                                                            shipping_fee: shipping_fee
                                                        })
                                                    })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        console.log('API Response:', data);
                                                        if (typeof data === 'string') {
                                                            alert(data); // e.g. "invalid_coupon"
                                                            return;
                                                        }
                                                        if (!data.coupon_discount) {
                                                            alert('Invalid coupon');
                                                            return;
                                                        }
                                                        const discountAmountNum = parseFloat(data.coupon_discount);
                                                        if (isNaN(discountAmountNum)) {
                                                            alert('Invalid discount amount');
                                                            return;
                                                        }

                                                        const discountAmount = discountAmountNum.toFixed(2);

                                                        document.getElementById('coupon-charge').textContent = '-₹' + discountAmount;
                                                        document.getElementById('coupon_codes').value = data.coupon_code;

                                                        const coupon_cost = discountAmountNum;
                                                        const totalAmountSpan = document.getElementById('total-amount');

                                                        const totalAmountText = totalAmountSpan.textContent.replace(/[₹,]/g, '').trim();
                                                        const totalAmount = parseFloat(totalAmountText);

                                                        if (isNaN(totalAmount)) {
                                                            console.error('Total amount is NaN:', totalAmountText);
                                                            return;
                                                        }

                                                        const finalAmount = totalAmount - coupon_cost;
                                                        totalAmountSpan.textContent = '₹' + finalAmount.toFixed(2);
                                                        clickedButton.textContent = "Applied";
                                                        clickedButton.disabled = true;
                                                        clickedButton.classList.add("btn-success");
                                                    })
                                                    .catch(error => {
                                                        console.error('Error:', error);
                                                    });
                                            });
                                        });
                                    </script>
                                    @if ($balance > 0)
                                        <div
                                            class="offerCoupWrapper offerCoupWrapperboxShadw bg-white cashWrapper mt-2 d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6>Use InteriorChowk Wallet cash</h6>
                                                <p>Available Balance: <span
                                                        id="wallet_balance">₹{{ $balance ? $balance : 0 }}</span></p>
                                            </div>
                                            @if ($balance == 0)
                                                <div>
                                                    <button class="btn btnCash" disabled>Use Cash</button>
                                                </div>
                                            @elseif($balance > 0)
                                                <div>
                                                    <button class="btn btnCash">Use Cash</button>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <script>
                                        document.querySelector('.btnCash').addEventListener('click', function() {
                                            var walletText = document.getElementById('wallet_balance').textContent;
                                            var wallet_amount = parseFloat(walletText.replace('₹', '').trim());
                                            var totalAmountText = document.getElementById('total-amount').textContent
                                                .replace('₹', '').trim();
                                            var total_amount = parseFloat(totalAmountText);

                                            var used_wallet_amount = Math.min(wallet_amount, total_amount);

                                            document.getElementById('wallet_amount').textContent = "₹" +
                                                used_wallet_amount.toFixed(2);

                                            var remaining_amount = total_amount - used_wallet_amount;
                                            document.getElementById('total-amount').textContent = "₹" + remaining_amount
                                                .toFixed(2);

                                            if (remaining_amount != 0) {
                                                document.querySelectorAll('.form-group.wallets').forEach(function(el) {
                                                    el.style.display = 'none';
                                                });
                                            }
                                            if (remaining_amount === 0) {
                                                document.querySelectorAll('.walletsss').forEach(function(el) {
                                                    el.style.display = 'none';
                                                });
                                            }

                                            this.textContent = "Cash Used";
                                            this.disabled = true;
                                            this.classList.add("btn-success");

                                            console.log("Remaining to pay after wallet: ₹" + remaining_amount.toFixed(
                                                2));
                                        });
                                    </script>
                                </li>
                            </ul>
                            <div class="billBreakdownWrapper">
                                <h4>Total Bill Breakdown</h4>
                                <div class="amtBrkdownWrapper">
                                    <div class="cillBrkCnt">
                                        <label>Bag Amount</label>
                                        <span class="d-flex">₹{{ $total_variant_mrp }}.00</span>
                                    </div>
                                    <div class="cillBrkCnt">
                                        <label>Bag Saving</label>
                                        <span class="d-flex">-₹{{ $total_variant_mrp - $total_listed_price }}</span>
                                    </div>
                                    <hr>
                                    <div class="cillBrkCnt">
                                        <label class="d-flex align-items-center justify-content-start">Coupon & Voucher<img
                                                src="{{ asset('storage/app/public/images/discount_87.png') }}"
                                                class="img-fluid" alt="voucher" /></label>
                                        <span id="coupon-charge" class="d-flex">₹0.00</span>
                                        <input type="hidden" id="coupon_codes" value="">
                                    </div>
                                    <div class="cillBrkCnt">
                                        <label>Delivery Charge</label>
                                        <span id="delivery-charge" class="d-flex">₹0.00</span>
                                    </div>
                                    <div class="cillBrkCnt">
                                        <label>Paid By Wallet</label>
                                        <span id="wallet_amount" class="d-flex">₹ 0.00</span>
                                    </div>
                                    <hr>
                                    <div class="cillBrkCnt">
                                        <label>Total Amount</label>
                                        <span id="total-amount"
                                            class="d-flex">₹{{ $total_variant_mrp - ($total_variant_mrp - $total_listed_price) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12">
                        <form id="payment-form" class="d-md-none">
                            @csrf
                            <input type="hidden" name="address_id" placeholder="Address" value="">
                            <input type="hidden" name="order_note" value="1">
                            <input type="hidden" name="transaction_id" value="">
                            <button type="submit" class="btn btnPayNow">Pay Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content d-md-none">
            @php
                $mobilePrimaryAddress = DB::table('shipping_addresses')
                    ->where('customer_id', auth()->id())
                    ->first();
                $mobileAddresses = DB::table('shipping_addresses')
                    ->where('customer_id', auth()->id())
                    ->get();

                $mobileUserWallet = DB::table('users')
                    ->where('id', auth()->id())
                    ->first();

                $mobileBalance = $mobileUserWallet ? $mobileUserWallet->wallet_balance : 0;

                $mBagAmount = $total_variant_mrp;
                $mBagSaving = $total_variant_mrp - $total_listed_price;
                $mInitialDelivery = 0;
                $mInitialCoupon = 0;
                $mInitialWallet = 0;
                $mTotalAmount = $total_listed_price;
                $mInstant = true;
                if (!empty($carts) && count($carts) > 0) {
                    foreach ($carts as $car) {
                        $pr = DB::table('products')->where('id', $car->product_id)->value('add_warehouse');
                        $warehouse = DB::table('warehouse')->where('id', $pr)->value('pincode');
                        $adres = DB::table('shipping_addresses')
                            ->where('customer_id', auth()->id())
                            ->where('is_selected', 1)
                            ->first();
                        if (!$adres || !$adres->zip) {
                            $mInstant = false;
                            break;
                        }
                        if ($warehouse != $adres->zip) {
                            $mInstant = false;
                            break;
                        }
                    }
                }

                $mHasFreeDelivery = false;
                foreach ($carts as $cartTmp) {
                    if ($cartTmp->free_delivery == 1) {
                        $mHasFreeDelivery = true;
                        break;
                    }
                }
            @endphp

            <div class="container">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12">
                        <div class="mobileAddrWrapper">

                            <div class="addrMainWrap">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="mb-0">Primary Address</h6>
                                        @if ($mobilePrimaryAddress)
                                            <button type="button" class="btn edit-btn editAddressBtn"
                                                data-id="{{ $mobilePrimaryAddress->id }}"
                                                data-name="{{ $mobilePrimaryAddress->contact_person_name }}"
                                                data-phone="{{ $mobilePrimaryAddress->phone }}"
                                                data-address="{{ $mobilePrimaryAddress->address }}"
                                                data-landmark="{{ $mobilePrimaryAddress->landmark }}"
                                                data-zip="{{ $mobilePrimaryAddress->zip }}"
                                                data-city="{{ $mobilePrimaryAddress->city }}"
                                                data-state="{{ $mobilePrimaryAddress->state }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btnEdit" data-toggle="modal"
                                        data-target="#exampleModal">
                                        Change Address
                                    </button>
                                </div>

                                @if ($mobilePrimaryAddress)
                                    <p>{{ $mobilePrimaryAddress->contact_person_name }} -
                                        {{ $mobilePrimaryAddress->phone }}</p>
                                    <p>
                                        {{ $mobilePrimaryAddress->address }},
                                        {{ $mobilePrimaryAddress->city }} - {{ $mobilePrimaryAddress->zip }},
                                        {{ $mobilePrimaryAddress->state }}
                                    </p>
                                @else
                                    <p>No primary address selected.</p>
                                @endif

                                <input type="hidden" id="mobile_shipping_address_id"
                                    value="{{ $mobilePrimaryAddress->id ?? '' }}">
                            </div>

                            <div class="addrMainWrap">
                                <h6 class="mb-1">Billing Address</h6>
                                <div class="custom_radio">
                                    <div class="form-group position-relative border-bottom-0 selected">
                                        <div class="form-check pl-0">
                                            <input type="radio" name="shipAddMob" id="shipAddMob1" checked required>
                                            <label for="shipAddMob1" class="mb-0">Same as Shipping address</label>
                                        </div>
                                    </div>

                                    <div class="form-group position-relative">
                                        <div class="form-check pl-0">
                                            <input type="radio" name="shipAddMob" id="shipAddMob2">
                                            <label for="shipAddMob2" class="mb-0">Use a different billing
                                                address</label>
                                        </div>

                                        <div class="selAddWrappers mob-inp mt-1 mb-1" style="display:none;">
                                            @if ($mobileAddresses->isNotEmpty())
                                                <select class="form-control" id="mobileBillingDropdown">
                                                    <option value="">-- Select Address --</option>
                                                    @foreach ($mobileAddresses as $mKey => $addres)
                                                        <option value="{{ $addres->id }}"
                                                            data-name="{{ $addres->contact_person_name }}"
                                                            data-phone="{{ $addres->phone }}"
                                                            data-full="{{ $addres->address }}, {{ $addres->city }} - {{ $addres->zip }}, {{ $addres->state }}">
                                                            {{ $addres->contact_person_name }} - {{ $addres->city }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div class="displaySelAddr mt-1" style="display:none;"></div>
                                            @else
                                                <div class="saveaddress" style="width: 50%; margin: auto;">
                                                    <img src="{{ asset('public/website/assets/images/nosavedaddress.webp') }}"
                                                        alt="">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="addrMainWrap">
                                <div class="form-group form-check d-flex align-items-center gstinChkBox mb-0">
                                    <input type="checkbox" class="form-check-input" id="gstCheckMob" name="gstCheckMob">
                                    <label class="form-check-label font-weight-bold ml-2" for="gstCheckMob">
                                        Use GSTIN details for this order
                                    </label>
                                </div>
                                <div class="mob-inp" id="gstFieldsMob" style="display: none;">
                                    <div class="form-group mt-1">
                                        <input type="text" class="form-control" id="company_name_mobile"
                                            placeholder="Company Name">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="gst_no_mobile"
                                            placeholder="GSTIN">
                                    </div>
                                </div>
                            </div>

                            @if ($mInstant === true)
                                <div class="addrMainWrap">
                                    <div class="form-group form-check d-flex align-items-center mb-0">
                                        <input type="checkbox" class="form-check-input" id="instant_delivery_mobile"
                                            name="instant_delivery">
                                        <label class="form-check-label font-weight-bold ml-2"
                                            for="instant_delivery_mobile">
                                            Instant Delivery — Get it in 4 hours!
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div class="addrMainWrap">
                                @foreach ($carts as $cart)
                                    @php
                                        $images = json_decode($cart->image, true);
                                        $cartshipping = DB::table('cart_shippings')
                                            ->where('cart_group_id', $cart->cart_group_id)
                                            ->first();

                                        $deliveryDate = \Carbon\Carbon::now()
                                            ->addDays($cartshipping->estimated_delivery_days ?? 0)
                                            ->format('d M Y');
                                    @endphp

                                    <div class="mobProDetails">
                                        <h6 class="proHeading">Product Details</h6>
                                        <div class="d-flex align-items-start">
                                            <img src="{{ 'https://pub-3593718b2c3a49558e703e35d10e7897.r2.dev' . '/' . ltrim($images[0] ?? 'default.jpg', '/') }}"
                                                class="img-fluid" alt="pro-img">
                                            <div class="ml-3">
                                                <a href="{{ url('product/' . $cart->slug) }}">
                                                    <p class="proName">
                                                        {{ strlen($cart->name) > 40 ? substr($cart->name, 0, 40) . '...' : $cart->name }}
                                                    </p>
                                                </a>
                                                <p>₹ {{ $cart->listed_price }}
                                                    <span class="text-decoration-line-through">₹
                                                        {{ $cart->variant_mrp }}</span>
                                                </p>
                                                <label class="offValue">
                                                    @if ($cart->discount_type == 'percent')
                                                        {{ $cart->discount }}% off
                                                    @else
                                                        ₹ {{ $cart->discount }} {{ $cart->discount_type }} off
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mobProExtDetails">
                                        <p>
                                            @if ($cart->Return_days != 0)
                                                {{ $cart->Return_days }} Return Days
                                            @else
                                                Non Returnable
                                            @endif
                                        </p>
                                        <p>Quantity - {{ $cart->cart_qty }}</p>
                                        <p>Size - {{ $cart->sizes }}</p>
                                        <p>Colour - {{ $cart->color_name }}</p>
                                        <p>Variation - {{ $cart->variation }}</p>
                                        <p>Delivered by - {{ $deliveryDate }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="addrMainWrap">
                                <h6 class="proHeading">Coupons</h6>

                                @if ($coupon_discount->count())
                                    @php $mFirstCoupon = $coupon_discount->first(); @endphp
                                    <div class="offerCoupWrapper">
                                        <div class="offCoupLeftWrap">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <img src="{{ asset('storage/app/public/images/discount_87.png') }}"
                                                    class="img-fluid" alt="discount">
                                                <div>
                                                    <h5>{{ $mFirstCoupon->code }}<span>Best offer for
                                                            you</span></h5>
                                                    <p>{{ $mFirstCoupon->description }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                @php
                                                    $isApplicable = $total_listed_price >= $mFirstCoupon->min_purchase;
                                                @endphp
                                                <button type="button" class="btn btnApplyMobile"
                                                    data-code="{{ $mFirstCoupon->code }}"
                                                    {{ $isApplicable ? '' : 'disabled' }}>
                                                    {{ $isApplicable ? 'Apply' : 'Apply' }}
                                                </button>
                                                @if (!$isApplicable)
                                                    <p class="coupValidty" style="color:red;font-size:12px;">
                                                        (Min purchase ₹{{ $mFirstCoupon->min_purchase }})
                                                    </p>
                                                @endif
                                                <p class="coupValidty">
                                                    Valid till :
                                                    {{ \Carbon\Carbon::parse($mFirstCoupon->expire_date)->format('d M, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($coupon_discount->count() > 1)
                                    <!-- Button trigger modal -->
                                    <button type="button" class="btn viewOffBtn" data-toggle="modal" data-target="#couponsModal">
                                        View More Coupon codes<i class="fa fa-arrow-right" aria-hidden="true"></i>
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal couponsModal fade" id="couponsModal" tabindex="-1" aria-labelledby="couponsModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="couponsModalLabel">Coupons</h6>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    @foreach ($coupon_discount->skip(1) as $mCoupon)
                                                        <div class="offerCoupWrapper">
                                                            <div class="offCoupLeftWrap">
                                                                <div
                                                                    class="d-flex align-items-start justify-content-between">
                                                                    <img src="{{ asset('storage/app/public/images/discount_87.png') }}"
                                                                        class="img-fluid" alt="discount">
                                                                    <div>
                                                                        <h5>{{ $mCoupon->code }}<span>Best offer
                                                                                for you</span></h5>
                                                                        <p>{{ $mCoupon->description }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="text-right">
                                                                    <button type="button" class="btn btnApplyMobile"
                                                                        data-code="{{ $mCoupon->code }}">
                                                                        Apply
                                                                    </button>
                                                                    <p class="coupValidty">Valid till :
                                                                        {{ \Carbon\Carbon::parse($mCoupon->expire_date)->format('d M, Y') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <input type="hidden" id="m_coupon_code" value="">
                            </div>

                            @if ($mobileBalance > 0)
                                <div class="addrMainWrap">
                                    <div
                                        class="offerCoupWrapper offerCoupWrapperboxShadw bg-white cashWrapper mt-2 d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6>Use InteriorChowk Wallet cash</h6>
                                            <p>Available Balance:
                                                <span id="m_wallet_balance_display">₹{{ $mobileBalance }}</span>
                                            </p>
                                        </div>
                                        <div>
                                            <button class="btn btnCashMobile">Use Cash</button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="addrMainWrap">
                                <div class="billBreakdownWrapper">
                                    <h6 class="proHeading">Price Breakdown</h6>
                                    <div class="amtBrkdownWrapper">
                                        <div class="cillBrkCnt">
                                            <label>Bag Amount</label>
                                            <span class="d-flex" id="m_bag_amount">
                                                ₹{{ number_format($mBagAmount, 2) }}
                                            </span>
                                        </div>
                                        <div class="cillBrkCnt">
                                            <label>Bag Saving</label>
                                            <span class="d-flex" id="m_bag_saving">
                                                -₹{{ number_format($mBagSaving, 2) }}
                                            </span>
                                        </div>
                                        <hr>
                                        <div class="cillBrkCnt">
                                            <label class="d-flex align-items-center justify-content-start">
                                                Coupon & Voucher
                                                <img src="{{ asset('storage/app/public/images/discount_87.png') }}"
                                                    class="img-fluid" alt="voucher" />
                                            </label>
                                            <span class="d-flex" id="m_coupon_charge">
                                                ₹{{ number_format($mInitialCoupon, 2) }}
                                            </span>
                                        </div>
                                        <div class="cillBrkCnt">
                                            <label>Delivery Charge</label>
                                            <span class="d-flex" id="m_delivery_charge">
                                                ₹{{ number_format($mInitialDelivery, 2) }}
                                            </span>
                                        </div>
                                        <div class="cillBrkCnt">
                                            <label>Paid By Wallet</label>
                                            <span class="d-flex" id="m_wallet_amount">
                                                ₹{{ number_format($mInitialWallet, 2) }}
                                            </span>
                                        </div>
                                        <hr>
                                        <div class="cillBrkCnt">
                                            <label>Total Amount</label>
                                            <span class="d-flex" id="m_total_amount">
                                                ₹{{ number_format($mTotalAmount, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="addrMainWrap">
                                <h6 class="mb-1">Payment Mode</h6>
                                <div class="custom_radio">
                                    <div class="form-group position-relative border-bottom-0 selected">
                                        <div class="form-check pl-0">
                                            <input type="radio" name="payMode" id="payMode1" checked required>
                                            <label for="payMode1" class="mb-0">
                                                Razorpay Secure (UPI, Cards, Wallets, Netbanking)
                                            </label>
                                        </div>
                                    </div>

                                    @if (!$mHasFreeDelivery)
                                        <div class="form-group position-relative">
                                            <div class="form-check pl-0">
                                                <input type="radio" name="payMode" id="payMode2">
                                                <label for="payMode2" class="mb-0">Cash on delivery (COD)</label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btnPayNow" id="mobile-pay-btn">
                                    <span class="m-btn-text">Pay Now</span>
                                    <span class="m-btn-loader" style="display:none;">Processing...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const payNowBtn = document.getElementById('mobile-pay-btn');
                const btnText = payNowBtn?.querySelector('.m-btn-text');

                const razorpayRadio = document.getElementById('payMode1');
                const codRadio = document.getElementById('payMode2');

                if (!payNowBtn || !btnText) return;

                function updateButtonText() {
                    if (codRadio && codRadio.checked) {
                        btnText.textContent = "Order Now";
                    } else {
                        btnText.textContent = "Pay Now";
                    }
                }

                updateButtonText();

                if (razorpayRadio) {
                    razorpayRadio.addEventListener('change', updateButtonText);
                }

                if (codRadio) {
                    codRadio.addEventListener('change', updateButtonText);
                }
            });
        </script>


        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const shipAddMob1 = document.getElementById("shipAddMob1");
                const shipAddMob2 = document.getElementById("shipAddMob2");
                const mobSelWrapper = document.querySelector(".selAddWrappers.mob-inp");
                const mobDisplaySelAddr = document.querySelector(".displaySelAddr");

                if (shipAddMob1 && shipAddMob2 && mobSelWrapper) {
                    shipAddMob1.addEventListener("change", function() {
                        if (this.checked) {
                            mobSelWrapper.style.display = "none";
                            if (mobDisplaySelAddr) mobDisplaySelAddr.style.display = "none";
                        }
                    });

                    shipAddMob2.addEventListener("change", function() {
                        if (this.checked) {
                            mobSelWrapper.style.display = "block";
                        } else {
                            mobSelWrapper.style.display = "none";
                            if (mobDisplaySelAddr) mobDisplaySelAddr.style.display = "none";
                        }
                    });
                }

                const mobileBillingDropdown = document.getElementById("mobileBillingDropdown");
                if (mobileBillingDropdown && mobDisplaySelAddr) {
                    mobileBillingDropdown.addEventListener("change", function() {
                        const opt = this.options[this.selectedIndex];
                        if (!opt.value) {
                            mobDisplaySelAddr.style.display = "none";
                            mobDisplaySelAddr.innerHTML = "";
                            return;
                        }
                        const name = opt.getAttribute("data-name");
                        const phone = opt.getAttribute("data-phone");
                        const full = opt.getAttribute("data-full");
                        mobDisplaySelAddr.innerHTML = `
                        <p><strong>${name}</strong> - ${phone}</p>
                        <p>${full}</p>
                    `;
                        mobDisplaySelAddr.style.display = "block";
                    });
                }
            });

            document.addEventListener("DOMContentLoaded", function() {
                const gstCheckMob = document.getElementById('gstCheckMob');
                const gstFieldsMob = document.getElementById('gstFieldsMob');
                if (gstCheckMob && gstFieldsMob) {
                    gstCheckMob.addEventListener('change', function() {
                        gstFieldsMob.style.display = this.checked ? 'block' : 'none';
                    });
                }

                ['company_name_mobile', 'gst_no_mobile'].forEach(function(id) {
                    const inp = document.getElementById(id);
                    if (inp) {
                        inp.addEventListener('input', function() {
                            this.value = this.value.toUpperCase();
                        });
                    }
                });
            });

            document.addEventListener("DOMContentLoaded", function() {
                const buttons = document.querySelectorAll('.btnApplyMobile');
                if (!buttons.length) return;

                buttons.forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();

                        const cod = this.getAttribute('data-code');
                        if (!cod) return;

                        const shipping_fee_raw = document.getElementById('m_delivery_charge')
                            ?.textContent.trim() || '';
                        const shipping_fee = shipping_fee_raw.replace(/[^\d.]/g, '') || 0;

                        fetch('{{ route('applys') }}', {
                                method: 'POST',
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify({
                                    code: cod,
                                    shipping_fee: shipping_fee
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Mobile coupon response:', data);

                                if (typeof data === 'string') {
                                    alert(data); // e.g. "invalid_coupon"
                                    return;
                                }

                                if (data.coupon_discount === undefined || data.coupon_discount ===
                                    null) {
                                    alert(data.message || 'Invalid coupon');
                                    return;
                                }

                                const discountAmountNum = parseFloat(data.coupon_discount);
                                if (isNaN(discountAmountNum)) {
                                    alert('Invalid discount amount');
                                    return;
                                }

                                const discountAmount = discountAmountNum.toFixed(2);

                                const couponSpan = document.getElementById('m_coupon_charge');
                                if (couponSpan) {
                                    couponSpan.textContent = '-₹' + discountAmount;
                                }

                                const hiddenCouponCode = document.getElementById('m_coupon_code');
                                if (hiddenCouponCode) {
                                    hiddenCouponCode.value = data.coupon_code || cod;
                                }

                                const totalSpan = document.getElementById('m_total_amount');
                                if (totalSpan) {
                                    const totalText = totalSpan.textContent.replace(/[₹,]/g, '')
                                        .trim();
                                    const total = parseFloat(totalText);

                                    if (isNaN(total)) {
                                        console.error('Mobile total amount is NaN:', totalText);
                                        alert('Something went wrong in total calculation.');
                                        return;
                                    }

                                    const finalAmount = total - discountAmountNum;
                                    totalSpan.textContent = '₹' + finalAmount.toFixed(2);
                                }

                                btn.textContent = "Applied";
                                btn.disabled = true;
                                btn.classList.add("btn-success");
                            })
                            .catch(error => {
                                console.error('Mobile coupon error:', error);
                                alert('Something went wrong. Please try again.');
                            });
                    });
                });
            });


            document.addEventListener("DOMContentLoaded", function() {
                const walletBtn = document.querySelector('.btnCashMobile');
                if (!walletBtn) return;

                walletBtn.addEventListener('click', function() {
                    const walletText = document.getElementById('m_wallet_balance_display').textContent;
                    const wallet_amount = parseFloat(walletText.replace(/[^\d.]/g, '').trim()) || 0;

                    const totalSpan = document.getElementById('m_total_amount');
                    const totalText = totalSpan.textContent.replace(/[^\d.]/g, '').trim();
                    const total_amount = parseFloat(totalText || 0) || 0;

                    const used_wallet_amount = Math.min(wallet_amount, total_amount);

                    const walletUsedSpan = document.getElementById('m_wallet_amount');
                    if (walletUsedSpan) {
                        walletUsedSpan.textContent = "₹" + used_wallet_amount.toFixed(2);
                    }

                    const remaining_amount = total_amount - used_wallet_amount;
                    totalSpan.textContent = "₹" + remaining_amount.toFixed(2);

                    this.textContent = "Cash Used";
                    this.disabled = true;
                    this.classList.add("btn-success");
                });
            });

            document.addEventListener("DOMContentLoaded", function() {
                let lastShippingMobile = 0;

                const mobileCartGroups = @json($carts->pluck('cart_group_id')->values());

                function updateMobileShippingCost() {
                    const addressIdInput = document.getElementById('mobile_shipping_address_id');
                    const addressId = addressIdInput ? addressIdInput.value : '';
                    if (!addressId) return;

                    let iscod = 0;
                    const payMode2 = document.getElementById('payMode2');
                    if (payMode2 && payMode2.checked) {
                        iscod = 1;
                    }

                    const instantChecked = document.getElementById('instant_delivery_mobile') ?
                        document.getElementById('instant_delivery_mobile').checked : false;

                    fetch('{{ route('get_shipping_cost') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                cart_group_id: mobileCartGroups,
                                address_id: addressId,
                                iscod: iscod,
                                instant_delivery: instantChecked
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (!data || data.status !== "success") return;

                            const totalSpan = document.getElementById('m_total_amount');
                            let currentTotal = 0;
                            if (totalSpan) {
                                const txt = totalSpan.textContent.replace(/[^\d.]/g, '').trim();
                                currentTotal = parseFloat(txt || 0) || 0;
                            }

                            const deliverySpan = document.getElementById('m_delivery_charge');

                            if (data.free_delivery == 1) {
                                const originalCost = parseFloat(data.shipping_cost || 0) || 0;

                                if (deliverySpan) {
                                    deliverySpan.innerHTML =
                                        `<span style="color:#3f9339;font-weight:600;">Free</span>&nbsp;<span style="text-decoration:line-through;color:#999;">₹${originalCost.toFixed(2)}</span>`;
                                }

                                const newTotal = currentTotal - lastShippingMobile;
                                if (totalSpan) {
                                    totalSpan.textContent = "₹" + newTotal.toFixed(2);
                                }

                                lastShippingMobile = 0;
                                return;
                            }

                            const newShippingCost = parseFloat(data.shipping_cost || 0) || 0;

                            if (deliverySpan) {
                                deliverySpan.textContent = '₹' + newShippingCost.toFixed(2);
                            }

                            const finalAmount = currentTotal - lastShippingMobile + newShippingCost;
                            if (totalSpan) {
                                totalSpan.textContent = '₹' + finalAmount.toFixed(2);
                            }

                            lastShippingMobile = newShippingCost;
                        })
                        .catch(err => console.error('Mobile shipping error:', err));
                }

                const payModeRadios = document.querySelectorAll('input[name="payMode"]');
                payModeRadios.forEach(r => r.addEventListener('change', updateMobileShippingCost));
                const instantBox = document.getElementById('instant_delivery_mobile');
                if (instantBox) instantBox.addEventListener('change', updateMobileShippingCost);

                updateMobileShippingCost();
            });
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const mobilePayBtn = document.getElementById('mobile-pay-btn');
                if (!mobilePayBtn) return;

                const btnText = mobilePayBtn.querySelector('.m-btn-text');
                const btnLoader = mobilePayBtn.querySelector('.m-btn-loader');

                function showMobileLoader() {
                    if (btnText && btnLoader) {
                        btnText.style.display = 'none';
                        btnLoader.style.display = 'inline-block';
                    }
                    mobilePayBtn.disabled = true;
                }

                function hideMobileLoader() {
                    if (btnText && btnLoader) {
                        btnText.style.display = 'inline-block';
                        btnLoader.style.display = 'none';
                    }
                    mobilePayBtn.disabled = false;
                }

                function parseMoney(text) {
                    if (!text) return "0";
                    return text.replace(/[^\d.-]/g, '').trim();
                }

                mobilePayBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const shippingInput = document.getElementById('mobile_shipping_address_id');
                    const shipping_address_id = shippingInput ? shippingInput.value : '';
                    if (!shipping_address_id) {
                        alert("Please add a shipping address.");
                        return;
                    }

                    let billing_address_id = null;
                    const sameBill = document.getElementById('shipAddMob1');
                    const diffBill = document.getElementById('shipAddMob2');

                    if (sameBill && sameBill.checked) {
                        billing_address_id = shipping_address_id;
                    } else if (diffBill && diffBill.checked) {
                        const dd = document.getElementById('mobileBillingDropdown');
                        if (!dd || !dd.value) {
                            alert("Please select a billing address from dropdown.");
                            dd && dd.focus();
                            return;
                        }
                        billing_address_id = dd.value;
                    } else {
                        alert("Please select a billing address option.");
                        return;
                    }

                    let iscod = '0';
                    const payMode2 = document.getElementById('payMode2');
                    if (payMode2 && payMode2.checked) {
                        iscod = '1';
                    }

                    let delivery_charge = parseMoney(
                        document.getElementById('m_delivery_charge').innerText
                    );

                    let wallet_amount = parseMoney(
                        document.getElementById('m_wallet_amount').innerText
                    );

                    let coupon_charge = parseMoney(
                        document.getElementById('m_coupon_charge').innerText
                    );

                    let total_amount = parseMoney(
                        document.getElementById('m_total_amount').innerText
                    );

                    let coupon_code = document.getElementById('m_coupon_code') ?
                        document.getElementById('m_coupon_code').value :
                        '';

                    let company_name = document.getElementById('company_name_mobile') ?
                        document.getElementById('company_name_mobile').value :
                        '';

                    let gst_no = document.getElementById('gst_no_mobile') ?
                        document.getElementById('gst_no_mobile').value :
                        '';

                    let data = {};
                    data['_token'] = "{{ csrf_token() }}";
                    data['iscod'] = iscod;
                    data['shipping_fee'] = delivery_charge; // e.g. "49.00"
                    data['wallet_deduct_amount'] = wallet_amount; // e.g. "200.00"
                    data['coupon_discount'] = coupon_charge; // e.g. "-100.00"
                    data['amount'] = total_amount; // e.g. "5023.00"
                    data['shipping_address_id'] = shipping_address_id;
                    data['billing_address_id'] = billing_address_id;
                    data['coupon_code'] = coupon_code;
                    data['company_name'] = company_name;
                    data['gst_no'] = gst_no;

                    console.log('MOBILE -> create.razorpay.order payload:', data);

                    if (!total_amount || isNaN(parseFloat(total_amount))) {
                        alert("Invalid total amount. Please refresh the page and try again.");
                        return;
                    }

                    showMobileLoader();

                    fetch("{{ route('create.razorpay.order') }}", {
                            method: "POST",
                            credentials: "include",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify(data)
                        })
                        .then(res => res.json())
                        .then(res => {
                            console.log('MOBILE -> create.razorpay.order response:', res);

                            if (iscod === '1') {
                                if (res.order_ids && res.order_ids.length > 0) {
                                    window.location.href = res.redirect_url;
                                    return;
                                } else {
                                    alert(res.message || "Order creation failed");
                                    hideMobileLoader();
                                    return;
                                }
                            }

                            if (!res.online_payment || !res.online_payment.id) {
                                alert(res.message || "Failed to create Razorpay order.");
                                hideMobileLoader();
                                return;
                            }

                            let created_cart_group_ids = res.cart_group_ids;

                            var options = {
                                "key": "{{ config('razor.razor_key') }}",
                                "amount": res.amount * 100, // backend se ab 5023 aaega, to yaha 502300
                                "currency": "INR",
                                "name": "Interior Chowk",
                                "description": "Order Payment",
                                "order_id": res.online_payment.id,

                                "handler": function(response) {
                                    showMobileLoader();
                                    console.log('MOBILE -> Razorpay handler response:', response);

                                    fetch("{{ route('payment.razorpay') }}", {
                                            method: "POST",
                                            credentials: "include",
                                            headers: {
                                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                                "Content-Type": "application/json"
                                            },
                                            body: JSON.stringify({
                                                razorpay_payment_id: response
                                                    .razorpay_payment_id,
                                                razorpay_order_id: response
                                                    .razorpay_order_id,
                                                razorpay_signature: response
                                                    .razorpay_signature,
                                                cart_group_ids: created_cart_group_ids
                                            })
                                        })
                                        .then(res => res.json())
                                        .then(final => {
                                            console.log('MOBILE -> payment.razorpay response:',
                                                final);

                                            if (final && final.success) {
                                                window.location.href = final.redirect_url;
                                            } else {
                                                alert(final.message ||
                                                    "Payment verification failed on server."
                                                );
                                                hideMobileLoader();
                                            }
                                        })
                                        .catch(err => {
                                            console.error("MOBILE verify error:", err);
                                            alert(
                                                "Something went wrong while verifying payment."
                                            );
                                            hideMobileLoader();
                                        });
                                },
                                "modal": {
                                    "ondismiss": function() {
                                        console.log("MOBILE -> Payment popup closed.");
                                        hideMobileLoader();
                                        location.reload();
                                    }
                                }
                            };

                            var rzp1 = new Razorpay(options);
                            rzp1.open();
                        })
                        .catch(err => {
                            console.error("MOBILE -> create order error:", err);
                            alert("Something went wrong while creating order.");
                            hideMobileLoader();
                        });
                });
            });
        </script>


    </main>


    <script>
        (function($) {
            const $tabLink = $('#tabs-section .tab-link');
            const $tabBody = $('#tabs-section .tab-body');
            let timerOpacity;
            const init = () => {
                $tabLink.off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.clearTimeout(timerOpacity);
                    $tabLink.removeClass('active');
                    $tabBody.removeClass('active');
                    $tabBody.removeClass('active-content');
                    $(this).addClass('active');
                    $($(this).attr('href')).addClass('active');
                    $(".tab-head-m").hide();
                    $(".bg-texture").hide();
                    timerOpacity = setTimeout(() => {
                        $($(this).attr('href')).addClass('active-content');
                    }, 50);
                });
            };
            $(function() {
                init();
            });
        }(jQuery));
    </script>
    <script>
        jQuery(".profile-menu").click(function() {
            if (jQuery(".bg-texture").is(":hidden")) {
                jQuery(".tab-head-m").show();
                jQuery(".bg-texture").show();
                jQuery('.tab-head-m').toggle('slide', {
                    direction: 'left'
                }, 1000);
            } else {
                jQuery(".bg-texture").hide();
                jQuery('.tab-head-m').toggle('slide', {
                    direction: 'left'
                }, 1000);
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            var pincode = $('#pincode');
            pincode.on('input', function() {
                var pincodes = pincode.val();
                $.ajax({
                    url: '{{ route('pincode') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        pincode: pincodes
                    },
                    success: function(response) {
                        $('#city').val(response.city);
                        $('#state').val(response.state);
                        $('#country').val(response.country);
                    },
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#addressForm').on('submit', function(e) {
                e.preventDefault();

                $('#nameError, #phoneErrorRR, #addressError, #landmarkError, #pincodeError, #cityError, #stateError, #addressTypeError')
                    .text('');

                $.ajax({
                    url: '{{ route('save_address') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status === false) {
                            $.each(res.errors, function(key, value) {
                                switch (key) {
                                    case 'contact_person_name':
                                        $('#nameError').text(value[0]);
                                        break;
                                    case 'phone':
                                        $('#phoneErrorRR').text(value[0]);
                                        break;
                                    case 'address':
                                        $('#addressError').text(value[0]);
                                        break;
                                    case 'landmark':
                                        $('#landmarkError').text(value[0]);
                                        break;
                                    case 'zip':
                                        $('#pincodeError').text(value[0]);
                                        break;
                                    case 'city':
                                        $('#cityError').text(value[0]);
                                        break;
                                    case 'state':
                                        $('#stateError').text(value[0]);
                                        break;
                                    case 'address_type':
                                        $('#addressTypeError').text(value[0]);
                                        break;
                                }
                            });
                        } else {
                            // Success: show message and reset form
                            toastr.success(res.message);
                            $('#addressForm')[0].reset();
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                    }
                });
            });
        });

        $(document).ready(function() {
            $('.address').on('change', function() {

                const addressId = $(this).data('address-id');
                const isChecked = $(this).is(':checked') ? 1 : 0;
                console.log("Address ID:", addressId);
                console.log("Status:", isChecked);
                $.ajax({
                    url: '{{ route('select_address') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        address_id: addressId,
                        status: isChecked
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            localStorage.setItem('redirectTab', 'pills-chosAdd-tab');
                            location.reload(); // reload page
                        }
                        console.log("Success:", response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", error);
                    }
                });
            });
            const tabToActivate = localStorage.getItem('redirectTab');
            if (tabToActivate) {
                document.getElementById(tabToActivate)?.click();
                localStorage.removeItem('redirectTab'); // clean up
            }
        });

        $(document).ready(function() {
            $('#payWay2').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.btnPayNow').text('Order Now');
                }
            });
            $('#payWay1').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.btnPayNow').text('Pay Now');
                }
            })
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputs = document.querySelectorAll("#company_name, #gst_no");

            inputs.forEach(input => {
                input.addEventListener("input", function() {
                    this.value = this.value.toUpperCase();
                });
            });
        });
    </script>
    <script>
        document.getElementById('useLocationBtn').addEventListener('click', function() {
            if (!navigator.geolocation) return alert("Geolocation not supported.");

            navigator.geolocation.getCurrentPosition(
                pos => {
                    const {
                        latitude: lat,
                        longitude: lng
                    } = pos.coords;
                    const apiKey = 'AIzaSyBjSaMOYIsNMmMcqZI6iyd9bjREm0oBhjY';
                    const url =
                        `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`;

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status !== "OK" || !data.results.length) {
                                return alert('Failed to fetch location info.');
                            }

                            const addressComponents = data.results[0].address_components;
                            let streetParts = [];

                            addressComponents.forEach(function(comp) {
                                if (
                                    comp.types.includes('premise') ||
                                    comp.types.includes('route') ||
                                    comp.types.includes('sublocality') ||
                                    comp.types.includes('neighborhood') ||
                                    comp.types.includes('point_of_interest')
                                ) {
                                    streetParts.push(comp.long_name);
                                }
                            });

                            const shortAddress = streetParts.join(', ');

                            let city = '',
                                state = '',
                                country = '',
                                postalCode = '';

                            addressComponents.forEach(comp => {
                                if (comp.types.includes('locality')) city = comp.long_name;
                                if (comp.types.includes('administrative_area_level_1')) state = comp
                                    .long_name;
                                if (comp.types.includes('country')) country = comp.long_name;
                                if (comp.types.includes('postal_code')) postalCode = comp.long_name;
                            });


                            if (!postalCode) {
                                alert(
                                    'Could not detect a postal code from the first result. Try moving the pin slightly.'
                                );
                                return;
                            }

                            console.log({
                                city,
                                state,
                                country,
                                postalCode
                            });

                            document.getElementById('zip').value = postalCode || '';
                            document.getElementById('city').value = city || '';
                            document.getElementById('state').value = state || '';
                            document.getElementById('address').value = shortAddress || '';
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Failed to fetch location info.');
                        });
                },
                () => alert('Location access denied.')
            );
        });


        $(document).ready(function() {
            var pincode = $('#zip');

            pincode.on('input', function() {
                var pincodes = pincode.val();
                console.log(pincodes);

                $.ajax({
                    url: '{{ route('pincode') }}', // Replace with your server-side route
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel CSRF token for security
                    },
                    data: {
                        pincode: pincodes
                    },
                    success: function(response) {
                        console.log('Pincode response:', response);
                        $('#city').val(response.city);
                        $('#state').val(response.state);
                    },
                });
            });
        });
    </script>

    <!-- Modal -->
    <div class="modal chngeAddrModal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Address</h5>
                    <h5 class="modal-title d-none">Add a new address</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <button class="btn btnEdit">Add a new address<i
                                    class="fas fa-plus ml-1 mr-0 bg-white"></i></button>
                            <div class="selAddWrapper">
                                <div class="custom_radio">
                                    @if (!empty($mobileAddresses) && count($mobileAddresses) > 0)
                                        @foreach ($mobileAddresses as $key => $addres)
                                        {{-- @dd($addres); --}}
                                            <div class="form-group position-relative">
                                                <div class="form-check text-right">
                                                    <input type="radio" name="selectAddress"
                                                        id="selectAddress{{ $key + 1 }}" class="address"
                                                        value="{{ $addres->id ?? $key + 1 }}"
                                                        {{ $addres->is_selected == 1 ? 'checked' : '' }}>
                                                    <label for="selectAddress{{ $key + 1 }}" {{ $addres->is_selected == 1 ? 'checked' : '' }}></label>
                                                </div>

                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <p class="mt-1">{{ $addres->contact_person_name }} -
                                                        {{ $addres->phone }}
                                                    </p>
                                                </div>

                                                <p>{{ $addres->address }}, {{ $addres->city }} -
                                                    {{ $addres->zip }}, {{ $addres->state }}</p>

                                                <span class="addTag addTagCol1">
                                                    {{ $addres->address_type }}
                                                    <span>
                                                        @if ($addres->is_selected == 1)
                                                            default
                                                        @endif
                                                    </span>
                                                </span>
                                            </div>
                                        @endforeach
                                    @endif

                                </div>
                            </div>

                            <div class="chngeAddrFormWrapper p-3">
                                <form>
                                    <input type="text" name="contact_person_name" class="form-control"
                                        placeholder="Enter Name" required>
                                    <input type="text" name="phone" class="form-control" placeholder="Phone No."
                                        required>
                                    <textarea name="address" class="form-control" rows="10" placeholder="Address" required></textarea>
                                    <input type="text" name="landmark" class="form-control"
                                        placeholder="Landmark (optional)">
                                    <input type="text" name="zip" class="form-control" placeholder="Pincode"
                                        required>
                                    <input type="text" name="city" class="form-control" placeholder="City"
                                        required>
                                    <input type="text" name="state" class="form-control" placeholder="State"
                                        required>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="proHeading">Address Type</h6>
                                        <div class="custom_radio d-flex">
                                            <div class="form-group position-relative">
                                                <div class="form-check text-right">
                                                    <input type="radio" name="addressType" id="addressType1"
                                                        class="address" />
                                                    <label for="addressType1">Home</label>
                                                </div>
                                            </div>
                                            <div class="form-group position-relative">
                                                <div class="form-check text-right">
                                                    <input type="radio" name="addressType" id="addressType2"
                                                        class="address" />
                                                    <label for="addressType2">Work</label>
                                                </div>
                                            </div>
                                            <div class="form-group position-relative">
                                                <div class="form-check text-right">
                                                    <input type="radio" name="addressType" id="addressType3"
                                                        class="address" />
                                                    <label for="addressType3">Other</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btnEdit">Add</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .selAddWrapper {
            display: block;
        }

        .chngeAddrFormWrapper {
            display: none;
        }

        .chngeAddrFormWrapper.p-3 {
    overflow: scroll;
}
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selWrap = document.querySelector('.selAddWrapper');
            const formWrap = document.querySelector('.chngeAddrFormWrapper');
            const btnEdit = document.querySelector('.btnEdit');

            if (btnEdit && selWrap && formWrap) {
                // default
                selWrap.style.display = 'block';
                formWrap.style.display = 'none';

                btnEdit.addEventListener('click', function(e) {
                    e.preventDefault();
                    selWrap.style.display = 'none';
                    formWrap.style.display = 'block';
                });
            }
        });
    </script>





    </body>

    </html>
@endsection
