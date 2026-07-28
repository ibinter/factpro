@component('mail::message')

{!! nl2br(e($body)) !!}

@component('mail::button', ['url' => $documentUrl])
Voir le document
@endcomponent

---
*Ce message vous a été envoyé via {{ config('app.name') }}.*

@endcomponent
