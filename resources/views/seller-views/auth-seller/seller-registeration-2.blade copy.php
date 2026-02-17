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
                            <form class="__shop-apply" action="{{ route('seller.auth.seller-registeration-2') }}"
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

<script>
    $(document).ready(function() {
        $("#shop_country").change(function() {
            $.ajax({
                url: "{{ route('shop.country') }}",
                type: 'POST',
                data: {
                    country_id: $(this).val(),
                    _token: '{{ csrf_token() }}'
                },
                dataType: "JSON",
                success: function(response) {
                    if (response.status1 == 1) {
                        $('#shop_state').html(response.state);
                    }
                    if (response.status1 == 0) {
                        $('#shop_state').html(response.state);
                        $('#shop_city').html(response.city);
                        $('#shop_pin').html(response.pincode);
                    }
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });
        });

        $("#shop_state").change(function() {
            $.ajax({
                url: "{{ route('shop.state') }}",
                type: 'POST',
                data: {
                    state_id: $(this).val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#shop_city').html(response);
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });
        });

        $("#shop_city").change(function() {
            $.ajax({
                url: "{{ route('shop.city') }}",
                type: 'POST',
                data: {
                    city_id: $(this).val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#shop_pin').html(response);
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });
        });

        $('#verifyBankBtn').click(function(e) {
            e.preventDefault();

            var bank_name = $('input[name="bank_name"]').val();
            var acc_no = $('input[name="acc_no"]').val();
            var ifsc = $('input[name="ifsc"]').val();

            if (bank_name && acc_no && ifsc) {
                $('#bank_status').text('Verifying...');

                $.ajax({
                    url: "{{ route('seller.verify.bank') }}",
                    type: "POST",
                    data: {
                        bank_name: bank_name,
                        acc_no: acc_no,
                        ifsc: ifsc,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#bank_status').html('✅ Bank Verified Successfully');
                            $('#apply').removeAttr('disabled');
                        } else {
                            $('#bank_status').html('❌ ' + response.message);
                            $('#apply').attr('disabled', 'disabled');
                        }
                    },
                    error: function() {
                        $('#bank_status').html('❌ Something went wrong! Try again.');
                        $('#apply').attr('disabled', 'disabled');
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Details',
                    text: 'Please fill Bank Name, Account No, and IFSC before verification.'
                });
            }
        });
    });
</script>
