{{-- Announcement bars. Themes can place them anywhere with @include('tkt-announcements::bars') --}}
@php
    $bars ??= \Azuriom\Plugin\TktAnnouncements\Support\Announcements::forRequest(request(), auth()->user())['bars'];
    $barMode = \Azuriom\Plugin\TktAnnouncements\Support\Announcements::barMode();
    $rotate = $barMode === 'rotate' && count($bars) > 1;
@endphp
@if(! empty($bars))
    <div id="tkt-announcements-bars" class="flex-shrink-0" role="region" aria-label="{{ trans('tkt-announcements::messages.region') }}"
         data-tkt-announcements-mode="{{ $barMode }}" data-tkt-announcements-delay="{{ \Azuriom\Plugin\TktAnnouncements\Support\Announcements::rotationDelay() }}">
        @foreach($bars as $bar)
            @php($custom = $bar['style'] === 'custom')
            <div class="alert @unless($custom) alert-{{ $bar['style'] }} @endunless rounded-0 border-top-0 border-start-0 border-end-0 mb-0 py-2 px-0 @if($rotate) fade show @endif @if($rotate && ! $loop->first) d-none @endif"
                 @if($custom) style="{{ \Azuriom\Plugin\TktAnnouncements\Support\Announcements::customColors($bar, 'bar') }}" @endif
                 data-tkt-announcements-bar data-tkt-announcements-id="{{ $bar['id'] }}" data-tkt-announcements-version="{{ $bar['version'] }}"
                 @if($bar['dismissible']) data-tkt-announcements-dismissible @endif>
                <div class="container d-flex align-items-center gap-2">
                    @if($rotate)
                        <div class="d-flex align-items-center flex-shrink-0 small" data-tkt-announcements-pager>
                            <button type="button" class="btn btn-sm btn-link text-reset text-decoration-none px-1 py-0" data-tkt-announcements-prev aria-label="{{ trans('tkt-announcements::messages.previous') }}">
                                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                            </button>
                            <span data-tkt-announcements-position>{{ $loop->iteration }}/{{ $loop->count }}</span>
                            <button type="button" class="btn btn-sm btn-link text-reset text-decoration-none px-1 py-0" data-tkt-announcements-next aria-label="{{ trans('tkt-announcements::messages.next') }}">
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endif

                    <div class="flex-grow-1 d-flex flex-wrap align-items-center justify-content-center column-gap-3 row-gap-1 text-center">
                        <div>
                            <i class="bi bi-{{ \Azuriom\Plugin\TktAnnouncements\Support\Announcements::icon($bar['style']) }} me-1" aria-hidden="true"></i>
                            <strong class="me-1">{{ $bar['title'] }}</strong>
                            <span>{!! $bar['html'] !!}</span>
                            @if($bar['countdown'])
                                <span class="badge rounded-pill @unless($custom) text-bg-{{ $bar['style'] }} @endunless ms-1"
                                      @if($custom) style="{{ \Azuriom\Plugin\TktAnnouncements\Support\Announcements::customColors($bar, 'bar-badge') }}" @endif
                                      data-tkt-announcements-countdown="{{ $bar['ends_at'] }}"></span>
                            @endif
                        </div>
                        @if($bar['button_label'] && $bar['button_url'])
                            <a href="{{ $bar['button_url'] }}" class="btn btn-sm @unless($custom) btn-{{ $bar['style'] }} @endunless"
                               @if($custom) style="{{ \Azuriom\Plugin\TktAnnouncements\Support\Announcements::customColors($bar, 'bar-button') }}" @endif>{{ $bar['button_label'] }}</a>
                        @endif
                    </div>

                    @if($bar['dismissible'])
                        <button type="button" class="btn btn-sm btn-link text-reset text-decoration-none flex-shrink-0 px-1 py-0" data-tkt-announcements-close aria-label="{{ trans('tkt-announcements::messages.dismiss', ['title' => $bar['title']]) }}">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <script>
        (function () {
            try {
                var bars = document.getElementById('tkt-announcements-bars');
                Array.prototype.forEach.call(bars.querySelectorAll('[data-tkt-announcements-dismissible]'), function (bar) {
                    if (window.localStorage.getItem('tkt-announcements-dismissed-' + bar.getAttribute('data-tkt-announcements-id')) === bar.getAttribute('data-tkt-announcements-version')) {
                        bar.parentNode.removeChild(bar);
                    }
                });
                var first = bars.querySelector('[data-tkt-announcements-bar]');
                if (!first) {
                    bars.parentNode.removeChild(bars);
                } else {
                    first.classList.remove('d-none');
                }
            } catch (e) {
            }
        })();
    </script>
@endif
