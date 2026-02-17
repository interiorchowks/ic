<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GoogleFeedController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('google_feed_xml_v2', now()->addHours(6), function () {

            $baseUrl = rtrim(config('app.url'), '/');

            $out  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$out .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
    $out .= "<channel>\n";
        $out .= "<title>InteriorChowk Product Feed</title>\n";
        $out .= "
        <link>" . $this->escape($baseUrl) . "</link>\n";
        $out .= "<description>Google Merchant Center product feed</description>\n";
        $query = DB::table('sku_product_new as sp')
        ->join('products as p', 'p.id', '=', 'sp.product_id')
        ->where('p.status', '=', 1) // active products
        ->where('sp.listed_price', '>', 0)
        ->whereNotNull('p.slug')
        ->select([
        'sp.id as sku_row_id',
        'sp.product_id',
        'sp.sku',
        'sp.variation',
        'sp.sizes',
        'sp.color_name',
        'sp.quantity as sku_quantity',
        'sp.listed_price',
        'sp.variant_mrp',
        'sp.image as sku_image',
        'sp.thumbnail_image as sku_thumb',

        'p.id as product_table_id',
        'p.name as product_name',
        'p.slug',
        'p.details',
        'p.thumbnail as product_thumbnail',
        'p.images as product_images',
        'p.brand_id',
        'p.current_stock as product_stock',
        'p.shipping_cost',
        'p.free_shipping',
        'p.free_delivery',
        'p.temp_shipping_cost',
        'p.is_shipping_cost_updated',
        ])
        ->orderBy('sp.id');

        $query->chunk(500, function ($rows) use (&$out, $baseUrl) {
        foreach ($rows as $r) {

        $id = $r->sku ? $r->sku : ('SP-' . $r->sku_row_id);

        $titleParts = [$r->product_name];
        if (!empty($r->color_name)) $titleParts[] = $r->color_name;
        if (!empty($r->sizes)) $titleParts[] = $r->sizes;
        if (!empty($r->variation)) $titleParts[] = $r->variation;
        $title = trim(implode(' - ', array_filter($titleParts)));

        $desc = $r->details ?: $r->product_name;

        $link = $baseUrl . '/product/' . ltrim($r->slug, '/');

        $image = $r->sku_image ?: $r->product_thumbnail;

        if (empty($image) && !empty($r->product_images)) {
        $first = $this->firstImageFromJson($r->product_images);
        if ($first) $image = $first;
        }

        if (!empty($image) && !str_starts_with($image, 'http')) {
        $image = $baseUrl . '/' . ltrim($image, '/');
        }

        if (empty($image)) {
        continue;
        }

        $qty = is_numeric($r->sku_quantity) ? (int)$r->sku_quantity : null;
        if ($qty === null) {
        $qty = is_numeric($r->product_stock) ? (int)$r->product_stock : 0;
        }
        $availability = ($qty > 0) ? 'in_stock' : 'out_of_stock';

        $price = number_format((float)$r->listed_price, 2, '.', '') . ' INR';

        $mrp = is_numeric($r->variant_mrp) ? (float)$r->variant_mrp : 0.0;
        $salePriceTag = '';
        if ($mrp > 0 && $mrp > (float)$r->listed_price) {
        $mrpFormatted = number_format($mrp, 2, '.', '') . ' INR';
        $salePriceTag = " <g:price>{$mrpFormatted}</g:price>\n"
        . " <g:sale_price>{$price}</g:sale_price>\n";
        }

        $brand = 'InteriorChowk';

        $itemGroupId = 'P-' . $r->product_id;

        $out .= "<item>\n";
            $out .= " <g:id>{$this->escape($id)}</g:id>\n";
            $out .= " <g:item_group_id>{$this->escape($itemGroupId)}</g:item_group_id>\n";
            $out .= " <g:title>{$this->cdata($title)}</g:title>\n";
            $out .= " <g:description>{$this->cdata($desc)}</g:description>\n";
            $out .= " <g:link>{$this->escape($link)}</g:link>\n";
            $out .= " <g:image_link>{$this->escape($image)}</g:image_link>\n";
            $out .= " <g:availability>{$availability}</g:availability>\n";

            if (!empty($salePriceTag)) {
            $out .= $salePriceTag;
            } else {
            $out .= " <g:price>{$price}</g:price>\n";
            }

            $out .= " <g:brand>{$this->escape($brand)}</g:brand>\n";
            $out .= " <g:condition>new</g:condition>\n";

            $out .= " <g:identifier_exists>false</g:identifier_exists>\n";

            if (!empty($r->color_name)) {
            $out .= " <g:color>{$this->cdata($r->color_name)}</g:color>\n";
            }
            if (!empty($r->sizes)) {
            $out .= " <g:size>{$this->cdata($r->sizes)}</g:size>\n";
            }

            $out .= "</item>\n";
        }
        });

        $out .= "
    </channel>\n</rss>\n";

return $out;
});

return response($xml, 200)
->header('Content-Type', 'application/xml; charset=UTF-8');
}

private function escape($value): string
{
return htmlspecialchars((string)$value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

private function cdata($value): string
{
$v = (string)$value;
$v = str_replace(']]>', ']]]]>
<![CDATA[>', $v);
        return "<![CDATA[{$v}]]>";
}

private function firstImageFromJson($json): ?string
{
try {
$arr = json_decode($json, true);
if (is_array($arr) && !empty($arr)) {
$first = $arr[0];
if (is_string($first) && trim($first) !== '') return $first;

if (is_array($first) && isset($first['url']) && is_string($first['url'])) {
return $first['url'];
}
}
} catch (\Throwable $e) {
}
return null;
}
}