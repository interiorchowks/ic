<style>
    #imagePreview {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px;
    }

    .image-container {
        position: relative;
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 2px solid #ccc;
        border-radius: 8px;
        cursor: grab;
    }

    #dropArea {
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
        transition: background-color 0.3s;
    }

    #dropArea.dragover {
        background-color: #f0f0f0;
    }

    .image-container {
        display: inline-block;
        margin-right: 10px;
    }

    .image-preview {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .remove-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 5px;
        color: white;
        font-size: 16px;
        border-radius: 50%;
        padding: 5px;
        cursor: pointer;
        z-index: 10;
    }

    .image-container {
        position: relative;
        display: inline-block;
        margin: 10px;
    }

    .image-container img {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }

    .popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
    }

    .popup-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        width: 300px;
        text-align: center;
    }

    .popup-content h3 {
        margin-bottom: 20px;
    }

    .popup .close {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 25px;
        cursor: pointer;
    }

    .table td,
    .table th {
        padding: .1rem;
        vertical-align: 10px;
        border-top: .0625rem solid rgba(231, 234, 243, .7);
    }

    label {
        display: inline-block;
    }
</style>

@php
    use App\Model\Color;
    use Illuminate\Support\Facades\DB;

    $commission = DB::table('sellers')
        ->where('id', auth('seller')->id())
        ->first();
@endphp

@if (count($combinations[0]) > 0)
    <table class="table table-bordered physical_product_show">
        <thead>
            <tr>
                <td class="text-center">
                    <label for="" class="control-label">{{ \App\CPU\translate('Variant') }}</label>
                </td>
                <td class="text-center">
                    <label for="" class="control-label">{{ \App\CPU\translate('Variant SKU') }}</label>
                </td>
                <td class="text-center">
                    <label class="title-color">{{ \App\CPU\translate('Tax Included') }}</label>
                    <label class="badge badge-soft-info">{{ \App\CPU\translate('Percent') }} ( % )</label>
                </td>
                <td class="text-center">
                    <label for="" class="control-label">{{ \App\CPU\translate('Variant MRP') }}</label>
                </td>

                <td class="text-center">
                    <label class="title-color">{{ \App\CPU\translate('discount_type') }}</label>
                </td>
                <td class="text-center">
                    <label class="title-color">{{ \App\CPU\translate('Discount') }}</label>
                </td>
                <td class="text-center">
                    <label for="" class="control-label">
                        {{ \App\CPU\translate('Listed price after discount') }}<br>
                    </label>
                </td>
                @if (optional($commission)->commission_fee == 3)
                    <td class="text-center">
                        <label class="title-color">{{ \App\CPU\translate('Transfer Price') }}</label>
                    </td>
                @endif
                <td class="text-center">
                    <label class="title-color">{{ \App\CPU\translate('Commission fee') }}</label>
                </td>
                <td class="text-center">
                    <label class="title-color">{{ \App\CPU\translate('total') }}
                        {{ \App\CPU\translate('Stock') }}</label>
                </td>
            </tr>
        </thead>
        <tbody>
@endif

@foreach ($combinations as $key => $combo)
    @php
        $parts = [];
        $startIndex = 0;

        if ($colors_active && isset($combo[0])) {
            $colorCode = $combo[0]; // "#000000"
            $colorRow = Color::where('code', $colorCode)->first();

            $colorName = $colorRow ? trim($colorRow->name) : trim($colorCode);
            $parts[] = $colorName;
            $startIndex = 1;
        }

        for ($i = $startIndex; $i < count($combo); $i++) {
            $clean = str_replace([' ', ','], '', trim($combo[$i]));
            $parts[] = $clean;
        }

        $variantKey = implode('-', $parts);
        $data = $existingRows[$variantKey] ?? null;

        // SAFE image handling
        $images = [];
        if ($data && isset($data->image) && $data->image) {
            $decoded = json_decode($data->image, true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        }
    @endphp

    <tr data-row-id="{{ $key }}">
        <td>
            <input type="hidden" name="sizes[]" value="{{ $variantKey }}">
            <label class="control-label">{{ $variantKey }}</label>
        </td>
        <td>
            <input type="text" name="skues[]" value="{{ $data ? $data->sku : '' }}" class="form-control">
        </td>
        <td>
            <input type="text" min="0" step="0.01" placeholder="Tax" name="taxes[]"
                value="{{ $data ? $data->tax : '' }}" class="form-control tax" id="tax_{{ $key }}">
        </td>
        <td>
            <div class="d-flex align-items-center">
                <input type="text" id="tax_gst_{{ $key }}" name="tax_gst[]"
                    class="form-control ms-2 text-center" style="width: 40%; height: 10%;"
                    value="{{ $data ? $data->gst_percent : '' }}" />
                <span class="fw-bold"> plus </span>
                <input type="text" id="var_tax_{{ $key }}" name="var_tax[]"
                    class="form-control me-2 text-center" style="width: 40%; height:10%;"
                    value="{{ $data ? $data->discount_percent : '' }}" />
            </div>
            <input type="number" placeholder="Variant MRP" name="unit_prices[]" id="unit_price_{{ $key }}"
                value="{{ $data ? $data->variant_mrp : '' }}" class="form-control unit_price mt-2">
        </td>

        <td>
            @php
                $dt = $data && isset($data->discount_type) ? $data->discount_type : 'percent';
            @endphp
            <select class="form-control js-select2-custom discount_type" name="discount_types[]"
                id="discount_type_{{ $key }}">
                <option value="percent" {{ $dt == 'percent' ? 'selected' : '' }}>Percent</option>
                <option value="flat" {{ $dt == 'flat' ? 'selected' : '' }}>Flat</option>
            </select>
        </td>

        <td>
            <input type="text" placeholder="Discount" name="discounts[]" value="{{ $data ? $data->discount : '' }}"
                id="discount_{{ $key }}" class="form-control discount">
        </td>
        <td>
            <div class="d-flex align-items-center">
                <input type="text" id="selling_tax_{{ $key }}" name="selling_taxs[]"
                    class="form-control me-2 text-center" style="width: 40%; height:10%;"
                    value="{{ $data ? $data->listed_percent : '' }}" />
                <span class="fw-bold"> plus </span>
                <input type="text" id="tax1_gst_{{ $key }}" name="tax1_gst[]"
                    class="form-control me-2 text-center" style="width: 40%; height:10%;"
                    value="{{ $data ? $data->listed_gst_percent : '' }}" />
            </div>
            <input type="text" id="selling_price_{{ $key }}" name="selling_prices[]"
                class="form-control selling_price mt-2" value="{{ $data ? $data->listed_price : '' }}"
                placeholder="Selling Price">
        </td>
        @if (optional($commission)->commission_fee == 3)
            <td>
                <input type="number" name="transfer_price[]" value="{{ $data ? $data->transfer_price : '' }}"
                    id="transfer_price_{{ $key }}" class="form-control quant">
            </td>
        @endif

        <td>
            <input type="number" name="commission_fee[]" value="{{ $data ? $data->commission_fee : '' }}"
                id="commission_fee_{{ $key }}" class="form-control quant">
        </td>
        <td>
            <input type="number" id="quant_{{ $key }}" name="quant[]"
                value="{{ $data ? $data->quantity : '' }}" class="form-control quant">
        </td>
    </tr>

    <tr>
        <td colspan="5" style="text-align:center; padding-top: 20px;">Packaging dimensions* (in Cm)</td>
        <td>
            <label style="position: relative; left: 20px;">Length (in Cm)</label>
            <input type="text" name="lengths[]" value="{{ $data ? $data->length : '' }}" class="form-control">
        </td>
        <td>
            <label style="position: relative; left: 20px;">Breadth (in Cm)</label>
            <input type="text" name="breadths[]" value="{{ $data ? $data->breadth : '' }}" class="form-control">
        </td>
        <td>
            <label style="position: relative; left: 20px;">Height (in Cm)</label>
            <input type="text" name="heights[]" value="{{ $data ? $data->height : '' }}" class="form-control">
        </td>
        <td>
            <label style="position: relative; left: 20px;">Weight (in Kg)</label>
            <input type="text" name="weights[]" value="{{ $data ? $data->weight : '' }}" class="form-control">
        </td>
    </tr>

    <tr>
        <td colspan="2" style="text-align:center;">
            <p
                style="background-color: #ffffff; width: 50px; height: 50px; margin: 0 auto; border-radius: 50%; margin-top: 10px;">
            </p>
            <input type="text" name="color_names[]" placeholder="Write a color name"
                style="margin-top: 10px; text-align:center;" value="{{ $data ? $data->color_name : '' }}">
        </td>

        <td colspan="5">
            <div id="dropArea_{{ $key }}" class="drop-area"
                style="border: 2px dashed #ccc; padding: 10px;">
                <p style="margin:0;">
                    Upload Images* (Ratio 1:1)
                    <span data-toggle="tooltip" title="Checked image is thumbnail image">
                        <img src="/public/assets/back-end/img/info-circle.svg" alt="img">
                    </span>
                    <input type="file" id="imageInput_{{ $key }}" name="image_{{ $key }}[]"
                        class="image-input" data-key="{{ $key }}" multiple accept="image/*"
                        style="margin-top:10px;">
                </p>
            </div>

            <input type="hidden" id="thumbnail_input_{{ $key }}"
                name="thumbnail_image_{{ $key }}"
                value="{{ $data && isset($data->thumbnail_image) ? $data->thumbnail_image : '' }}">
            <input type="hidden" id="image_order_{{ $key }}" name="image_order_{{ $key }}"
                value="">

            <div id="imagePreview_{{ $key }}" class="image-preview"
                style="margin-top:10px; display:flex; flex-wrap:wrap;">
                @if (is_array($images))
                    @foreach ($images as $img)
                        @php
                            $imgClean = ltrim(trim($img), '/');

                            $thumbClean =
                                $data && isset($data->thumbnail_image) ? ltrim(trim($data->thumbnail_image), '/') : '';

                        @endphp

                        <div class="image-container" draggable="true" style="position:relative; margin:5px;">
                            <input type="hidden" name="old_image_{{ $key }}[]"
                                value="{{ $imgClean }}">

                            <img src="{{ env('CLOUDFLARE_R2_PUBLIC_URL') . '/' . $imgClean }}" width="100">

                            <input type="radio" class="image-radio" name="thumbnail_{{ $key }}"
                                value="{{ $imgClean }}" @if ($thumbClean && basename($imgClean) == basename($thumbClean)) checked @endif
                                style="position:absolute; top:5px; right:80px;">

                            <span class="remove-btn"
                                style="color:#000; position:absolute;width:20px; top:-4px; left:80px; cursor:pointer; font-weight:bold;">×</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </td>
    </tr>

    <div id="popup_{{ $key }}" class="popup" style="display:none;">
        <div class="popup-content">
            <span class="close" onclick="closePopup()">&times;</span>
            <h3>Upload your image</h3>
        </div>
    </div>
@endforeach

@if (count($combinations[0]) > 0)
    </tbody>
    </table>
@endif

{{-- JS part --}}

<script>
    document.addEventListener('input', function(event) {
        if (event.target.matches('.unit_price, .quant, .discount, .discount_type, .tax')) {
            var row = event.target.closest('tr');

            if (row) {
                var rowId = row.getAttribute('data-row-id');

                var unitPriceInput = document.getElementById(`unit_price_${rowId}`);
                var quantityInput = document.getElementById(`quant_${rowId}`);
                var discountInput = document.getElementById(`discount_${rowId}`);
                var discountTypeInput = document.getElementById(`discount_type_${rowId}`);
                var taxInput = document.getElementById(`tax_${rowId}`);
                var sellingPriceInput = document.getElementById(`selling_price_${rowId}`);
                var sellingTaxInput = document.getElementById(`selling_tax_${rowId}`);
                var gstTaxInput = document.getElementById(`tax1_gst_${rowId}`);
                var transferPriceInput = document.getElementById(`transfer_price_${rowId}`); // FIXED ID
                var commissionFeeInput = document.getElementById(`commission_fee_${rowId}`);
                var gstTaxInput1 = document.getElementById(`tax_gst_${rowId}`);
                var varTaxInput = document.getElementById(`var_tax_${rowId}`);

                var unitPrice = parseFloat(unitPriceInput?.value) || 0;
                var quantity = parseFloat(quantityInput?.value) || 1;
                var discount = parseFloat(discountInput?.value) || 0;
                var discountType = discountTypeInput?.value || "none";
                var tax = parseFloat(taxInput?.value) || 0;

                var sellingPrice = unitPrice;
                if (discountType === "percent") {
                    sellingPrice -= (sellingPrice * (discount / 100));
                } else if (discountType === "flat") {
                    sellingPrice -= discount;
                }
                sellingPrice = Math.max(sellingPrice, 0);

                var taxMultiplier = (tax + 100) / 100;
                var selling = unitPrice / taxMultiplier;
                var gst = (selling * tax) / 100;

                var sellingTax = sellingPrice / taxMultiplier;
                var gstTax = (sellingPrice * tax) / (tax + 100);

                if (sellingPriceInput) sellingPriceInput.value = sellingPrice.toFixed(2);
                if (sellingTaxInput) sellingTaxInput.value = sellingTax.toFixed(2);
                if (gstTaxInput) gstTaxInput.value = gstTax.toFixed(2);
                if (varTaxInput) varTaxInput.value = gst.toFixed(2);
                if (gstTaxInput1) gstTaxInput1.value = selling.toFixed(2);

                // Commission calculation
                if (transferPriceInput && commissionFeeInput) {
                    var transferPrice = parseFloat(transferPriceInput.value) || 0;
                    var commissionFee = transferPrice ? ((sellingTax - transferPrice) / sellingTax) * 100 : 0;
                    commissionFeeInput.value = commissionFee.toFixed(2);
                }
            }
        }
    });

    document.querySelectorAll('[id^="imageInput_"]').forEach((input) => {
        const key = input.dataset.key;
        const dropArea = document.getElementById(`dropArea_${key}`);
        const imagePreview = document.getElementById(`imagePreview_${key}`);
        const thumbnailInput = document.getElementById(`thumbnail_input_${key}`);
        const imageOrderInput = document.getElementById(`image_order_${key}`);

        let draggedElement = null;

        input.addEventListener('change', (e) => handleFiles(e.target.files));

        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.classList.add('dragover');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('dragover');
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        imagePreview.addEventListener('dragstart', (e) => {
            draggedElement = e.target.closest('.image-container');
            e.dataTransfer.effectAllowed = 'move';
        });

        imagePreview.addEventListener('dragover', (e) => {
            e.preventDefault();
            const target = e.target.closest('.image-container');
            if (!target || target === draggedElement) return;

            const bounding = target.getBoundingClientRect();
            const offset = bounding.y + bounding.height / 2;

            if (e.clientY - offset > 0) {
                target.after(draggedElement);
            } else {
                target.before(draggedElement);
            }
        });

        imagePreview.addEventListener('drop', (e) => {
            e.preventDefault();
            draggedElement = null;
            updateImageOrder();
        });

        function handleFiles(files) {
            Array.from(files).forEach((file) => {
                if (!file.type.startsWith('image/')) return;

                const reader = new FileReader();
                reader.onload = () => {
                    const imgContainer = document.createElement('div');
                    imgContainer.classList.add('image-container');
                    imgContainer.style = "display:inline-block; position:relative; margin:5px;";
                    imgContainer.setAttribute('draggable', true);

                    const img = document.createElement('img');
                    img.src = reader.result;
                    img.width = 100;
                    img.style = "display:block;";

                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `uploaded_images_${key}[]`;
                    hiddenInput.value = file.name;

                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.classList.add('image-radio');
                    radio.name = `thumbnail_${key}`;
                    radio.value = file.name;
                    radio.style = "position:absolute; top:5px; right:5px;";

                    radio.addEventListener('change', () => {
                        if (radio.checked) {
                            thumbnailInput.value = radio.value;
                        }
                    });

                    const removeBtn = document.createElement('span');
                    removeBtn.textContent = '×';
                    removeBtn.style =
                        "position:absolute; top:5px; left:5px; cursor:pointer; font-weight:bold;";
                    removeBtn.classList.add('remove-btn');
                    removeBtn.addEventListener('click', () => {
                        imgContainer.remove();
                        updateImageOrder();
                        if (thumbnailInput.value === file.name) {
                            thumbnailInput.value = '';
                        }
                    });

                    imgContainer.appendChild(removeBtn);
                    imgContainer.appendChild(img);
                    imgContainer.appendChild(radio);
                    imgContainer.appendChild(hiddenInput);
                    imagePreview.appendChild(imgContainer);

                    updateImageOrder();
                };
                reader.readAsDataURL(file);
            });
        }

        function updateImageOrder() {
            const imgs = imagePreview.querySelectorAll('.image-container');
            const order = Array.from(imgs).map(container => {
                const radio = container.querySelector('input.image-radio');
                return radio ? radio.value : null;
            }).filter(Boolean);
            imageOrderInput.value = JSON.stringify(order);
        }

        imagePreview.querySelectorAll('.remove-btn').forEach(removeBtn => {
            removeBtn.addEventListener('click', () => {
                const container = removeBtn.closest('.image-container');
                const radio = container.querySelector('input.image-radio');
                if (radio && thumbnailInput.value === radio.value) {
                    thumbnailInput.value = '';
                }
                container.remove();
                updateImageOrder();
            });
        });

        updateImageOrder();
    });
</script>
