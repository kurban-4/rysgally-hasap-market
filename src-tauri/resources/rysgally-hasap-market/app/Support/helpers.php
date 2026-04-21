<?php
// app/Support/helpers.php

if (! function_exists('parseWeightBarcode')) {
    /**
     * Разбирает EAN-13 весовой штрихкод.
     * Возвращает ['is_weight'=>bool,'product_code'=>string|null,'weight_grams'=>int|null,'raw'=>string]
     * Поддерживает префиксы 22,21 по умолчанию.
     */
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

        // Формат: 22 AAAAA V WWWW K
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
