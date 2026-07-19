@if(!empty($document->notes))
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px 12px;margin-bottom:12px;background:#fffbeb;">
  <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:#92400e;margin-bottom:5px;letter-spacing:0.5px;">
    Notes / Observations
  </div>
  <div style="font-size:8.5px;color:#374151;line-height:1.6;">
    {!! nl2br(e($document->notes)) !!}
  </div>
</div>
@endif
