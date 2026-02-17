@extends('layouts.back-end.common_seller_1')
@section('content')

    @push('head')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
        <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
        <link rel="stylesheet" href="{{ asset('public/website/assets/css/product.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <title>{{ $product->name }}</title>
        <meta name="description" content="{!! strip_tags($product->details) !!}">
        <!-- Open Graph -->

        @php
            $imageUrl = null;

            if (!empty($product->thumbnail_image)) {
                $base = rtrim(env('CLOUDFLARE_R2_PUBLIC_URL'), '/');
                $path = ltrim($product->thumbnail_image, '/');

                $imageUrl = $base . '/' . $path;
            }
        @endphp

        <meta property="og:title" content="{{ $product->name }}">
        <meta property="og:description" content="{!! strip_tags($product->details) !!}">
        <meta property="og:image" content="{{ $imageUrl }}">
        <meta property="og:type" content="product">
        <meta property="og:url" content="{{ url()->current() }}">
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Optional: Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $product->name }}">
        <meta name="twitter:description" content="{!! strip_tags($product->details) !!}">
        <meta name="twitter:image" content="{{ $imageUrl }}">

        {{-- @dd($product); --}}
        @if (isset($product))
            @php
                $reviews = App\Model\Review::where('product_id', $product->id)->get();
                $brand = App\Model\Brand::where('id', $product->brand_id)->first();
            @endphp
            <script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "{{ $product->name }}",
  "image": [
    "{{ secure_url('storage/images/' . $product->thumbnail_image) }}"
  ],
  "description": "{{ strip_tags($product->details) }}",
  "sku": "{{ $product->sku ?? $product->id }}",
  "brand": {
    "@type": "Brand",
    "name": "{{ $brand->name ?? 'InteriorChowk' }}"
  },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "INR",
    "price": "{{ number_format($product->listed_price, 2, '.', '') }}",
    "availability": "https://schema.org/{{ $product->current_stock > 0 ? 'InStock' : 'OutOfStock' }}",
    "url": "{{ url()->current() }}"
  },
  
}
</script>
@endif
<style>
    .video-banner.video-banner-bg.bg-image.text-right {
        margin-top: 30px;
    }

    img.profile-img {
        border-radius: 50% !important;
        width: 100px !important;
    }

    div#related-products-carousel .owl-item {
        padding: 5px;
    }

    a#btn-add-to-wishlist {
        background: none;
    }

    button.btn.btn-primary.bulk-button {
        border-radius: 5px;
        padding: 6px;
        width: max-content;
    }

    #product-zoom.zoomed {
        transform: scale(2);
        /* Adjust the zoom level as needed */
        transition: transform 0.3s ease-in-out;
        cursor: zoom-out;
    }

    /* Modal Background */
    .img-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        padding-top: 50px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.9);
    }

    /* Image inside Modal */
    .img-modal-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 90%;
        animation: zoomIn 0.3s ease;
    }

    /* Close Button */
    .img-modal-close {
        position: absolute;
        top: 30px;
        right: 50px;
        color: #fff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    button#cart {
        width: 100%;
    }

    /* Zoom Animation */
    @keyframes zoomIn {
        from {
            transform: scale(0.8);
            opacity: 0
        }

        to {
            transform: scale(1);
            opacity: 1
        }
    }

    .product-main-image img {
        height: 450px;
    }

    button.check {
        background: #2e6cb2;
        color: #fff;
        padding: 10px;
        border-radius: 7px;
        width: 88px;
        border: none;
        cursor: pointer;
    }

    .edt {
        margin-left: 10px;
        color: #555;
        font-weight: 600;
        white-space: nowrap;
    }

    form#edtForm {
        display: flex;
        gap: 12px;
    }

    .titless {
        display: none;
    }

    @media (max-width: 768px) {
        .titles {
            display: none;
        }

        .titless {
            display: block;
        }

        .video-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .video-container {
            padding-bottom: 75%;
        }

        #product-zoom-gallery {
            gap: 0px;
        }

        .galleryImages {
            width: 50px;
            height: 50px;

        }
    }
</style>
@endpush
<!-- "aggregateRating": {
"@type": "AggregateRating",
"ratingValue": "{{ number_format($reviews->avg('rating'), 1) ?? '0' }}",
"reviewCount": "{{ $reviews->count() }}"
},
"review": [
@foreach ($reviews as $review)
@php
    $ratinguser = App\Model\Customer::find($review->customer_id);
@endphp
{
"@type": "Review",
"author": {
    "@type": "Person",
    "name": "{{ $ratinguser->name ?? 'Anonymous' }}"
},
"reviewRating": {
    "@type": "Rating",
    "ratingValue": "{{ $review->rating }}"
},
"reviewBody": "{{ strip_tags($review->comment) }}"
}@if (!$loop->last)
,
@endif
@endforeach
] -->

<main class="main productRespoClass">
    <nav aria-label="breadcrumb" class="breadcrumb-nav border-0 mb-0 d-none d-md-block">
        <div class="container d-flex align-items-center">
            <ol class="breadcrumb" id="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ url('test_1') }}">Home</a></li>
                @if ($category_name_1 !== null && $category_name_1->slug !== null)
                    <li class="breadcrumb-item">
                        <a href="{{ url('products_1/' . $category_name_1->slug) }}">
                            {{ $category_name_1->name }}
                        </a>
                    </li>
                @endif

                @if ($category_name_2 !== null && $category_name_2->slug !== null)
                    <li class="breadcrumb-item">
                        <a href="{{ url('products_1/' . $category_name_2->slug) }}">
                            {{ $category_name_2->name }}
                        </a>
                    </li>
                @endif
                @if ($category_name_3 !== null && $category_name_3->slug !== null)
                    <li class="breadcrumb-item">
                        <a href="{{ url('products_1/' . $category_name_3->slug) }}">
                            {{ $category_name_3->name }}
                        </a>
                    </li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">
                    {{ \Illuminate\Support\Str::limit($product->name, 20) }}
                    ({{ $product->id }})
                </li>
                <input type="hidden" id="product_id" value="{{ $product->id }}">
            </ol>
        </div>
    </nav>
    <center>
        <h1 class="product-title titless d-none d-md-block">{{ $product->name }}</h1>
    </center>
    <div class="page-content">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="owl-carousel owl-simple carousel-equal-height trend-media d-md-none" id="single-product-carousel">
                        <div class="product product-7">
                            <figure class="product-media">
                                <a href="#">
                                    <img src="https://images.unsplash.com/photo-1770462988018-7d1c4b198ead?q=80&w=435&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="product-image" alt="pro-img">
                                </a>
                                <a style="bottom:85%" href="javascript:void(0);"
                                        class="btn-product-gallery share-button" onclick="toggleSharePopup(event)">
                                        <i class="fa fa-share-alt" aria-hidden="true"></i>
                                    </a>
                                    <div id="sharePopup" class="share-popup">
                                        <button onclick="copyLink()"><i class="fa-brands fa-copy"></i> Copy
                                            Link</button>
                                        <a href="mailto:?subject=Check this out&body=Check this product: {{ request()->fullUrl() }}"
                                            target="_blank"><i class="fa-brands fa-envelope"></i> Email</a>
                                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}"
                                            target="_blank"><i class="fa-brands fa-twitter"></i> Twitter</a>
                                        <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(request()->fullUrl()) }}&media={{ asset('path/to/product.jpg') }}"
                                            target="_blank"><i class="fa-brands fa-pinterest"></i> Pinterest</a>
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}"
                                            target="_blank"><i class="fa-brands fa-facebook"></i> Facebook</a>
                                    </div>
                                    @php
                                        $isWishlisted = DB::table('wishlists')
                                            ->where('customer_id', auth()->id())
                                            ->where('product_id', $product->id)
                                            ->exists();
                                    @endphp
                                    @auth
                                        <a style="bottom:72%;" href="javascript:void(0);" id="btn-add-to-wishlist"
                                            class="btn-product-gallery btn-toggle-wishlist" data-id="{{ $product->id }}">
                                            <i class="fa {{ $isWishlisted ? 'fa-heart text-danger' : 'fa-heart-o' }}"
                                                aria-hidden="true"></i>
                                        </a>
                                    @else
                                        <a style="bottom:350px;" href="javascript:void(0);" id="btn-add-to-wishlist"
                                            class="btn-product-gallery btn-toggle-wishlist" data-bs-toggle="modal"
                                            data-bs-target="#loginModal">
                                            <i class="fa {{ $isWishlisted ? 'fa-heart text-danger' : 'fa-heart-o' }}"
                                                aria-hidden="true"></i>
                                        </a>
                                    @endauth
                                    <a href="#" id="zoomed-imagess" class="btn-product-gallery">
                                        <i class="fa-solid fa-arrows-up-down-left-right"></i>
                                    </a>
                                    <div id="imgModal" class="img-modal">
                                        <span class="img-modal-close">&times;</span>
                                        <img class="img-modal-content" id="modalImg" style="width:60% !important;">
                                    </div>
                            </figure>
                        </div>
                        <div class="product product-7">
                            <figure class="product-media">
                                <a href="#">
                                    <img src="https://images.unsplash.com/photo-1770349781649-b7722e0de3fc?q=80&w=688&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="product-image" alt="pro-img">
                                </a>
                            </figure>
                        </div>
                        <div class="product product-7">
                            <figure class="product-media">
                                <a href="#">
                                    <img src="https://images.unsplash.com/photo-1590610123854-cff24ee40cf3?q=80&w=725&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="product-image" alt="pro-img">
                                </a>
                            </figure>
                        </div>
                        <div class="product product-7">
                            <figure class="product-media">
                                <a href="#">
                                    <img src="https://plus.unsplash.com/premium_photo-1678304223767-b9aa73c1e147?q=80&w=387&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="product-image" alt="pro-img">
                                </a>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="product-details-top">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="product-gallery product-gallery-vertical d-none d-md-block">
                            <div class="row">
                                <figure class="product-main-image">
                                    <img id="product-zoom" src="{{ asset('/storage/images/default.jpg') }}"
                                        data-zoom-image="{{ asset('/storage/images/default.jpg') }}"
                                        alt="product image">
                                    <a style="bottom:400px;" href="javascript:void(0);"
                                        class="btn-product-gallery share-button" onclick="toggleSharePopup(event)">
                                        <i class="fa fa-share-alt" aria-hidden="true"></i>
                                    </a>
                                    <div id="sharePopup" class="share-popup">
                                        <button onclick="copyLink()"><i class="fa-brands fa-copy"></i> Copy
                                            Link</button>
                                        <a href="mailto:?subject=Check this out&body=Check this product: {{ request()->fullUrl() }}"
                                            target="_blank"><i class="fa-brands fa-envelope"></i> Email</a>
                                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}"
                                            target="_blank"><i class="fa-brands fa-twitter"></i> Twitter</a>
                                        <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(request()->fullUrl()) }}&media={{ asset('path/to/product.jpg') }}"
                                            target="_blank"><i class="fa-brands fa-pinterest"></i> Pinterest</a>
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}"
                                            target="_blank"><i class="fa-brands fa-facebook"></i> Facebook</a>
                                    </div>
                                    @php
                                        $isWishlisted = DB::table('wishlists')
                                            ->where('customer_id', auth()->id())
                                            ->where('product_id', $product->id)
                                            ->exists();
                                    @endphp
                                    @auth
                                        <a style="bottom:350px;" href="javascript:void(0);" id="btn-add-to-wishlist"
                                            class="btn-product-gallery btn-toggle-wishlist" data-id="{{ $product->id }}">
                                            <i class="fa {{ $isWishlisted ? 'fa-heart text-danger' : 'fa-heart-o' }}"
                                                aria-hidden="true"></i>
                                        </a>
                                    @else
                                        <a style="bottom:350px;" href="javascript:void(0);" id="btn-add-to-wishlist"
                                            class="btn-product-gallery btn-toggle-wishlist" data-bs-toggle="modal"
                                            data-bs-target="#loginModal">
                                            <i class="fa {{ $isWishlisted ? 'fa-heart text-danger' : 'fa-heart-o' }}"
                                                aria-hidden="true"></i>
                                        </a>
                                    @endauth
                                    <a href="#" id="zoomed-imagess" class="btn-product-gallery">
                                        <i class="fa-solid fa-arrows-up-down-left-right"></i>
                                    </a>
                                    <div id="imgModal" class="img-modal">
                                        <span class="img-modal-close">&times;</span>
                                        <img class="img-modal-content" id="modalImg" style="width:60% !important;">
                                    </div>
                                </figure>
                                <div id="product-zoom-gallery" class="product-image-gallery"></div>
                            </div>
                        </div>

                        <div class="d-none d-md-block" id="accordion">
                            @foreach (['specification', 'key_features', 'technical_specification', 'other_details'] as $index => $field)
                                @if (!empty($category->$field) && !empty($category_values->$field))
                                    <div class="card">
                                        <div class="card-header" id="heading-{{ $field }}">
                                            <a class="card-link d-flex justify-content-between align-items-center {{ $index > 0 ? 'collapsed' : '' }}"
                                                data-toggle="collapse"
                                                href="#collapse-{{ $field }}-{{ $category->id }}"
                                                aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                                aria-controls="collapse-{{ $field }}-{{ $category->id }}">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}</strong>
                                                <i class="fa-solid fa-chevron-down toggle-icon"></i>
                                            </a>
                                        </div>
                                        <div id="collapse-{{ $field }}-{{ $category->id }}"
                                            class="collapse {{ $index == 0 ? 'show' : '' }}"
                                            data-parent="#accordion">
                                            <div class="card-body">

                                                <table class="table-bordered full-width-table">
                                                    @php
                                                        $keys = explode(',', $category->$field);
                                                        $values = json_decode($category_values->$field, true) ?? [];
                                                    @endphp
                                                    @foreach ($keys as $i => $key)
                                                        <tr>
                                                            <td>{{ trim($key) }}</td>
                                                            <td>{{ $values[$i] ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <table class="d-none d-md-block mt-4 bulk-purchase-table">
                            <tr>
                                <td>
                                    <img class="bulk-thumb"
                                        src="{{ rtrim(env('CLOUDFLARE_R2_PUBLIC_URL'), '/') . '/' . ltrim($product->thumbnail_image ?? 'default.jpg', '/') }}"
                                        alt="Product image">
                                </td>
                                <td>
                                    <div class="bulk-info">
                                        <h6 style="font-size:13px !important;">Looking to purchase this product in
                                            bulk? </h6>
                                        <p>
                                            <i class="fa fa-check-square"></i> Purchase item in bulk quantity<br>
                                            <i class="fa fa-check-square"></i> Get at best price for your business
                                        </p>
                                    </div>
                                </td>
                                <td>
                                    @auth
                                        <button type="button" class="btn btn-primary bulk-button" data-bs-toggle="modal"
                                            data-bs-target="#bulkPurchaseModal" style="margin-left: 15px;">
                                            CLICK TO RAISE REQUEST
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-default bulk-button" data-bs-toggle="modal"
                                            data-bs-target="#loginModal">
                                            CLICK TO RAISE REQUEST
                                        </button>
                                    @endauth
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="product-details product-details-mobile">
                            <h1 class="product-title  titles">{{ $product->name }}</h1>
                            <input type="hidden" id="id_s" value="{{ $product->id }}">
                            @if ($product->featured == 1)
                                <div class="featured-badge">
                                    <img src="{{ asset('public/website/assets/images/img-2.png') }}"
                                        alt="Featured" />
                                </div>
                            @endif
                            <h5 class="more-from-store">
                                <a href="{{ route('products_2') }}?seller_id={{ $product->user_id }}">
                                    More from Store
                                </a>
                            </h5>
                        </div>
                        <div class="prodCostWrapper">
                            <div class="row">
                                <div class="col-12 col-md-6 singlrProductDtl">
                                    <div  class="d-flex align-items-center justify-content-start">
                                        <div id="listed-price" class="product-price listed-price">
                                            ₹ 0
                                        </div>
                                        <div id="mrp-discount" class="product-price mrp-discount mt-1 mb-1">
                                        </div>
                                        <div class="size mt-2 mb-2"></div>
                                        @if ($product->featured == 1)
                                            <h6 class="limited-offer-text">Limited time Offer!</h6>
                                        @endif
                                    </div>
                                    <div class="ratingsWrapper">
                                        <div class="ratingCnt">
                                            <div class="ratingsVal">4.2<i class="fas fa-star ml-2"></i></div>
                                        </div>
                                        <span class="ratingsRev ml-3">90</span>
                                    </div>
                                </div>
                                @if ($product->quantity <= 0)
                                    <div class="col-12 col-md-6 product-action-buttons">
                                        <button
                                            style="background: #f08e64; color:#ffffff; border-radius: 4px; height: 30px;border: 1px;padding-left:30px; padding-right:30px;}"
                                            disabled>
                                            Out of Stock
                                        </button>
                                    </div>
                                @else
                                    <div class="col-12 col-md-6 product-action-buttons">
                                        @auth
                                            @if ($alreadyAdded)
                                            <button id="cart" class="view-cart cart">
                                                View Cart
                                            </button>
                                            @else
                                            <button id="cart" class="btn-add-to-cart cart">
                                                Add to cart
                                            </button>
                                            @endif
                                            <button class="btn-buy-now buy_now">
                                                Buy now
                                            </button>
                                        @else
                                            <button class="btn-add-to-cart" data-bs-toggle="modal"
                                                data-bs-target="#loginModal">
                                                Add to cart
                                            </button>
                                            <button class="btn-add-to-cart" data-bs-toggle="modal"
                                                data-bs-target="#loginModal">
                                                Buy now
                                            </button>
                                        @endauth
                                    </div>
                                @endif
                            </div>
                        </div>
                        {{-- <hr class="mt-0 mb-1" /> --}}
                        <div class="details-filter-row details-row-size mb-0">
                            <label>Size:</label>
                        </div>
                        <div id="variant-list" class="row mb-1"></div>
                        <div class="details-filter-row details-row-size mb-0">
                            <label>Color: </label>
                            <div class="product-nav product-nav-thumbs">
                                <span id="colors" class="selected-color"
                                    style="width:-webkit-fill-available"></span>
                            </div>
                        </div>
                        <div>
                            <ul class="color-product">
                                @foreach (json_decode($product->colors) as $color)
                                    <li class="color-item" data-color="{{ $color }}">
                                        <span style="background-color: {{ $color }};"></span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- delivery picode wrapper start-------------------------------------------------- --}}
                        <div class="delPincodeWrapper pincode-section mb-2">
                            <form id="edtForm" action="{{ route('get_edt') }}">
                                @csrf
                                <input type="text" placeholder="Enter pincode to check delivery date" name="edt_zip" maxlength="6"
                                    inputmode="numeric" pattern="[0-9]*" class="form-control" required>

                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <button type="submit" class="check">Submit</button>

                            </form>
                            <div class="edt"></div>
                        </div>
                        {{-- delivery picode wrapper ends---------------------------------------------------- --}}

                        <div class="addOfferWrapper">
                            <h6 class="mt-2"><b>Additional Offers</b></h6>
                            <div class="offers-box">
                                @foreach ($couponTitles as $coupon)
                                    <p><i class="fa fa-bandcamp offer-icon" aria-hidden="true"></i>{{ $coupon }}
                                    </p>
                                @endforeach
                            </div>
                        </div>

                        <div class="features-row">
                            <div class="feature-item">
                                <img src="{{ asset('public/website/new/assets/images/cash-on-delivery.png') }}"
                                    alt="Pay on Delivery">
                                <span class="feature-label">Pay on Delivery</span>
                            </div>
                            @if ($product->replacement_days > 0)
                                <div class="feature-item">
                                    <img src="{{ asset('public/website/new/assets/images/replacement.png') }}"
                                        alt="Replacement">
                                    <span class="feature-label">{{ $product->replacement_days }} Days
                                        Replacement</span>
                                </div>
                            @endif
                            @if ($product->Return_days > 0)
                                <div class="feature-item">
                                    <img src="{{ asset('public/website/new/assets/images/return.png') }}"
                                        alt="Return">
                                    <span class="feature-label">{{ $product->Return_days }} Days Return</span>
                                </div>
                            @endif
                            @if ($product->free_delivery == 1)
                                <div class="feature-item">
                                    <img src="{{ asset('public/website/new/assets/images/free-delivery.png') }}"
                                        alt="Free Delivery">
                                    <span class="feature-label">Free Delivery</span>
                                </div>
                            @endif
                            @php
                                $warehouse = \DB::table('warehouse')->find($product->add_warehouse);
                                $warehousePincode = $warehouse ? $warehouse->pincode : null;
                            @endphp
                            @if ($warehousePincode)
                                <div id="instantDelivery" class="feature-item d-none"
                                    data-warehouse-pincode="{{ $warehousePincode }}">
                                    <img src="{{ asset('public/website/new/assets/images/instant-delivery.png') }}"
                                        alt="Instant Delivery">
                                    <span class="feature-label">4‑Hour Instant Delivery</span>
                                </div>
                            @endif
                        </div>

                        <script>
                            document.getElementById('edtForm').addEventListener('submit', function(e) {
                                e.preventDefault();

                                let form = this;
                                let formData = new FormData(form);
                                let edtBox = document.querySelector('.edt');

                                edtBox.innerHTML = 'Checking...';

                                fetch(form.action, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                                        },
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {

                                        if (data.status === 'success' && data.edt !== null) {

                                            let today = new Date();
                                            today.setDate(today.getDate() + data.edt);

                                            let options = {
                                                day: '2-digit',
                                                month: 'short',
                                                year: 'numeric'
                                            };

                                            let deliveryDate = today.toLocaleDateString('en-GB', options);

                                            edtBox.innerHTML = `Delivered by ${deliveryDate}`;
                                        } else {
                                            edtBox.innerHTML = 'Delivery not available';
                                        }

                                    })
                                    .catch(error => {
                                        console.error(error);
                                        edtBox.innerHTML = 'Something went wrong';
                                    });
                            });
                        </script>

                        <img class="product-banner"
                            src="{{ rtrim(env('CLOUDFLARE_R2_PUBLIC_URL'), '/') . ($Product_banner_1->photo ?? 'default.jpg') }}"
                            alt="product desc">

                        <h6 class="lookPurchHead">Looking to purchase this product in bulk?</h6>
                        <div class="d-block d-md-none bulk-purchase-table">
                            <div class="d-flex align-items-start justify-content-start">
                                {{-- <img class="bulk-thumb"
                                src="{{ asset('storage/app/public/images/' . ($product->thumbnail_image ?? 'default.jpg')) }}"
                                alt="Product image"> --}}
                                <img class="bulk-thumb" src="https://images.unsplash.com/photo-1770349781649-b7722e0de3fc?q=80&w=688&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Product image">

                                <div class="bulk-info">
                                    <span><i class="fas fa-check-circle"></i>Purchase item in bulk quantity</span>
                                    <span><i class="fas fa-check-circle"></i>Get at best price for your business</span>
                                    @auth
                                    <button type="button" class="btn btn-primary bulk-button" data-bs-toggle="modal"
                                        data-bs-target="#bulkPurchaseModal">
                                        CLICK TO RAISE REQUEST
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-default bulk-button" data-bs-toggle="modal"
                                        data-bs-target="#loginModal">
                                        CLICK TO RAISE REQUEST
                                    </button>
                                    @endauth
                                </div>
                            </div>
                        </div>
                        <div class="product-details-tab mt-4">
                            <div class="tab-content description-box">
                                <div class="tab-pane fade show active" id="product-desc-tab" role="tabpanel">
                                    <div class="product-desc-content">
                                        <h3><b style="color:#000;">Descriptionsffsffsdf</b></h3>
                                        {!! $product->details !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-block d-md-none" id="accordion">
                            @foreach (['specification', 'key_features', 'technical_specification', 'other_details'] as $index => $field)
                                @if (!empty($category->$field) && !empty($category_values->$field))
                                    <div class="card">
                                        <div class="card-header" id="heading-{{ $field }}">
                                            <a class="card-link d-flex justify-content-between align-items-center {{ $index > 0 ? 'collapsed' : '' }}"
                                                data-toggle="collapse"
                                                href="#collapse-{{ $field }}-{{ $category->id }}"
                                                aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                                aria-controls="collapse-{{ $field }}-{{ $category->id }}">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}</strong>
                                                <i class="fa-solid fa-chevron-down toggle-icon"></i>
                                            </a>
                                        </div>
                                        <div id="collapse-{{ $field }}-{{ $category->id }}"
                                            class="collapse {{ $index == 0 ? 'show' : '' }}"
                                            data-parent="#accordion">
                                            <div class="card-body">
                                                <table class="table-bordered full-width-table">
                                                    @php
                                                        $keys = explode(',', $category->$field);
                                                        $values = json_decode($category_values->$field, true) ?? [];
                                                    @endphp
                                                    @foreach ($keys as $i => $key)
                                                        <tr>
                                                            <td>{{ trim($key) }}</td>
                                                            <td>{{ $values[$i] ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 col-md-6 d-block d-md-none video-container">
                        <iframe src="https://www.youtube.com/embed/ZMgzHN9XoPI" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
        @php
            $url = $product->video_url;
            $videoId = '';
            if (strpos($url, 'youtu.be/') !== false) {
                $videoId = substr(parse_url($url, PHP_URL_PATH), 1);
            } elseif (strpos($url, 'youtube.com/watch') !== false) {
                parse_str(parse_url($url, PHP_URL_QUERY), $params);
                $videoId = $params['v'] ?? '';
            }
        @endphp
        @if ($videoId)
            <div class="col-12 video-container">
                <iframe src="https://www.youtube.com/embed/{{ $videoId }}" frameborder="0"
                    allowfullscreen></iframe>
            </div>
        @endif
        @if (!empty($product->thumbnail))
            <div class="product-thumbnail-wrapper">
                <img src="{{ asset('/storage/pdfs/' . $product->thumbnail) }}" alt="Product image">
            </div>
        @endif
        <div class="container d-block d-md-none">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="ratingReviewWrapper">
                        <img src="https://images.unsplash.com/photo-1770462988018-7d1c4b198ead?q=80&w=435&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid ratRevMainImage" alt="pro-imggy">
                        <h4>Rating & Reviews</h4>
                        <div class="d-flex align-items-center">
                            <div class="ratingCnt">
                                <div class="ratingsVal">4.2<i class="fas fa-star ml-2"></i></div>
                            </div>
                            <div class="ml-2">
                                <a href="">515 ratings</a>
                                <a href="">92 Reviews</a>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="ratingCnt">
                                <div class="ratingsVal">4.2<i class="fas fa-star ml-2"></i></div>
                            </div>
                            <span class="ml-2 text-dark">5 Months ago</span>
                        </div>
                        <p>
                            The geometric metal shade creates a beautiful light pattern, giving the room a warm, artistic touch. The wooden base adds a nice earthy balance to the metallic texture, making it look stylish yet grounded. The glow against the deep blue wall enhances the ambiance — perfect for a relaxing or aesthetic corner setup.
                        </p>
                        <div class="customerShrImg">
                            <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                            <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                            <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                            <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                            <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                            <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                            <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                        </div>
                        <div class="intChwkSmallTxt">
                            <span>InteriorChwok customer</span>
                        </div>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btnRatModal" data-toggle="modal" data-target="#exampleModal">
                            View all 92 reviews <i class="fas fa-chevron-right ml-2"></i>
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="ratingClone">
                                            <h4>Rating & Reviews</h4>
                                            <div class="d-flex align-items-center">
                                                <div class="ratingCnt">
                                                    <div class="ratingsVal">4.2<i class="fas fa-star ml-2"></i></div>
                                                </div>
                                                <div class="ml-2">
                                                    <a href="">515 ratings</a>
                                                    <a href="">92 Reviews</a>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="ratingCnt">
                                                    <div class="ratingsVal">4.2<i class="fas fa-star ml-2"></i></div>
                                                </div>
                                                <span class="ml-2 text-dark">5 Months ago</span>
                                            </div>
                                            <p>
                                                The geometric metal shade creates a beautiful light pattern, giving the room a warm, artistic touch. The wooden base adds a nice earthy balance to the metallic texture, making it look stylish yet grounded. The glow against the deep blue wall enhances the ambiance — perfect for a relaxing or aesthetic corner setup.
                                            </p>
                                            <div class="customerShrImg">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                            </div>
                                            <div class="intChwkSmallTxt">
                                                <span>InteriorChwok customer</span>
                                            </div>
                                        </div>
                                        <div class="ratingClone">
                                            <h4>Rating & Reviews</h4>
                                            <div class="d-flex align-items-center">
                                                <div class="ratingCnt">
                                                    <div class="ratingsVal">4.2<i class="fas fa-star ml-2"></i></div>
                                                </div>
                                                <div class="ml-2">
                                                    <a href="">515 ratings</a>
                                                    <a href="">92 Reviews</a>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="ratingCnt">
                                                    <div class="ratingsVal">4.2<i class="fas fa-star ml-2"></i></div>
                                                </div>
                                                <span class="ml-2 text-dark">5 Months ago</span>
                                            </div>
                                            <p>
                                                The geometric metal shade creates a beautiful light pattern, giving the room a warm, artistic touch. The wooden base adds a nice earthy balance to the metallic texture, making it look stylish yet grounded. The glow against the deep blue wall enhances the ambiance — perfect for a relaxing or aesthetic corner setup.
                                            </p>
                                            <div class="customerShrImg">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                            </div>
                                            <div class="intChwkSmallTxt">
                                                <span>InteriorChwok customer</span>
                                            </div>
                                        </div>
                                        <div class="ratingClone">
                                            <h4>Rating & Reviews</h4>
                                            <div class="d-flex align-items-center">
                                                <div class="ratingCnt">
                                                    <div class="ratingsVal">4.2<i class="fas fa-star ml-2"></i></div>
                                                </div>
                                                <div class="ml-2">
                                                    <a href="">515 ratings</a>
                                                    <a href="">92 Reviews</a>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="ratingCnt">
                                                    <div class="ratingsVal">4.2<i class="fas fa-star ml-2"></i></div>
                                                </div>
                                                <span class="ml-2 text-dark">5 Months ago</span>
                                            </div>
                                            <p>
                                                The geometric metal shade creates a beautiful light pattern, giving the room a warm, artistic touch. The wooden base adds a nice earthy balance to the metallic texture, making it look stylish yet grounded. The glow against the deep blue wall enhances the ambiance — perfect for a relaxing or aesthetic corner setup.
                                            </p>
                                            <div class="customerShrImg">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                                <img src="https://plus.unsplash.com/premium_photo-1733682103251-841f373eb195?q=80&w=1112&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" alt="img1">
                                            </div>
                                            <div class="intChwkSmallTxt">
                                                <span>InteriorChwok customer</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if ($related_products->isNotEmpty())
            <div class="container featured mt-2 pb-2">
                <div class="product-details-top">
                    <div class="heading relproductsHead mb-1">
                        <div class="heading-left">
                            <h2 class="title">Related Products</h2>
                        </div>
                        <div class="heading-right">

                            <a href="{{ url('category/' . $product->category_slug) }}"
                                class="btn explore-more-btn d-flex align-items-center">
                                Explore More
                                <span class="circle-arrow" style="margin-left: 10px;">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="owl-carousel owl-simple" id="related-products-carousel">
                        @foreach ($related_products as $rp)
                            <div class="product product-7">
                                <figure class="product-media">
                                    @if ($rp->sku_discount_type === 'percent' && $rp->sku_discount > 0)
                                        <span class="product-label label-new">{{ round($rp->sku_discount) }}%
                                            off</span>
                                    @elseif($rp->sku_discount_type === 'flat' && $rp->sku_discount > 0)
                                        <span class="product-label label-new">₹{{ number_format($rp->sku_discount) }}
                                            off</span>
                                    @endif
                                    <a href="{{ url('product/' . $rp->slug) }}">
                                        <img src="{{ rtrim(env('CLOUDFLARE_R2_PUBLIC_URL'), '/') . ($rp->thumbnail_image ?? 'default.jpg') }}"
                                            alt="{{ $rp->name }}" class="product-image"
                                            style="max-width: 100% !important;">
                                    </a>
                                    @if ($rp->quantity <= 0)
                                        <div class="product-action">
                                            <button class="btn-product btn-add-to-cart" disabled
                                                style="pointer-events: none;"><span>out of stock</span></button>
                                        </div>
                                    @else
                                        <div class="product-action">
                                            @php
                                                $alreadyAddeds = \App\Model\Cart::where('user_id', auth()->id())
                                                    ->where('product_id', $rp->id)
                                                    ->exists();
                                            @endphp
                                            {{-- @dd($alreadyAddeds); --}}
                                            @if ($alreadyAddeds)
                                                <button id="cart" class="view-cart">
                                                    View Cart
                                                </button>
                                            @else
                                                <a href="javascript:void(0);" class="btn-product btn-add-to-cart"
                                                    data-id="{{ $rp->id }}"
                                                    data-variant="{{ $rp->variation ?? '' }}">
                                                    <span style="color:#fff !important;">Add to Cart</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                </figure>
                                <div class="product-body">
                                    <div class="product-cat">
                                        <a href="{{ url('category/' . $rp->category_id) }}">{{ $rp->category_id }}</a>
                                    </div>
                                    <h3 class="product-title">
                                        <a href="{{ url('product/' . $rp->slug) }}" class="clamp-1-line">{{ $rp->name }}</a>
                                    </h3>
                                    <div class="product-price">
                                        ₹{{ number_format($rp->listed_price) }}
                                        @if ($rp->variant_mrp > $rp->listed_price)
                                            <span class="price-cut">₹{{ number_format($rp->variant_mrp) }}</span>
                                        @endif
                                    </div>
                                    <div class="ratings-container">
                                        <div class="ratings">
                                            <div class="ratings-val" style="width: {{ rand(20, 100) }}%;"></div>
                                        </div>
                                        <span class="ratings-text">({{ rand(1, 10) }} Reviews)</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <div class="section-6 mt-0">
            @if ($rel_service_providers->isEmpty())
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="heading relproductsHead mb-1">
                                <div class="heading-left">
                                    <h2 class="title">Related Service Providers</h2>
                                </div>
                                <div class="heading-right">
                                    <button class="btn explore-more-btn d-flex align-items-center">
                                        Explore More
                                        <span class="circle-arrow ms-3" style="margin-left: 10px;">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="owl-carousel owl-simple" id="service-provider-carousel">
                        @foreach ($rel_service_providers as $provider)
                            <a href="{{ url('/interior-designers' . '/' . Str::slug($provider->name)) }}">
                                <div class="product product-7 p-0">
                                    <div class="card text-center border-0">
                                        <img src="{{ asset($provider->banner_image) }}"
                                            class="card-img-top top-banner" alt="Service Provider Banner">
                                        <div class="overlap-img">
                                            <img src="{{ asset('storage/app/public/service-provider/profile/' . $provider->image) }}"
                                                alt="Profile" class="profile-img">
                                        </div>

                                        <div class="card-body p-3">
                                            <h5 class="card-title">{{ $provider->name ?? 'Unknown' }}</h5>
                                            <p class="card-text text-dark text-ellipsis">
                                                {{ $provider->city ? str_replace(['[', ']', '"'], '', $provider->city) : 'Location not available' }}
                                            </p>
                                            <div class="ratings-container">
                                                <div class="ratings">
                                                    <div class="ratings-val" style="width: 20%;"></div>
                                                </div>
                                                <span class="ratings-text text-dark">( 2 Reviews )</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        
        <div class="banner-head mb-0">
            <img style="height: 250px; object-fit: cover; width: 100%;margin-top:20px;"
                src="{{ asset('public/website/new/assets/images/banners/multiple category banner.webp') }}"
                alt="" />
        </div>
    </div>
</main>

<div class="modal fade" id="bulkPurchaseModal" tabindex="-1" aria-labelledby="bulkPurchaseLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkPurchaseForm" method="POST">
                @csrf
                <input type="hidden" name="product_name" value="{{ $product->name }}">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="seller_id" value="{{ $product->seller_id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkPurchaseLabel">Bulk Purchase Enquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="border: none; background: none; cursor: pointer; font-size: 1.5rem;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" value="{{ $product->name }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="quantity_bulk" class="form-label">Quantity</label>
                        <input type="number" class="form-control" value="0" id="quantity_bulk"
                            name="quantity" required>
                    </div>

                    <div class="mb-3">
                        <label for="remarks_bulk" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks_bulk" name="remarks" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('zoomed-imagess').addEventListener('click', function(e) {
        e.preventDefault();
        const galleryImages = document.querySelectorAll('.galleryImages');
        const items = Array.from(galleryImages).map(img => ({
            src: img.getAttribute('src'), // Only src used
            type: 'image'
        }));
        Fancybox.show(items, {
            Thumbs: false, // You can set this to true if you want thumbnail navigation
            loop: true
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const productId = document.getElementById('product_id')?.value;
        if (productId) {
            console.log("Current Product ID:", productId);
        }

        const activeItem = document.querySelector('.breadcrumb-item.active');
        if (activeItem) {
            activeItem.style.fontStyle = 'italic';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        window.toggleSharePopup = function(event) {
            event.stopPropagation();
            const popup = document.getElementById('sharePopup');
            popup.style.display = popup.style.display === 'flex' ? 'none' : 'flex';
        };

        window.copyLink = function() {
            const dummy = document.createElement('input');
            dummy.value = window.location.href;
            document.body.appendChild(dummy);
            dummy.select();
            document.execCommand('copy');
            document.body.removeChild(dummy);
            // alert('Link copied to clipboard!');
        };

        document.addEventListener('click', function(e) {
            const popup = document.getElementById('sharePopup');
            const isClickInside = e.target.closest('.share-button') || e.target.closest('#sharePopup');
            if (!isClickInside) {
                popup.style.display = 'none';
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const productId = document.getElementById('id_s')?.value;
        if (productId) {
            console.log("Viewing Product ID:", productId);
        }
    });

    $(document).ready(function() {
        $(".cart").on("click", function(e) {
            e.preventDefault();
            const $btn = $(this);

            // if button is already converted, redirect to cart
            if ($btn.hasClass("view-cart")) {
                window.location.href = "{{ url('/cart') }}";
                return;
            }

            const product_id = $('#id_s').val();
            const variant = $('input[name="selected_size"]').val();

            $.ajax({
                url: "{{ route('cart.add_1') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    product: product_id,
                    variant: variant,
                    type: 0
                },
                success: function(response) {
                    toastr.success('Added to cart successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                    $btn.text("View Cart")
                        .addClass("view-cart")
                        .removeClass("btn-add-to-cart");
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        alert("You must be logged in to add to cart.");
                    } else {
                        alert("Something went wrong.");
                    }
                }
            });
        });

        $(".buy_now").on("click", function(e) {
            e.preventDefault();
            const product_id = $('#id_s').val();
            const variant = $('input[name="selected_size"]').val();

            $.ajax({
                url: "{{ route('cart.add_1') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    product: product_id,
                    variant: variant,
                    type: 1
                },
                success: function(response) {
                    console.log(response);
                    if (response.status == 1) {
                        const checkoutUrl = "{{ route('checkout') }}"; // base URL
                        const encodedPlanId = btoa(product_id.toString());
                        window.location.href = `${checkoutUrl}?p=${encodedPlanId}`;
                    }
                }
            });
        });
    });


    $(document).ready(function() {
        $(".btn-product").on("click", function(e) {
            e.preventDefault();
            const $btn = $(this);

            // if button is already converted, redirect to cart
            if ($btn.hasClass("view-cart")) {
                window.location.href = "{{ url('/cart') }}";
                return;
            }

            const product_id = $(this).data('id');
            const variant = $(this).data('variant');

            $.ajax({
                url: "{{ route('cart.add_1') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    product: product_id,
                    variant: variant,
                    type: 0
                },
                success: function(response) {
                    toastr.success('Added to cart successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                    $btn.text("View Cart")
                        .css({
                            "font-size": "1.1rem",
                            "padding": "0px 0px",
                            "border-radius": "4px",
                            "border": "1px solid",
                            "height": "35px",
                            "min-width": "135px"
                        })
                        .addClass("view-cart")
                        .removeClass("btn-add-to-cart");
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        alert("You must be logged in to add to cart.");
                    } else {
                        alert("Something went wrong.");
                    }
                }
            });
        });

        $(".buy_now").on("click", function(e) {
            e.preventDefault();
            const product_id = $('#id_s').val();
            const variant = $('input[name="selected_size"]').val();

            $.ajax({
                url: "{{ route('cart.add_1') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    product: product_id,
                    variant: variant,
                    type: 1
                },
                success: function(response) {
                    console.log(response);
                    if (response.status == 1) {
                        const checkoutUrl = "{{ route('checkout') }}"; // base URL
                        const encodedPlanId = btoa(product_id.toString());
                        window.location.href = `${checkoutUrl}?p=${encodedPlanId}`;
                    }
                }
            });
        });
    });


    document.addEventListener('DOMContentLoaded', () => {
        const listedPrice = parseInt({{ $product->listed_price ?? 0 }});
        const mrp = parseInt({{ $product->variant_mrp ?? 0 }});

        const discount = mrp > listedPrice ? Math.round(((mrp - listedPrice) / mrp) * 100) : 0;

        const priceEl = document.getElementById('listed-price');
        if (priceEl) {
            priceEl.textContent = `₹ ${listedPrice.toLocaleString()}`;
        }

        const discountEls = document.getElementById('mrp-discount');
        if (discountEl && discount > 0) {
            discountEl.innerHTML = `
        <span style="text-decoration: line-through;">₹ ${mrp.toLocaleString()}</span>
        <span style="color: green; font-weight: 500;"> &nbsp;(${discount}% OFF)</span>
    `;
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('btn')?.addEventListener('click', () => {
            // alert("Proceeding to buy now...");
        });

        document.getElementById('cart')?.addEventListener('click', () => {
            // alert("Item added to cart!");
        });
    });

    function initThumbnailClick() {
        document.querySelectorAll('.product-gallery-item').forEach(function(thumb) {
            thumb.addEventListener('mouseenter', function(e) {
                e.preventDefault();
                const imgSrc = this.dataset.image;
                const zoomImg = this.dataset['zoom-image'];
                const mainImg = document.getElementById('product-zoom');
                mainImg.src = imgSrc;
                mainImg.setAttribute('data-zoom-image', zoomImg);
                document.querySelectorAll('.product-gallery-item').forEach(el => el.classList.remove(
                    'active'));
                this.classList.add('active');
            });
        });
    }

    $(document).ready(function() {
        $("#btn-add-to-wishlist").on("click", function(e) {
            e.preventDefault();

            const slug = $(this).data('id');
            const $icon = $(this).find('i');

            $.ajax({
                url: "{{ route('store-wishlist-1') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    slug: slug
                },
                success: function(response) {
                    if (response.success) {
                        if (response.status === 'added') {
                            $icon.removeClass('fa-heart-o').addClass('fa-heart').css(
                                'color', 'red');
                            toastr.success('Added to wishlist successfully');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else if (response.status === 'removed') {
                            $icon.removeClass('fa-heart').addClass('fa-heart-o').css(
                                'color', '');
                            toastr.success('Removed from wishlist successfully');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }

                    }
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        //  alert("Please login to use wishlist.");
                    } else {
                        // alert("Something went wrong.");
                    }
                }
            });
        });
    });


    document.addEventListener("DOMContentLoaded", function() {
        const colorItems = document.querySelectorAll(".color-item");
        const selectedColor = '';

        colorItems.forEach(item => {
            item.addEventListener("click", function() {
                colorItems.forEach(ci => ci.classList.remove("active"));
                this.classList.add("active");
                const color = this.getAttribute("data-color");
                selectedColor.textContent = color;
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const instantDeliveryEl = document.getElementById('instantDelivery');
        if (!instantDeliveryEl) return;

        const warehousePin = instantDeliveryEl.getAttribute('data-warehouse-pincode');
        const userPin = localStorage.getItem('pincode');

        if (userPin && /^\d{6}$/.test(userPin) && userPin === warehousePin) {
            instantDeliveryEl.classList.remove('d-none');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const instantEl = document.getElementById('instantDelivery');
        if (!instantEl) return;

        const warehousePin = instantEl.dataset.warehousePincode;
        const userPin = localStorage.getItem('pincode');

        if (userPin && /^\d{6}$/.test(userPin) && userPin === warehousePin) {
            instantEl.classList.remove('d-none');
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        $('#related-products-carousel').owlCarousel({
            nav: false,
            dots: true,
            margin: 20,
            loop: false,
            responsive: {
                0: {
                    items: 1
                },
                480: {
                    items: 2
                },
                768: {
                    items: 3
                },
                992: {
                    items: 4
                },
                1200: {
                    items: 6,
                    nav: true,
                    dots: false
                }
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        $('#service-provider-carousel').owlCarousel({
            nav: false,
            dots: true,
            margin: 20,
            loop: false,
            responsive: {
                0: {
                    items: 1
                },
                480: {
                    items: 2
                },
                768: {
                    items: 3
                },
                992: {
                    items: 4
                },
                1200: {
                    items: 5,
                    nav: true,
                    dots: false
                }
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const title = document.querySelector('.video-banner-title');
        title.style.opacity = 0;
        setTimeout(() => {
            title.style.transition = 'opacity 1s ease-in-out';
            title.style.opacity = 1;
        }, 300);
    });

    document.addEventListener('DOMContentLoaded', function() {
        const colorItems = document.querySelectorAll('.color-item');

        // Helper function to join URLs safely
        function joinUrl(base, path) {
            if (!path) return '';
            path = path.replace(/^\/+/, ''); // remove leading slashes
            return base + '/' + path;
        }

        const basePath = "{{ rtrim(env('CLOUDFLARE_R2_PUBLIC_URL'), '/') }}";

        colorItems.forEach(function(item) {
            item.addEventListener('click', function() {
                // Highlight selected color
                document.querySelectorAll('.color-item').forEach(el => el.classList.remove(
                    'selected'));
                this.classList.add('selected');

                const selectedColor = this.getAttribute('data-color');
                const id = document.getElementById('id_s').value;

                fetch(`{{ route('variation') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            color: selectedColor,
                            id: id
                        })
                    })
                    .then(res => res.json())
                    .then(response => {
                        let html = '';
                        let colorNames = new Set();
                        let colorToId = {};

                        response.variant.forEach(item => {
                            const color = item.color_name;
                            colorNames.add(color);
                            if (!colorToId[color]) colorToId[color] = item.id;

                            // Parse images safely
                            let images = [];
                            try {
                                images = JSON.parse(item.image || '[]');
                            } catch (e) {
                                images = [];
                            }

                            html += `
                    <div class="col-3 col-md-2">
                        <div class="card variant-box"
                                data-listed_price="${item.listed_price}"
                                data-variant_mrp="${item.variant_mrp}"
                                data-discount_type="${item.discount_type || ''}"
                                data-discount="${item.discount || 0}"
                                data-sizes="${item.sizes}"
                                data-variation="${item.variation}"
                                data-thumbnail="${item.thumbnail_image || ''}"
                                data-images='${JSON.stringify(images)}'>
                            <div class="card-header text-center p-1 font-weight-bold mb-0" style="background-color: #FFECE2;">
                                ${item.sizes}
                            </div>
                            <div class="card-body p-2">
                                <p class="card-text">₹ ${Math.floor(Number(item.listed_price)).toLocaleString('en-IN')}</p>
                                ${item.discount != 0 ? `<p><span class="price-cut ml-0">₹ ${Number(item.variant_mrp).toLocaleString()}</span></p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
                        });

                        // Update color names display
                        document.getElementById('colors').innerText = Array.from(colorNames)
                            .join(', ');

                        // Update wishlist button data-id
                        const wishlistBtn = document.getElementById('btn-add-to-wishlist');
                        wishlistBtn.setAttribute('data-id', Object.values(colorToId).join(
                            ','));

                        // Render variants
                        document.getElementById('variant-list').innerHTML = html;

                        // Variant click handler
                        document.querySelectorAll('.variant-box').forEach(function(box) {
                            box.addEventListener('click', function() {
                                document.querySelectorAll('.variant-box')
                                    .forEach(el => el.classList.remove(
                                        'selected'));
                                this.classList.add('selected');

                                const listedPrice = this.dataset
                                    .listed_price;
                                const variantMrp = this.dataset.variant_mrp;
                                const discountType = this.dataset
                                    .discount_type;
                                const discount = this.dataset.discount;
                                const vari_ant = this.dataset.variation;
                                const thumbnail = this.dataset.thumbnail;

                                let images = [];
                                try {
                                    images = JSON.parse(this.dataset
                                        .images || '[]');
                                } catch (e) {
                                    images = [];
                                }

                                const mainImg = document.getElementById(
                                    'product-zoom');

                                // Set main image with fallback
                                if (images.length > 0) {
                                    mainImg.src = joinUrl(basePath, images[
                                        0]);
                                    mainImg.setAttribute('data-zoom-image',
                                        joinUrl(basePath, images[0]));
                                } else if (thumbnail) {
                                    mainImg.src = joinUrl(basePath,
                                        thumbnail);
                                    mainImg.setAttribute('data-zoom-image',
                                        joinUrl(basePath, thumbnail));
                                } else {
                                    mainImg.src =
                                        "{{ asset('/storage/images/default.jpg') }}";
                                    mainImg.setAttribute('data-zoom-image',
                                        "{{ asset('/storage/images/default.jpg') }}"
                                        );
                                }

                                // Build gallery
                                const gallery = document.getElementById(
                                    'product-zoom-gallery');
                                let galleryHTML = '';
                                images.forEach((img, index) => {
                                    galleryHTML += `
                            <a class="product-gallery-item ${index === 0 ? 'active' : ''}" href="#"
                                data-image="${joinUrl(basePath, img)}"
                                data-zoom-image="${joinUrl(basePath, img)}">
                                <img class="galleryImages" src="${joinUrl(basePath, img)}" alt="Product side" style="width: 100px; height: 100px;">
                            </a>`;
                                });
                                gallery.innerHTML = galleryHTML;
                                initThumbnailClick();

                                // Update price
                                document.querySelector('.product-price')
                                    .innerHTML =
                                    `₹ ${Math.floor(+listedPrice).toLocaleString('en-IN')}`;

                                // Update discount badge
                                let discountHTML = '';
                                if (discountType === 'percent' && discount >
                                    0) {
                                    discountHTML =
                                        `<span class="badge badge-danger" style="background-color: #E26526;">${Math.round(discount)}% off</span>`;
                                } else if (discountType === 'flat' &&
                                    discount > 0) {
                                    discountHTML =
                                        `<span class="badge badge-danger" style="background-color: #E26526;">₹${Number(discount).toLocaleString()} off</span>`;
                                }

                                if (discount > 0) {
                                    document.querySelector(
                                            '.product-price.mt-1.mb-1')
                                        .innerHTML = `
                                    <span class="price-cut">MRP ₹ ${Number(variantMrp).toLocaleString()}</span>
                                    ${discountHTML}
                                    `;
                                }

                                // Update selected size
                                document.querySelector('.size').innerHTML =
                                    `<input type="hidden" name="selected_size" value="${vari_ant}">`;
                            });
                        });

                        // Auto-click first variant
                        const firstVariant = document.querySelector('.variant-box');
                        if (firstVariant) firstVariant.click();

                    })
                    .catch(err => console.error("AJAX error:", err));
            });
        });

        // Auto-click first color
        if (colorItems.length > 0) colorItems[0].click();
    });
</script>
<script>
    $(document).ready(function() {
        $('#bulkPurchaseForm').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let url = "{{ route('product-bulk-purchase') }}";
            let msgBox = $('#bulkPurchaseMsg');

            $.ajax({
                type: 'POST',
                url: url,
                data: form.serialize(),
                success: function(response) {
                    toastr.success(response.message);
                    $('#bulkPurchaseModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    let html = '<div class="alert alert-danger"><ul>';
                    $.each(errors, function(key, value) {
                        html += '<li>' + value[0] + '</li>';
                    });
                    html += '</ul></div>';
                    msgBox.html(html);
                }
            });
        });
    });
</script>

@endsection
