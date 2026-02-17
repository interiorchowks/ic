<?php

namespace App\Http\Controllers\Seller;

use App\CPU\BackEndHelper;
use App\CPU\Convert;
use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\Brand;
use App\Model\BusinessSetting;
use App\Model\Category;
use App\Model\Color;
use App\Model\State;
use App\Model\DealOfTheDay;
use App\Model\FlashDealProduct;
use App\Model\Product;
use App\Model\Review;
use App\Model\Tag;
use Illuminate\Support\Facades\Cache;
use App\Model\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Model\Cart;
use Carbon\Carbon;
use function App\CPU\translate;
// use Intervention\Image\Facades\Image;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Bus;
use Intervention\Image\Drivers\Imagick\Driver as ImgDriver;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use Intervention\Image\ImageManagerStatic as Image;






class ProductController extends Controller
{

    public function record_sub_category(Request $request)
    {
        $category_id = $request->category_id;

        $found = DB::table('categories')->where('parent_id',$category_id)->where('position',1)->first();
        
        if($found){
             $data = DB::table('categories')->where('parent_id',$category_id)->where('position',1)->orderBy('name')->get();
          $select = ' <select class="form-control" name="sub_category_id" id="sub-category-id">';
           foreach ($data as $datas) {
        $select .= '<option value="' . $datas->id . '">' . $datas->name . '</option>';
        }
         $select .= '</select>';

        }else{
            $select =' <select class="form-control" name="sub_category_id" id="sub-category-id"><option value="0">No data</option></select>';
        }

       echo  $select;
    }

    public function add_new()
    {
        $category_id = session('category_id');
        $sub_category_id = session('sub_category_id');
        $sub_sub_category_id = session('sub_sub_category_id');
        $cat = Category::where(['position' => 0, 'home_status' => 1])->orderBy('name')->get();
        $br = Brand::active()->orderBY('name', 'ASC')->get();
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;
        $digital_product_setting = BusinessSetting::where('type', 'digital_product')->first()->value;
        $state_city = DB::table('state_city')->where('parent_id',null)->get();
        $warehouse = DB::table('warehouse')->where('seller_id', auth('seller')->id())->get();
        return view('seller-views.product.add-new', compact('cat', 'br', 'brand_setting', 'digital_product_setting','category_id', 'sub_category_id', 'sub_sub_category_id','warehouse','state_city'));
    }

    public function state_city(Request $request)
    {
        try {
            $state_id = $request->state;

            if (!$state_id) {
                return response()->json(['error' => 'State ID is missing'], 400);
            }

            $cities = DB::table('state_city')->where('parent_id', $state_id)->get();

            if ($cities->isEmpty()) {
                return response()->json(['error' => 'No cities found for this state'], 404);
            }

            $options = '';
            foreach ($cities as $city) {
                $options .= '<li><button onclick="addToSelection(this, \'city\')">' . $city->name . '</button></li>';
            }

            return response()->json(['options' => $options]);

        } catch (\Exception $e) {
            \Log::error('Error in state_city: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error'], 500);
        }
    }

    public function getCities($stateId)
    {
        $cities = DB::table('state_city')->where('parent_id', $stateId)->pluck('name');
            return response()->json($cities);
    }

    public function image_get()
    {
        $image =  DB::table('sku_products')->get();
        return $image;
    }

    public function add_search_new()
    {
        $cat = Category::where(['position' => 0, 'home_status' => 1])->orderBy('name')->get();
        $br = Brand::active()->orderBY('name', 'ASC')->get();
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;
        $digital_product_setting = BusinessSetting::where('type', 'digital_product')->first()->value;
        return view('seller-views.product.add-search-new', compact('cat', 'br', 'brand_setting', 'digital_product_setting'));
    }
    
    public function searchCategories_post(Request $request)
    {
        session([
        'category_id' => $request->category_id,
        'sub_category_id' => $request->sub_category_id,
        'sub_sub_category_id' => $request->sub_sub_category_id,
        ]);
     
        return redirect()->route('seller.product.add-new');
    }

    public function searchCategories(Request $request)
    {
        $query = $request->input('query');
        $suggestions = Category::where('name', 'like', "%$query%")
                            ->limit(5)  // Limiting the number of suggestions
                            ->get(['id', 'name']);

         $cat  = Category::where('name', 'like', "%$query%") ->first();

            if(!$cat){
            $html = '<div>
                <form id="productForm">
                    <label>Add Product* </label>
                    <input type="text" class="form-control" name="category" id="cate_name" placeholder="Type product name">
                    
                    <label class="mt-1">HSN Code* </label>
                    <input class="form-control" type="text" name="hsn" id="hsn_code" placeholder="">
                    
                    <input type="button" class="btn btn--primary mt-2" value="Save" id="saveBtn">
                </form>
            </div>';
            return response()->json([
                'html'=>$html,
            ]);
            }else{
            if($cat->parent_id==0){
                $category = $cat;
                $subcategory = "";
                $subsubcategory = "";
            }elseif($cat->parent_id !=0 && $cat->sub_parent_id==0){
                    $category = $cat->parent;
                    $subcategory = $cat;
                    if ($subcategory) {
                        $sub_sub_category = Category::where('sub_parent_id', $cat->id)->get();
                        $options = '';
                        foreach ($sub_sub_category as $sub_sub) {
                            $options .= '<option value="' . $sub_sub->id . '">' . $sub_sub->name . '</option>';
                        }
                        $subsubcategory = ['options' => $options]; // HTML options array
                    }

            }else{
                if($cat){
                    $sub = Category::where('id',$cat->sub_parent_id)->first();
                }
                $category = $cat->parent;
                $subcategory = $sub;
                $subsubcategory = $cat;
            }

            return response()->json([
                'category' => $category,  // Fetching parent category
                'subcategory' => $subcategory,  // Fetching subcategories
                'subsubcategory' => $subsubcategory,
                'suggestions' => $suggestions // Returning the subsubcategory
            ]);
        }
    }

    public function addCategories()
    {
        $cat = Category::where(['position' => 0, 'home_status' => 1])->orderBy('name')->get();
        $br = Brand::active()->orderBY('name', 'ASC')->get();
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;
        $digital_product_setting = BusinessSetting::where('type', 'digital_product')->first()->value;
        return view('seller-views.product.add-seller-category', compact('cat', 'br', 'brand_setting', 'digital_product_setting'));
    }

    public function status_update(Request $request)
    {
        if ($request['status'] == 0) {
            Product::where(['id' => $request['id'], 'added_by' => 'seller', 'user_id' => \auth('seller')->id()])->update([
                'status' => $request['status'],
            ]);
            return response()->json([
                'success' => 1,
            ], 200);
        } elseif ($request['status'] == 1) {
            if (Product::find($request['id'])->request_status == 1) {
                Product::where(['id' => $request['id']])->update([
                    'status' => $request['status'],
                ]);
                return response()->json([
                    'success' => 1,
                ], 200);
            } else {
                return response()->json([
                    'success' => 0,
                ], 200);
            }
        }
    }

    public function featured_status(Request $request)
    {
        if ($request->ajax()) {
            $product = Product::find($request->id);
            $product->featured_status = $request->status;
            $product->save();
            $data = $request->status;
            return response()->json($data);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        ], [
        ]);
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;
       
        if ($request['discount_type'] == 'percent') {
            $dis = ($request['unit_price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if (is_null($request->name[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'name', 'Name field is required!'
                );
            });
        }
      
        $product = new Product();
        $product->user_id = auth('seller')->id();
        $select_location = $request->cities;
        $select_location = array_map('trim', explode(',', $select_location));
        $product->cities = json_encode($select_location);
        $product->added_by = "seller";
        $product->HSN_code = $request->HSN_code;
        $product->Return_days     = $request->Return_days;
        $product->Replacement_days     = $request->Replacement_days;
        $product->name = $request->name[array_search('en', $request->lang)];
        $product->slug = Str::slug($request->name[array_search('en', $request->lang)], '-');
        $product->add_warehouse = $request->warehouse;
          
        $product_images = [];
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            foreach ($request->colors as $color) {
                $color_ = str_replace('#','',$color);
                $img = 'color_image_'.$color_;
                if ($request->file($img)) {
                    $file = $request->file($img);
                    $ext = strtolower($file->getClientOriginalExtension());

                    $folder = 'products';
                    $fileName = uniqid() . '.webp';

                    if ($ext === 'webp') {
                        $imageContent = file_get_contents($file->getRealPath());
                    } else {
                        $imageContent = (string) Image::make($file->getRealPath())->encode('webp', 90);
                    }

                    $path = $folder . '/' . $fileName;
                    Storage::disk('r2')->put($path, $imageContent);

                    $image_name = '/' . $path;

                    $product_images[] = $image_name;
                    $color_image_serial[] = [
                        'color' => $color_,
                        'image_name' => $image_name,
                    ];
                }
            }
        }

        $category = [];

        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }
      
        $request->product_type = "physical";
        $request->product_type == 'digital';
        $product->category_ids          = json_encode($category);
        $product->category_id           = $request->category_id;
        $product->sub_category_id       = $request->sub_category_id;
        $product->sub_sub_category_id   = $request->sub_sub_category_id;
        $product->brand_id              = $request->brand_id;
        $product->unit                  = $request->product_type == 'physical' ? $request->unit : null;
        $product->digital_product_type  = $request->product_type == 'digital' ? $request->digital_product_type : null;
        $product->product_type          = $request->product_type;
        $product->code                  = $request->code;
        $product->minimum_order_qty     = $request->minimum_order_qty;
        $product->details               = $request->description[array_search('en', $request->lang)];
        
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $product->colors = $request->product_type == 'physical' ? json_encode($request->colors) : json_encode([]);
        } else {
            $colors = [];
            $product->colors = $request->product_type == 'physical' ? json_encode($colors) : json_encode([]);
        }
        $choice_options = [];
        if ($request->has('choice')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['name'] = 'choice_' . $no;
                $item['title'] = $request->choice[$key];
                $item['options'] = array_map('trim', explode(',', implode('|', $request[$str])));
                array_push($choice_options, $item);
            }
        }
        $product->choice_options = $request->product_type == 'physical' ? json_encode($choice_options) : json_encode([]);
        
        $options = [];
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        }
        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('|', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }

        $combinations = Helpers::combinations($options);

        $variations = [];
        $stock_count = 0;
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $k => $item) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
                            $color_name = Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = Convert::usd(abs($request['price_' . str_replace('.', '_', $str)]));
                $item['sku'] = $request['sku_' . str_replace('.', '_', $str)];
                $item['qty'] = abs($request['qty_' . str_replace('.', '_', $str)]);
                array_push($variations, $item);
                $stock_count += $item['qty'];
            }

        } else {
            $stock_count = (integer)$request['current_stock'];
        }

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }
       
        $product->variation      = $request->product_type == 'physical' ? json_encode($variations) : json_encode([]);
        $product->unit_price     = Convert::usd($request->unit_price);
        $product->purchase_price = Convert::usd($request->purchase_price ?? 0);
        $product->discount       = $request->discount_type == 'flat' ? Convert::usd($request->discount) : $request->discount;
        $product->discount_type  = $request->discount_type;
        $product->attributes     = $request->product_type == 'physical' ? json_encode($request->choice_attributes) : json_encode([]);
        $product->current_stock  = $request->product_type == 'physical' ? abs($stock_count) : 0;
        $product->video_provider = 'youtube';
        $product->video_url      = $request->video_link;
        $product->request_status = Helpers::get_business_settings('new_product_approval')==1?0:1;
        $product->status         = 0;
        $product->shipping_cost  = $request->product_type == 'physical' ? Convert::usd($request->shipping_cost) : 0;
        $product->multiply_qty   = ($request->product_type == 'physical') ? ($request->multiplyQTY=='on'?1:0) : 0;
        $product->free_delivery      = $request->free_delivery=='on'?1:0;
        $product->available_instant_delivery      = $request->available_instant_delivery=='on'?1:0;
        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfPath = 'products' .  '/' . uniqid() . '.pdf';

            Storage::disk('r2')->put($pdfPath, file_get_contents($pdf));

            $product->thumbnail = '/' . $pdfPath;
        }

        $product->length    = $request->length ?? 0;
        $product->breadth   = $request->breadth ?? 0;
        $product->height    = $request->height ?? 0;
        $product->weight    = $request->weight ?? 0;
   
        if ($request->ajax()) {
            return response()->json([], 200);
        } else 
        {
          
            $product->save();
            
            $product_id = $product->id;
    
            foreach ($request->sizes as $index => $skus) {
                $sizeParts = explode('-', $skus);
                $size = end($sizeParts);

                $imageNames = [];
                $thumbnailImage = $request->input('thumbnail_image_' . $index);
                $imageOrderJson = $request->input('image_order_' . $index);
                $imageOrder = $imageOrderJson ? json_decode($imageOrderJson, true) : [];

                if ($request->hasFile('image_' . $index)) {

                    $files = $request->file('image_' . $index);
                    $storedFiles = [];

                    foreach ($files as $file) {

                        if ($file->isValid()) {

                            $ext = strtolower($file->getClientOriginalExtension());

                            $folder = 'products';
                            $safeName = uniqid() . '.webp';

                            if ($ext === 'webp') {
                                $imageContent = file_get_contents($file->getRealPath());
                            } else {
                                $imageContent = (string) Image::make($file->getRealPath())->encode('webp', 90);
                            }

                            $r2Path = $folder . '/' . $safeName;

                            Storage::disk('r2')->put($r2Path, $imageContent);

                            $storedFiles[$file->getClientOriginalName()] = '/' . $r2Path;

                            if ($thumbnailImage && $file->getClientOriginalName() == $thumbnailImage) {
                                $thumbnailImage = '/' . $r2Path;
                            }
                        }
                    }

                    foreach ($imageOrder as $originalName) {
                        if (isset($storedFiles[$originalName])) {
                            $imageNames[] = $storedFiles[$originalName];
                        }
                    }
                }

                $imageJson = !empty($imageNames) ? json_encode($imageNames) : null;

                $skuProduct = [
                    'seller_id' => auth('seller')->id() ?? null,
                    'product_id' => $product_id,
                    // 'sizes' => $size,
                    'sizes' => $skus,
                    'variation' => $skus,
                    'sku' => $request->skues[$index],
                    'tax' => $request->taxes[$index] ?? null,
                    'variant_mrp' => $request->unit_prices[$index] ?? null,
                    'discount_percent' => $request->var_tax[$index] ?? null,
                    'gst_percent' => $request->tax_gst[$index] ?? null,
                    'discount_type' => $request->discount_types[$index] ?? null,
                    'discount' => $request->discounts[$index] ?? null,
                    'listed_price' => $request->selling_prices[$index] ?? null,
                    'listed_percent' => $request->selling_taxs[$index] ?? null,
                    'listed_gst_percent' => $request->tax1_gst[$index] ?? null,
                    'commission_fee' => $request->commission_fee[$index] ?? null,
                    'quantity' => $request->quant[$index] ?? null,
                    'length' => $request->lengths[$index] ?? null,
                    'breadth' => $request->breadths[$index] ?? null,
                    'weight' => $request->weights[$index] ?? null,
                    'height' => $request->heights[$index] ?? null,
                    'color_name' => $request->color_names[$index] ?? null,
                    'thumbnail_image' => $thumbnailImage,
                    'image' => $imageJson,
                ];
                DB::table('sku_product_new')->insert($skuProduct);
            }

            $post = [
                'seller_id'               => auth('seller')->id() ?? null,
                'product_id'              => $product_id,
                'specification'           => json_encode($request->specification_values),
                'key_features'            => json_encode($request->features_values),
                'technical_specification' => json_encode($request->technical_specification_values),
                'other_details'           => json_encode($request->other_details_values),
                'created_at'              => now(),
                'updated_at'              => now()
            ];
            
            DB::table('key_specification_values')->insert($post);

            $tag_ids = [];

            if ($request->tags != null) {
                $tags = explode(",", $request->tags);
            }

            if (isset($tags)) {
                foreach ($tags as $key => $value) {

                    $value = trim($value);

                    if ($value === '') {
                        continue;
                    }

                    if (mb_strlen($value) > 50) {
                        continue;
                    }

                    $tag = Tag::firstOrNew([
                        'tag' => $value
                    ]);

                    $tag->save();
                    $tag_ids[] = $tag->id;
                }
            }

            $product->tags()->sync($tag_ids);


            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Model\Product',
                        'translationable_id' => $product->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $request->name[$index],
                    ));
                }
                if ($request->description[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Model\Product',
                        'translationable_id' => $product->id,
                        'locale' => $key,
                        'key' => 'description',
                        'value' => $request->description[$index],
                    ));
                }
            }
            Translation::insert($data);
            Toastr::success('Product added successfully!');
            return redirect()->route('seller.product.list');
        }
    }

    function list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {

            $products = Product::where(['added_by' => 'seller', 'user_id' => \auth('seller')->id()])
                ->where(function ($q) use ($search) {
                    $key = explode(' ', $search);
                    foreach ($key as $value) {
                         if (preg_match('/PR\d+/', $search)) {
                        $digits = substr($search, 2);
                        $q->orWhere('id', $digits);
                    } else {
                        $q->orWhere('name', 'like', "%{$search}%")
                         ->orWhere('id',$search)
                        ->orWhere('HSN_code', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('variation', 'like', "%{$search}%");
                    }
                    }
                });
            $query_param = ['search' => $request['search']];
        } else {
            $products = Product::where(['added_by' => 'seller', 'user_id' => \auth('seller')->id()]);
        }

         if ($request->has('filter')) {

             $request['category_id'] = $request['category_id'] ?? false;
             $request['sub_category_id'] = $request['sub_category_id'] ?? false;
             $request['brand_id'] = $request['brand_id'] ?? false;
             $request['min_price'] = $request['min_price'] ?? false;
             $request['max_price'] = $request['max_price'] ?? false;
             $request['min_reviews'] = $request['min_reviews'] ?? false;

            $products = $products->with(['rating','reviews'])->when($request['category_id'] , function ($q) use ($request) {
                        return $q->where('category_id', $request['category_id']);
                    })
                    ->when($request['sub_category_id'] , function ($q) use ($request) {
                        return $q->where('sub_category_id',  $request['sub_category_id']);
                    })
                   ->when($request['brand_id'] , function ($q) use ($request) {
                        return $q->where('brand_id', $request['brand_id']);
                    })
                    ->when($request['min_price'] , function ($q) use ($request) {
                        return $q->where('unit_price', '>=', $request['min_price']);
                    })
                    ->when($request['max_price'] , function ($q) use ($request) {
                        return $q->where('unit_price', '<=', $request['max_price']);
                    })
                   ->when($request['min_reviews'] , function ($q) use ($request) {

                     return $q->whereHas('reviews', function ($query) use ($request) {
                        $query->where('reviews.rating', '<=', $request['min_reviews']);

                    });
                    });
             $query_param = ['filter' => $request['filter'], 'category_id' => $request['category_id'],'sub_category_id' => $request['sub_category_id'],'brand_id' => $request['brand_id'],'min_price' => $request['min_price'],'max_price' => $request['max_price'],'min_reviews' => $request['min_reviews']];
        }

        $filter         = $request['filter'];

        $products = $products->orderBy('id', 'DESC')->paginate(Helpers::pagination_limit())->appends($query_param);
        
        return view('seller-views.product.list', compact('products', 'search', 'filter'));
    }

    public function stock_limit_list(Request $request, $type)
    {
        $stock_limit = Helpers::get_business_settings('stock_limit');
        $sort_oqrderQty = $request['sort_oqrderQty'];
        $query_param = $request->all();
        $search = $request['search'];
        $pro = Product::where(['added_by' => 'seller', 'product_type'=>'physical', 'user_id' => auth('seller')->id()])
            ->where('request_status',1)
            ->when($request->has('status') && $request->status != null, function ($query) use ($request) {
                $query->where('request_status', $request->status);
            });

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $pro = $pro->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('name', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }

        $request_status = $request['status'];

        $pro = $pro->withCount('order_details')->when($request->sort_oqrderQty == 'quantity_asc', function ($q) use ($request) {
            return $q->orderBy('current_stock', 'asc');
        })
            ->when($request->sort_oqrderQty == 'quantity_desc', function ($q) use ($request) {
                return $q->orderBy('current_stock', 'desc');
            })
            ->when($request->sort_oqrderQty == 'order_asc', function ($q) use ($request) {
                return $q->orderBy('order_details_count', 'asc');
            })
            ->when($request->sort_oqrderQty == 'order_desc', function ($q) use ($request) {
                return $q->orderBy('order_details_count', 'desc');
            })
            ->when($request->sort_oqrderQty == 'default', function ($q) use ($request) {
                return $q->orderBy('id');
            })->where('current_stock', '<', $stock_limit);


        $products = $pro->orderBy('id', 'DESC')->paginate(Helpers::pagination_limit())->appends(['status' => $request['status']])->appends($query_param);
        return view('seller-views.product.stock-limit-list', compact('products', 'search', 'request_status', 'sort_oqrderQty'));
    }

    public function stock_limit_export(Request $request)
    {
        $sort = $request['sort'] ?? 'ASC';
        $products = Product::when(empty($request['seller_id']) || $request['seller_id'] == 'all',function ($query){
            $query->whereIn('added_by', ['admin', 'seller']);
        })
            ->when($request['seller_id'] == 'in_house',function ($query){
                $query->where(['added_by' => 'admin']);
            })
            ->when($request['seller_id'] != 'in_house' && isset($request['seller_id']) && $request['seller_id'] != 'all',function ($query) use($request){
                $query->where(['added_by' => 'seller', 'user_id' => $request['seller_id']]);
            })
            ->orderBy('current_stock', $sort)->get();

        $data = array();
        foreach($products as $product){
            $data[] = array(
                'Product Name'   => $product->name,
                'Date'           => date('d M Y',strtotime($product->created_at)),
                'Total Stock'    => $product->current_stock,
            );
        }
        return (new FastExcel($data))->download('total_product_stock.xlsx');
    }

    public function update_quantity(Request $request)
    {
        $variations = [];
        $stock_count = $request['current_stock'];
        if ($request->has('type')) {
            foreach ($request['type'] as $key => $str) {
                $item = [];
                $item['type'] = $str;
                $item['price'] = BackEndHelper::currency_to_usd(abs($request['price_' . str_replace('.', '_', $str)]));
                $item['sku'] = $request['sku_' . str_replace('.', '_', $str)];
                $item['qty'] = abs($request['qty_' . str_replace('.', '_', $str)]);
                array_push($variations, $item);
            }
        }

        $product = Product::find($request['product_id']);
        if ($stock_count >= 0) 
        {
            $product->current_stock = $stock_count;
            $product->variation = json_encode($variations);
            $product->save();
            Toastr::success(\App\CPU\translate('product_quantity_updated_successfully!'));
            return back();
        } else {
            Toastr::warning(\App\CPU\translate('product_quantity_can_not_be_less_than_0_!'));
            return back();
        }
    }

    public function get_categories(Request $request)
    {
        $cat = Category::where(['parent_id' => $request->parent_id])->where('position',1)->orderBy('name')->get();

        if(count($cat)==0){
          $cat = Category::where(['sub_parent_id' => $request->parent_id])->orderBy('name')->get();
        }

        $res = '<option value="' . 0 . '" disabled selected>---Select---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="' . $row->id . '" selected >' . $row->name . '</option>';
            } else {
                $res .= '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }
        return response()->json([
            'select_tag' => $res,
        ]);
    }

    public function get_variations(Request $request)
    {
        $product = Product::find($request['id']);
        return response()->json([
            'view' => view('seller-views.product.partials._update_stock', compact('product'))->render()
        ]);
    }

    public function sku_combination(Request $request)
    {
        $options = [];
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }
        $sub_category = $request->sub_category_id;
        $unit_price = $request->unit_price;
        $product_name = $request->name[array_search('en', $request->lang)];
       

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }

        $combinations = Helpers::combinations($options);
        return response()->json([
            'view' => view('seller-views.product.partials._sku_combinations', compact(
                'combinations',
                'unit_price',
                'colors_active',
                'product_name',
                'sub_category'
            ))->render()
        ]);

    }

    public function sku_combination_edit(Request $request)
    {
        $product_id = $request->ids;

        $formRows = [];

        if ($request->has('sizes')) {
            $sizes        = $request->sizes;             // ["Beige-20Dx10Wx5H", "Black-20Dx10Wx5H", ...]
            $skues        = $request->skues            ?? [];
            $taxes        = $request->taxes            ?? [];
            $unitMRP      = $request->unit_prices      ?? [];
            $discTypes    = $request->discount_types   ?? [];
            $discounts    = $request->discounts        ?? [];
            $sellingTaxs  = $request->selling_taxs     ?? [];
            $sellingPrice = $request->selling_prices   ?? [];
            $transfer     = $request->transfer_price   ?? [];
            $commission   = $request->commission_fee   ?? [];
            $quantity     = $request->quant            ?? [];
            $lengths      = $request->lengths          ?? [];
            $breadths     = $request->breadths         ?? [];
            $heights      = $request->heights          ?? [];
            $weights      = $request->weights          ?? [];
            $colorNames   = $request->color_names      ?? [];
            $varTax       = $request->var_tax          ?? [];
            $gstMrp       = $request->tax_gst          ?? [];
            $gstSell      = $request->tax1_gst         ?? [];

            foreach ($sizes as $i => $variantKeyRaw) {
                if (!$variantKeyRaw) {
                    continue;
                }

                $sizesClean = rtrim(trim($variantKeyRaw), ',');
                $parts      = explode('-', $sizesClean);
                $color      = count($parts) ? array_shift($parts) : null;

                $normalizedParts = array_map(function ($v) {
                    return str_replace([' ', ','], '', trim($v));
                }, $parts);

                if ($color !== null && $color !== '') {
                    $variantKey = trim($color);
                    if (count($normalizedParts)) {
                        $variantKey .= '-' . implode('-', $normalizedParts);
                    }
                } else {
                    $variantKey = implode('-', $normalizedParts);
                }

                $formRows[$variantKey] = (object) [
                    'variation'           => $variantKey,
                    'sku'                 => $skues[$i]           ?? null,
                    'tax'                 => $taxes[$i]           ?? null,
                    'variant_mrp'         => $unitMRP[$i]         ?? null,
                    'discount_type'       => $discTypes[$i]       ?? null,
                    'discount'            => $discounts[$i]       ?? null,
                    'listed_percent'      => $sellingTaxs[$i]     ?? null,
                    'listed_price'        => $sellingPrice[$i]    ?? null,
                    'transfer_price'      => $transfer[$i]        ?? null,
                    'commission_fee'      => $commission[$i]      ?? null,
                    'quantity'            => $quantity[$i]        ?? null,
                    'length'              => $lengths[$i]         ?? null,
                    'breadth'             => $breadths[$i]        ?? null,
                    'height'              => $heights[$i]         ?? null,
                    'weight'              => $weights[$i]         ?? null,
                    'color_name'          => $colorNames[$i]      ?? null,
                    'discount_percent'    => $varTax[$i]          ?? null,
                    'gst_percent'         => $gstMrp[$i]          ?? null,
                    'listed_gst_percent'  => $gstSell[$i]         ?? null,
                ];
            }
        }

        $options = [];

        // COLORS
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            $options[]     = $request->colors;
        } else {
            $colors_active = 0;
        }

        $unit_price   = $request->unit_price;
        $product_name = $request->name[array_search('en', $request->lang)];

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name      = 'choice_options_' . $no;
                $rawValues = $request[$name] ?? [];
                if (!is_array($rawValues)) {
                    $rawValues = [$rawValues];
                }

                $cleanValues = [];

                foreach ($rawValues as $val) {
                    $parts = explode(',', $val);

                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            $cleanValues[] = $p;
                        }
                    }
                }

                if (count($cleanValues) > 0) {
                    $options[] = $cleanValues;
                }
            }
        }

        $combinations = \App\CPU\Helpers::combinations($options);

        $dbRows = DB::table('sku_product_new')
            ->where('product_id', $product_id)
            ->get()
            ->mapWithKeys(function ($row) {
                $sizes = rtrim(trim($row->variation), ',');

                $parts = explode('-', $sizes);
                $color = count($parts) ? array_shift($parts) : null;

                $normalizedParts = array_map(function ($v) {
                    return str_replace([' ', ','], '', trim($v));
                }, $parts);

                if ($color !== null && $color !== '') {
                    $normalizedKey = trim($color);
                    if (count($normalizedParts)) {
                        $normalizedKey .= '-' . implode('-', $normalizedParts);
                    }
                } else {
                    $normalizedKey = implode('-', $normalizedParts);
                }

                return [$normalizedKey => $row];
            })
            ->toArray();
            
        $existingRows = [];

        foreach ($dbRows as $key => $row) {
            $existingRows[$key] = $row;
        }

        foreach ($formRows as $key => $row) {
            if (isset($existingRows[$key])) {
                $existingRows[$key] = (object) array_merge(
                    (array) $existingRows[$key],
                    (array) $row
                );
            } else {
                $existingRows[$key] = $row;
            }
        }

        return response()->json([
            'view' => view(
                'seller-views.product.partials._sku11_combinations',
                compact('product_id', 'combinations', 'unit_price', 'colors_active', 'product_name', 'existingRows')
            )->render(),
        ]);
    }

    public function edit($id)
    {
        $product = Product::withoutGlobalScopes()->with('translations')->find($id);
        $product_category = json_decode($product->category_ids);
        $product->colors = json_decode($product->colors);
        $categories = Category::where(['parent_id' => 0])->orderBy('name')->get();
        $br = Brand::orderBY('name', 'ASC')->get();
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;
        $digital_product_setting = BusinessSetting::where('type', 'digital_product')->first()->value;
        $warehouse = DB::table('warehouse')->where('seller_id',auth('seller')->id())->get();
       
        try {
            return view('seller-views.product.edit', compact('categories', 'br','warehouse', 'product', 'product_category', 'brand_setting', 'digital_product_setting'));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        $product->name = $request->name[array_search('en', $request->lang)];
        $product->slug = Str::slug($request->name[array_search('en', $request->lang)], '-') . '-' . Str::random(6);

        $category = [];
        if ($request->category_id != null) {
            $category[] = ['id' => $request->category_id, 'position' => 1];
        }
        if ($request->sub_category_id != null) {
            $category[] = ['id' => $request->sub_category_id, 'position' => 2];
        }
        if ($request->sub_sub_category_id != null) {
            $category[] = ['id' => $request->sub_sub_category_id, 'position' => 3];
        }

        $product->HSN_code            = $request->HSN_code;
        $product->Return_days         = $request->Return_days;
        $product->Replacement_days    = $request->Replacement_days;
        $product->product_type        = $request->product_type;
        $product->category_ids        = json_encode($category);
        $product->category_id         = $request->category_id;
        $product->sub_category_id     = $request->sub_category_id;
        $product->sub_sub_category_id = $request->sub_sub_category_id;
        $product->brand_id            = $request->brand_id ?? null;
        $product->unit                = $request->unit;

        $product->digital_product_type = $request->product_type == 'digital' ? $request->digital_product_type : null;
        $product->details              = $request->description[array_search('en', $request->lang)];
        $product->free_delivery        = $request->free_delivery == 'on' ? 1 : 0;

        $product->add_warehouse = $request->warehouse;

        $request->product_type = 'physical';
        $request->product_type == 'digital';

        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $product->colors = $request->product_type == 'physical'
                ? json_encode($request->colors)
                : json_encode([]);
        } else {
            $product->colors = $request->product_type == 'physical'
                ? json_encode([])
                : json_encode([]);
        }

        $choice_options = [];
        if ($request->has('choice')) {
            foreach ($request->choice_no as $key => $no) {
                $str             = 'choice_options_' . $no;
                $item['name']    = 'choice_' . $no;
                $item['title']   = $request->choice[$key];
                $item['options'] = array_map('trim', explode('|', implode('|', $request[$str])));
                $choice_options[] = $item;
            }
        }
        $product->choice_options = $request->product_type == 'physical'
            ? json_encode($choice_options)
            : json_encode([]);

        $options         = [];
        $combinationKeys = [];
        $variations      = [];
        $stock_count     = 0;

        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            $options[]     = $request->colors;
        } else {
            $colors_active = 0;
        }

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name   = 'choice_options_' . $no;
                $my_str = implode('|', $request[$name] ?? []);
                $options[] = explode(',', $my_str);
            }
        }

        $combinations = Helpers::combinations($options);

        if (count($combinations) && count($combinations[0]) > 0) {
            foreach ($combinations as $idx => $combination) {
                $parts      = [];
                $startIndex = 0;

                if ($colors_active && isset($combination[0])) {
                    $colorCode = $combination[0]; // "#xxxxxx"
                    $colorRow  = Color::where('code', $colorCode)->first();
                    $colorName = $colorRow ? trim($colorRow->name) : trim($colorCode);
                    $parts[]   = $colorName;
                    $startIndex = 1;
                }

                for ($i = $startIndex; $i < count($combination); $i++) {
                    $clean   = str_replace([' ', ','], '', trim($combination[$i]));
                    $parts[] = $clean;
                }

                $keyStr = implode('-', $parts);
                $combinationKeys[$idx] = $keyStr;

                $row          = [];
                $row['type']  = $keyStr;
                $row['price'] = Convert::usd(abs($request['price_' . str_replace('.', '_', $keyStr)] ?? 0));
                $row['sku']   = $request['sku_' . str_replace('.', '_', $keyStr)] ?? null;
                $row['qty']   = abs($request['qty_' . str_replace('.', '_', $keyStr)] ?? 0);

                $variations[] = $row;
                $stock_count += $row['qty'];
            }
        } else {
            $stock_count = (int) ($request['current_stock'] ?? 0);
        }

        $product->variation         = $request->product_type == 'physical' ? json_encode($variations) : json_encode([]);
        $product->unit_price        = Convert::usd($request->unit_price);
        $product->purchase_price    = Convert::usd($request->purchase_price ?? 0);
        $product->tax_model         = $request->tax_model ?? 'include';
        $product->code              = $request->code;
        $product->minimum_order_qty = $request->minimum_order_qty;
        $product->discount          = $request->discount_type == 'flat'
            ? Convert::usd($request->discount)
            : $request->discount;
        $product->attributes        = $request->product_type == 'physical'
            ? json_encode($request->choice_attributes)
            : json_encode([]);
        $product->discount_type     = $request->discount_type;
        $product->current_stock     = $request->product_type == 'physical' ? abs($stock_count) : 0;
        $product->shipping_cost     = $request->product_type == 'physical'
            ? (Helpers::get_business_settings('product_wise_shipping_cost_approval') == 1
                ? $product->shipping_cost
                : Convert::usd($request->shipping_cost))
            : 0;
        $product->multiply_qty      = ($request->product_type == 'physical')
            ? ($request->multiplyQTY == 'on' ? 1 : 0)
            : 0;
        $product->request_status    = Helpers::get_business_settings('new_product_approval') == 1 ? 0 : 1;
        $product->status            = 0;

        if (Helpers::get_business_settings('product_wise_shipping_cost_approval') == 1 &&
            $product->shipping_cost != Convert::usd($request->shipping_cost)
        ) {
            $product->temp_shipping_cost       = Convert::usd($request->shipping_cost);
            $product->is_shipping_cost_updated = 0;
        }

        $product->video_provider = 'youtube';
        $product->video_url      = $request->video_link;
        if ($product->request_status == 2) {
            $product->request_status = 0;
        }

        if ($request->ajax()) {
            $product->save();
            return response()->json([], 200);
        }

        $product->save();

        $sizeKeys = array_values($combinationKeys);

        $skues      = $request->skues ?? [];
        $taxes      = $request->taxes ?? [];
        $unitMRP    = $request->unit_prices ?? [];
        $varTax     = $request->var_tax ?? [];
        $gstTax     = $request->tax_gst ?? [];
        $discType   = $request->discount_types ?? [];
        $discounts  = $request->discounts ?? [];
        $listPrice  = $request->selling_prices ?? [];
        $listTax    = $request->selling_taxs ?? [];
        $listGst    = $request->tax1_gst ?? [];
        $commFee    = $request->commission_fee ?? [];
        $qtyArr     = $request->quant ?? [];
        $lenArr     = $request->lengths ?? [];
        $breArr     = $request->breadths ?? [];
        $heiArr     = $request->heights ?? [];
        $weiArr     = $request->weights ?? [];
        $colorNames = $request->color_names ?? [];

        if (empty($sizeKeys)) {
            DB::table('sku_product_new')->where('product_id', $id)->delete();
        } else {
            DB::table('sku_product_new')
                ->where('product_id', $id)
                ->whereNotIn('sizes', $sizeKeys)
                ->delete();

            foreach ($sizeKeys as $index => $sizeKey) {

                $thumbnailOriginal = $request->input('thumbnail_image_' . $index);
                $imageOrder        = json_decode($request->input('image_order_' . $index, '[]'), true) ?: [];
                $newImageNames     = [];

                if ($request->hasFile('image_' . $index)) {

                    $files   = $request->file('image_' . $index);
                    $fileMap = [];

                    foreach ($files as $file) {
                        $fileMap[$file->getClientOriginalName()] = $file;
                    }

                    $productFolder = 'products';

                    foreach ($imageOrder as $originalName) {
                        if (isset($fileMap[$originalName]) && $fileMap[$originalName]->isValid()) {

                            $file = $fileMap[$originalName];
                            $ext  = strtolower($file->getClientOriginalExtension());

                            $safeName = uniqid() . '.webp';

                            if ($ext === 'webp') {
                                $imageContent = file_get_contents($file->getRealPath());
                            } else {
                                $imageContent = (string) Image::make($file->getRealPath())
                                    ->encode('webp', 90);
                            }

                            $r2Path = $productFolder . '/' . $safeName;
                            Storage::disk('r2')->put($r2Path, $imageContent);

                            $publicPath      = '/' . $r2Path;
                            $newImageNames[] = $publicPath;

                            if ($thumbnailOriginal == $originalName) {
                                $thumbnailOriginal = $publicPath;
                            }
                        }
                    }
                }

                $oldImages   = $request->input('old_image_' . $index, []);
                $finalImages = array_merge($oldImages, $newImageNames);
                $imageJson   = !empty($finalImages) ? json_encode($finalImages) : null;

                $thumbnailFinal = $thumbnailOriginal;
                if ($thumbnailFinal && strpos($thumbnailFinal, '/products/') === false) {
                    foreach ($finalImages as $imgPath) {
                        if (str_ends_with($imgPath, $thumbnailFinal)) {
                            $thumbnailFinal = $imgPath;
                            break;
                        }
                    }
                }

                $skuProduct = [
                    'seller_id'          => auth('seller')->id() ?? null,
                    'product_id'         => $id,
                    'sizes'              => $sizeKey,
                    'variation'          => $sizeKey,
                    'sku'                => $skues[$index]          ?? null,
                    'tax'                => $taxes[$index]          ?? null,
                    'variant_mrp'        => $unitMRP[$index]        ?? null,
                    'discount_percent'   => $varTax[$index]         ?? null,
                    'gst_percent'        => $gstTax[$index]         ?? null,
                    'discount_type'      => $discType[$index]       ?? null,
                    'discount'           => $discounts[$index]      ?? null,
                    'listed_price'       => $listPrice[$index]      ?? null,
                    'listed_percent'     => $listTax[$index]        ?? null,
                    'listed_gst_percent' => $listGst[$index]        ?? null,
                    'commission_fee'     => $commFee[$index]        ?? null,
                    'quantity'           => $qtyArr[$index]         ?? null,
                    'length'             => $lenArr[$index]         ?? null,
                    'breadth'            => $breArr[$index]         ?? null,
                    'weight'             => $weiArr[$index]         ?? null,
                    'height'             => $heiArr[$index]         ?? null,
                    'color_name'         => $colorNames[$index]     ?? null,
                    'thumbnail_image'    => $thumbnailFinal ?? $request->new_thumbnail_image,
                    'image'              => $imageJson,
                ];

                DB::table('sku_product_new')->updateOrInsert(
                    [
                        'product_id' => $id,
                        'sizes'      => $sizeKey,
                    ],
                    $skuProduct
                );
            }
        }

        $post = [
            'seller_id'               => auth('seller')->id() ?? null,
            'product_id'              => $id,
            'specification'           => json_encode($request->specification_values),
            'key_features'            => json_encode($request->features_values),
            'technical_specification' => json_encode($request->technical_specification_values),
            'other_details'           => json_encode($request->other_details_values),
            'created_at'              => now(),
            'updated_at'              => now(),
        ];

        DB::table('key_specification_values')->where('product_id', $id)->update($post);

        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(',', $request->tags);
            foreach ($tags as $value) {
                $tag = Tag::firstOrNew(['tag' => trim($value)]);
                $tag->save();
                $tag_ids[] = $tag->id;
            }
        }
        $product->tags()->sync($tag_ids);

        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\Product',
                        'translationable_id'   => $product->id,
                        'locale'               => $key,
                        'key'                  => 'name',
                    ],
                    ['value' => $request->name[$index]]
                );
            }
            if ($request->description[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\Product',
                        'translationable_id'   => $product->id,
                        'locale'               => $key,
                        'key'                  => 'description',
                    ],
                    ['value' => $request->description[$index]]
                );
            }
        }

        Toastr::success('Product updated successfully.');
        return back();
    }

    public function view($id)
    {
        $product = Product::with(['reviews'])->where(['id' => $id])->first();
        $sku = DB::table('sku_product_new')->where('product_id',$product->id)->get();
        $reviews = Review::where(['product_id' => $id])->paginate(Helpers::pagination_limit());
        return view('seller-views.product.view', compact('product', 'reviews','sku'));
    }

    public function remove_image(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $images = json_decode($product->images, true);
        if (count($images) < 2) {
            Toastr::warning('You cannot delete all images!');
            return back();
        }

        $imagePath = 'products/' . $request->name; // adjust folder if needed
        Storage::disk('r2')->delete($imagePath);

        $colors = json_decode($product->colors, true);
        $color_images = json_decode($product->color_image, true);
        $color_image_arr = [];

        if ($colors && $color_images) {
            foreach ($color_images as $img) {
                if ($img['color'] != $request->color && $img['image_name'] != $request->name) {
                    $color_image_arr[] = [
                        'color' => $img['color'] ?? null,
                        'image_name' => $img['image_name'],
                    ];
                }
            }
        }

        $updatedImages = array_filter($images, fn($img) => $img != $request->name);

        $product->update([
            'images' => json_encode(array_values($updatedImages)),
            'color_image' => json_encode($color_image_arr),
        ]);

        Toastr::success('Product image removed successfully!');
        return back();
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);

        Cart::where('product_id', $product->id)->delete();

        if (!empty($product->images)) {
            $images = json_decode($product->images, true);
            if (is_array($images)) {
                foreach ($images as $image) {
                    // Storage::disk('r2')->delete(ltrim($image, '/'));
                }
            }
        }

        if (!empty($product->thumbnail)) {
            // Storage::disk('r2')->delete(ltrim($product->thumbnail, '/'));
        }

        $paths = [];

        if (!empty($product->thumbnail)) {
            $paths[] = ltrim($product->thumbnail, '/');   // yaha bhi same normalisation
        }

        if (!empty($product->images)) {
            $images = json_decode($product->images, true);
            if (is_array($images)) {
                foreach ($images as $img) {
                    $paths[] = ltrim($img, '/');          // har image normalise
                }
            }
        }

        if (!empty($paths)) {
            // DB::table('bulk_image')
            //     ->where('seller_id', $product->user_id)   // auth('seller') ki jagah product ka seller
            //     ->whereIn('image_path', $paths)
            //     ->delete();
        }

        FlashDealProduct::where('product_id', $id)->delete();
        DealOfTheDay::where('product_id', $id)->delete();

        $product->delete();

        Toastr::success('Product removed successfully!');
        return back();
    }

    public function bulk_import_index()
    {
        return view('seller-views.product.bulk-import');
    }

    public function search_bulk_import_index()
    {
        $cat = Category::where(['position' => 0, 'home_status' => 1])->orderBy('name')->get();
        $br = Brand::active()->orderBY('name', 'ASC')->get();
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;
        $digital_product_setting = BusinessSetting::where('type', 'digital_product')->first()->value;
        return view('seller-views.product.search_bulk_import_index', compact('cat', 'br', 'brand_setting', 'digital_product_setting'));

    }

    public function search_bulk_import_category(Request $request)
    {
        session([
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'sub_sub_category_id' => $request->sub_sub_category_id,
            'brand_id' => $request->brand_id,
        'tax'=>$request->tax,
        'hsn_code'=>$request->hsn_code,
        'procurement_time'=>$request->procurement_time,
        ]);

        $category_id = session('category_id');
        $sub_category_id = session('sub_category_id');
        $sub_sub_category_id = session('sub_sub_category_id');
        $brand_id = session('brand_id');
        $tax = session('tax');
        $procurement_time = session('procurement_time');
        $hsn_code = session('hsn_code');
        return view('seller-views.product.bulk-import', compact('category_id', 'sub_category_id', 'sub_sub_category_id','brand_id','tax','procurement_time','hsn_code'));
    }

    private function normalizeSpecifications(array $row, array $allowed): array
    {
        $result = [
            'specification' => [],
            'key_features' => [],
            'technical_specification' => [],
            'other_details' => [],
        ];

        // Excel se values map karo
        $excel = [
            'specification' => [],
            'key_features' => [],
            'technical_specification' => [],
            'other_details' => [],
        ];

        foreach ($row as $header => $value) {

            $header = trim((string)$header);
            $value  = trim((string)$value);

            if ($value === '' || $value === '-') {
                continue;
            }

            if (stripos($header, 'Specification:') === 0) {
                $key = trim(str_ireplace('Specification:', '', $header));
                $excel['specification'][$key] = $value;
            }

            elseif (stripos($header, 'Key features:') === 0) {
                $key = trim(str_ireplace('Key features:', '', $header));
                $excel['key_features'][$key] = $value;
            }

            elseif (stripos($header, 'Technical specification:') === 0) {
                $key = trim(str_ireplace('Technical specification:', '', $header));
                $excel['technical_specification'][$key] = $value;
            }

            elseif (stripos($header, 'Other details:') === 0) {
                $key = trim(str_ireplace('Other details:', '', $header));
                $excel['other_details'][$key] = $value;
            }
        }

        foreach ($allowed as $type => $keys) {
            foreach ($keys as $k) {
                if (isset($excel[$type][$k])) {
                    $result[$type][] = $excel[$type][$k];
                } else {
                    $result[$type][] = 'N/A';
                }
            }
        }

        return $result;
    }

    public function bulk_import_data(Request $request)
    {
        // Validate that file is present & is a spreadsheet
        $request->validate([
            'products_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        if (!$request->hasFile('products_file')) {
            Toastr::error('Please upload the products_file file.');
            return back();
        }

        try {
            $file = $request->file('products_file');

            // Optional: you can check validity
            if (!$file->isValid()) {
                Toastr::error('Uploaded file is not valid.');
                return back();
            }

            // Load spreadsheet
            $spreadsheet = IOFactory::load($file->getRealPath());

            // Sirf Product_Bulk sheet se data lo (Guideline ignore)
            $productBulkSheet = $spreadsheet->getSheetByName('Product_Bulk');
            if (!$productBulkSheet) {
                // Fallback: Active sheet, but warning bhi de do
                Toastr::warning('Product_Bulk sheet not found. Using active sheet. Please do not rename the "Product_Bulk" sheet in the template.');
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $productBulkSheet;
            }

            $data = $sheet->toArray(null, true, true, true);

            $headers    = array_map('trim', $data[1]);
            $dimheaders = array_map('trim', $data[2]);

            $rows       = [];
            $dimensions = [];

            foreach (array_slice($data, 3) as $index => $row) {
                $clean = array_map('trim', $row);

                if (count($clean) == count($headers)) {
                    $rows[$index] = array_combine($headers, $clean);
                }

                if (count($clean) == count($dimheaders)) {
                    $dimensions[$index] = array_combine($dimheaders, $clean);
                }
            }
        } catch (\Exception $e) {
            Toastr::error('File Error: ' . $e->getMessage());
            return back();
        }

        $dbColors = DB::table('colors')->pluck('code', 'name')->toArray();

        $groupedProducts = [];

        foreach ($rows as $i => $row) {
            if (empty($row['Product Title'])) continue;

            $title  = trim($row['Product Title']);

            $dimRow = $dimensions[$i] ?? [];

            // ✅ Colour & size correctly mapped from your Excel headers
            $colorName = trim(
                $row['Colour']
                ?? $dimRow['Colour Name']
                ?? ''
            );

            $size = trim(
                $row['Size']
                ?? $dimRow['Add size']
                ?? ''
            );

            $packaging_dimensions = [
                'length'  => (float)($dimRow['Length (CM)'] ?? 0),
                'breadth' => (float)($dimRow['Breadth (CM)'] ?? 0),
                'height'  => (float)($dimRow['Height (CM)'] ?? 0),
                'weight'  => (float)($dimRow['Weight (KG)'] ?? 0),
            ];

            $colorNames = [
                'Colour_Name'        => $colorName,
                'Rename_Colour_Name' => trim($dimRow['Rename Colour Name'] ?? $colorName),
            ];

            // Description + Bullet Points yahi se aa rahe hain (dimRow)
            $descriptionParts = [
                'Description'     => $dimRow['Description']     ?? null,
                'Bullet Point 1'  => $dimRow['Bullet Point 1']  ?? null,
                'Bullet Point 2'  => $dimRow['Bullet Point 2']  ?? null,
                'Bullet Point 3'  => $dimRow['Bullet Point 3']  ?? null,
                'Bullet Point 4'  => $dimRow['Bullet Point 4']  ?? null,
                'Bullet Point 5'  => $dimRow['Bullet Point 5']  ?? null,
            ];

            $colorCode = $dbColors[$colorName] ?? '#000000';

            if (!isset($groupedProducts[$title])) {
                $groupedProducts[$title] = [
                    'product_data' => [
                        'title'                => $title,
                        'brand'                => $row['Brands'] ?? null,
                        'description'          => $row['Product Description'] ?? null,
                        'description_parts'    => $descriptionParts,
                        'packaging_dimensions' => $packaging_dimensions,
                        'RenameColour'         => $colorNames,
                        'full_row'             => $row,
                    ],
                    'variations'  => [],
                    'colors'      => [],
                    'color_names' => [],
                ];
            }

            $variationType = trim($colorName . '-' . $size);

            $mrp          = (float)($row['MRP (INR)'] ?? 0);
            $tax          = (float)($row['Tax'] ?? 0);
            $discountType = $row['Discount Type'] ?? null;
            $discountVal  = $row['Discount'] ?? 0;

            $discountAmount = ($mrp * $tax) / 100;

            $listedPrice = 0;
            if ($discountType === 'percent') {
                $listedPrice = $mrp - (($mrp * (float)$discountVal) / 100);
            } elseif ($discountType === 'flat') {
                $listedPrice = $mrp - (float)$discountVal;
            } else {
                // Safe fallback: use MRP
                $listedPrice = $mrp;
            }

            $groupedProducts[$title]['variations'][] = [
                'type'          => $variationType,
                'price'         => $listedPrice,
                'sku'           => $row['Seller SKU ID'] ?? null,
                'qty'           => (int)($row['Stock'] ?? 0),
                'size'          => $size,
                '_full_row'     => $row,
                '_dimensions'   => $packaging_dimensions,
                '_color_name'   => $colorName,
                '_color_rename' => $colorNames,
                '_color_code'   => $colorCode,
                '_size'         => $size,
            ];

            if (!in_array($colorCode, $groupedProducts[$title]['colors'])) {
                $groupedProducts[$title]['colors'][] = $colorCode;
            }
            $renameColor = $colorNames['Rename_Colour_Name'] ?? $colorName;
            if (!in_array($renameColor, $groupedProducts[$title]['color_names'])) {
                $groupedProducts[$title]['color_names'][] = $renameColor;
            }
        }

        $countRow = 0;

        DB::beginTransaction();
        try {
            foreach ($groupedProducts as $title => $data) {
                $row = $data['product_data']['full_row'];
                $dim = $data['product_data']['packaging_dimensions'];

                $category_id = DB::table('categories')
                    ->where('name', $row['Product Category'] ?? null)
                    ->where('parent_id', 0)
                    ->value('id');

                $sub_category_id = DB::table('categories')
                    ->where('name', $row['Product Sub Category'] ?? null)
                    ->where('parent_id', $category_id)
                    ->value('id');

                $sub_category_row = DB::table('categories')->where('id', $sub_category_id)->first();
                $commission_fee   = optional($sub_category_row)->commission ?? 0;

                $sub_sub_category_id = DB::table('categories')
                    ->where('name', $row['Product Sub Sub Category'] ?? null)
                    ->where('sub_parent_id', $sub_category_id)
                    ->value('id');

                $brand_id = DB::table('brands')
                    ->where('name', $row['Brands'] ?? null)
                    ->value('id');

                $replacement = '';
                if (!empty($row['Return Days']) && strtoupper($row['Return Days']) != 'NO') {
                    $replacement = $row['Return Days'];
                } else {
                    $replacement = $row['Replacement Days'] ?? null;
                }

                $freeDel = '';
                if (($row['Free Delivery'] ?? '') == 'No') {
                    $freeDel = 0;
                } elseif (($row['Free Delivery'] ?? '') == 'Yes') {
                    $freeDel = 1;
                }

                $product = new Product();
                $product->added_by = 'seller';
                $product->user_id  = auth('seller')->id();
                $product->name     = $title;
                $product->HSN_code = $row['HSN'] ?? null;
                $product->slug     = Str::slug($title);

                $product->category_ids = json_encode([
                    ['id' => (string)$category_id,         'position' => 1],
                    ['id' => (string)$sub_category_id,     'position' => 2],
                    ['id' => (string)$sub_sub_category_id, 'position' => 3],
                ]);

                $product->category_id         = $category_id;
                $product->sub_category_id     = $sub_category_id;
                $product->sub_sub_category_id = $sub_sub_category_id;
                $product->brand_id            = $brand_id;
                $product->cities              = '[""]';
                $product->product_type        = "physical";
                $product->add_warehouse       = $row['Warehouse'] ?? null;
                $product->Return_days         = $row['Return Days'] ?? null;
                $product->replacement_days    = $replacement;
                $product->thumbnail           = isset($row['Thumbnail Image name']) ? $row['Thumbnail Image name'] : null;
                $product->images              = isset($row['Other Image name']) ? json_encode(array_filter(explode(',', $row['Other Image name']))) : null;

                // --------- DESCRIPTION + BULLET POINTS → HTML ----------
                $descParts = $data['product_data']['description_parts'] ?? [];

                $mainDescription = trim($descParts['Description'] ?? '');

                $bulletPoints = [];
                $bpKeys = [
                    'Bullet Point 1',
                    'Bullet Point 2',
                    'Bullet Point 3',
                    'Bullet Point 4',
                    'Bullet Point 5',
                ];

                foreach ($bpKeys as $key) {
                    if (!empty(trim($descParts[$key] ?? ''))) {
                        $bulletPoints[] = trim($descParts[$key]);
                    }
                }

                $detailsHtml = '';

                if ($mainDescription !== '') {
                    $detailsHtml .= '<p>' . htmlspecialchars($mainDescription, ENT_QUOTES, 'UTF-8') . '</p>';
                }

                if (!empty($bulletPoints)) {
                    $detailsHtml .= '<ul>';
                    foreach ($bulletPoints as $bp) {
                        $detailsHtml .= '<li>' . htmlspecialchars($bp, ENT_QUOTES, 'UTF-8') . '</li>';
                    }
                    $detailsHtml .= '</ul>';
                }

                // Fallback: agar naya format empty hai to purana Product Description use karo
                if ($detailsHtml === '' && !empty($row['Product Description'] ?? '')) {
                    $detailsHtml = '<p>' . htmlspecialchars($row['Product Description'], ENT_QUOTES, 'UTF-8') . '</p>';
                }

                $product->details = $detailsHtml;
                // -------------------------------------------------------

                $product->unit           = $row['Unit'] ?? 'pc';
                $product->min_qty        = $row['MOQ'] ?? 1;
                $product->free_shipping  = (strtolower($row['Free Delivery'] ?? 'no') === 'yes') ? 1 : 0;
                $product->video_provider = 'youtube';
                $product->video_url      = $row['Video URL'] ?? null;
                $product->colors         = json_encode($data['colors']);
                $product->length         = $dim['length'];
                $product->breadth        = $dim['breadth'];
                $product->height         = $dim['height'];
                $product->weight         = $dim['weight'];
                $product->free_delivery  = $freeDel;

                $product->status    = 0;
                $product->published = 1;

                $all_color_names = array_values(array_filter(array_unique($data['color_names'])));
                $all_sizes       = array_values(array_filter(array_unique(array_column($data['variations'], '_size'))));

                $choice_options = [];
                if (count($all_sizes) > 0) {
                    $choice_options[] = [
                        'name'    => 'choice_1',
                        'title'   => 'Size',
                        'options' => $all_sizes,
                    ];
                }

                $product->choice_options = json_encode($choice_options);
                $product->attributes     = json_encode([1]);

                $options = [];
                if (count($all_color_names) > 0) {
                    $options[] = $all_color_names; // Color
                }
                if (count($all_sizes) > 0) {
                    $options[] = $all_sizes; // Size
                }

                $combinations = [];
                if (!empty($options)) {
                    if (class_exists('Helpers') && method_exists('Helpers', 'combinations')) {
                        $combinations = Helpers::combinations($options);
                    } else {
                        $combinations = [[]];
                        foreach ($options as $optionSet) {
                            $new = [];
                            foreach ($combinations as $comb) {
                                foreach ($optionSet as $opt) {
                                    $new[] = array_merge($comb, [$opt]);
                                }
                            }
                            $combinations = $new;
                        }
                    }
                }

                $finalVariations = [];
                $stock_count     = 0;

                if (!empty($combinations)) {
                    foreach ($combinations as $combination) {
                        $type = implode('-', $combination);

                        $matched = null;
                        foreach ($data['variations'] as $v) {
                            if (trim($v['type']) === trim($type)) {
                                $matched = $v;
                                break;
                            }
                        }

                        if ($matched) {
                            $finalVariations[] = [
                                'type'  => $matched['type'],
                                'price' => $matched['price'],
                                'sku'   => $matched['sku'],
                                'qty'   => $matched['qty'],
                            ];
                            $stock_count += (int)$matched['qty'];
                        } else {
                            $finalVariations[] = [
                                'type'  => $type,
                                'price' => 0,
                                'sku'   => null,
                                'qty'   => 0,
                            ];
                        }
                    }
                } else {
                    if (!empty($data['variations'])) {
                        $sumQty = 0;
                        foreach ($data['variations'] as $v) {
                            $finalVariations[] = [
                                'type'  => $v['type'],
                                'price' => $v['price'],
                                'sku'   => $v['sku'],
                                'qty'   => $v['qty'],
                            ];
                            $sumQty += (int)$v['qty'];
                        }
                        $stock_count = $sumQty;
                    } else {
                        $finalVariations[] = [
                            'type'  => $title,
                            'price' => 0,
                            'sku'   => null,
                            'qty'   => 0,
                        ];
                    }
                }

                $product->variation     = json_encode($finalVariations);
                $product->current_stock = (int)$stock_count;

                $product->save();

                foreach ($data['variations'] as $variation) {
                    $vrow = $variation['_full_row'];
                    $vdim = $variation['_dimensions'];
                    $vcol = $variation['_color_rename'];

                    $mrp          = (float)($vrow['MRP (INR)'] ?? 0);
                    $tax          = (float)($vrow['Tax'] ?? 0);
                    $discountType = $vrow['Discount Type'] ?? null;
                    $discountVal  = $vrow['Discount'] ?? 0;

                    $discountAmount = ($mrp * $tax) / 100;

                    $listedPrice = 0;
                    if ($discountType === 'percent') {
                        $listedPrice = $mrp - (($mrp * (float)$discountVal) / 100);
                    } elseif ($discountType === 'flat') {
                        $listedPrice = $mrp - (float)$discountVal;
                    } else {
                        $listedPrice = (float)($variation['price'] ?? $mrp);
                    }

                    $listedPercent = ($listedPrice * $tax) / 100;

                    DB::table('sku_product_new')->insert([
                        'seller_id'           => auth('seller')->id(),
                        'product_id'          => $product->id,
                        'commission_fee'      => $commission_fee ?? 0,
                        'sku'                 => $variation['sku'],
                        'variation'           => $variation['type'],
                        'variant_mrp'         => $mrp,
                        'discount_percent'    => $discountAmount,
                        'gst_percent'         => max(0, $mrp - $discountAmount),
                        'discount_type'       => $discountType,
                        'discount'            => $vrow['Discount'] ?? 0,
                        'listed_price'        => $listedPrice,
                        'listed_percent'      => $listedPercent,
                        'listed_gst_percent'  => max(0, $listedPrice - $listedPercent),
                        'sizes'               => $variation['size'],
                        'quantity'            => $variation['qty'],
                        'color_name'          => $vcol['Rename_Colour_Name'] ?? null,
                        'tax'                 => $tax,
                        'length'              => $vdim['length'],
                        'breadth'             => $vdim['breadth'],
                        'height'              => $vdim['height'],
                        'weight'              => $vdim['weight'],
                        'image'               => isset($vrow['Other Image name']) ? json_encode(array_filter(explode(',', $vrow['Other Image name']))) : null,
                        'thumbnail_image'     => $vrow['Thumbnail Image name'] ?? null,
                    ]);
                }

                $category = DB::table('categories')->where('id', $sub_sub_category_id)->first();

                $allowedSpec     = array_map('trim', explode(',', $category->specification ?? ''));
                $allowedFeatures = array_map('trim', explode(',', $category->key_features ?? ''));
                $allowedTech     = array_map('trim', explode(',', $category->technical_specification ?? ''));
                $allowedOther    = array_map('trim', explode(',', $category->other_details ?? ''));

                $allowed = [
                    'specification'           => $allowedSpec,
                    'key_features'            => $allowedFeatures,
                    'technical_specification' => $allowedTech,
                    'other_details'           => $allowedOther,
                ];

                $normalized = $this->normalizeSpecifications($row, $allowed);

                DB::table('key_specification_values')->updateOrInsert(
                    ['product_id' => $product->id],
                    [
                        'seller_id'               => auth('seller')->id(),
                        'specification'           => json_encode($normalized['specification']),
                        'key_features'            => json_encode($normalized['key_features']),
                        'technical_specification' => json_encode($normalized['technical_specification']),
                        'other_details'           => json_encode($normalized['other_details']),
                        'created_at'              => now(),
                        'updated_at'              => now(),
                    ]
                );

                if (!empty($row['Search Tags'])) {
                    $tagNames = array_map('trim', explode(',', $row['Search Tags']));
                    $tag_ids  = [];

                    foreach ($tagNames as $tagName) {
                        if ($tagName === '') {
                            continue;
                        }

                        if (mb_strlen($tagName) > 50) {
                            continue;
                        }

                        $tag = Tag::firstOrCreate([
                            'tag' => $tagName,
                        ]);

                        $tag_ids[] = $tag->id;
                    }

                    if (!empty($tag_ids) && method_exists($product, 'tags')) {
                        $product->tags()->sync($tag_ids);
                    }
                }

                $countRow++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Import Error: ' . $e->getMessage());
            return back();
        }

        Toastr::success($countRow . ' Products Imported Successfully!');
        return back();
    }





    










    







    

    public function bulk_image()
    {
        $images = DB::table('bulk_image')
            ->where('seller_id', auth('seller')->id())
            ->orderBy('id', 'desc')
            ->paginate(14); // ✅ per page 14 records

        return view('seller-views.product.bulk-image', compact('images'));
    }


    public function bulk_image_import(Request $request)
    {
        if ($request->hasFile('images')) {

            $seller_id = auth('seller')->id();

            foreach ($request->file('images') as $image) {

                $originalName = pathinfo(
                    $image->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                $extension = strtolower($image->getClientOriginalExtension());

                $webpName = $seller_id . '_' . $originalName . '.webp';

                $r2Folder = '/products';
                $r2Path = $r2Folder . '/' . $webpName;

                if ($extension === 'webp') {
                    $imageContent = file_get_contents($image->getRealPath());
                } 
                else {

                    $imageType = exif_imagetype($image->getPathname());

                    switch ($imageType) {

                        case IMAGETYPE_JPEG:
                            $imgResource = imagecreatefromjpeg($image->getPathname());
                            break;

                        case IMAGETYPE_PNG:
                            $imgResource = imagecreatefrompng($image->getPathname());
                            imagepalettetotruecolor($imgResource);
                            imagealphablending($imgResource, true);
                            imagesavealpha($imgResource, true);
                            break;

                        case IMAGETYPE_GIF:
                            $imgResource = imagecreatefromgif($image->getPathname());
                            break;

                        default:
                            return back()->with('error', 'Unsupported image type');
                    }

                    ob_start();
                    imagewebp($imgResource, null, 80);
                    $imageContent = ob_get_clean();
                    imagedestroy($imgResource);
                }

                Storage::disk('r2')->put($r2Path, $imageContent);
                DB::table('bulk_image')->insert([
                    'seller_id' => auth('seller')->id(),
                    'image_path' => $r2Path
                ]);
            }

            Toastr::success('All images uploaded successfully.');
            return back();
        }
        return back()->with('error', 'No images found.');
    }   

    public function bulk_export_data_category_wise()
    {
        $category_id          = session('category_id');
        $sub_category_id      = session('sub_category_id');
        $sub_sub_category_id  = session('sub_sub_category_id');
        $product_id           = session('product_id');
        $brand_id             = session('brand_id');
        $tax                  = session('tax');
        $procurement_time     = session('procurement_time');
        $hsn_code             = session('hsn_code');

        $seller_id = auth('seller')->id();
        $commission_fee = DB::table('categories')->where('id', $sub_category_id)->first();

        $commission_type = match ($commission_fee->commission_fee ?? 1) {
            1       => 'Default',
            2       => 'In Percent',
            default => 'Transfer Price',
        };

        $category          = DB::table('categories')->where('id', $category_id)->first();
        $product           = Product::where('id', $product_id)->first() ?? new Product();
        $skuProduct        = DB::table('sku_product_new')->where('product_id', $product_id)->first();
        $sub_category      = DB::table('categories')->where('id', $sub_category_id)->first();
        $sub_sub_category  = DB::table('categories')->where('id', $sub_sub_category_id)->first();
        $brand_name        = DB::table('brands')->where('id', $brand_id)->first();

        $specifications           = !empty($sub_sub_category->specification) ? explode(',', $sub_sub_category->specification) : [];
        $key_features             = !empty($sub_sub_category->key_features) ? explode(',', $sub_sub_category->key_features) : [];
        $technical_specifications = !empty($sub_sub_category->technical_specification) ? explode(',', $sub_sub_category->technical_specification) : [];
        $other_details            = !empty($sub_sub_category->other_details) ? explode(',', $sub_sub_category->other_details) : [];

        $colors = DB::table('colors')->pluck('name')->toArray();

        // ----------------- SHEET 1 BASE DATA -----------------
        $baseData = [
            'InteriorChowk Product Code' => '',
            'Catelogue QC Status'        => '',
            'QC Failed Reason (if any)'  => $product->qc_failed_reason ?? '',
            'Product Category'           => $category->name ?? '',
            'Product Sub Category'       => $sub_category->name ?? '',
            'Product Sub Sub Category'   => $sub_sub_category->name ?? '',
            'Brands'                     => $brand_name->name ?? '',
            'Seller SKU ID'              => '',
            'Commission Type'            => $commission_type,
            'Commission Fee'             => $commission_fee->commission ?? '',
            'Listing Status'             => '',
            'MRP (INR)'                  => $skuProduct->variant_mrp ?? '',
            'Discount Type'              => $skuProduct->discount_type ?? '',
            'Discount'                   => $skuProduct->discount ?? '',
            'Your selling price (INR)'   => '=IF(M2="Flat", MAX(0, L2-N2), IF(M2="Percent", MAX(0, L2-(L2*N2/100)), ""))',
            'Product Title'              => '',
            'Product Description'        => '',
            'Colour'                     => implode(', ', $colors),
            'Size'                       => '',
            'Return Days'                => '',
            'Replacement Days'           => '',
            'Unit'                       => '',
            'Free Delivery'              => '',
            'Self Delivery'              => '',
            'Warehouse'                  => 'Noida warehouse',
            'Procurement SLA (DAY)'      => $procurement_time,
            'Stock'                      => '',
            'MOQ'                        => '',
            'Packaging Dimensions'       => '',
            'HSN'                        => $hsn_code,
            'Tax'                        => $tax,
            'Thumbnail Image name'       => '',
            'Other Image name'           => '',
            'Video URL'                  => '',
            'PDF URL'                    => '',
            'Search Tags'                => '',
            'Excel error status'         => '',
        ];

        foreach ($specifications as $spec) {
            $baseData['Specification: ' . trim($spec)] = '';
        }
        foreach ($key_features as $feature) {
            $baseData['Key features: ' . trim($feature)] = '';
        }
        foreach ($technical_specifications as $tech) {
            $baseData['Technical specification: ' . trim($tech)] = '';
        }
        foreach ($other_details as $other) {
            $baseData['Other details: ' . trim($other)] = '';
        }

        // Re-add some keys at the end (same as aapke code me tha – overwrite same values)
        $baseData = array_merge($baseData, [
            'Return Days'           => '',
            'Replacement Days'      => '',
            'Unit'                  => '',
            'Free Delivery'         => '',
            'Self Delivery'         => '',
            'Warehouse'             => 'Noida warehouse',
            'Procurement SLA (DAY)' => $procurement_time,
            'Stock'                 => '',
            'MOQ'                   => '',
            'Packaging Dimensions'  => '',
            'HSN'                   => $hsn_code,
            'Tax'                   => $tax,
            'Thumbnail Image name'  => '',
            'Other Image name'      => '',
            'Video URL'             => '',
            'PDF URL'               => '',
            'Search Tags'           => '',
            'Excel error status'    => '',
        ]);

        // MAP – same as aapka code
        $map = [
            'InteriorChowk Product Code' => ['', 'To be filled by InteriorChowk'],
            'Catelogue QC Status'        => ['', 'To be filled by InteriorChowk'],
            'QC Failed Reason (if any)'  => ['', 'To be filled by InteriorChowk'],
            'Product Category'           => ['', 'To be filled by InteriorChowk'],
            'Product Sub Category'       => ['', 'To be filled by InteriorChowk'],
            'Product Sub Sub Category'   => ['', 'To be filled by InteriorChowk'],
            'Brands'                     => ['', 'To be filled by InteriorChowk'],
            'Seller SKU ID'              => [
                'Text - limited to 64 characters (including spaces)',
                'Seller SKU ID is the identification number maintained by seller to keep track of SKUs. This will be mapped with InteriorChowk product code.'
            ],
            'Commission Type'            => ['', 'To be filled by InteriorChowk'],
            'Commission Fee'            => ['', 'To be filled by InteriorChowk'],
            'Listing Status'            => ['Single - Text', 'Inactive listings are not available for buyers on InteriorChowk'],
            'MRP (INR)'                 => ['Single - Positive_integer', 'Maximum retail price of the product'],
            'Discount Type'             => ['-', 'Write flat or percent'],
            'Discount'                  => ['-', 'In Rs. Or In %'],
            'Your selling price (INR)'  => ['Single - Positive_integer', 'Price at which you want to sell this listing'],
            'Product Title'             => [
                'Single - Text Used For: Title',
                'Product Title is the identity of the product that helps in distinguishing it from other products.'
            ],
            'Product Description'       => [
                [
                    'Description',
                    'Bullet Point 1',
                    'Bullet Point 2',
                    'Bullet Point 3',
                    'Bullet Point 4',
                    'Bullet Point 5',
                ],
                [
                    'Please write few lines describing your product...',
                    'Bullet Point 1',
                    'Bullet Point 2',
                    'Bullet Point 3',
                    'Bullet Point 4',
                    'Bullet Point 5',
                ]
            ],
            'Colour'                    => [
                ['Colour Name', 'Rename Colour Name'],
                ['Eg. Silver', 'Eg. Matt Finish']
            ],
            'Size'                      => ['Add size', 'Eg. S, M, L or custom size'],

            'Specification'             => [
                ['Brand', 'Product Dimensions', 'Wattage', 'Voltage'],
                [
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                ]
            ],
            'Key Features'              => [
                ['Manufacturer', 'Packer', 'Net Quantity', 'Included Components'],
                [
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                ]
            ],
            'Technical Specification'   => [
                ['A', 'B', 'C', 'D'],
                [
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                ]
            ],
            'Other Details'             => [
                ['A', 'B', 'C', 'D'],
                [
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                    'If data is not available, mention N/A.',
                ]
            ],

            'Return Days'               => ['Number', 'Enter the number of days you want to accept returns.(Min. 3 days required)'],
            'Replacement Days'          => ['Number', 'Enter the number of days you want to accept replacement.'],
            'Unit'                      => ['-', 'Eg. - Kg, pc, gms, lts, set, Pair, Sqft, Sq Mtr., Box'],
            'Free Delivery'             => ['-', 'Yes / No'],
            'Self Delivery'             => ['-', 'If this product is heavy flammable fragile etc. Please enter yes.'],
            'Warehouse'                 => ['Noida warehouse', "Fill your warehouse id here."],
            'Procurement SLA (DAY)'     => ['Single - Number', 'Time required to keep the product ready for dispatch.'],
            'Stock'                     => ['Number', 'Number of items you have in stock. Add minimum 5 quantity to ensure listing visibility'],
            'MOQ'                       => ['Number', 'Write a minimum order quantity'],
            'Packaging Dimensions'      => [
                ['Length (CM)', 'Breadth (CM)', 'Height (CM)', 'Weight (KG)'],
                [
                    'Length of the package in cms',
                    'Breadth of the package in cms',
                    'Height of the package in cms',
                    'Weight of the final package in kgs'
                ]
            ],
            'HSN'                       => ['Single - Text', 'To be filled by InteriorChowk'],
            'Tax'                       => ['Single - Text', "InteriorChowk's tax code which decides the GST for the listing"],
            'Thumbnail Image name'      => ['Image name', '1st Image (Thumbnail): Front View – Minimum resolution 100x500'],
            'Other Image name'          => [
                'Eg.TABLELAMP01.webp,TABLELAMP02.webp',
                'Upload images in order (2nd – Back, 3rd – Open, 4th – Side, 5th – Lifestyle, 6th – Detail).'
            ],
            'Video URL'                 => ['URL', 'See the summary sheet for Video URL guidelines.'],
            'PDF URL'                   => ['URL', 'See the summary sheet for PDF URL guidelines.'],
            'Search Tags'               => ['-', 'Write search keywords and tags for the product'],
            'Excel error status'        => ['-', '❌ Error if missing, ✅ OK when all details are filled.'],
        ];

        // ----------------- CREATE SPREADSHEET -----------------
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // SHEET 1: BULK UPLOAD
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Product_Bulk');

        $keys = array_keys($baseData);

        $headerRow  = 1; // Green row
        $instRow1   = 2; // Yellow row
        $instRow2   = 3; // Orange row
        $dataRow    = 4; // Default data row

        $colIndex = 1;

        foreach ($keys as $key) {
            $value = $baseData[$key];

            // Grouped columns (jahan map me [ [..], [..] ] hai )
            if (isset($map[$key]) && is_array($map[$key][0])) {
                $subHeaders = $map[$key][0];
                $subInst    = $map[$key][1];

                $startCol = $colIndex;
                $endCol   = $colIndex + count($subHeaders) - 1;

                // Merge header (row 1)
                $sheet1->mergeCellsByColumnAndRow($startCol, $headerRow, $endCol, $headerRow);
                $sheet1->setCellValueByColumnAndRow($startCol, $headerRow, $key);

                // Row 2 – sub headers
                foreach ($subHeaders as $subHeader) {
                    $sheet1->setCellValueByColumnAndRow($colIndex, $instRow1, $subHeader);
                    $colIndex++;
                }

                // Row 3 – sub instructions
                $tempCol = $startCol;
                foreach ($subInst as $sub) {
                    $sheet1->setCellValueByColumnAndRow($tempCol, $instRow2, $sub);
                    $tempCol++;
                }

                // Row 4 – same default value repeat
                $tempCol = $startCol;
                foreach ($subHeaders as $subHeader) {
                    $sheet1->setCellValueByColumnAndRow($tempCol, $dataRow, $value);
                    $tempCol++;
                }

            } else {
                // Simple single column
                $sheet1->setCellValueByColumnAndRow($colIndex, $headerRow, $key);

                if (isset($map[$key])) {
                    $inst1 = $map[$key][0];
                    $inst2 = $map[$key][1];

                    // Row 2
                    if (is_array($inst1)) {
                        // Agar array aaya bhi toh newline join
                        $sheet1->setCellValueByColumnAndRow($colIndex, $instRow1, implode("\n", $inst1));
                    } else {
                        $sheet1->setCellValueByColumnAndRow($colIndex, $instRow1, $inst1);
                    }

                    // Row 3
                    if (is_array($inst2)) {
                        $sheet1->setCellValueByColumnAndRow($colIndex, $instRow2, implode("\n", $inst2));
                    } else {
                        $sheet1->setCellValueByColumnAndRow($colIndex, $instRow2, $inst2);
                    }
                } else {
                    // No map entry
                    $sheet1->setCellValueByColumnAndRow($colIndex, $instRow1, '-');

                    if (
                        strpos($key, 'Specification:') === 0 ||
                        strpos($key, 'Key features:') === 0 ||
                        strpos($key, 'Technical specification:') === 0 ||
                        strpos($key, 'Other details:') === 0
                    ) {
                        $sheet1->setCellValueByColumnAndRow($colIndex, $instRow2, 'If data is not available, mention N/A.');
                    } else {
                        $sheet1->setCellValueByColumnAndRow($colIndex, $instRow2, '-');
                    }
                }

                // Default value row
                $sheet1->setCellValueByColumnAndRow($colIndex, $dataRow, $value);
                $colIndex++;
            }
        }

        $lastColIndex = $colIndex - 1;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);

        // STYLING SHEET 1
        // Header row (Row 1 – Green)
        $sheet1->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'C4D79B'],
            ],
        ]);

        // Row 2 – Yellow
        $sheet1->getStyle("A{$instRow1}:{$lastColLetter}{$instRow1}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'],
            ],
        ]);

        // Row 3 – Orange + Red text
        $sheet1->getStyle("A{$instRow2}:{$lastColLetter}{$instRow2}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'name' => 'Calibri',
                'color' => ['rgb' => 'FF0000'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FCD5B4'],
            ],
        ]);

        // Row 4 – data row
        $sheet1->getStyle("A{$dataRow}:{$lastColLetter}{$dataRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'font' => [
                'size' => 10,
                'name' => 'Calibri',
            ],
        ]);

        // Column autosize
        for ($i = 1; $i <= $lastColIndex; $i++) {
            $sheet1->getColumnDimensionByColumn($i)->setAutoSize(false);
            $sheet1->getColumnDimensionByColumn($i)->setWidth(22);
        }

        // ----------------- SHEET 2: GUIDELINE (AS-IS FROM YOUR CODE) -----------------
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Guideline');

        // Columns width
        $sheet2->getColumnDimension('A')->setWidth(80);
        $sheet2->getColumnDimension('B')->setWidth(45);

        // ROW 1
        $sheet2->mergeCells('A1:B1');
        $sheet2->setCellValue(
            'A1',
            '📘 InteriorChowk Bulk Product Listing – Seller Guidelines'
        );

        $sheet2->getStyle('A1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 24,
                'name'  => 'Calibri',
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            ],
        ]);
        $sheet2->getRowDimension(1)->setRowHeight(24);

        $sheet2->mergeCells('A2:B3');
        $sheet2->setCellValue(
            'A2',
            "Please read these instructions carefully before filling and uploading the bulk product listing Excel file.Following these guidelines will help your products go live faster and avoid QC rejections."
        );

        $sheet2->getStyle('A2')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 11,
                'name'  => 'Calibri',
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
        ]);

        $sheet2->getRowDimension(2)->setRowHeight(30);

        //Row 2
        $sheet2->mergeCells('A5:B5');
        $sheet2->setCellValue('A5', 'IMPORTANT GENERAL RULES');

        $sheet2->getStyle('A5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(5)->setRowHeight(24);

        $sheet2->mergeCells('A6:B6');

        $rulesText =
            "- Do not change any prefilled columns.\n" .
            "- One Excel file supports only ONE category at a time.\n" .
            "- Each SKU, color, and size variation must be added in a separate row.\n" .
            "- Ensure all mandatory fields are filled correctly before upload.\n" .
            "- Incorrect or missing data may cause QC failure or upload errors.";

        $sheet2->setCellValue('A6', $rulesText);

        $sheet2->getStyle('A6')->applyFromArray([
            'font' => [
                'bold' => false,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 6; $r <= 6; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(70);
        }

        // ROW 3
        $sheet2->mergeCells('A9:B9');
        $sheet2->setCellValue('A9', 'COLUMNS PREFILLED / MANAGED BY INTERIORCHOWK');

        $sheet2->getStyle('A9')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 18,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(9)->setRowHeight(20);

        $sheet2->mergeCells('A10:B10');

        $rulesText =
            "(Seller should NOT edit these columns)";

        $sheet2->setCellValue('A10', $rulesText);

        $sheet2->getStyle('A10')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 9; $r <= 10; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(18);
        }

        //Row 4
        $sheet2->mergeCells('A12:B12');
        $sheet2->setCellValue('A12', 'Column A – InteriorChowk Product Code');

        $sheet2->getStyle('A12')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(12)->setRowHeight(18);

        $sheet2->mergeCells('A13:B13');

        $rulesText =
            "- Auto-generated by InteriorChowk after upload.\n" .
            "- Do not enter any value.\n";

        $sheet2->setCellValue('A13', $rulesText);

        $sheet2->getStyle('A13')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 12; $r <= 14; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(25);
        }

        //Row 5
        $sheet2->mergeCells('A16:B16');
        $sheet2->setCellValue('A16', 'Column B – Catalogue QC Status');

        $sheet2->getStyle('A16')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(16)->setRowHeight(18);

        $sheet2->mergeCells('A17:B17');

        $rulesText =
            "- Shows product approval status after review.\n" .
            "Pass = Product is live\n" .
            "Fail = Product is inactive";

        $sheet2->setCellValue('A17', $rulesText);

        $sheet2->getStyle('A17')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 16; $r <= 17; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(45);
        }

        //Row 6
        $sheet2->mergeCells('A19:B19');
        $sheet2->setCellValue('A19', 'Column C – QC Failed Reason (If Any)');

        $sheet2->getStyle('A19')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(19)->setRowHeight(18);

        $sheet2->mergeCells('A20:B20');

        $rulesText =
            "- Displays the reason for QC failure (e.g. wrong image, missing details).";

        $sheet2->setCellValue('A20', $rulesText);

        $sheet2->getStyle('A20')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 19; $r <= 20; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(20);
        }

        //Row 7
        $sheet2->mergeCells('A22:B22');
        $sheet2->setCellValue('A22', 'Column D – Product Category');

        $sheet2->getStyle('A22')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(22)->setRowHeight(18);

        $sheet2->mergeCells('A23:B23');

        $rulesText =
            "- Prefilled while downloading the format.\n" .
            "- Do not edit.\n" .
            "- Only one category per bulk upload is allowed.";

        $sheet2->setCellValue('A23', $rulesText);

        $sheet2->getStyle('A23')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 22; $r <= 23; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(35);
        }

        //Row 8
        $sheet2->mergeCells('A25:B25');
        $sheet2->setCellValue('A25', 'Product Sub Category');

        $sheet2->getStyle('A25')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(25)->setRowHeight(18);

        $sheet2->mergeCells('A26:B26');

        $rulesText =
            "- Prefilled automatically.\n" .
            "- Do not change.";

        $sheet2->setCellValue('A26', $rulesText);

        $sheet2->getStyle('A26')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 25; $r <= 26; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(25);
        }

        //Row 9
        $sheet2->mergeCells('A28:B28');
        $sheet2->setCellValue('A28', 'Brand');

        $sheet2->getStyle('A28')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(28)->setRowHeight(18);

        $sheet2->mergeCells('A29:B29');

        $rulesText =
            "- Prefilled while downloading the Excel file.\n" .
            "- Do not edit.";

        $sheet2->setCellValue('A29', $rulesText);

        $sheet2->getStyle('A29')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 28; $r <= 29; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(25);
        }

        //Row 10
        $sheet2->mergeCells('A31:B31');
        $sheet2->setCellValue('A31', 'Excel Error Status');

        $sheet2->getStyle('A31')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(31)->setRowHeight(18);

        $sheet2->mergeCells('A32:B32');

        $rulesText =
            "If any mandatory data is missing or incorrect, an error will be shown during upload.\n" .
            "Fix the error or remove that product row before re-uploading.";

        $sheet2->setCellValue('A32', $rulesText);

        $sheet2->getStyle('A32')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 30; $r <= 32; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(25);
        }

        // Row 11 – Commission Type
        $sheet2->mergeCells('A34:B34');
        $sheet2->setCellValue('A34', 'Commission Type (Defines how commission is calculated)');

        $sheet2->getStyle('A34')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(34)->setRowHeight(18);

        $sheet2->mergeCells('A35:B35');

        $rulesText =
            "- Category % (Default)\n" .
            "- Transfer Price\n" .
            "- Fixed Percentage (same % for all categories)";

        $sheet2->setCellValue('A35', $rulesText);

        $sheet2->getStyle('A35')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 34; $r <= 35; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(30);
        }

        //Row 12
        $sheet2->mergeCells('A37:B37');
        $sheet2->setCellValue('A37', 'Commission Fee');

        $sheet2->getStyle('A37')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(37)->setRowHeight(18);

        $sheet2->mergeCells('A38:B38');

        $rulesText =
            "- Commission percentage charged by InteriorChowk on sale.";

        $sheet2->setCellValue('A38', $rulesText);

        $sheet2->getStyle('A38')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 37; $r <= 38; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(20);
        }

        //Row 13
        $sheet2->mergeCells('A40:B40');
        $sheet2->setCellValue('A40', 'Listing Status');

        $sheet2->getStyle('A40')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(40)->setRowHeight(18);

        $sheet2->mergeCells('A41:B41');

        $rulesText =
            "- Shows whether product is Active or Inactive on the website.";

        $sheet2->setCellValue('A41', $rulesText);

        $sheet2->getStyle('A41')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 40; $r <= 41; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(20);
        }

        //Row 14
        $sheet2->mergeCells('A43:B43');
        $sheet2->setCellValue('A43', 'Warehouse');

        $sheet2->getStyle('A43')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(43)->setRowHeight(18);

        $sheet2->mergeCells('A44:B44');

        $rulesText =
            "- Prefilled warehouse ID for product pickup.\n" .
            "- Do not edit.";

        $sheet2->setCellValue('A44', $rulesText);

        $sheet2->getStyle('A44')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 43; $r <= 44; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(35);
        }

        //Row 15
        $sheet2->mergeCells('A46:B46');
        $sheet2->setCellValue('A46', 'Procurement SLA (Days)');

        $sheet2->getStyle('A46')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(46)->setRowHeight(18);

        $sheet2->mergeCells('A47:B47');

        $rulesText =
            "- Number of days required for product pickup from warehouse.";

        $sheet2->setCellValue('A47', $rulesText);

        $sheet2->getStyle('A47')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 46; $r <= 47; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(20);
        }

        //Row 16
        $sheet2->mergeCells('A49:B49');
        $sheet2->setCellValue('A49', 'HSN Code');

        $sheet2->getStyle('A49')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(49)->setRowHeight(18);

        $sheet2->mergeCells('A50:B50');

        $rulesText =
            "- Prefilled HSN code.\n" .
            "- Do not edit.";

        $sheet2->setCellValue('A50', $rulesText);

        $sheet2->getStyle('A50')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 49; $r <= 50; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(25);
        }

        //Row 17
        $sheet2->mergeCells('A52:B52');
        $sheet2->setCellValue('A52', 'Tax (%)');

        $sheet2->getStyle('A52')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(52)->setRowHeight(18);

        $sheet2->mergeCells('A53:B53');

        $rulesText =
            "- GST percentage based on HSN.\n" .
            "- Do not edit.";

        $sheet2->setCellValue('A53', $rulesText);

        $sheet2->getStyle('A53')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 52; $r <= 53; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(25);
        }

        // Row 18
        $sheet2->mergeCells('A55:B55');
        $sheet2->setCellValue(
            'A55',
            'COLUMNS TO BE FILLED SELLER'
        );

        $sheet2->getStyle('A55')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 24,
                'name'  => 'Calibri',
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            ],
        ]);
        $sheet2->getRowDimension(55)->setRowHeight(24);

        //Row 19 – Seller SKU ID
        $sheet2->mergeCells('A57:B57');
        $sheet2->setCellValue('A57', 'Seller SKU ID');

        $sheet2->getStyle('A57')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(57)->setRowHeight(18);

        $sheet2->mergeCells('A58:B58');

        $rulesText =
            "- Max 64 characters (including spaces).\n" .
            "- Must be unique for every product and variation.\n" .
            "- Example: IC-SOFA-GREEN-L";

        $sheet2->setCellValue('A58', $rulesText);

        $sheet2->getStyle('A58')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        for ($r = 58; $r <= 58; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(40);
        }

        // Row 21 – Colour Name
        $sheet2->mergeCells('A61:B61');
        $sheet2->setCellValue('A61', 'Colour Name');

        $sheet2->getStyle('A61')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(61)->setRowHeight(18);

        $sheet2->mergeCells('A62:B62');

        $rulesText =
            "- Select only ONE color from the available list.\n" .
            "- Remove all other colors from that cell.\n\n" .
            "If a product has multiple colors:\n" .
            "- Add the product in multiple rows\n" .
            "- Change SKU, Color, Images for each row\n" .
            "Example:\n" .
            "Product A – Green (Row 1)\n" .
            "Product A – Blue (Row 2)";

        $sheet2->setCellValue('A62', $rulesText);

        $sheet2->getStyle('A62')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(62)->setRowHeight(100);

        // Row 22 – Rename Colour Name
        $sheet2->mergeCells('A64:B64');
        $sheet2->setCellValue('A64', 'Rename Colour Name');

        $sheet2->getStyle('A64')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(64)->setRowHeight(18);

        $sheet2->mergeCells('A65:B65');

        $rulesText =
            "Use this if the shade is different.\n\n" .
            "Example:\n" .
            "Colour Name: Silver\n" .
            "Rename Colour Name: Matt Silver";

        $sheet2->setCellValue('A65', $rulesText);

        $sheet2->getStyle('A65')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(65)->setRowHeight(80);

        // Row 23 – SIZE
        $sheet2->mergeCells('A67:B67');
        $sheet2->setCellValue('A67', 'SIZE');

        $sheet2->getStyle('A67')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(67)->setRowHeight(18);

        $sheet2->mergeCells('A68:B68');

        $rulesText =
            "Enter only ONE size per row.\n" .
            "Examples: S, M, L, Small, Large, Custom\n\n" .
            "For multiple sizes:\n" .
            "- Duplicate the product row\n" .
            "- Change SKU, Size, Images";

        $sheet2->setCellValue('A68', $rulesText);

        $sheet2->getStyle('A68')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(68)->setRowHeight(90);

        // Row 24 – Return Days
        $sheet2->mergeCells('A70:B70');
        $sheet2->setCellValue('A70', 'Return Days');

        $sheet2->getStyle('A70')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(70)->setRowHeight(18);

        $sheet2->mergeCells('A71:B71');

        $rulesText =
            "- Number of days seller accepts returns.\n" .
            "- Minimum: 3 days";

        $sheet2->setCellValue('A71', $rulesText);

        $sheet2->getStyle('A71')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(71)->setRowHeight(45);

        // Row 25 – Replacement Days
        $sheet2->mergeCells('A73:B73');
        $sheet2->setCellValue('A73', 'Replacement Days');

        $sheet2->getStyle('A73')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(73)->setRowHeight(18);

        $sheet2->mergeCells('A74:B74');

        $rulesText =
            "- Number of days seller accepts replacements.";

        $sheet2->setCellValue('A74', $rulesText);

        $sheet2->getStyle('A74')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(74)->setRowHeight(35);

        // Row 26 – UNIT
        $sheet2->mergeCells('A76:B76');
        $sheet2->setCellValue('A76', 'UNIT');

        $sheet2->getStyle('A76')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(76)->setRowHeight(18);

        $sheet2->mergeCells('A77:B77');

        $rulesText =
            "Enter the selling unit.\n\n" .
            "Examples:\n" .
            "- Kg, Pc, Gms, Lts, Set, Pair, Sqft, Sq Mtr, Box";

        $sheet2->setCellValue('A77', $rulesText);

        $sheet2->getStyle('A77')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(77)->setRowHeight(60);

        // Row 27 – IMAGE GUIDELINES
        $sheet2->mergeCells('A79:B79');
        $sheet2->setCellValue('A79', 'IMAGE GUIDELINES (VERY IMPORTANT)');

        $sheet2->getStyle('A79')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(79)->setRowHeight(22);

        $sheet2->mergeCells('A80:B80');

        $rulesText =
            "- Before adding image names\n" .
            "- Login to Seller Panel\n" .
            "- Go to Products → Add Products → Bulk Images\n" .
            "- Upload all product images\n" .
            "- Copy the image path/name\n" .
            "- Paste it into the Excel file";

        $sheet2->setCellValue('A80', $rulesText);

        $sheet2->getStyle('A80')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(80)->setRowHeight(110);

        // Row 28 – Thumbnail Image Name
        $sheet2->mergeCells('A82:B82');
        $sheet2->setCellValue('A82', 'Thumbnail Image Name');

        $sheet2->getStyle('A82')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(82)->setRowHeight(18);

        $sheet2->mergeCells('A83:B83');

        $rulesText =
            "- Main product image.\n" .
            "- Enter only one image name.";

        $sheet2->setCellValue('A83', $rulesText);

        $sheet2->getStyle('A83')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(83)->setRowHeight(40);

        // Row 29 – Other Image Name
        $sheet2->mergeCells('A85:B85');
        $sheet2->setCellValue('A85', 'Other Image Name');

        $sheet2->getStyle('A85')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(85)->setRowHeight(18);

        $sheet2->mergeCells('A86:B86');

        $rulesText =
            "- First paste the thumbnail image name again\n" .
            "- Then add other images separated by comma\n\n" .
            "Example: image1.jpg,image2.jpg,image3.jpg";

        $sheet2->setCellValue('A86', $rulesText);

        $sheet2->getStyle('A86')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(86)->setRowHeight(70);

        // Row 30 – VIDEO URL
        $sheet2->mergeCells('A88:B88');
        $sheet2->setCellValue('A88', 'VIDEO URL');

        $sheet2->getStyle('A88')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(88)->setRowHeight(18);

        $sheet2->mergeCells('A89:B89');

        $rulesText =
            "- Paste YouTube embed link (if available).\n" .
            "- Used for product demos, unboxing, or reviews.";

        $sheet2->setCellValue('A89', $rulesText);

        $sheet2->getStyle('A89')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(89)->setRowHeight(45);

        // Row 31 – PDF URL
        $sheet2->mergeCells('A91:B91');
        $sheet2->setCellValue('A91', 'PDF URL');

        $sheet2->getStyle('A91')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(91)->setRowHeight(18);

        $sheet2->mergeCells('A92:B92');

        $rulesText =
            "- Upload product information images or brochures (horizontal format preferred).\n" .
            "- Paste the PDF link here.";

        $sheet2->setCellValue('A92', $rulesText);

        $sheet2->getStyle('A92')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(92)->setRowHeight(60);

        // Row 32 – SEARCH TAGS
        $sheet2->mergeCells('A94:B94');
        $sheet2->setCellValue('A94', 'SEARCH TAGS');

        $sheet2->getStyle('A94')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(94)->setRowHeight(18);

        $sheet2->mergeCells('A95:B95');

        $rulesText =
            "- Keywords to help customers find your product easily.\n" .
            "- Use comma-separated words.\n\n" .
            "Example: Modern sofa, living room sofa, wooden sofa";

        $sheet2->setCellValue('A95', $rulesText);

        $sheet2->getStyle('A95')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(95)->setRowHeight(70);

        // Row 33 – OTHER DETAILS (VERY IMPORTANT)
        $sheet2->mergeCells('A97:B97');
        $sheet2->setCellValue('A97', 'OTHER DETAILS (VERY IMPORTANT)');

        $sheet2->getStyle('A97')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(97)->setRowHeight(22);

        $sheet2->mergeCells('A98:B98');

        $rulesText =
            "Add:\n" .
            "- Specifications\n" .
            "- Key features\n" .
            "- Technical details\n" .
            "- Material, finish, usage, warranty, etc.\n\n" .
            "These details:\n" .
            "- Appear on product page\n" .
            "- Help customers filter products easily\n\n" .
            "Tip: Use short, clear keywords so customers can easily find your product through filters.";

        $sheet2->setCellValue('A98', $rulesText);

        $sheet2->getStyle('A98')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(98)->setRowHeight(140);

        // Row 34 – FINAL CHECK BEFORE UPLOAD
        $sheet2->mergeCells('A100:B100');
        $sheet2->setCellValue('A100', 'FINAL CHECK BEFORE UPLOAD');

        $sheet2->getStyle('A100')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet2->getRowDimension(100)->setRowHeight(22);

        $sheet2->mergeCells('A101:B101');

        $rulesText =
            "- All mandatory fields filled\n" .
            "- Unique SKU for each variation\n" .
            "- Correct images uploaded and mapped\n" .
            "- One category only per Excel file";

        $sheet2->setCellValue('A101', $rulesText);

        $sheet2->getStyle('A101')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $sheet2->getRowDimension(101)->setRowHeight(70);

        // ---------------- OUTPUT ----------------
        $fileName = 'products_bulk.xlsx';

        // Clean output buffer (optional but recommended before sending headers)
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    public function bulk_export_data()
    {
        $sellerId = auth('seller')->id();
        $seller = DB::table('sellers')->where('id', $sellerId)->first();

        $products = Product::where([
            'added_by' => 'seller',
            'user_id'  => $sellerId
        ])->get();

        $brandMap = DB::table('brands')->pluck('name', 'id')->toArray();
        $catMap   = DB::table('categories')->pluck('name', 'id')->toArray();

        $skuRows = DB::table('sku_product_new')
            ->where('seller_id', $sellerId)
            ->whereIn('product_id', $products->pluck('id')->toArray())
            ->get()
            ->groupBy('product_id');

        $productIds = $products->pluck('id')->toArray();

        $productTagsMap = DB::table('product_tag as pt')
            ->join('tags as t', 't.id', '=', 'pt.tag_id')
            ->whereIn('pt.product_id', $productIds)
            ->select('pt.product_id', 't.tag')
            ->get()
            ->groupBy('product_id')
            ->map(function ($rows) {
                return $rows->pluck('tag')->filter()->unique()->implode(', ');
            })
            ->toArray();

        $colors = DB::table('colors')->pluck('name')->toArray();

        $baseData = [
            'InteriorChowk Product Code' => '',
            'Catelogue QC Status'        => '',
            'QC Failed Reason (if any)'  => '',
            'Product Category'           => '',
            'Product Sub Category'       => '',
            'Product Sub Sub Category'   => '',
            'Brands'                     => '',
            'Seller SKU ID'              => '',
            'Commission Type'            => '',
            'Commission Fee'             => '',
            'Listing Status'             => '',
            'MRP (INR)'                  => '',
            'Discount Type'              => '',
            'Discount'                   => '',
            'Your selling price (INR)'   => '',
            'Product Title'              => '',
            'Product Description'        => '',
            'Colour'                     => implode(', ', $colors),
            'Size'                       => '',
            'Return Days'                => '',
            'Replacement Days'           => '',
            'Unit'                       => '',
            'Free Delivery'              => '',
            'Self Delivery'              => '',
            'Warehouse'                  => '',
            'Procurement SLA (DAY)'      => '',
            'Stock'                      => '',
            'MOQ'                        => '',
            'Packaging Dimensions'       => '',
            'HSN'                        => '',
            'Tax'                        => '',
            'Thumbnail Image name'       => '',
            'Other Image name'           => '',
            'Video URL'                  => '',
            'PDF URL'                    => '',
            'Search Tags'                => '',
            'Excel error status'         => '',
        ];

        $map = [
            'InteriorChowk Product Code' => ['', 'To be filled by InteriorChowk'],
            'Catelogue QC Status'        => ['', 'To be filled by InteriorChowk'],
            'QC Failed Reason (if any)'  => ['', 'To be filled by InteriorChowk'],
            'Product Category'           => ['', 'To be filled by InteriorChowk'],
            'Product Sub Category'       => ['', 'To be filled by InteriorChowk'],
            'Product Sub Sub Category'   => ['', 'To be filled by InteriorChowk'],
            'Brands'                     => ['', 'To be filled by InteriorChowk'],
            'Seller SKU ID'              => [
                'Text - limited to 64 characters (including spaces)',
                'Seller SKU ID is the identification number maintained by seller to keep track of SKUs. This will be mapped with InteriorChowk product code.'
            ],
            'Commission Type'            => ['', 'To be filled by InteriorChowk'],
            'Commission Fee'             => ['', 'To be filled by InteriorChowk'],
            'Listing Status'             => ['Single - Text', 'Inactive listings are not available for buyers on InteriorChowk'],
            'MRP (INR)'                  => ['Single - Positive_integer', 'Maximum retail price of the product'],
            'Discount Type'              => ['-', 'Write flat or percent'],
            'Discount'                   => ['-', 'In Rs. Or In %'],
            'Your selling price (INR)'   => ['Single - Positive_integer', 'Price at which you want to sell this listing'],
            'Product Title'              => [
                'Single - Text Used For: Title',
                'Product Title is the identity of the product that helps in distinguishing it from other products.'
            ],
            'Product Description' => [
                [
                    'Description',
                    'Bullet Point 1',
                    'Bullet Point 2',
                    'Bullet Point 3',
                    'Bullet Point 4',
                    'Bullet Point 5',
                ],
                [
                    'Please write few lines describing your product...',
                    'Bullet Point 1',
                    'Bullet Point 2',
                    'Bullet Point 3',
                    'Bullet Point 4',
                    'Bullet Point 5',
                ]
            ],
            'Colour'                     => [
                ['Colour Name', 'Rename Colour Name'],
                ['Eg. Silver', 'Eg. Matt Finish']
            ],
            'Size'                       => ['Add size', 'Eg. S, M, L or custom size'],
            'Return Days'                => ['Number', 'Enter the number of days you want to accept returns.(Min. 3 days required)'],
            'Replacement Days'           => ['Number', 'Enter the number of days you want to accept replacement.'],
            'Unit'                       => ['-', 'Eg. - Kg, pc, gms, lts, set, Pair, Sqft, Sq Mtr., Box'],
            'Free Delivery'              => ['-', 'Yes / No'],
            'Self Delivery'              => ['-', 'If this product is heavy flammable fragile etc. Please enter yes.'],
            'Warehouse'                  => ['Noida warehouse', "Fill your warehouse id here."],
            'Procurement SLA (DAY)'      => ['Single - Number', 'Time required to keep the product ready for dispatch.'],
            'Stock'                      => ['Number', 'Number of items you have in stock. Add minimum 5 quantity to ensure listing visibility'],
            'MOQ'                        => ['Number', 'Write a minimum order quantity'],
            'Packaging Dimensions'       => [
                ['Length (CM)', 'Breadth (CM)', 'Height (CM)', 'Weight (KG)'],
                [
                    'Length of the package in cms',
                    'Breadth of the package in cms',
                    'Height of the package in cms',
                    'Weight of the final package in kgs'
                ]
            ],
            'HSN'                        => ['Single - Text', 'To be filled by InteriorChowk'],
            'Tax'                        => ['Single - Text', "InteriorChowk's tax code which decides the GST for the listing"],
            'Thumbnail Image name'       => ['Image name', '1st Image (Thumbnail): Front View – Minimum resolution 100x500'],
            'Other Image name'           => [
                'Eg.TABLELAMP01.webp,TABLELAMP02.webp',
                'Upload images in order (2nd – Back, 3rd – Open, 4th – Side, 5th – Lifestyle, 6th – Detail).'
            ],
            'Video URL'                  => ['URL', 'See the summary sheet for Video URL guidelines.'],
            'PDF URL'                    => ['URL', 'See the summary sheet for PDF URL guidelines.'],
            'Search Tags'                => ['-', 'Write search keywords and tags for the product'],
            'Excel error status'         => ['-', '❌ Error if missing, ✅ OK when all details are filled.'],
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Product_Bulk');

        $headerRow = 1; $instRow1 = 2; $instRow2 = 3; $dataStart = 4;

        $keys = array_keys($baseData);
        $colIndex = 1;

        foreach ($keys as $key) {
            $value = $baseData[$key];

            if (isset($map[$key]) && is_array($map[$key][0])) {
                $subHeaders = $map[$key][0];
                $subInst    = $map[$key][1];

                $startCol = $colIndex;
                $endCol   = $colIndex + count($subHeaders) - 1;

                $sheet1->mergeCellsByColumnAndRow($startCol, $headerRow, $endCol, $headerRow);
                $sheet1->setCellValueByColumnAndRow($startCol, $headerRow, $key);

                foreach ($subHeaders as $subHeader) {
                    $sheet1->setCellValueByColumnAndRow($colIndex, $instRow1, $subHeader);
                    $colIndex++;
                }

                $tempCol = $startCol;
                foreach ($subInst as $sub) {
                    $sheet1->setCellValueByColumnAndRow($tempCol, $instRow2, $sub);
                    $tempCol++;
                }

                $tempCol = $startCol;
                foreach ($subHeaders as $_) {
                    $sheet1->setCellValueByColumnAndRow($tempCol, $dataStart, $value);
                    $tempCol++;
                }
            } else {
                $sheet1->setCellValueByColumnAndRow($colIndex, $headerRow, $key);

                if (isset($map[$key])) {
                    $inst1 = $map[$key][0];
                    $inst2 = $map[$key][1];
                    $sheet1->setCellValueByColumnAndRow($colIndex, $instRow1, is_array($inst1) ? implode("\n", $inst1) : $inst1);
                    $sheet1->setCellValueByColumnAndRow($colIndex, $instRow2, is_array($inst2) ? implode("\n", $inst2) : $inst2);
                } else {
                    $sheet1->setCellValueByColumnAndRow($colIndex, $instRow1, '-');
                    $sheet1->setCellValueByColumnAndRow($colIndex, $instRow2, '-');
                }

                $sheet1->setCellValueByColumnAndRow($colIndex, $dataStart, $value);
                $colIndex++;
            }
        }

        $lastColIndex  = $colIndex - 1;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);

        // Styles for header + instruction rows
        $sheet1->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true,'size' => 11,'name' => 'Calibri'],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor' => ['rgb' => 'C4D79B']],
        ]);

        $sheet1->getStyle("A{$instRow1}:{$lastColLetter}{$instRow1}")->applyFromArray([
            'font' => ['bold' => true,'size' => 10,'name' => 'Calibri'],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor' => ['rgb' => 'FFFF00']],
        ]);

        $sheet1->getStyle("A{$instRow2}:{$lastColLetter}{$instRow2}")->applyFromArray([
            'font' => ['bold' => true,'size' => 10,'name' => 'Calibri','color' => ['rgb' => 'FF0000']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor' => ['rgb' => 'FCD5B4']],
        ]);

        $headerToCol = [];
        for ($c = 1; $c <= $lastColIndex; $c++) {
            $h = trim((string)$sheet1->getCellByColumnAndRow($c, $headerRow)->getValue());
            if ($h !== '') $headerToCol[$h] = $c;
        }

        $dimToCol = [];
        for ($c = 1; $c <= $lastColIndex; $c++) {
            $h = trim((string)$sheet1->getCellByColumnAndRow($c, $instRow1)->getValue());
            if ($h !== '') $dimToCol[$h] = $c;
        }

        $rowIndex = $dataStart;

        foreach ($products as $p) {

            $category_id = $sub_category_id = $sub_sub_category_id = null;
            foreach (json_decode($p->category_ids, true) ?? [] as $cat) {
                if (($cat['position'] ?? null) == 1) $category_id = (int)($cat['id'] ?? 0);
                if (($cat['position'] ?? null) == 2) $sub_category_id = (int)($cat['id'] ?? 0);
                if (($cat['position'] ?? null) == 3) $sub_sub_category_id = (int)($cat['id'] ?? 0);
            }

            $categoryName    = $catMap[$category_id] ?? '';
            $subCategoryName = $catMap[$sub_category_id] ?? '';
            $subSubCatName   = $catMap[$sub_sub_category_id] ?? '';

            $brandName = $brandMap[$p->brand_id] ?? '';

            $thumb = $p->thumbnail ?? '';
            $otherImages = [];
            if (!empty($p->images)) {
                $decoded = json_decode($p->images, true);
                if (is_array($decoded)) $otherImages = $decoded;
            }
            $other = !empty($otherImages) ? implode(',', $otherImages) : '';

            $variants = $skuRows[$p->id] ?? collect([]);

            if ($variants->count() === 0) {
                $variants = collect([(object)[
                    'sku' => '',
                    'variant_mrp' => $p->unit_price ?? '',
                    'discount_type' => $p->discount_type ?? '',
                    'discount' => $p->discount ?? '',
                    'quantity' => $p->current_stock ?? 0,
                    'sizes' => '',
                    'color_name' => '',
                    'tax' => $p->tax ?? '',
                    'length' => $p->length ?? 0,
                    'breadth' => $p->breadth ?? 0,
                    'height' => $p->height ?? 0,
                    'weight' => $p->weight ?? 0,
                    'thumbnail_image' => $p->thumbnail ?? null,
                ]]);
            }

            $commissionTypeMap = [
                1 => 'Default',
                2 => 'In Percent',
                3 => 'Transfer Price',
            ];

            // NOTE: your code uses commission_fee as type; keeping same logic
            $commissionType = $commissionTypeMap[(int)($seller->commission_fee ?? 1)] ?? 'Default';

            $CatelogueQCStatus = [
                1 => 'Active',
                2 => 'InActive',
            ];
            $statusqc = $CatelogueQCStatus[(int)($p->status ?? 1)] ?? 'Active';

            foreach ($variants as $v) {

                $set = function($header, $val) use ($sheet1, $headerToCol, $rowIndex) {
                    if (isset($headerToCol[$header])) {
                        $sheet1->setCellValueByColumnAndRow($headerToCol[$header], $rowIndex, $val);
                    }
                };
                $setDim = function($dimHeader, $val) use ($sheet1, $dimToCol, $rowIndex) {
                    if (isset($dimToCol[$dimHeader])) {
                        $sheet1->setCellValueByColumnAndRow($dimToCol[$dimHeader], $rowIndex, $val);
                    }
                };

                // ---- Parse description + bullets from HTML/details ----
                $parsed = $this->parseDescriptionBullets($p->details ?? '');

                // Basic fields
                $set('InteriorChowk Product Code', $p->id);
                $set('Product Title', $p->name);
                $set('Brands', $brandName);

                $set('Catelogue QC Status', is_null($p->qc_failed_reason) ? 'Approved' : 'Rejected');
                $set('QC Failed Reason (if any)', $p->qc_failed_reason);
                $set('Search Tags', $productTagsMap[$p->id] ?? '');

                $set('Product Category', $categoryName);
                $set('Product Sub Category', $subCategoryName);
                $set('Product Sub Sub Category', $subSubCatName);

                $set('Return Days', $p->Return_days);
                $set('Replacement Days', $p->replacement_days);

                $set('Seller SKU ID', $v->sku ?? '');
                $set('Commission Type', $commissionType);
                $set('Commission Fee', $v->commission_fee ?? '');
                $set('Listing Status', $statusqc ?? '');

                $set('MRP (INR)', $v->variant_mrp ?? '');
                $set('Discount Type', $v->discount_type ?? '');
                $set('Discount', $v->discount ?? '');
                $set('Tax', $v->tax ?? ($p->tax ?? ''));

                $set(
                    'Your selling price (INR)',
                    "=IF(M{$rowIndex}=\"Flat\", MAX(0, L{$rowIndex}-N{$rowIndex}), IF(M{$rowIndex}=\"Percent\", MAX(0, L{$rowIndex}-(L{$rowIndex}*N{$rowIndex}/100)), \"\"))"
                );

                $set('Stock', (int)($v->quantity ?? 0));
                $set('MOQ', $p->min_qty ?? 1);
                $set('Unit', $p->unit ?? 'pc');
                $set('Warehouse', $p->add_warehouse ?? '');
                $set('HSN', $p->HSN_code ?? '');
                $set('Free Delivery', ((int)($p->free_shipping ?? 0) === 1) ? 'Yes' : 'No');

                $set('Video URL', $p->video_url ?? '');
                $set('Thumbnail Image name', $v->thumbnail_image ?? $thumb);
                $set('Other Image name', $other);

                // Grouped: description + bullet points
                $setDim('Description', $parsed['description']);

                for ($b = 1; $b <= 5; $b++) {
                    $setDim("Bullet Point {$b}", $parsed['bullets'][$b - 1] ?? '');
                }

                // Grouped: color + size
                $setDim('Colour Name', $v->color_name ?? '');
                $setDim('Rename Colour Name', $v->color_name ?? '');
                $setDim('Add size', $v->sizes ?? '');
                $set('Size', $v->sizes ?? '');

                // Packaging grouped
                $setDim('Length (CM)',  (float)($v->length ?? $p->length ?? 0));
                $setDim('Breadth (CM)', (float)($v->breadth ?? $p->breadth ?? 0));
                $setDim('Height (CM)',  (float)($v->height ?? $p->height ?? 0));
                $setDim('Weight (KG)',  (float)($v->weight ?? $p->weight ?? 0));

                $rowIndex++;
            }
        }

        $lastRow = max($dataStart, $rowIndex - 1);

        $sheet1->getStyle("A{$dataStart}:{$lastColLetter}{$lastRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'font' => ['size' => 10,'name' => 'Calibri'],
        ]);

        $fixedWidth = 22; // aap change kar sakte ho
        for ($i = 1; $i <= $lastColIndex; $i++) {
            $sheet1->getColumnDimensionByColumn($i)->setAutoSize(false);
            $sheet1->getColumnDimensionByColumn($i)->setWidth($fixedWidth);
        }

        $sheet1->getStyle("A{$headerRow}:{$lastColLetter}{$lastRow}")
            ->getAlignment()
            ->setWrapText(true);

        for ($r = $headerRow; $r <= $lastRow; $r++) {
            $sheet1->getRowDimension($r)->setRowHeight(-1); // auto height
        }

        // OUTPUT
        $fileName = 'products_bulk.xlsx';
        if (ob_get_length()) ob_end_clean();

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }


    private function parseDescriptionBullets($html): array
    {
        $html = trim((string) $html);

        if ($html === '') {
            return ['description' => '', 'bullets' => []];
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
                '<?xml encoding="utf-8" ?><div id="root">'.$html.'</div>',LIBXML_HTML_NOIMPLIED |
LIBXML_HTML_NODEFDTD);

$xpath = new \DOMXPath($dom);

$bullets = [];
foreach ($xpath->query('//*[@id="root"]//ul//li | //*[@id="root"]//ol//li') as $li) {
$t = trim(preg_replace('/\s+/', ' ', $li->textContent));
if ($t !== '') $bullets[] = $t;
}

$bullets = array_values(array_unique($bullets));

$bullets = array_slice($bullets, 0, 5);

foreach ($xpath->query('//*[@id="root"]//ul | //*[@id="root"]//ol') as $listNode) {
if ($listNode->parentNode) {
$listNode->parentNode->removeChild($listNode);
}
}

foreach ($xpath->query('//*[@id="root"]//br') as $br) {
if ($br->parentNode) {
$br->parentNode->replaceChild($dom->createTextNode("\n"), $br);
}
}

$root = $xpath->query('//*[@id="root"]')->item(0);
$description = $root ? (string) $root->textContent : '';

$description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$description = preg_replace("/\r\n|\r/", "\n", $description);
$description = preg_replace("/\n{3,}/", "\n\n", $description);
$description = trim(preg_replace('/[ \t]+/', ' ', $description));

libxml_clear_errors();

return [
'description' => $description,
'bullets' => $bullets,
];
}







public function barcode(Request $request, $id)
{
if ($request->limit > 270) {
Toastr::warning(translate('You can not generate more than 270 barcode'));
return back();
}
$product = Product::findOrFail($id);
$range_data = range(1, $request->limit ?? 4);
$array_chunk = array_chunk($range_data, 24);

return view('seller-views.product.barcode', compact('product', 'array_chunk'));
}

public function save_product(Request $request)
{
DB::table('category_request')->insert([
'seller_id'=>auth('seller')->id(),
'name'=>$request->name,
'hsn_code'=>$request->hsn_code,
'created_at'=>now(),
'updated_at'=>now()
]);

return response()->json([
'message' => 'Product added successfully!',
]);

}



public function bulkimageurl()
{
return view('seller-views.product.bulk_images_upload');
}


// public function bulkimageurlupload(Request $request)
// {
// $request->validate([
// 'seller_id' => 'required|integer',
// 'file' => 'required|file|mimes:xlsx',
// ]);

// $sellerId = (int) $request->seller_id;

// set_time_limit(0);
// ini_set('memory_limit', '1024M');

// \Log::info("BulkImage: START", [
// 'seller_id' => $sellerId,
// 'file_name' => $request->file('file')->getClientOriginalName(),
// ]);

// try {
// $filePath = $request->file('file')->getRealPath();
// $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
// $sheet = $spreadsheet->getActiveSheet();

// // Excel mapping
// $idCol = 1; // A = image_id
// $urlCol = 2; // B = urls

// // ✅ safest
// $highestRow = (int) $sheet->getHighestRow();

// $inserted = 0;
// $failed = 0;

// for ($row = 1; $row <= $highestRow; $row++) { $idCell=$sheet->getCellByColumnAndRow($idCol, $row)->getValue();
    // $idCell = is_object($idCell) ? (string) $idCell : (string) $idCell;
    // $idCell = trim($idCell);

    // $cell = $sheet->getCellByColumnAndRow($urlCol, $row)->getValue();
    // $cell = is_object($cell) ? (string) $cell : (string) $cell;
    // $cell = trim($cell);

    // if ($cell === '') continue;

    // if ($idCell === '' || !is_numeric($idCell)) {
    // $failed++;
    // \Log::warning("BulkImage: Missing/invalid image_id", [
    // 'row' => $row,
    // 'image_id' => $idCell,
    // 'seller_id' => $sellerId
    // ]);
    // continue;
    // }
    // $imageGroupId = (int) $idCell;

    // $urls = array_filter(array_map('trim', explode(',', $cell)));

    // foreach ($urls as $url) {
    // if ($url === '') continue;

    // if (!filter_var($url, FILTER_VALIDATE_URL)) {
    // $failed++;
    // \Log::warning("BulkImage: Invalid URL", [
    // 'row' => $row, 'url' => $url, 'seller_id' => $sellerId, 'image_id' => $imageGroupId
    // ]);
    // continue;
    // }

    // $parts = parse_url($url);
    // if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
    // $failed++;
    // \Log::warning("BulkImage: parse_url failed", [
    // 'row' => $row, 'url' => $url, 'seller_id' => $sellerId, 'image_id' => $imageGroupId
    // ]);
    // continue;
    // }

    // $path = $parts['path'] ?? '';
    // $path = implode('/', array_map('rawurlencode', explode('/', $path)));
    // $safeUrl = $parts['scheme'] . '://' . $parts['host'] . $path;
    // if (!empty($parts['query'])) $safeUrl .= '?' . $parts['query'];

    // try {
    // $resp = \Illuminate\Support\Facades\Http::withHeaders([
    // 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    // 'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
    // 'Referer' => 'https://www.amazon.in/',
    // ])->timeout(45)->get($safeUrl);

    // if (!$resp->successful()) {
    // $failed++;
    // \Log::warning("BulkImage: HTTP failed", [
    // 'row' => $row,
    // 'url' => $url,
    // 'safe_url' => $safeUrl,
    // 'status' => $resp->status(),
    // 'seller_id' => $sellerId,
    // 'image_id' => $imageGroupId
    // ]);
    // continue;
    // }

    // $contentType = (string) $resp->header('Content-Type', '');
    // $body = $resp->body();

    // if (empty($body)) {
    // $failed++;
    // \Log::warning("BulkImage: Empty body", [
    // 'row' => $row, 'url' => $url, 'seller_id' => $sellerId, 'image_id' => $imageGroupId
    // ]);
    // continue;
    // }

    // $ext = 'jpg';
    // if (str_contains($contentType, 'png')) $ext = 'png';
    // if (str_contains($contentType, 'webp')) $ext = 'webp';
    // if (str_contains($contentType, 'jpeg')) $ext = 'jpg';

    // $base = basename(parse_url($url, PHP_URL_PATH) ?? 'image');
    // $base = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $base);
    // $base = \Illuminate\Support\Str::limit($base, 60, '');
    // $base = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '', $base);
    // $rand = \Illuminate\Support\Str::random(6);

    // $r2Path = "products/{$sellerId}_{$base}_{$rand}.{$ext}";

    // $ok = \Illuminate\Support\Facades\Storage::disk('r2')->put($r2Path, $body);

    // if (!$ok) {
    // $failed++;
    // \Log::error("BulkImage: R2 put returned false", [
    // 'row' => $row, 'url' => $url, 'r2_path' => $r2Path,
    // 'seller_id' => $sellerId, 'image_id' => $imageGroupId
    // ]);
    // continue;
    // }

    // \Illuminate\Support\Facades\DB::table('bulk_image')->insert([
    // 'seller_id' => $sellerId,
    // 'image_path' => $r2Path,
    // 'is_from_excel' => 1,
    // 'image_id' => $imageGroupId, // ✅ excel column A value
    // 'created_at' => now(),
    // 'updated_at' => now(),
    // ]);

    // $inserted++;

    // } catch (\Throwable $e) {
    // $failed++;
    // \Log::error("BulkImage: Exception inner", [
    // 'row' => $row,
    // 'url' => $url,
    // 'seller_id' => $sellerId,
    // 'image_id' => $imageGroupId,
    // 'message' => $e->getMessage(),
    // ]);
    // continue;
    // }
    // }
    // }

    // \Log::info("BulkImage: DONE", ['seller_id' => $sellerId, 'inserted' => $inserted, 'failed' => $failed]);

    // return back()->with('success', "Done! Inserted: {$inserted}, Failed: {$failed}. Logs:
    // storage/logs/laravel.log");

    // } catch (\Throwable $e) {
    // \Log::error("BulkImage: Import failed outer", ['seller_id' => $sellerId, 'message' => $e->getMessage()]);
    // return back()->with('error', 'Import failed: ' . $e->getMessage());
    // }
    // }















    // public function bulkimageurlupload(Request $request)
    // {

    // $request->validate([
    // 'seller_id' => 'required|integer',
    // 'file' => 'required|file|mimes:xlsx',
    // ]);

    // $sellerId = (int) $request->seller_id;

    // set_time_limit(0);
    // ini_set('memory_limit', '1024M');

    // Log::info("BulkImage: START", [
    // 'seller_id' => $sellerId,
    // 'file_name' => $request->file('file')->getClientOriginalName(),
    // ]);

    // // ✅ DO NOT force imagick if not installed
    // if (extension_loaded('imagick')) {
    // Image::configure(['driver' => 'imagick']);
    // Log::info("BulkImage: Using driver imagick");
    // } else {
    // Image::configure(['driver' => 'gd']);
    // Log::warning("BulkImage: Imagick not installed, using GD");
    // }

    // // ✅ If GD doesn't support webp, stop early (else encode('webp') will fail)
    // if (!extension_loaded('imagick') && function_exists('gd_info')) {
    // $gd = gd_info();
    // if (isset($gd['WebP Support']) && !$gd['WebP Support']) {
    // Log::error("BulkImage: GD WebP Support is OFF. Enable GD webp or install Imagick.");
    // return back()->with('error', 'Server GD WebP support off. Enable WebP or install Imagick.');
    // }
    // }

    // try {
    // $tmpDir = storage_path('app/tmp');
    // if (!is_dir($tmpDir)) {
    // @mkdir($tmpDir, 0775, true);
    // }

    // $filePath = $request->file('file')->getRealPath();
    // $spreadsheet = IOFactory::load($filePath);
    // $sheet = $spreadsheet->getActiveSheet();

    // // Excel mapping: A=image_id, B=urls (comma separated)
    // $idCol = 1;
    // $urlCol = 2;

    // $highestRow = (int) $sheet->getHighestRow();

    // $inserted = 0;
    // $failed = 0;

    // // ✅ assume row 1 = header
    // for ($row = 2; $row <= $highestRow; $row++) { $idCell=trim((string) $sheet->getCellByColumnAndRow($idCol,
        // $row)->getValue());
        // $cell = trim((string) $sheet->getCellByColumnAndRow($urlCol, $row)->getValue());

        // if ($cell === '') continue;

        // if ($idCell === '' || !is_numeric($idCell)) {
        // $failed++;
        // Log::warning("BulkImage: Missing/invalid image_id", [
        // 'row' => $row,
        // 'image_id' => $idCell,
        // 'seller_id' => $sellerId
        // ]);
        // continue;
        // }

        // $imageGroupId = (int) $idCell;
        // $urls = array_filter(array_map('trim', explode(',', $cell)));

        // foreach ($urls as $url) {
        // if ($url === '') continue;

        // if (!filter_var($url, FILTER_VALIDATE_URL)) {
        // $failed++;
        // Log::warning("BulkImage: Invalid URL", [
        // 'row' => $row,
        // 'url' => $url,
        // 'seller_id' => $sellerId,
        // 'image_id' => $imageGroupId
        // ]);
        // continue;
        // }

        // // ✅ Build safe URL but KEEP '+' in path (Amazon images sometimes have '+')
        // $parts = parse_url($url);
        // if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
        // $failed++;
        // Log::warning("BulkImage: parse_url failed", [
        // 'row' => $row,
        // 'url' => $url,
        // 'seller_id' => $sellerId,
        // 'image_id' => $imageGroupId
        // ]);
        // continue;
        // }

        // $path = $parts['path'] ?? '';
        // $pathSegments = explode('/', $path);
        // $pathSegments = array_map(function ($seg) {
        // // rawurlencode turns + into %2B, revert it back
        // return str_replace('%2B', '+', rawurlencode($seg));
        // }, $pathSegments);
        // $safePath = implode('/', $pathSegments);

        // $safeUrl = $parts['scheme'] . '://' . $parts['host'] . $safePath;
        // if (!empty($parts['query'])) $safeUrl .= '?' . $parts['query'];

        // $tmpIn = $tmpDir . "/bulk_in_{$sellerId}_{$row}_" . Str::random(8);
        // $tmpOut = $tmpDir . "/bulk_out_{$sellerId}_{$row}_" . Str::random(8) . ".webp";

        // try {
        // // ✅ Download to file (no big RAM usage)
        // $resp = Http::withHeaders([
        // 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        // 'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
        // 'Referer' => 'https://www.amazon.in/',
        // ])
        // ->retry(3, 800)
        // ->timeout(120)
        // ->withOptions(['sink' => $tmpIn])
        // ->get($safeUrl);

        // if (!$resp->successful()) {
        // $failed++;
        // Log::warning("BulkImage: HTTP failed", [
        // 'row' => $row,
        // 'url' => $url,
        // 'safe_url' => $safeUrl,
        // 'status' => $resp->status(),
        // 'seller_id' => $sellerId,
        // 'image_id' => $imageGroupId
        // ]);
        // @unlink($tmpIn);
        // continue;
        // }

        // if (!file_exists($tmpIn) || filesize($tmpIn) <= 0) { $failed++; Log::warning("BulkImage: Empty downloaded
            //file", [ 'row'=> $row,
            // 'url' => $url,
            // 'seller_id' => $sellerId,
            // 'image_id' => $imageGroupId
            // ]);
            // @unlink($tmpIn);
            // continue;
            // }

            // // ✅ Convert: 1000x1000 (aspect ratio OFF), transparent background, webp
            // $img = Image::make($tmpIn)->resize(1000, 1000);

            // $canvas = Image::canvas(1000, 1000, [0, 0, 0, 0]);
            // $canvas->insert($img, 'center');

            // $webpBinary = (string) $canvas->encode('webp', 85);

            // file_put_contents($tmpOut, $webpBinary);

            // if (!file_exists($tmpOut) || filesize($tmpOut) <= 0) { $failed++; Log::error("BulkImage: tmpOut not // //
                // created/empty", [ 'row'=> $row,
                // 'tmpOut' => $tmpOut,
                // 'seller_id' => $sellerId,
                // 'image_id' => $imageGroupId
                // ]);
                // @unlink($tmpIn);
                // @unlink($tmpOut);
                // continue;
                // }

                // // ✅ R2 path
                // $base = basename(parse_url($url, PHP_URL_PATH) ?? 'image');
                // $base = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $base);
                // $base = Str::limit($base, 60, '');
                // $base = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '', $base);
                // $rand = Str::random(6);

                // $r2Path = "products/{$sellerId}_{$base}_{$rand}.webp";

                // // ✅ Upload via stream (reliable)
                // $stream = fopen($tmpOut, 'r');
                // Storage::disk('r2')->writeStream($r2Path, $stream, [
                // 'visibility' => 'public', // optional
                // 'mimetype' => 'image/webp',
                // ]);
                // if (is_resource($stream)) fclose($stream);

                // DB::table('bulk_image')->insert([
                // 'seller_id' => $sellerId,
                // 'image_path' => $r2Path,
                // 'is_from_excel' => 1,
                // 'image_id' => $imageGroupId,
                // 'created_at' => now(),
                // 'updated_at' => now(),
                // ]);

                // $inserted++;

                // @unlink($tmpIn);
                // @unlink($tmpOut);

                // } catch (\Throwable $e) {
                // $failed++;
                // Log::error("BulkImage: Exception inner", [
                // 'row' => $row,
                // 'url' => $url,
                // 'safe_url' => $safeUrl ?? null,
                // 'seller_id' => $sellerId,
                // 'image_id' => $imageGroupId,
                // 'message' => $e->getMessage(),
                // ]);
                // @unlink($tmpIn);
                // @unlink($tmpOut);
                // continue;
                // }
                // }
                // }

                // Log::info("BulkImage: DONE", [
                // 'seller_id' => $sellerId,
                // 'inserted' => $inserted,
                // 'failed' => $failed
                // ]);

                // return back()->with('success', "Done! Inserted: {$inserted}, Failed: {$failed}. Logs:
                // storage/logs/laravel.log");

                // } catch (\Throwable $e) {
                // Log::error("BulkImage: Import failed outer", [
                // 'seller_id' => $sellerId,
                // 'message' => $e->getMessage()
                // ]);
                // return back()->with('error', 'Import failed: ' . $e->getMessage());
                // }
                // }














                // public function bulkimageurluploadAjax(Request $request)
                // {
                // $request->validate([
                // 'seller_id' => 'required|integer',
                // 'file' => 'required|file|mimes:xlsx',
                // ]);

                // $sellerId = (int) $request->seller_id;

                // // Store excel so queue can access it
                // $storedPath = $request->file('file')->store("bulk_import/{$sellerId}", 'local');

                // $jobId = (string) Str::uuid();

                // Cache::put("bulkimg:{$jobId}", [
                // 'status' => 'queued',
                // 'percent' => 0,
                // 'total' => 0,
                // 'processed' => 0,
                // 'inserted' => 0,
                // 'failed' => 0,
                // 'message' => null,
                // ], now()->addHours(6));

                // // ✅ Run async in queue (controller-only, no separate Job class)
                // dispatch(function () use ($jobId, $sellerId, $storedPath) {

                // set_time_limit(0);
                // ini_set('memory_limit', '1024M');

                // try {
                // $this->bulkImgProgressPatch($jobId, [
                // 'status' => 'processing',
                // 'message' => null,
                // ]);

                // Log::info("BulkImage: START", [
                // 'job_id' => $jobId,
                // 'seller_id' => $sellerId,
                // 'stored_path' => $storedPath,
                // ]);

                // // ✅ DO NOT force imagick if not installed
                // if (extension_loaded('imagick')) {
                // Image::configure(['driver' => 'imagick']);
                // Log::info("BulkImage: Using driver imagick", ['job_id' => $jobId]);
                // } else {
                // Image::configure(['driver' => 'gd']);
                // Log::warning("BulkImage: Imagick not installed, using GD", ['job_id' => $jobId]);
                // }

                // // ✅ If GD doesn't support webp, stop early
                // if (!extension_loaded('imagick') && function_exists('gd_info')) {
                // $gd = gd_info();
                // if (isset($gd['WebP Support']) && !$gd['WebP Support']) {
                // Log::error("BulkImage: GD WebP Support is OFF.", ['job_id' => $jobId]);
                // $this->bulkImgProgressPatch($jobId, [
                // 'status' => 'failed',
                // 'message' => 'Server GD WebP support off. Enable WebP or install Imagick.',
                // ]);
                // return;
                // }
                // }

                // $tmpDir = storage_path('app/tmp');
                // if (!is_dir($tmpDir)) {
                // @mkdir($tmpDir, 0775, true);
                // }

                // $filePath = storage_path('app/' . $storedPath);
                // $spreadsheet = IOFactory::load($filePath);
                // $sheet = $spreadsheet->getActiveSheet();

                // // Excel mapping: A=image_id, B=urls (comma separated)
                // $idCol = 1;
                // $urlCol = 2;

                // $highestRow = (int) $sheet->getHighestRow();
                // $totalRows = max(0, $highestRow - 1); // header excluded

                // $inserted = 0;
                // $failed = 0;
                // $processed = 0;

                // // ✅ row 1 = header
                // for ($row = 2; $row <= $highestRow; $row++) { $processed++; $idCell=trim((string) $sheet->
                    // getCellByColumnAndRow($idCol, $row)->getValue());
                    // $cell = trim((string) $sheet->getCellByColumnAndRow($urlCol, $row)->getValue());

                    // if ($cell === '') {
                    // $this->bulkImgTick($jobId, $processed, $totalRows, $inserted, $failed);
                    // continue;
                    // }

                    // if ($idCell === '' || !is_numeric($idCell)) {
                    // $failed++;
                    // Log::warning("BulkImage: Missing/invalid image_id", [
                    // 'job_id' => $jobId,
                    // 'row' => $row,
                    // 'image_id' => $idCell,
                    // 'seller_id' => $sellerId
                    // ]);
                    // $this->bulkImgTick($jobId, $processed, $totalRows, $inserted, $failed);
                    // continue;
                    // }

                    // $imageGroupId = (int) $idCell;
                    // $urls = array_filter(array_map('trim', explode(',', $cell)));

                    // foreach ($urls as $url) {
                    // if ($url === '') continue;

                    // if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    // $failed++;
                    // Log::warning("BulkImage: Invalid URL", [
                    // 'job_id' => $jobId,
                    // 'row' => $row,
                    // 'url' => $url,
                    // 'seller_id' => $sellerId,
                    // 'image_id' => $imageGroupId
                    // ]);
                    // continue;
                    // }

                    // // ✅ Build safe URL but KEEP '+'
                    // $parts = parse_url($url);
                    // if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
                    // $failed++;
                    // Log::warning("BulkImage: parse_url failed", [
                    // 'job_id' => $jobId,
                    // 'row' => $row,
                    // 'url' => $url,
                    // 'seller_id' => $sellerId,
                    // 'image_id' => $imageGroupId
                    // ]);
                    // continue;
                    // }

                    // $path = $parts['path'] ?? '';
                    // $pathSegments = explode('/', $path);
                    // $pathSegments = array_map(function ($seg) {
                    // return str_replace('%2B', '+', rawurlencode($seg));
                    // }, $pathSegments);
                    // $safePath = implode('/', $pathSegments);

                    // $safeUrl = $parts['scheme'] . '://' . $parts['host'] . $safePath;
                    // if (!empty($parts['query'])) $safeUrl .= '?' . $parts['query'];

                    // $tmpIn = $tmpDir . "/bulk_in_{$sellerId}_{$row}_" . Str::random(8);
                    // $tmpOut = $tmpDir . "/bulk_out_{$sellerId}_{$row}_" . Str::random(8) . ".webp";

                    // try {
                    // $resp = Http::withHeaders([
                    // 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    // 'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    // 'Referer' => 'https://www.amazon.in/',
                    // ])
                    // ->retry(3, 800)
                    // ->timeout(120)
                    // ->withOptions(['sink' => $tmpIn])
                    // ->get($safeUrl);

                    // if (!$resp->successful()) {
                    // $failed++;
                    // Log::warning("BulkImage: HTTP failed", [
                    // 'job_id' => $jobId,
                    // 'row' => $row,
                    // 'url' => $url,
                    // 'safe_url' => $safeUrl,
                    // 'status' => $resp->status(),
                    // 'seller_id' => $sellerId,
                    // 'image_id' => $imageGroupId
                    // ]);
                    // @unlink($tmpIn);
                    // continue;
                    // }

                    // if (!file_exists($tmpIn) || filesize($tmpIn) <= 0) { $failed++; Log::warning("BulkImage: Empty //
                        //downloaded file", [ 'job_id'=> $jobId,
                        // 'row' => $row,
                        // 'url' => $url,
                        // 'seller_id' => $sellerId,
                        // 'image_id' => $imageGroupId
                        // ]);
                        // @unlink($tmpIn);
                        // continue;
                        // }

                        // // ✅ Convert to webp
                        // $img = Image::make($tmpIn)->resize(1000, 1000);

                        // $canvas = Image::canvas(1000, 1000, [0, 0, 0, 0]);
                        // $canvas->insert($img, 'center');

                        // $webpBinary = (string) $canvas->encode('webp', 85);
                        // file_put_contents($tmpOut, $webpBinary);

                        // if (!file_exists($tmpOut) || filesize($tmpOut) <= 0) { $failed++; Log::error("BulkImage:
                            //tmpOut // empty", [ 'job_id'=> $jobId,
                            // 'row' => $row,
                            // 'tmpOut' => $tmpOut,
                            // 'seller_id' => $sellerId,
                            // 'image_id' => $imageGroupId
                            // ]);
                            // @unlink($tmpIn);
                            // @unlink($tmpOut);
                            // continue;
                            // }

                            // // ✅ R2 path
                            // $base = basename(parse_url($url, PHP_URL_PATH) ?? 'image');
                            // $base = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $base);
                            // $base = Str::limit($base, 60, '');
                            // $base = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '', $base);
                            // $rand = Str::random(6);

                            // $r2Path = "products/{$sellerId}_{$base}_{$rand}.webp";

                            // // ✅ Upload via stream
                            // $stream = fopen($tmpOut, 'r');
                            // Storage::disk('r2')->writeStream($r2Path, $stream, [
                            // 'visibility' => 'public',
                            // 'mimetype' => 'image/webp',
                            // ]);
                            // if (is_resource($stream)) fclose($stream);

                            // DB::table('bulk_image')->insert([
                            // 'seller_id' => $sellerId,
                            // 'image_path' => $r2Path,
                            // 'is_from_excel' => 1,
                            // 'image_id' => $imageGroupId,
                            // 'created_at' => now(),
                            // 'updated_at' => now(),
                            // ]);

                            // $inserted++;

                            // @unlink($tmpIn);
                            // @unlink($tmpOut);

                            // } catch (\Throwable $e) {
                            // $failed++;
                            // Log::error("BulkImage: Exception inner", [
                            // 'job_id' => $jobId,
                            // 'row' => $row,
                            // 'url' => $url,
                            // 'safe_url' => $safeUrl ?? null,
                            // 'seller_id' => $sellerId,
                            // 'image_id' => $imageGroupId,
                            // 'message' => $e->getMessage(),
                            // ]);
                            // @unlink($tmpIn);
                            // @unlink($tmpOut);
                            // continue;
                            // }
                            // }

                            // $this->bulkImgTick($jobId, $processed, $totalRows, $inserted, $failed);
                            // }

                            // $this->bulkImgProgressPatch($jobId, [
                            // 'status' => 'done',
                            // 'percent' => 100,
                            // 'total' => $totalRows,
                            // 'processed' => $processed,
                            // 'inserted' => $inserted,
                            // 'failed' => $failed,
                            // 'message' => 'Completed',
                            // ]);

                            // Log::info("BulkImage: DONE", [
                            // 'job_id' => $jobId,
                            // 'seller_id' => $sellerId,
                            // 'inserted' => $inserted,
                            // 'failed' => $failed
                            // ]);

                            // } catch (\Throwable $e) {
                            // Log::error("BulkImage: Import failed outer", [
                            // 'job_id' => $jobId,
                            // 'seller_id' => $sellerId,
                            // 'message' => $e->getMessage()
                            // ]);

                            // $this->bulkImgProgressPatch($jobId, [
                            // 'status' => 'failed',
                            // 'message' => $e->getMessage(),
                            // ]);

                            // throw $e;
                            // }

                            // })->onQueue('default');

                            // return response()->json([
                            // 'ok' => true,
                            // 'job_id' => $jobId,
                            // ]);
                            // }

                            // public function bulkImagesProgress($jobId)
                            // {
                            // $data = Cache::get("bulkimg:{$jobId}");
                            // if (!$data) {
                            // return response()->json(['status' => 'not_found', 'percent' => 0], 404);
                            // }
                            // return response()->json($data);
                            // }

                            // /** =================== HELPERS =================== */

                            // private function bulkImgTick(string $jobId, int $processed, int $total, int $inserted,
                            //int
                            // $failed): void
                            // {
                            // $percent = $total > 0 ? (int) floor(($processed / $total) * 100) : 100;

                            // $this->bulkImgProgressPatch($jobId, [
                            // 'status' => 'processing',
                            // 'percent' => $percent,
                            // 'total' => $total,
                            // 'processed' => $processed,
                            // 'inserted' => $inserted,
                            // 'failed' => $failed,
                            // 'message' => null,
                            // ]);
                            // }

                            // private function bulkImgProgressPatch(string $jobId, array $patch): void
                            // {
                            // $key = "bulkimg:{$jobId}";
                            // $old = Cache::get($key, []);

                            // $new = array_merge([
                            // 'status' => 'queued',
                            // 'percent' => 0,
                            // 'total' => 0,
                            // 'processed' => 0,
                            // 'inserted' => 0,
                            // 'failed' => 0,
                            // 'message' => null,
                            // ], $old, $patch);

                            // Cache::put($key, $new, now()->addHours(6));
                            // }







                            public function bulkimageurluploadAjax(Request $request)
                            {
                            $request->validate([
                            'seller_id' => 'required|integer',
                            'file' => 'required|file|mimes:xlsx',
                            ]);

                            $sellerId = (int) $request->seller_id;

                            // store excel so queue can access later
                            $storedPath = $request->file('file')->store("bulk_import/{$sellerId}", 'local');

                            $jobId = (string) Str::uuid();

                            Cache::put("bulkimg:{$jobId}", [
                            'status' => 'queued',
                            'percent' => 0,
                            'total' => 0,
                            'processed' => 0,
                            'inserted' => 0,
                            'failed' => 0,
                            'message' => null,
                            ], now()->addHours(6));

                            // IMPORTANT: don't use $this inside closure, use static helpers
                            dispatch(function () use ($jobId, $sellerId, $storedPath) {

                            set_time_limit(0);
                            ini_set('memory_limit', '1024M');

                            try {
                            self::bulkImgProgressPatch($jobId, ['status' => 'processing', 'message' => null]);

                            Log::info("BulkImage: START", [
                            'job_id' => $jobId,
                            'seller_id' => $sellerId,
                            'stored_path' => $storedPath,
                            ]);

                            // Image driver
                            if (extension_loaded('imagick')) {
                            Image::configure(['driver' => 'imagick']);
                            } else {
                            Image::configure(['driver' => 'gd']);
                            }

                            // GD WebP support check
                            if (!extension_loaded('imagick') && function_exists('gd_info')) {
                            $gd = gd_info();
                            if (isset($gd['WebP Support']) && !$gd['WebP Support']) {
                            self::bulkImgProgressPatch($jobId, [
                            'status' => 'failed',
                            'message' => 'Server GD WebP support off. Enable WebP or install Imagick.',
                            ]);
                            return;
                            }
                            }

                            $tmpDir = storage_path('app/tmp');
                            if (!is_dir($tmpDir)) @mkdir($tmpDir, 0775, true);

                            $filePath = storage_path('app/' . $storedPath);
                            $spreadsheet = IOFactory::load($filePath);
                            $sheet = $spreadsheet->getActiveSheet();

                            $idCol = 1; // A
                            $urlCol = 2; // B

                            $highestRow = (int) $sheet->getHighestRow();
                            $totalRows = max(0, $highestRow - 1);

                            $inserted = 0;
                            $failed = 0;
                            $processed = 0;

                            for ($row = 2; $row <= $highestRow; $row++) { $processed++; $idCell=trim((string) $sheet->
                                getCellByColumnAndRow($idCol, $row)->getValue());
                                $cell = trim((string) $sheet->getCellByColumnAndRow($urlCol, $row)->getValue());

                                if ($cell === '') {
                                self::bulkImgTick($jobId, $processed, $totalRows, $inserted, $failed);
                                continue;
                                }

                                if ($idCell === '' || !is_numeric($idCell)) {
                                $failed++;
                                self::bulkImgTick($jobId, $processed, $totalRows, $inserted, $failed);
                                continue;
                                }

                                $imageGroupId = (int) $idCell;
                                $urls = array_filter(array_map('trim', explode(',', $cell)));

                                foreach ($urls as $url) {
                                if ($url === '') continue;

                                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                                $failed++;
                                continue;
                                }

                                $parts = parse_url($url);
                                if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
                                $failed++;
                                continue;
                                }

                                // safe url (amazon paths etc)
                                $path = $parts['path'] ?? '';
                                $pathSegments = explode('/', $path);
                                $pathSegments = array_map(function ($seg) {
                                return str_replace('%2B', '+', rawurlencode($seg));
                                }, $pathSegments);
                                $safePath = implode('/', $pathSegments);

                                $safeUrl = $parts['scheme'] . '://' . $parts['host'] . $safePath;
                                if (!empty($parts['query'])) $safeUrl .= '?' . $parts['query'];

                                $tmpIn = $tmpDir . "/bulk_in_{$sellerId}_{$row}_" . Str::random(8);
                                $tmpOut = $tmpDir . "/bulk_out_{$sellerId}_{$row}_" . Str::random(8) . ".webp";

                                try {
                                $resp = Http::withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                                'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                                'Referer' => 'https://www.amazon.in/',
                                ])
                                ->retry(3, 800)
                                ->timeout(120)
                                ->withOptions(['sink' => $tmpIn])
                                ->get($safeUrl);

                                if (!$resp->successful() || !file_exists($tmpIn) || filesize($tmpIn) <= 0) { $failed++;
                                    @unlink($tmpIn); continue; } // resize with padding 1000x1000
                                    $img=Image::make($tmpIn)->resize(1000, 1000, function ($c) {
                                    $c->aspectRatio();
                                    $c->upsize();
                                    });

                                    $canvas = Image::canvas(1000, 1000, [0, 0, 0, 0]);
                                    $canvas->insert($img, 'center');

                                    file_put_contents($tmpOut, (string) $canvas->encode('webp', 85));

                                    if (!file_exists($tmpOut) || filesize($tmpOut) <= 0) { $failed++; @unlink($tmpIn);
                                        @unlink($tmpOut); continue; } $base=basename(parse_url($url, PHP_URL_PATH)
                                        ?? 'image' ); $base=preg_replace('/[^a-zA-Z0-9\-_\.]/', '_' , $base);
                                        $base=Str::limit($base, 60, '' );
                                        $base=preg_replace('/\.(jpg|jpeg|png|webp)$/i', '' , $base);
                                        $rand=Str::random(6); $r2Path="products/lists/{$sellerId}_{$base}_{$rand}.webp"
                                        ; $stream=fopen($tmpOut, 'r' ); Storage::disk('r2')->writeStream($r2Path,
                                        $stream,
                                        [
                                        'visibility' => 'public',
                                        'mimetype' => 'image/webp',
                                        ]);
                                        if (is_resource($stream)) fclose($stream);

                                        DB::table('bulk_image')->insert([
                                        'seller_id' => $sellerId,
                                        'image_path' => $r2Path,
                                        'is_from_excel' => 1,
                                        'image_id' => $imageGroupId,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                        ]);

                                        $inserted++;

                                        @unlink($tmpIn);
                                        @unlink($tmpOut);

                                        } catch (\Throwable $e) {
                                        $failed++;
                                        Log::error("BulkImage: inner exception", [
                                        'job_id' => $jobId,
                                        'row' => $row,
                                        'url' => $url,
                                        'message' => $e->getMessage(),
                                        ]);
                                        @unlink($tmpIn);
                                        @unlink($tmpOut);
                                        continue;
                                        }
                                        }

                                        self::bulkImgTick($jobId, $processed, $totalRows, $inserted, $failed);
                                        }

                                        self::bulkImgProgressPatch($jobId, [
                                        'status' => 'done',
                                        'percent' => 100,
                                        'total' => $totalRows,
                                        'processed' => $processed,
                                        'inserted' => $inserted,
                                        'failed' => $failed,
                                        'message' => 'Completed',
                                        ]);

                                        } catch (\Throwable $e) {
                                        self::bulkImgProgressPatch($jobId, [
                                        'status' => 'failed',
                                        'message' => $e->getMessage(),
                                        ]);
                                        throw $e;
                                        }

                                        })->onQueue('default');

                                        // Return fast to avoid gateway timeout
                                        return response()->json(['ok' => true, 'job_id' => $jobId], 202);
                                        }


                                        public function bulkImagesProgress($jobId)
                                        {
                                        $data = Cache::get("bulkimg:{$jobId}");
                                        if (!$data) return response()->json(['status' => 'not_found', 'percent' => 0],
                                        404);
                                        return response()->json($data);
                                        }


                                        private static function bulkImgTick(string $jobId, int $processed, int $total,
                                        int $inserted, int $failed): void
                                        {
                                        $percent = $total > 0 ? (int) floor(($processed / $total) * 100) : 100;

                                        self::bulkImgProgressPatch($jobId, [
                                        'status' => 'processing',
                                        'percent' => $percent,
                                        'total' => $total,
                                        'processed' => $processed,
                                        'inserted' => $inserted,
                                        'failed' => $failed,
                                        'message' => null,
                                        ]);
                                        }

                                        private static function bulkImgProgressPatch(string $jobId, array $patch): void
                                        {
                                        $key = "bulkimg:{$jobId}";
                                        $old = Cache::get($key, []);

                                        $new = array_merge([
                                        'status' => 'queued',
                                        'percent' => 0,
                                        'total' => 0,
                                        'processed' => 0,
                                        'inserted' => 0,
                                        'failed' => 0,
                                        'message' => null,
                                        ], $old, $patch);

                                        Cache::put($key, $new, now()->addHours(6));
                                        }















                                        // public function exportExcelUploadedImages(Request $request)
                                        // {
                                        // // seller_id optional
                                        // $request->validate([
                                        // 'seller_id' => 'nullable|integer',
                                        // ]);

                                        // $sellerId = $request->filled('seller_id') ? (int) $request->seller_id : null;

                                        // // Get rows
                                        // $q = DB::table('bulk_image')
                                        // ->select('image_id', 'seller_id', 'image_path', 'created_at')
                                        // ->where('is_from_excel', 1)
                                        // ->orderBy('id', 'asc');

                                        // if ($sellerId) {
                                        // $q->where('seller_id', $sellerId);
                                        // }

                                        // $rows = $q->get();

                                        // // Create excel
                                        // $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                                        // $sheet = $spreadsheet->getActiveSheet();
                                        // $sheet->setTitle('bulk_images');

                                        // // Header
                                        // $sheet->setCellValue('A1', 'image_id');
                                        // $sheet->setCellValue('B1', 'seller_id');
                                        // $sheet->setCellValue('C1', 'image_path');
                                        // $sheet->setCellValue('D1', 'created_at');

                                        // $r = 2;
                                        // foreach ($rows as $row) {
                                        // $path = ltrim((string) $row->image_path, '/');

                                        // $sheet->setCellValue("A{$r}", (int) $row->image_id);
                                        // $sheet->setCellValue("B{$r}", (int) $row->seller_id);
                                        // $sheet->setCellValue("C{$r}", $path);
                                        // $sheet->setCellValue("D{$r}", (string) $row->created_at);

                                        // $r++;
                                        // }

                                        // foreach (range('A', 'D') as $col) {
                                        // $sheet->getColumnDimension($col)->setAutoSize(true);
                                        // }

                                        // $fileName = $sellerId
                                        // ? "bulk_images_excel_seller_{$sellerId}.xlsx"
                                        // : "bulk_images_excel_all.xlsx";

                                        // return response()->streamDownload(function () use ($spreadsheet) {
                                        // $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                                        // $writer->save('php://output');
                                        // }, $fileName, [
                                        // 'Content-Type' =>
                                        // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        // ]);
                                        // }



                                        public function exportExcelUploadedImages(Request $request)
                                        {
                                        // ✅ Get authenticated seller id
                                        // Change guard/model as per your project (seller guard / user->seller_id etc.)
                                        $sellerId = auth('seller')->id();
                                        // OR: $sellerId = auth()->user()->seller_id;

                                        if (!$sellerId) {
                                        return response()->json(['message' => 'Unauthorized'], 401);
                                        }

                                        $q = DB::table('bulk_image')
                                        ->select(
                                        'image_id',
                                        'seller_id',
                                        DB::raw("GROUP_CONCAT(TRIM(LEADING '/' FROM image_path) ORDER BY id SEPARATOR
                                        '||') as images"),
                                        DB::raw("MIN(created_at) as created_at")
                                        )
                                        ->where('is_from_excel', 1)
                                        ->where('seller_id', $sellerId) // ✅ force only auth seller
                                        ->orderBy('image_id', 'asc')
                                        ->groupBy('image_id', 'seller_id');

                                        $rows = $q->get();

                                        // Find max images count to create headers
                                        $maxImages = 0;
                                        $parsed = [];
                                        foreach ($rows as $row) {
                                        $imgs = array_values(array_filter(explode('||', (string) $row->images)));
                                        $maxImages = max($maxImages, count($imgs));
                                        $parsed[] = [$row, $imgs];
                                        }

                                        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                                        $sheet = $spreadsheet->getActiveSheet();
                                        $sheet->setTitle('bulk_images');

                                        // Base headers
                                        $sheet->setCellValue('A1', 'image_id');
                                        $sheet->setCellValue('B1', 'seller_id');

                                        // Dynamic image headers
                                        $startColIndex = 3; // C
                                        for ($i = 1; $i <= $maxImages; $i++) {
                                            $col=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex
                                            + $i - 1); $sheet->setCellValue($col . '1', "image_{$i}");
                                            }

                                            // created_at last column
                                            $createdAtCol =
                                            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex
                                            + $maxImages);
                                            $sheet->setCellValue($createdAtCol . '1', 'created_at');

                                            $r = 2;
                                            foreach ($parsed as [$row, $imgs]) {
                                            $sheet->setCellValue("A{$r}", (int) $row->image_id);
                                            $sheet->setCellValue("B{$r}", (int) $row->seller_id);

                                            for ($i = 0; $i < $maxImages; $i++) {
                                                $col=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex
                                                + $i); $sheet->setCellValue($col . $r, $imgs[$i] ?? '');
                                                }

                                                $sheet->setCellValue($createdAtCol . $r, (string) $row->created_at);
                                                $r++;
                                                }

                                                // Autosize
                                                $lastCol =
                                                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex
                                                + $maxImages);
                                                foreach (range('A', $lastCol) as $col) {
                                                $sheet->getColumnDimension($col)->setAutoSize(true);
                                                }

                                                $fileName = "bulk_images_excel_seller_{$sellerId}.xlsx";

                                                return response()->streamDownload(function () use ($spreadsheet) {
                                                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                                                $writer->save('php://output');
                                                }, $fileName, [
                                                'Content-Type' =>
                                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                                ]);
                                                }






                                                }