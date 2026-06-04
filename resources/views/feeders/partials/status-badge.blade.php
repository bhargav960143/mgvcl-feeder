@php
$map = [
    'fully_on'     => ['class' => 'badge-fully-on',     'label' => 'Fully ON',     'icon' => 'circle-fill text-white'],
    'partially_on' => ['class' => 'badge-partially-on', 'label' => 'Partially ON', 'icon' => 'circle-half'],
    'fully_off'    => ['class' => 'badge-fully-off',     'label' => 'Fully OFF',    'icon' => 'circle'],
];
$s = $map[$status] ?? ['class' => 'bg-secondary', 'label' => $status, 'icon' => 'circle'];
@endphp
<span class="badge {{ $s['class'] }}" style="font-size:.8rem; padding:.35em .65em;">
    {{ $s['label'] }}
</span>
