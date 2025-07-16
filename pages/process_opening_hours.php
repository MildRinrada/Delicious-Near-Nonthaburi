<?php
// process_opening_hours.php

// ไฟล์นี้จะถูกรวมใน restaurant_detail.php
// ดังนั้นตัวแปร $pdo, $restaurant_id, $current_day_of_week, $current_time
// และ $all_opening_hours จะสามารถใช้งานได้โดยตรง

// --- คำนวณสถานะเปิด/ปิด (ใช้ $current_day_hours ที่ดึงมา) ---
// ตรวจสอบให้แน่ใจว่า $current_day_hours ถูกกำหนดค่าไว้ก่อนเรียกใช้ไฟล์นี้
// (ซึ่งมันถูกกำหนดใน restaurant_detail.php ก่อนเรียกใช้ไฟล์นี้อยู่แล้ว)

$status = 'ไม่ทราบสถานะ';
$status_class = 'text-gray-600'; // Default text color for unknown
$status_icon_path = '../images/gray_status_circle.png'; // Make sure this path is correct

if ($current_day_hours && $current_day_hours['is_closed'] == 1) {
    $status = 'ปิดถาวรในวันนี้';
    $status_class = 'text-red-600'; // Text color for closed
    $status_icon_path = '../static/images/icon/red_status_circle.png'; // Icon for closed
} else if ($current_day_hours && (empty($current_day_hours['open_time']) || empty($current_day_hours['close_time']))) {
    $status = 'ไม่ระบุเวลาทำการในวันนี้';
    $status_class = 'text-gray-600'; // Text color for unknown/no hours
    $status_icon_path = '../static/images/icon/gray_status_circle.png'; // Icon for unknown/no hours
} else if ($current_day_hours) {
    if ($current_time >= $current_day_hours['open_time'] && $current_time <= $current_day_hours['close_time']) {
        $status = 'เปิดอยู่';
        $status_class = 'text-green-600'; // Text color for open
        $status_icon_path = '../static/images/icon/green_status_circle.png'; // Icon for open
    } else {
        $status = 'ปิด (นอกเวลาทำการ)';
        $status_class = 'text-red-600'; // Text color for closed
        $status_icon_path = '../static/images/icon/red_status_circle.png'; // Icon for closed
    }
} else {
    $status = 'ไม่มีข้อมูลเวลาทำการสำหรับวันนี้';
    $status_class = 'text-gray-600'; // Text color for no data
    $status_icon_path = '../images/gray_status_circle.png'; // Icon for no data
}

// --- จัดกลุ่มเวลาเปิด-ปิดสำหรับแสดงผลทั้งหมด ---
$formatted_opening_hours = [];
// เปลี่ยน day_names ให้วันอาทิตย์อยู่ท้ายสุด (เนื่องจาก DB อาจใช้ 7 หรือ 0)
// ในฐานข้อมูล 1=จันทร์ ถึง 7=อาทิตย์
$day_names = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];

// สร้าง array ที่มีข้อมูลเวลาทำการของทุกวันเริ่มต้นด้วย null หรือค่า default
$daily_hours_map = [];
for ($i = 1; $i <= 7; $i++) {
    $daily_hours_map[$i] = ['open_time' => null, 'close_time' => null, 'is_closed' => 0];
}

// ใส่ข้อมูลเวลาทำการที่ดึงมาจาก DB เข้าไปใน map
foreach ($all_opening_hours as $oh) {
    // ปรับ day_of_week หาก 0 เป็น 7
    $day_key = ($oh['day_of_week'] == 0) ? 7 : $oh['day_of_week']; 
    if (isset($daily_hours_map[$day_key])) {
        $daily_hours_map[$day_key] = [
            'open_time' => $oh['open_time'],
            'close_time' => $oh['close_time'],
            'is_closed' => $oh['is_closed']
        ];
    }
}
ksort($daily_hours_map); // เรียงตามวันของสัปดาห์ 1-7

$grouped_hours = []; // ใช้เก็บเวลาเป็นคีย์และ array ของวันเป็นค่า

foreach ($daily_hours_map as $day_of_week => $times) {
    // ข้ามวันปิดถาวร
    if ($times['is_closed'] == 1) {
        continue; // ข้ามวันปิดไปเลย
    }

    $time_str = '';
    if (empty($times['open_time']) || empty($times['close_time'])) {
        $time_str = 'ไม่ระบุเวลาทำการ';
    } else {
        $time_str = substr($times['open_time'], 0, 5) . ' - ' . substr($times['close_time'], 0, 5);
    }

    // เพิ่มวันเข้าไปในกลุ่มของเวลานั้นๆ
    if (!isset($grouped_hours[$time_str])) {
        $grouped_hours[$time_str] = [];
    }
    $grouped_hours[$time_str][] = $day_of_week;
}

// แปลงข้อมูลที่จัดกลุ่มแล้วให้เป็นข้อความที่แสดงผล
$final_display_hours = [];
foreach ($grouped_hours as $time => $days) {
    sort($days); // เรียงวันในกลุ่มให้ถูกต้อง

    $day_parts = [];
    $current_sequence = [];

    foreach ($days as $day_index_db) {
        // ปรับ index ให้ตรงกับ $day_names array (0-based)
        $day_index_for_name = $day_index_db - 1; // 1 -> 0, 7 -> 6

        if (empty($current_sequence) || $day_index_db === end($current_sequence) + 1) {
            // วันต่อเนื่องกัน หรือเริ่มกลุ่มใหม่
            $current_sequence[] = $day_index_db;
        } else {
            // วันไม่ต่อเนื่องกัน, จบกลุ่มปัจจุบันและเริ่มกลุ่มใหม่
            if (count($current_sequence) === 1) {
                $day_parts[] = $day_names[$current_sequence[0] - 1];
            } else {
                $start_day_name = $day_names[$current_sequence[0] - 1];
                $end_day_name = $day_names[end($current_sequence) - 1];
                $day_parts[] = $start_day_name . ' - ' . $end_day_name;
            }
            $current_sequence = [$day_index_db]; // เริ่ม segment ใหม่
        }
    }

    // เพิ่ม segment สุดท้าย
    if (!empty($current_sequence)) {
        if (count($current_sequence) === 1) {
            $day_parts[] = $day_names[$current_sequence[0] - 1];
        } else {
            $start_day_name = $day_names[$current_sequence[0] - 1];
            $end_day_name = $day_names[end($current_sequence) - 1];
            $day_parts[] = $start_day_name . ' - ' . $end_day_name;
        }
    }
    $final_display_hours[] = implode(', ', $day_parts) . ' ' . $time;
}

$formatted_opening_hours = $final_display_hours; // กำหนดค่าให้ตัวแปรเดิมเพื่อใช้ใน restaurant_detail.php
?>