<table>
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: left; vertical-align: middle; width: 300px; border: 1px solid #000000;">Karyawan</th>
            <th style="font-weight: bold; text-align: left; vertical-align: middle; width: 150px; border: 1px solid #000000;">NIP</th>
            @foreach($days as $day)
                <th style="font-weight: bold; text-align: center; vertical-align: middle; width: 50px; border: 1px solid #000000; {{ $day['is_holiday'] ? 'background-color: #fee2e2; color: #dc2626;' : ($day['is_weekend'] ? 'background-color: #fef2f2; color: #f87171;' : 'background-color: #f9fafb; color: #9ca3af;') }}">
                    {{ $day['day'] }}<br>
                    {{ $day['weekday'] }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $employee)
            <tr>
                <td style="vertical-align: middle; border: 1px solid #000000;">{{ $employee->full_name_with_title ?? $employee->name }}</td>
                <td style="vertical-align: middle; border: 1px solid #000000;">{{ $employee->nip ?? '-' }}</td>
                @foreach($days as $day)
                    @php
                        $data = $attendanceMatrix[$employee->id][$day['day']] ?? null;
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
                                    break;
                                case 'late':
                                    $bgStyle = 'background-color: #facc15;'; // yellow-400
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    break;
                                case 'absent':
                                    $bgStyle = 'background-color: #ef4444;'; // red-500
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    break;
                                case 'half_day':
                                    $bgStyle = 'background-color: #60a5fa;'; // blue-400
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    break;
                                case 'sick':
                                    $bgStyle = 'background-color: #9ca3af;'; // gray-400
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    break;
                                case 'leave':
                                    $bgStyle = 'background-color: #c084fc;'; // purple-400
                                    $textStyle = 'color: #ffffff; font-weight: bold;';
                                    break;
                            }
                        }
                    @endphp
                    <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; {{ $bgStyle }} {{ $textStyle }}">
                        {{ $data ? $data['short_label'] : '' }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
