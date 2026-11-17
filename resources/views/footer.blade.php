@foreach($popups as $popup)
    @php($custom = $popup['style'] === 'custom')
    <div class="modal fade" id="plugiit-ann-popup-{{ $popup['id'] }}" tabindex="-1" aria-labelledby="plugiit-ann-popup-{{ $popup['id'] }}-title" aria-hidden="true"
         data-plugiit-ann-popup data-plugiit-ann-id="{{ $popup['id'] }}" data-plugiit-ann-version="{{ $popup['version'] }}" data-plugiit-ann-frequency="{{ $popup['frequency'] }}">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="plugiit-ann-popup-{{ $popup['id'] }}-title">
                        <i class="bi bi-{{ \Azuriom\Plugin\Announcements\Support\Announcements::icon($popup['style']) }} @unless($custom) text-{{ $popup['style'] }} @endunless me-1"
                           @if($custom) style="{{ \Azuriom\Plugin\Announcements\Support\Announcements::customColors($popup, 'popup-icon') }}" @endif aria-hidden="true"></i>
                        {{ $popup['title'] }}
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ trans('announcements::messages.close') }}"></button>
                </div>
                <div class="modal-body">
                    {!! $popup['html'] !!}

                    @if($popup['countdown'])
                        <span class="badge rounded-pill @unless($custom) text-bg-{{ $popup['style'] }} @endunless"
                              @if($custom) style="{{ \Azuriom\Plugin\Announcements\Support\Announcements::customColors($popup, 'popup-badge') }}" @endif
                              data-plugiit-ann-countdown="{{ $popup['ends_at'] }}"></span>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('announcements::messages.close') }}</button>
                    @if($popup['button_label'] && $popup['button_url'])
                        <a href="{{ $popup['button_url'] }}" class="btn @unless($custom) btn-{{ $popup['style'] }} @endunless"
                           @if($custom) style="{{ \Azuriom\Plugin\Announcements\Support\Announcements::customColors($popup, 'popup-button') }}" @endif>{{ $popup['button_label'] }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
<script type="application/json" id="plugiit-ann-config">@json([
    'texts' => trans('announcements::messages.countdown'),
])</script>
<script src="{{ plugin_asset('announcements', 'js/announcements.js') }}" defer></script>
