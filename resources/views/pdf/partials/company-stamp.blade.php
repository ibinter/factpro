{{-- Signature et cachet de l'entreprise émettrice (cahier §3 ERP Premium) --}}
@php
    $showSig   = ! empty($company->show_signature) && ! empty($company->signature_path);
    $showStamp = ! empty($company->show_stamp)     && ! empty($company->stamp_path);
    $sigUri    = null;
    $stampUri  = null;

    if ($showSig) {
        try {
            $raw = \Illuminate\Support\Facades\Storage::disk('public')->get($company->signature_path);
            $ext = pathinfo($company->signature_path, PATHINFO_EXTENSION);
            $mime = in_array($ext, ['png']) ? 'image/png' : 'image/jpeg';
            $sigUri = "data:{$mime};base64," . base64_encode($raw);
        } catch (\Throwable) { $showSig = false; }
    }

    if ($showStamp) {
        try {
            $raw = \Illuminate\Support\Facades\Storage::disk('public')->get($company->stamp_path);
            $ext = pathinfo($company->stamp_path, PATHINFO_EXTENSION);
            $mime = in_array($ext, ['png']) ? 'image/png' : 'image/jpeg';
            $stampUri = "data:{$mime};base64," . base64_encode($raw);
        } catch (\Throwable) { $showStamp = false; }
    }
@endphp

@if ($showSig || $showStamp)
<table style="width:100%; margin-top:28px;">
    <tr>
        <td style="width:55%">
            @if ($showSig)
                <div style="font-size:7px;text-transform:uppercase;letter-spacing:1px;color:#6B7C93;margin-bottom:4px;">
                    {{ $company->signature_label ?: 'Signature autorisée' }}
                </div>
                <img src="{{ $sigUri }}" alt="Signature" style="max-height:60px;max-width:200px;">
                <div style="font-size:8px;color:#1a2332;margin-top:3px;font-weight:bold;">{{ $company->name }}</div>
            @endif
        </td>
        <td style="width:45%; text-align:right;">
            @if ($showStamp)
                <img src="{{ $stampUri }}" alt="Cachet" style="max-height:80px;max-width:140px;opacity:0.85;">
            @endif
        </td>
    </tr>
</table>
@endif
