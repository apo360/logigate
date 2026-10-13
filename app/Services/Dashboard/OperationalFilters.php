<?php

namespace App\Services\Dashboard;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class OperationalFilters
{
    public function __construct(public array $values, public CarbonImmutable $start, public CarbonImmutable $end) {}

    public static function fromRequest(Request $request): self
    {
        $today = CarbonImmutable::now('Africa/Luanda')->startOfDay();
        $values = Validator::make($request->query(), [
            'period' => 'nullable|in:month,quarter,year,last30,custom',
            'start' => 'required_if:period,custom|nullable|date_format:Y-m-d',
            'end' => 'required_if:period,custom|nullable|date_format:Y-m-d|before_or_equal:'.$today->toDateString(),
            'customer' => 'nullable|integer|min:1', 'type' => 'nullable|integer|min:1',
            'transport' => 'nullable|integer|min:1', 'country' => 'nullable|string|max:30',
            'office' => 'nullable|integer|min:1',
            'table' => 'nullable|in:oldest,clients,quality,unlinked,conclusions,country,destination,transport,office,port',
            'sort' => 'nullable|in:date,number,name,count,age', 'direction' => 'nullable|in:asc,desc',
        ])->validate();
        $period = $values['period'] ?? 'month';
        $start = match ($period) {
            'quarter' => $today->startOfQuarter(), 'year' => $today->startOfYear(),
            'last30' => $today->subDays(29),
            'custom' => CarbonImmutable::createFromFormat('!Y-m-d', $values['start'], 'Africa/Luanda'),
            default => $today->startOfMonth(),
        };
        $end = $period === 'custom' ? CarbonImmutable::createFromFormat('!Y-m-d', $values['end'], 'Africa/Luanda') : $today;
        if ($end->lt($start) || $start->diffInDays($end) > 3660) {
            throw ValidationException::withMessages(['start' => 'Use um intervalo ordenado de até dez anos.']);
        }
        $values = array_filter($values, fn ($value) => $value !== null && $value !== '');
        $values['period'] = $period;
        $values['start'] = $start->toDateString();
        $values['end'] = $end->toDateString();

        return new self($values, $start, $end);
    }

    public function reference(): string
    {
        return $this->end->toDateString();
    }

    public function utcBounds(): array
    {
        return [$this->start->utc()->format('Y-m-d H:i:s'), $this->end->addDay()->utc()->format('Y-m-d H:i:s')];
    }

    public function cacheValues(): array
    {
        return array_diff_key($this->values, array_flip(['table', 'sort', 'direction']));
    }

    public function previous(): self
    {
        $days = (int) $this->start->diffInDays($this->end) + 1;
        $end = $this->start->subDay();
        $start = $end->subDays($days - 1);

        return new self(array_merge($this->values, ['start' => $start->toDateString(), 'end' => $end->toDateString()]), $start, $end);
    }
}
