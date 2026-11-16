{{-- Progress bars for each spending limit. Needs: $usage = $wallet->limitUsage() --}}
@foreach ($usage as $row)
    <div class="limit-row">
        <div class="d-flex justify-content-between small mb-1">
            <span class="fw-medium">{{ $row['label'] }}</span>
            <span class="text-secondary">৳{{ number_format($row['spent'], 2) }} of ৳{{ number_format($row['limit'], 2) }}</span>
        </div>

        <div class="progress limit-progress" role="progressbar" aria-label="{{ $row['label'] }} spending"
             aria-valuenow="{{ $row['percent'] }}" aria-valuemin="0" aria-valuemax="100">
            <div @class([
                    'progress-bar',
                    'bg-success' => $row['status'] === 'ok',
                    'bg-warning' => $row['status'] === 'warning',
                    'bg-danger' => $row['status'] === 'reached',
                 ]) style="width: {{ $row['percent'] }}%"></div>
        </div>

        <div @class([
                'small mt-1',
                'text-secondary' => $row['status'] === 'ok',
                'text-warning-emphasis fw-medium' => $row['status'] === 'warning',
                'text-danger fw-medium' => $row['status'] === 'reached',
             ])>
            @if ($row['status'] === 'reached')
                <i class="bi bi-x-octagon"></i> Limit reached
            @elseif ($row['status'] === 'warning')
                <i class="bi bi-exclamation-triangle"></i> Almost there: ৳{{ number_format($row['remaining'], 2) }} left
            @else
                ৳{{ number_format($row['remaining'], 2) }} left
            @endif
        </div>
    </div>
@endforeach