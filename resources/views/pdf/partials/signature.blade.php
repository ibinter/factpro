{{-- Signature électronique du client (cahier §22.1) — bloc « Bon pour accord ». --}}
@if (! empty($document->signature_path))
    @php
        $signatureDataUri = null;
        try {
            $disk = \Illuminate\Support\Facades\Storage::disk(config('factpro.proofs.disk'));
            if ($disk->exists($document->signature_path)) {
                $signatureDataUri = 'data:image/png;base64,'.base64_encode($disk->get($document->signature_path));
            }
        } catch (\Throwable $e) {
            $signatureDataUri = null;
        }
        $signedAt = $document->signed_at
            ? \Illuminate\Support\Carbon::parse($document->signed_at)->format('d/m/Y H:i')
            : null;
    @endphp
    <table style="width:100%; margin-top:22px;">
        <tr>
            <td style="width:40%"></td>
            <td style="width:60%; border:1px solid #cbd5e1; border-radius:4px; padding:8px 12px;">
                <div style="font-size:8px; text-transform:uppercase; letter-spacing:1px; color:#6B7C93;">
                    Bon pour accord — Signé électroniquement
                </div>
                @if ($signatureDataUri)
                    <img src="{{ $signatureDataUri }}" alt="Signature" style="max-height:60px; max-width:100%; margin:4px 0;">
                @endif
                <div style="font-size:10px; font-weight:bold; color:#1a2332;">
                    {{ $document->signed_by_name }}
                </div>
                @if ($signedAt)
                    <div style="font-size:8px; color:#6B7C93;">Le {{ $signedAt }}</div>
                @endif
                @if (! empty($document->signature_ip))
                    <div style="font-size:6px; color:#9aa7b8;">IP : {{ $document->signature_ip }}</div>
                @endif
            </td>
        </tr>
    </table>
@endif
