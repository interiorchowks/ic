@extends('layouts.back-end.common_seller')

@section('content')
    @php
        $shop = \App\Model\Shop::where('seller_id', $id)->first();
    @endphp

    <section class="c-seller-login-w c-seller-registraion">
        <div class="c-seller-login-in">
            <div class="container">
                <div class="row align-items-end">

                    <div class="col-lg-5 col-md-12" data-aos="zoom-in" data-aos-duration="500">
                        <div class="c-seller-login-left c-password-left c-seller-registraion-heading">
                            <h2> Seller <span> Registration</span></h2>
                            <img src="{{ asset('public/asset/img/seller-register.png') }}" alt="">
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-12 seller-register-part-2" data-aos="zoom-in" data-aos-duration="500">
                        <div class="c-seller-registraion-step">
                            <ul>
                                <li class="done-form">
                                    <button type="button">
                                        <img src="{{ asset('public/asset/img/step-1.png') }}">
                                        <h3>Personal info.</h3>
                                    </button>
                                </li>
                                <li class="active">
                                    <button type="button">
                                        <img src="{{ asset('public/asset/img/step-2.png') }}">
                                        <h3>Business info </h3>
                                    </button>
                                </li>
                                <li>
                                    <button type="button">
                                        <img src="{{ asset('public/asset/img/step-3.png') }}">
                                        <h3>Upload docs</h3>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="c-seller-registraion-step-form">
                            {{-- <form class="__shop-apply" action="{{ route('seller.auth.seller-registeration-2') }}"
                                  id="form-id" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="seller_id" value="{{ $id }}">
                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input type="text" name="gst" value="{{ $shop?->gst ?? old('gst') }}" class="form-control" placeholder="GST Number" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input type="text" name="shop_name"
                                                   value="{{ $shop?->name ?? old('shop_name') }}"
                                                   class="form-control" placeholder="Business name" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group d-flex align-items-center">
                                            <div class="information-tag">
                                                <span class="ml-2" data-toggle="tooltip" data-placement="top"
                                                      title="{{ \App\CPU\translate('Where our delivery partner will pick up your product for shipping.') }}">
                                                    <img style="height: 16px; width: fit-content;" class="info-img"
                                                         src="{{ asset('/public/assets/back-end/img/info-circle.svg') }}"
                                                         alt="img">
                                                </span>
                                            </div>
                                            <input type="text" name="shop_address"
                                                   value="{{ $shop?->address ?? old('shop_address') }}"
                                                   class="form-control" placeholder="Registered Address" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4 d-none">
                                        <div class="form-group">
                                            <select class="form-control" id="shop_country" name="country" disabled required>
                                                <option>Country</option>
                                                @foreach ($data['countries'] as $country)
                                                    <option value="{{ $country->id }}" {{ $country->id == 1 ? 'selected' : '' }}>
                                                        {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <select class="form-control" id="shop_state" name="state" required>
                                                <option>State</option>
                                                @foreach ($data['states'] as $state)
                                                    <option value="{{ $state->id }}"
                                                        {{ $state->name == $shop?->state ? 'selected' : '' }}>
                                                        {{ $state->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <select class="form-control" id="shop_city" name="city" required>
                                                <option>City</option>
                                                @foreach ($data['cities'] as $cities)
                                                    <option value="{{ $cities->id }}"
                                                        {{ $cities->name == $shop?->city ? 'selected' : '' }}>
                                                        {{ $cities->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <select class="form-control" id="shop_pin" name="pincode" required>
                                                <option>Pincode</option>
                                                @foreach ($data['pincodes'] as $pincodes)
                                                    <option value="{{ $pincodes->id }}"
                                                        {{ $pincodes->code == $shop?->pincode ? 'selected' : '' }}>
                                                        {{ $pincodes->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="bank_name"
                                                   value="{{ $shop?->bank_name ?? old('bank_name') }}"
                                                   placeholder="Bank name" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="acc_no"
                                                   value="{{ $shop?->acc_no ?? old('acc_no') }}"
                                                   placeholder="Account No." required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="ifsc"
                                                   value="{{ $shop?->ifsc ?? old('ifsc') }}"
                                                   placeholder="IFSC" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-2">
                                        <button type="button" class="btn btn-info" id="verifyBankBtn">Verify Bank</button>
                                        <small id="bank_status" class="form-text text-muted"></small>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="c-btn-group text-center">
                                            <a href="{{ route('seller.auth.seller-registeration', ['id' => $id]) }}"
                                               class="c-btn-2">Back</a>
                                            <button type="submit" id="apply" class="c-btn-2 c-orange-btn">Next</button>
                                        </div>
                                    </div>
                                </div>
                            </form> --}}

                            <form class="__shop-apply" action="{{ route('seller.auth.seller-registeration-2') }}"
                                id="form-id" method="post" enctype="multipart/form-data">

                                @csrf
                                <input type="hidden" name="seller_id" value="{{ $id }}">

                                <div class="row">

                                    {{-- GST Number --}}
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input id="gst" type="text" name="gst"
                                                value="{{ $shop?->gst ?? old('gst') }}" class="form-control"
                                                placeholder="GST Number" required>
                                        </div>
                                    </div>

                                    {{-- Business / Shop Name --}}
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input id="shop_name" type="text" name="shop_name"
                                                value="{{ $shop?->name ?? old('shop_name') }}" class="form-control"
                                                placeholder="Business name" required>
                                        </div>
                                    </div>

                                    {{-- Shop Address --}}
                                    <div class="col-md-12">
                                        <div class="form-group d-flex align-items-center">
                                            <div class="information-tag">
                                                <span class="ml-2" data-toggle="tooltip" data-placement="top"
                                                    title="{{ \App\CPU\translate('Where our delivery partner will pick up your product for shipping.') }}">
                                                    <img style="height: 16px; width: fit-content;" class="info-img"
                                                        src="{{ asset('/public/assets/back-end/img/info-circle.svg') }}"
                                                        alt="info">
                                                </span>
                                            </div>
                                            <input id="shop_address" type="text" name="shop_address"
                                                value="{{ $shop?->address ?? old('shop_address') }}" class="form-control"
                                                placeholder="Registered Address" required>
                                        </div>
                                    </div>

                                    {{-- Country (hidden) --}}
                                    <div class="col-md-4 d-none">
                                        <div class="form-group">
                                            <select class="form-control" id="shop_country" name="country" disabled required>
                                                <option>Country</option>
                                                @foreach ($data['countries'] as $country)
                                                    <option value="{{ $country->id }}"
                                                        {{ $country->id == 1 ? 'selected' : '' }}>
                                                        {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- State --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <select class="form-control" id="shop_state" name="state" required>
                                                <option>State</option>
                                                @foreach ($data['states'] as $state)
                                                    <option value="{{ $state->id }}"
                                                        {{ $state->name == $shop?->state ? 'selected' : '' }}>
                                                        {{ $state->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- City --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <select class="form-control" id="shop_city" name="city" required>
                                                <option>City</option>
                                                @foreach ($data['cities'] as $city)
                                                    <option value="{{ $city->id }}"
                                                        {{ $city->name == $shop?->city ? 'selected' : '' }}>
                                                        {{ $city->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Pincode --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <select class="form-control" id="shop_pin" name="pincode" required>
                                                <option>Pincode</option>
                                                @foreach ($data['pincodes'] as $pincode)
                                                    <option value="{{ $pincode->id }}"
                                                        {{ $pincode->code == $shop?->pincode ? 'selected' : '' }}>
                                                        {{ $pincode->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Bank Name --}}
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input id="bank_name" type="text" class="form-control" name="bank_name"
                                                value="{{ $shop?->bank_name ?? old('bank_name') }}" placeholder="Bank name"
                                                required>
                                        </div>
                                    </div>

                                    {{-- Account Number --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input id="acc_no" type="text" class="form-control" name="acc_no"
                                                value="{{ $shop?->acc_no ?? old('acc_no') }}" placeholder="Account No."
                                                required>
                                        </div>
                                    </div>

                                    {{-- IFSC --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input id="ifsc" type="text" class="form-control" name="ifsc"
                                                value="{{ $shop?->ifsc ?? old('ifsc') }}" placeholder="IFSC" required>
                                        </div>
                                    </div>

                                    {{-- Verify Bank --}}
                                    <div class="col-md-12 mt-2">
                                        <button type="button" class="btn btn-info" id="verifyBankBtn">Verify
                                            Bank</button>
                                        <small id="bank_status" class="form-text text-muted"></small>
                                    </div>

                                    {{-- Form Actions --}}
                                    <div class="col-md-12">
                                        <div class="c-btn-group text-center">
                                            <a href="{{ route('seller.auth.seller-registeration', ['id' => $id]) }}"
                                                class="c-btn-2">Back</a>
                                            <button type="submit" id="apply"
                                                class="c-btn-2 c-orange-btn">Next</button>
                                        </div>
                                    </div>
                                </div>
                            </form>


                        </div> <!-- step-form -->
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<!-- JS Implementing Plugins -->
<script src="{{ asset('public/assets/back-end/js/vendor.min.js') }}"></script>

<style>
    .verified-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: green;
        font-size: 18px;
        display: none;
    }
    .form-group {
        position: relative;
    }
</style>

<script>
$(function(){

    function showTick($input){
        let $tick = $input.closest('.form-group').find('.verified-icon');
        if(!$tick.length){
            $tick = $('<span class="verified-icon">✅</span>');
            $input.closest('.form-group').append($tick);
        }
        $tick.show();
    }

    function hideTick($input){
        let $tick = $input.closest('.form-group').find('.verified-icon');
        $tick.hide();
    }

    const normalize = s => (s||'').toString().toLowerCase().replace(/\s+/g,'').replace(/[^a-z0-9]/g,'');

    function setSelectByText($select, text) {
        text = (text || '').trim();
        if (!text) return;

        let found = false;
        $select.find('option').each(function(){
            if (normalize($(this).text()) === normalize(text) || normalize($(this).val()) === normalize(text)) {
                $(this).prop('selected', true);
                found = true;
                return false;
            }
        });
        if (!found) {
            let newVal = 'new_' + Math.random().toString(36).substr(2,9);
            let $opt = $('<option>', { value: newVal, text: text, 'data-created': '1' });
            $select.append($opt);
            $opt.prop('selected', true);
        }
        $select.trigger('change');
    }

    const $gst = $('#gst');
    const $shopName = $('#shop_name');
    const $shopAddress = $('#shop_address');
    const $state = $('#shop_state');
    const $city = $('#shop_city');
    const $pin = $('#shop_pin');
    const $country = $('#shop_country');

    let gstCalling = false;
    function verifyGST(){
        if (gstCalling) return;
        const gstVal = $gst.val().trim();
        if (!gstVal) return;

        gstCalling = true;
        $.ajax({
            url: "{{ route('seller.shop.verifyGst') }}",
            type: 'POST',
            data: { gst: gstVal, _token: "{{ csrf_token() }}" },
            success: function(res){
                gstCalling = false;
                if (!res.success) {
                    alert(res.message || 'GST verification failed');
                    return;
                }
                let d = res.data;

                if (d.tradeNam) $shopName.val(d.tradeNam);
                if (d.pradr && d.pradr.addr){
                    let addr = d.pradr.addr;
                    let fullAddress = (addr.bno? addr.bno+', ':'')+(addr.st? addr.st+', ':'')+(addr.loc? addr.loc+', ':'')+(addr.dst? addr.dst+', ':'')+(addr.pncd? addr.pncd:'');
                    $shopAddress.val(fullAddress);
                    if (addr.stcd) setSelectByText($state, addr.stcd);
                    if (addr.dst) setSelectByText($city, addr.dst);
                    if (addr.pncd) setSelectByText($pin, addr.pncd);
                }
                if ($('#shop_country option[value="1"]').length) {
                    $country.val('1').trigger('change');
                }

                showTick($gst);
                showTick($shopName);
                showTick($shopAddress);
                showTick($state);
                showTick($city);
                showTick($pin);
            },
            error: function(xhr){
                gstCalling = false;
                alert('Something went wrong while verifying GST');
            }
        });
    }

    $gst.on('keydown', function(e){
        if (e.key === 'Enter') {
            e.preventDefault();
            verifyGST();
        }
    }).on('blur', verifyGST);


    // BANK VERIFICATION
    $('#verifyBankBtn').on('click', function(e){
        e.preventDefault();
        let bank_name = $('#bank_name').val().trim();
        let acc_no = $('#acc_no').val().trim();
        let ifsc = $('#ifsc').val().trim();

        if (!bank_name || !acc_no || !ifsc) {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Details',
                text: 'Please fill Bank Name, Account No, and IFSC before verification.'
            });
            return;
        }

        $('#bank_status').text('Verifying...');
        $.ajax({
            url: "{{ route('seller.verify.bank') }}",
            type: 'POST',
            data: {
                bank_name: bank_name,
                acc_no: acc_no,
                ifsc: ifsc,
                _token: "{{ csrf_token() }}"
            },
            success: function(res){
                if (res.status){
                    $('#bank_status').html('✅ Bank Verified Successfully');
                    $('#apply').removeAttr('disabled');
                    showTick($('#bank_name'));
                    showTick($('#acc_no'));
                    showTick($('#ifsc'));
                } else {
                    $('#bank_status').html('❌ ' + (res.message || 'Bank verification failed'));
                    $('#apply').attr('disabled', 'disabled');
                }
            },
            error: function(){
                $('#bank_status').html('❌ Something went wrong! Try again.');
                $('#apply').attr('disabled', 'disabled');
            }
        });
    });

});
</script>
