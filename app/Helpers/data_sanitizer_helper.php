<?php

/**
 * 資料清理 Helper
 * 
 * 提供通用的資料清理功能，包含去除空白字元、HTML 標籤等
 */

if (!function_exists('sanitize_string')) {
    /**
     * 清理字串資料
     * 
     * @param string $value 要清理的字串
     * @param bool $removeHtml 是否移除 HTML 標籤 (預設: false)
     * @return string 清理後的字串
     */
    function sanitize_string($value, $removeHtml = false)
    {
        if (!is_string($value)) {
            return $value;
        }

        // 去除前後空白字元
        $value = trim($value);
        
        // 可選：移除 HTML 標籤
        if ($removeHtml) {
            $value = strip_tags($value);
        }
        
        return $value;
    }
}

if (!function_exists('sanitize_array')) {
    /**
     * 清理陣列資料（遞迴處理）
     * 
     * @param array $data 要清理的陣列
     * @param bool $removeHtml 是否移除 HTML 標籤 (預設: false)
     * @return array 清理後的陣列
     */
    function sanitize_array($data, $removeHtml = false)
    {
        if (!is_array($data)) {
            return $data;
        }

        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // 遞迴處理巢狀陣列
                $sanitized[$key] = sanitize_array($value, $removeHtml);
            } elseif (is_string($value)) {
                // 清理字串
                $sanitized[$key] = sanitize_string($value, $removeHtml);
            } else {
                // 保持其他類型的值不變
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}

if (!function_exists('sanitize_form_data')) {
    /**
     * 清理表單資料
     * 
     * @param array $formData 表單資料
     * @param array $excludeFields 不需要清理的欄位 (預設: [])
     * @param bool $removeHtml 是否移除 HTML 標籤 (預設: false)
     * @return array 清理後的表單資料
     */
    function sanitize_form_data($formData, $excludeFields = [], $removeHtml = false)
    {
        if (!is_array($formData)) {
            return $formData;
        }

        $sanitized = [];
        
        foreach ($formData as $key => $value) {
            // 跳過不需要清理的欄位
            if (in_array($key, $excludeFields)) {
                $sanitized[$key] = $value;
                continue;
            }
            
            if (is_array($value)) {
                $sanitized[$key] = sanitize_array($value, $removeHtml);
            } elseif (is_string($value)) {
                $sanitized[$key] = sanitize_string($value, $removeHtml);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}

if (!function_exists('remove_empty_strings')) {
    /**
     * 移除陣列中的空字串
     * 
     * @param array $data 要處理的陣列
     * @param bool $preserveZero 是否保留數字 0 (預設: true)
     * @return array 處理後的陣列
     */
    function remove_empty_strings($data, $preserveZero = true)
    {
        if (!is_array($data)) {
            return $data;
        }

        return array_filter($data, function ($value) use ($preserveZero) {
            if ($preserveZero && $value === 0 || $value === '0') {
                return true;
            }
            return !empty($value);
        });
    }
}
