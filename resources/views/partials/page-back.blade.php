@php
    $previousUrl = url()->previous();
    $isSameSitePage = $previousUrl === url('/') || str_starts_with($previousUrl, url('/').'/');
    $backUrl = $isSameSitePage && $previousUrl !== url()->full() ? $previousUrl : $fallback;
@endphp
<a class="page-back" href="{{ $backUrl }}">
    <span aria-hidden="true">←</span> Back
</a>
