@extends('layouts.app')

@section('content')
<div class="main-column" style="max-width: 600px; margin: 40px auto; padding: 20px;">
    <section class="panel" style="padding: 24px;">
        <h2>Report Post</h2>
        <p class="subtext">Tell us why you're reporting this post.</p>

        <!-- Form submits to the report.store route -->
        <form action="{{ route('report.store') }}" method="POST" id="report-form">
            @csrf
            <!-- Hidden input holds the Post ID from the URL -->
            <input type="hidden" name="post_id" id="post_id" value="{{ $postId ?? '' }}">

            <div class="vp-form-field" style="margin-bottom: 15px;">
                <label>Report Reason <span class="vp-required">*</span></label>
                <select class="field" name="reason" required>
                    <option value="">Select a reason</option>
                    <option value="Problem involving someone under 18">Problem involving someone under 18</option>
                    <option value="Bullying, harassment or abuse">Bullying, harassment or abuse</option>
                    <option value="Suicide or self-harm">Suicide or self-harm</option>
                    <option value="Violent, hateful or disturbing content">Violent, hateful or disturbing content</option>
                    <option value="Selling or promoting restricted items">Selling or promoting restricted items</option>
                    <option value="Adult content">Adult content</option>
                    <option value="Scam, fraud or false information">Scam, fraud or false information</option>
                    <option value="Intellectual property">Intellectual property</option>
                    <option value="I don't want to see this">I don't want to see this</option>
                </select>
            </div>

            <div class="vp-form-field" style="margin-bottom: 15px;">
                <label>Additional Details (Optional)</label>
                <textarea class="field" name="details" maxlength="500" placeholder="Describe your concern..." style="min-height: 100px;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('home') }}" class="btn secondary">Cancel</a>
                <button type="submit" class="btn">Submit Report</button>
            </div>
        </form>
    </section>
</div>
@endsection