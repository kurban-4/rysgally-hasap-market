<?php


if (! function_exists('parseWeightBarcode')) {
    function parseWeightBarcode(string $barcode, array $prefixes = ['22','21']): array
    {
        $code = trim($barcode);
        if (strlen($code) !== 13) {
            return ['is_weight' => false, 'raw' => $code];
        }

        $prefix = substr($code, 0, 2);
        if (! in_array($prefix, $prefixes, true)) {
            return ['is_weight' => false, 'raw' => $code];
        }

        
        $product_code = substr($code, 2, 5);
        $weight_part  = substr($code, 8, 4);

        if (! ctype_digit($product_code) || ! ctype_digit($weight_part)) {
            return ['is_weight' => false, 'raw' => $code];
        }

        return [
            'is_weight' => true,
            'product_code' => ltrim($product_code, '0'),
            'weight_grams' => (int) $weight_part,
            'raw' => $code,
        ];
    }
}
