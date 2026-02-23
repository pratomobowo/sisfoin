<table>
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: left; vertical-align: middle; width: 300px; border: 1px solid #000000;">Karyawan</th>
            <th style="font-weight: bold; text-align: left; vertical-align: middle; width: 150px; border: 1px solid #000000;">NIP</th>
            @foreach($days as $day)
                <th style="font-weight: bold; text-align: center; vertical-align: middle; width: 100px; border: 1px solid #000000; {{ $day['is_holiday'] ? 'background-color: #fee2e2; color: #dc2626;' : ($day['is_weekend'] ? 'background-color: #fef2f2; color: #f87171;' : 'background-color: #f9fafb; color: #9ca3af;') }}">
                    {{ $day['day'] }}<br>
                    {{ $day['weekday'] }}
                </th>
            @endforeach
            <th style="font-weight: bold; text-align: center; vertical-align: middle; width: 100px; border: 1px solid #000000; background-color: #fef3c7;">Terlambat</th>
            <th style="font-weight: bold; text-align: center; vertical-align: middle; width: 100px; border: 1px solid #000000; background-color: #f3e8ff;">Tdk Lengkap</th>
            <th style="font-weight: bold; text-align: center; vertical-align: middle; width: 100px; border: 1px solid #000000; background-color: #dcfce7;">Hadir</th>
            <th style="font-weight: bold; text-align: center; vertical-align: middle; width: 100px; border: 1px solid #000000; background-color: #fee2e2;">Tdk Hadir</th>
            <th style="font-weight: bold; text-align: center; vertical-align: middle; width: 100px; border: 1px solid #000000; background-color: #e5e7eb;">Sakit</th>
            <th style="font-weight: bold; text-align: center; vertical-align: middle; width: 100px; border: 1px solid #000000; background-color: #fbcfe8;">Cuti</th>
            <th style="font-weight: bold; text-align: center; vertical-align: middle; width: 110px; border: 1px solid #000000; background-color: #dbeafe;">Setengah Hari</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $employee)
            @php
                $lateCount = 0;
                $incompleteCount = 0;
                $presentCount = 0;
                $absentCount = 0;
                $sickCount = 0;
                $leaveCount = 0;
                $halfDayCount = 0;
            @endphp
            <tr>
                <td style="vertical-align: middle; border: 1px solid #000000;">{{ $employee->nama ?? $employee->name }}</td>
                <td style="vertical-align: middle; border: 1px solid #000000;">{{ $employee->nip ?? '-' }}</td>
                @foreach($days as $day)
                    @php
                        $data = $attendanceMatrix[$employee->id][$day['date']] ?? null;
                        $bgStyle = '';
                        $textStyle = '';
                        
                        // Default weekend style
                        if ($day['is_weekend'] && !$data) {
                            $bgStyle = 'background-color: #fef2f2;'; 
                        }

                        if ($data) {
                            // Match colors from web view (Tailwind classes mapped to Hex)
                            switch($data['status']) {
                                case 'present': 
                                case 'on_time':
                                case 'early_arrival': 
                                    $bgStyle = 'background-color: #22c55e;'; // green-500
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    $presentCount++;
                                    break;
                                case 'late':
                                    $bgStyle = 'background-color: #facc15;'; // yellow-400
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    $lateCount++;
                                    break;
                                case 'absent':
                                    $bgStyle = 'background-color: #ef4444;'; // red-500
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    $absentCount++;
                                    break;
                                case 'incomplete':
                                    $bgStyle = 'background-color: #9333ea;'; // purple-600
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    $incompleteCount++;
                                    break;
                                case 'half_day':
                                    $bgStyle = 'background-color: #60a5fa;'; // blue-400
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    $halfDayCount++;
                                    break;
                                case 'sick':
                                    $bgStyle = 'background-color: #9ca3af;'; // gray-400
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    $sickCount++;
                                    break;
                                case 'leave':
                                    $bgStyle = 'background-color: #f472b6;'; // pink-400
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    $leaveCount++;
                                    break;
                            }
                        }
                    @endphp
                    <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; {{ $bgStyle }} {{ $textStyle }}">
                        @if($data)
                            <div style="font-size: 8px;">{{ $data['short_label'] }} {{ $data['check_in'] ?? '--:--' }}-{{ $data['check_out'] ?? '--:--' }}</div>
                        @endif
                    </td>
                @endforeach
                <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; font-weight: bold;">{{ $lateCount > 0 ? $lateCount : '' }}</td>
                <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; font-weight: bold;">{{ $incompleteCount > 0 ? $incompleteCount : '' }}</td>
                <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; font-weight: bold;">{{ $presentCount > 0 ? $presentCount : '' }}</td>
                <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; font-weight: bold;">{{ $absentCount > 0 ? $absentCount : '' }}</td>
                <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; font-weight: bold;">{{ $sickCount > 0 ? $sickCount : '' }}</td>
                <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; font-weight: bold;">{{ $leaveCount > 0 ? $leaveCount : '' }}</td>
                <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; font-weight: bold;">{{ $halfDayCount > 0 ? $halfDayCount : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
