<?php

namespace App\Livewire\Admin\Extras;

use App\Models\Extra;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $active = ''; // '', '1', '0'
    public int $perPage = 15;

    protected $queryString = [
        'q' => ['except' => ''],
        'active' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updated($name): void
    {
        if (in_array($name, ['q', 'active', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function toggle(int $id): void
    {
        $extra = Extra::findOrFail($id);
        $extra->update(['is_active' => !$extra->is_active]);
    }

    public function render()
    {
        $pricingLabels = [
            'per_rental' => 'За аренду (фикс)',
            'per_day'    => 'За день',
            'per_hour'   => 'За час',
            'per_qty'    => 'За штуку',
        ];

        $extras = Extra::query()
            ->when($this->q !== '', function ($q) {
                $s = trim($this->q);
                $q->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', "%{$s}%")
                        ->orWhere('code', 'like', "%{$s}%");
                });
            })
            ->when($this->active !== '', fn($q) => $q->where('is_active', (bool) (int) $this->active))
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.admin.extras.index', compact('extras', 'pricingLabels'));
    }
}
