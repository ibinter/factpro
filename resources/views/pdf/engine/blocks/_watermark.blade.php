@if(!empty($watermark))
<div style="position:fixed;top:18mm;right:10mm;z-index:1000;transform:rotate(0deg);">
  <div style="border:2px solid rgba(220,38,38,0.4);border-radius:4px;padding:4px 10px;background:rgba(254,226,226,0.5);">
    <span style="font-size:9px;font-weight:bold;color:rgba(220,38,38,0.6);text-transform:uppercase;letter-spacing:2px;">
      {{ $watermark }}
    </span>
  </div>
</div>
@endif
