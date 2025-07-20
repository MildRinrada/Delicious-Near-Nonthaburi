<?php
// กำหนดสถานะเริ่มต้น กรณีไม่มีข้อมูลเวลาทำการ
$status = 'ไม่ทราบสถานะ';
$status_class = 'text-gray-600';
$status_icon_path = '../images/gray_status_circle.png';

// ตรวจสอบข้อมูลเวลาทำการของวันปัจจุบัน
if ($current_day_hours && $current_day_hours['is_closed'] == 1) {
    // กรณีปิดถาวรในวันนี้
    $status = 'ปิดถาวรในวันนี้';
    $status_class = 'text-red-600';
    $status_icon_path = '../static/images/icon/red_status_circle.png';
} else if ($current_day_hours && (empty($current_day_hours['open_time']) || empty($current_day_hours['close_time']))) {
    // กรณีไม่มีเวลาทำการระบุในวันนี้
    $status = 'ไม่ระบุเวลาทำการในวันนี้';
    $status_class = 'text-gray-600';
    $status_icon_path = '../static/images/icon/gray_status_circle.png';
} else if ($current_day_hours) {
    // เช็คเวลาปัจจุบันว่าร้านเปิดหรือปิด
    if ($current_time >= $current_day_hours['open_time'] && $current_time <= $current_day_hours['close_time']) {
        $status = 'เปิดอยู่';
        $status_class = 'text-green-600';
        $status_icon_path = '../static/images/icon/green_status_circle.png';
    } else {
        $status = 'ปิด (นอกเวลาทำการ)';
        $status_class = 'text-red-600';
        $status_icon_path = '../static/images/icon/red_status_circle.png';
    }
} else {
    // กรณีไม่มีข้อมูลเวลาทำการสำหรับวันนี้
    $status = 'ไม่มีข้อมูลเวลาทำการสำหรับวันนี้';
    $status_class = 'text-gray-600';
    $status_icon_path = '../images/gray_status_circle.png';
}

// กำหนดชื่อวันในสัปดาห์ (จันทร์-อาทิตย์)
$day_names = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];

// สร้างแผนที่เก็บเวลาทำการของแต่ละวัน โดยกำหนดค่าเริ่มต้น
$daily_hours_map = [];
for ($i = 1; $i <= 7; $i++) {
    $daily_hours_map[$i] = ['open_time' => null, 'close_time' => null, 'is_closed' => 0];
}

// เติมข้อมูลเวลาทำการจากฐานข้อมูลลงในแผนที่ โดยแก้ไขกรณีวันอาทิตย์ (0) เป็น 7
foreach ($all_opening_hours as $oh) {
    $day_key = ($oh['day_of_week'] == 0) ? 7 : $oh['day_of_week'];
    if (isset($daily_hours_map[$day_key])) {
        $daily_hours_map[$day_key] = [
            'open_time' => $oh['open_time'],
            'close_time' => $oh['close_time'],
            'is_closed' => $oh['is_closed']
        ];
    }
}
ksort($daily_hours_map); // เรียงลำดับวันจันทร์-อาทิตย์

// จัดกลุ่มวันตามช่วงเวลาทำการเดียวกัน เพื่อแสดงผลสรุป
$grouped_hours = [];
foreach ($daily_hours_map as $day_of_week => $times) {
    if ($times['is_closed'] == 1) {
        // ข้ามวันที่ปิดถาวร
        continue;
    }

    // สร้างข้อความช่วงเวลาทำการ หรือข้อความแจ้งว่าไม่ระบุเวลา
    $time_str = '';
    if (empty($times['open_time']) || empty($times['close_time'])) {
        $time_str = 'ไม่ระบุเวลาทำการ';
    } else {
        $time_str = substr($times['open_time'], 0, 5) . ' - ' . substr($times['close_time'], 0, 5);
    }

    // เพิ่มวันเข้าไปในกลุ่มของช่วงเวลานั้น
    if (!isset($grouped_hours[$time_str])) {
        $grouped_hours[$time_str] = [];
    }
    $grouped_hours[$time_str][] = $day_of_week;
}

// แปลงกลุ่มวันที่และเวลาทำการเป็นข้อความแสดงผลแบบรวมกลุ่มวันต่อเนื่อง
$final_display_hours = [];
foreach ($grouped_hours as $time => $days) {
    sort($days); // เรียงลำดับวันในกลุ่ม

    $day_parts = [];
    $current_sequence = [];

    foreach ($days as $day_index_db) {
        if (empty($current_sequence) || $day_index_db === end($current_sequence) + 1) {
            // ถ้าวันนี้เป็นวันแรกหรือเชื่อมต่อกับวันก่อนหน้าในกลุ่ม
            $current_sequence[] = $day_index_db;
        } else {
            // ถ้าวันนี้ไม่เชื่อมต่อกับกลุ่มก่อนหน้า ให้จบกลุ่มเดิมและเริ่มกลุ่มใหม่
            if (count($current_sequence) === 1) {
                $day_parts[] = $day_names[$current_sequence[0] - 1];
            } else {
                $start_day_name = $day_names[$current_sequence[0] - 1];
                $end_day_name = $day_names[end($current_sequence) - 1];
                $day_parts[] = $start_day_name . ' - ' . $end_day_name;
            }
            $current_sequence = [$day_index_db];
        }
    }

    // เพิ่มกลุ่มวันที่สุดท้ายที่ค้างไว้
    if (!empty($current_sequence)) {
        if (count($current_sequence) === 1) {
            $day_parts[] = $day_names[$current_sequence[0] - 1];
        } else {
            $start_day_name = $day_names[$current_sequence[0] - 1];
            $end_day_name = $day_names[end($current_sequence) - 1];
            $day_parts[] = $start_day_name . ' - ' . $end_day_name;
        }
    }

    // รวมวันและเวลาทำการในรูปแบบข้อความเดียวกัน
    $final_display_hours[] = implode(', ', $day_parts) . ' ' . $time;
}

// กำหนดผลลัพธ์สุดท้าย เพื่อใช้แสดงใน restaurant_detail.php
$formatted_opening_hours = $final_display_hours;
?>
