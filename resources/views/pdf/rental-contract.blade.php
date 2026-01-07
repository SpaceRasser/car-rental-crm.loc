<!doctype html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Договор аренды № {{ $rental->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.35; }
        .title { text-align: center; font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .muted { color: #555; }
        .row { display: flex; justify-content: space-between; }
        .box { border: 1px solid #000; padding: 10px; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        td, th { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .no-border td { border: none; padding: 2px 0; }
        .sign { margin-top: 28px; }
        .sign td { border: none; padding: 10px 0; }
    </style>
</head>
<body>
<div class="title">ДОГОВОР АРЕНДЫ ТРАНСПОРТНОГО СРЕДСТВА № {{ $rental->id }}</div>

<div class="row muted">
    <div>г. {{ config('company.city', '—') }}</div>
    <div>{{ optional($rental->created_at)->format('d.m.Y') }}</div>
</div>

<div class="box">
    <b>1. Стороны</b><br><br>

    <table class="no-border" style="width:100%;">
        <tr>
            <td width="50%" style="padding-right:10px;">
                <b>Арендодатель:</b><br>
                {{ config('company.name', 'Автосалон') }}<br>
                {{ config('company.address', '—') }}<br>
                ИНН: {{ config('company.inn', '—') }}<br>
                Тел.: {{ config('company.phone', '—') }}
            </td>

            <td width="50%" style="padding-left:10px;">
                <b>Арендатор:</b><br>
                {{ $rental->client?->full_name ?? trim(($rental->client?->last_name ?? '').' '.($rental->client?->first_name ?? '').' '.($rental->client?->middle_name ?? '')) }}<br>
                Тел.: {{ $rental->client?->phone ?? '—' }}<br>
                Email: {{ $rental->client?->email ?? '—' }}<br>
                В/У: {{ $rental->client?->driver_license_number ?? '—' }}
            </td>
        </tr>
    </table>
</div>


<div class="box">
    <b>2. Автомобили</b>
    <table>
        <tr>
            <th>Марка / Модель</th>
            <th>Год</th>
            <th>VIN</th>
            <th>Гос. номер</th>
        </tr>
        @foreach(($groupRentals ?? collect([$rental])) as $item)
        <tr>
            <td>{{ $item->car?->brand }} {{ $item->car?->model }}</td>
            <td>{{ $item->car?->year ?? '—' }}</td>
            <td>{{ $item->car?->vin ?? '—' }}</td>
            <td>{{ $item->car?->plate_number ?? '—' }}</td>
        </tr>
        @endforeach
    </table>
</div>

<div class="box">
    <b>3. Срок аренды</b>
    <table class="no-border">
        <tr><td><b>Начало:</b> {{ optional($rental->starts_at)->format('d.m.Y H:i') ?? '—' }}</td></tr>
        <tr><td><b>Окончание:</b> {{ optional($rental->ends_at)->format('d.m.Y H:i') ?? '—' }}</td></tr>
    </table>
</div>

<div class="box">
    <b>4. Стоимость и депозит</b>

    <table>
        <tr>
            <th>Позиция</th>
            <th>Сумма</th>
        </tr>
        <tr>
            <td>Аренда (база)</td>
            <td>{{ number_format((float)$base, 2, '.', ' ') }} ₽</td>
        </tr>
        <tr>
            <td>Доп. услуги</td>
            <td>{{ number_format((float)$extrasTotal, 2, '.', ' ') }} ₽</td>
        </tr>

        @if((float)$discount > 0)
        <tr>
            <td>Скидка</td>
            <td>-{{ number_format((float)$discount, 2, '.', ' ') }} ₽</td>
        </tr>
        @endif

        @if((float)$penalty > 0)
        <tr>
            <td>Штрафы/доплаты</td>
            <td>+{{ number_format((float)$penalty, 2, '.', ' ') }} ₽</td>
        </tr>
        @endif

        <tr>
            <td><b>Стоимость аренды (итого)</b></td>
            <td><b>{{ number_format((float)$rent, 2, '.', ' ') }} ₽</b></td>
        </tr>
        <tr>
            <td>Депозит</td>
            <td>{{ number_format((float)$deposit, 2, '.', ' ') }} ₽</td>
        </tr>
        <tr>
            <td><b>Итого к оплате</b></td>
            <td><b>{{ number_format((float)$total, 2, '.', ' ') }} ₽</b></td>
        </tr>
        <tr>
            <td>Оплачено</td>
            <td>{{ number_format((float)$paid, 2, '.', ' ') }} ₽</td>
        </tr>
        <tr>
            <td>Остаток</td>
            <td>{{ number_format((float)$remaining, 2, '.', ' ') }} ₽</td>
        </tr>
    </table>

    @if(($extrasLines ?? collect())->count())
    <div style="margin-top:10px;">
        <b>Перечень доп. услуг</b>
        <table>
            <tr>
                <th>Услуга</th>
                <th>Тип</th>
                <th>Цена</th>
                <th>Кол-во</th>
                <th>Сумма</th>
            </tr>
            @foreach($extrasLines as $x)
            <tr>
                <td>{{ $x->name }}</td>
                <td>{{ $x->pricing_type === 'per_day' ? 'за день' : 'фикс.' }}</td>
                <td>{{ number_format((float)$x->price, 2, '.', ' ') }} ₽</td>
                <td>{{ $x->qty }}</td>
                <td>{{ number_format((float)$x->amount, 2, '.', ' ') }} ₽</td>
            </tr>
            @endforeach
        </table>
        @if(($extras ?? collect())->count())
        <div style="margin-top:10px;">
            <b>Дополнительные услуги</b>
            <table style="margin-top:6px;">
                <tr>
                    <th>Услуга</th>
                    <th>Тип</th>
                    <th>Цена</th>
                    <th>Кол-во</th>
                    <th>Сумма</th>
                </tr>

                @foreach($extras as $e)
                @php
                $type = (string) ($e->pivot->pricing_type ?? $e->pricing_type ?? 'fixed');
                $price = (float) ($e->pivot->price ?? $e->price ?? 0);
                $qty = max(1, (int) ($e->pivot->qty ?? 1));

                $typeLabel = $type === 'per_day' ? 'за день' : 'фикс';
                $line = $type === 'per_day'
                ? $price * $qty * ($days ?? 1)
                : $price * $qty;
                @endphp

                <tr>
                    <td>{{ $e->name }}</td>
                    <td>{{ $typeLabel }}</td>
                    <td>{{ number_format($price, 2, '.', ' ') }} ₽</td>
                    <td>{{ $qty }}</td>
                    <td>{{ number_format($line, 2, '.', ' ') }} ₽</td>
                </tr>
                @endforeach

                <tr>
                    <td colspan="4" style="text-align:right;"><b>Итого услуги</b></td>
                    <td><b>{{ number_format((float)($extrasTotal ?? 0), 2, '.', ' ') }} ₽</b></td>
                </tr>
            </table>

            <p class="muted" style="margin-top:6px;">
                Для услуг “за день” сумма = цена × кол-во × дней ({{ (int)($days ?? 1) }}).
            </p>
        </div>
        @else
        <p class="muted" style="margin-top:10px;">Дополнительные услуги: —</p>
        @endif

    </div>
    @endif

    <p class="muted" style="margin-top:8px;">
        Депозит возвращается после возврата автомобиля и проверки его состояния.
    </p>
</div>


<div class="box">
    <b>Реквизиты арендодателя</b>

    <table class="no-border" style="margin-top:6px;">
        <tr><td><b>Наименование:</b> {{ config('company.legal_name', config('company.name', '—')) }}</td></tr>
        <tr><td><b>ИНН:</b> {{ config('company.inn', '—') }} @if(config('company.kpp'))  <b>КПП:</b> {{ config('company.kpp') }} @endif</td></tr>
        @if(config('company.ogrn'))
        <tr><td><b>ОГРН:</b> {{ config('company.ogrn') }}</td></tr>
        @endif
        <tr><td><b>Адрес:</b> {{ config('company.address', '—') }}</td></tr>
        <tr><td><b>Телефон:</b> {{ config('company.phone', '—') }} @if(config('company.email'))  <b>Email:</b> {{ config('company.email') }} @endif</td></tr>

        @if(config('company.bank_name') || config('company.rs') || config('company.bik'))
        <tr><td style="padding-top:8px;"><b>Банковские реквизиты</b></td></tr>
        @if(config('company.bank_name'))
        <tr><td><b>Банк:</b> {{ config('company.bank_name') }}</td></tr>
        @endif
        <tr>
            <td>
                @if(config('company.bik')) <b>БИК:</b> {{ config('company.bik') }} @endif
                @if(config('company.rs')) &nbsp;&nbsp; <b>Р/с:</b> {{ config('company.rs') }} @endif
                @if(config('company.ks')) &nbsp;&nbsp; <b>К/с:</b> {{ config('company.ks') }} @endif
            </td>
        </tr>
        @endif

        @if(config('company.director_name') || config('company.director_basis'))
        <tr><td style="padding-top:8px;">
                <b>Представитель:</b>
                {{ config('company.director_position', '—') }}
                {{ config('company.director_name', '—') }}
                @if(config('company.director_basis'))
                , {{ config('company.director_basis') }}
                @endif
            </td></tr>
        @endif
    </table>
</div>


<div class="box">
    <b>5. Подписи сторон</b>
    <table class="sign">
        <tr>
            <td width="50%">
                <b>Арендодатель</b><br>
                {{ config('company.director_position', '—') }} {{ config('company.director_name', '—') }}<br><br>
                _____________________ / _____________________
            </td>
            <td width="50%">
                <b>Арендатор</b><br>
                {{ $rental->client?->full_name ?? '—' }}<br><br>
                _____________________ / _____________________
            </td>
        </tr>
    </table>
</div>

</body>
</html>
