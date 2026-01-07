<?php

namespace App\Livewire\Admin\Extras;

use App\Models\Extra;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?int $extraId = null;

    public string $name = '';
    public ?string $code = null;
    public string $pricing_type = 'per_rental';
    public string $price = '0.00';
    public bool $is_active = true;
    public ?string $description = null;

    public function mount(?int $extraId = null): void
    {
        $this->extraId = $extraId;

        if ($extraId) {
            $e = Extra::findOrFail($extraId);

            $this->name = $e->name;
            $this->code = $e->code;
            $this->pricing_type = $e->pricing_type;
            $this->price = (string) $e->price;
            $this->is_active = (bool) $e->is_active;
            $this->description = $e->description;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('extras', 'code')->ignore($this->extraId)],
            'pricing_type' => ['required', Rule::in(['per_rental','per_day','per_hour','per_qty'])],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        $data['price'] = round((float) $data['price'], 2);

        if ($this->extraId) {
            Extra::findOrFail($this->extraId)->update($data);
            session()->flash('success', 'Доп. услуга обновлена.');
        } else {
            Extra::create($data);
            session()->flash('success', 'Доп. услуга создана.');
        }

        return redirect()->route('admin.extras.index');
    }

    public function render()
    {
        $pricingLabels = [
            'per_rental' => 'За аренду (фикс)',
            'per_day'    => 'За день',
            'per_hour'   => 'За час',
            'per_qty'    => 'За штуку',
        ];

        return view('livewire.admin.extras.form', compact('pricingLabels'));
    }
}
