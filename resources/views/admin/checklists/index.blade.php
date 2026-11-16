@php
    $card = 'rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900';
    $input = 'block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm focus:border-emerald-500 focus:ring-emerald-500';
    $btn = 'rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500';
    $quiet = 'text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:underline';
@endphp
<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <header class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="font-mono text-[11px] uppercase tracking-widest text-gray-600 dark:text-gray-400"><span class="text-emerald-600 dark:text-emerald-400">#</span> checklists</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-50">Checklists</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Private by default. Keep lists for work, marriage preparation or anything else, attach a link to any item, and share a list with a personalised link so someone else can view or tick it.</p>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-gray-900 dark:text-gray-50 tabular-nums">{{ $openItems }}</span> open {{ \Illuminate\Support\Str::plural('item', $openItems) }} across {{ $checklists->count() }} {{ \Illuminate\Support\Str::plural('list', $checklists->count()) }}</p>
        </header>

        @if (session('status'))
            <p role="status" class="rounded-2xl border border-emerald-200 dark:border-emerald-900 bg-emerald-50 dark:bg-emerald-950/40 px-4 py-3 text-sm text-emerald-900 dark:text-emerald-200">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-2xl border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/40 px-4 py-3 text-sm text-red-900 dark:text-red-200">
                <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- New checklist --}}
        <section class="{{ $card }} p-5" aria-labelledby="new-title">
            <h2 id="new-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">New checklist</h2>
            <form method="POST" action="{{ route('admin.checklists.store') }}" class="mt-3 grid gap-3 sm:grid-cols-6 items-end">
                @csrf
                <div class="sm:col-span-2">
                    <label for="template" class="block text-xs text-gray-600 dark:text-gray-400">Start from</label>
                    <select id="template" name="template" class="mt-1 {{ $input }}">
                        <option value="">Blank list</option>
                        @foreach ($templates as $key => $t)
                            <option value="{{ $key }}" @selected(old('template') === $key)>{{ $t['title'] }} ({{ count($t['items']) }} items)</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label for="new-title-input" class="block text-xs text-gray-600 dark:text-gray-400">Title <span class="text-gray-500">(optional with a template)</span></label>
                    <input id="new-title-input" name="title" maxlength="120" value="{{ old('title') }}" placeholder="e.g. Marriage prep" class="mt-1 {{ $input }}">
                </div>
                <div><button type="submit" class="w-full {{ $btn }}">Create</button></div>
                <div class="sm:col-span-5">
                    <label for="new-slug" class="block text-xs text-gray-600 dark:text-gray-400">Personalised link <span class="text-gray-500">(optional; one is generated if left blank)</span></label>
                    <div class="mt-1 flex items-center overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700">
                        <span class="whitespace-nowrap bg-gray-50 dark:bg-gray-800 px-2.5 py-2 text-xs text-gray-600 dark:text-gray-400">{{ url('/checklist') }}/</span>
                        <input id="new-slug" name="slug" value="{{ old('slug') }}" maxlength="60" placeholder="our-wedding" class="block w-full border-0 bg-white dark:bg-gray-950 dark:text-gray-100 text-sm focus:ring-emerald-500">
                    </div>
                </div>
            </form>
        </section>

        {{-- Lists --}}
        @if ($checklists->isEmpty())
            <section class="{{ $card }} px-6 py-14 text-center">
                <x-icon name="grid" class="mx-auto w-10 h-10 text-gray-400 dark:text-gray-500" />
                <h2 class="mt-3 text-base font-semibold text-gray-900 dark:text-gray-50">No checklists yet</h2>
                <p class="mx-auto mt-1 max-w-md text-sm text-gray-600 dark:text-gray-400">Pick <strong>Work checklist</strong> or <strong>Marriage preparation</strong> above to start with ready-made items, or create a blank list.</p>
            </section>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 items-start">
                @foreach ($checklists as $list)
                    @php $p = $list->progress(); @endphp
                    <section id="list-{{ $list->id }}" class="{{ $card }} scroll-mt-6" aria-labelledby="list-title-{{ $list->id }}">
                        <div class="p-5 pb-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 id="list-title-{{ $list->id }}" class="truncate text-base font-semibold text-gray-900 dark:text-gray-50">{{ $list->title }}</h2>
                                    @if ($list->description)<p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">{{ $list->description }}</p>@endif
                                    @if ($list->is_public)<p class="mt-1.5"><span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-950 px-2 py-0.5 font-mono text-[10px] uppercase text-emerald-800 dark:text-emerald-300"><x-icon name="link" class="w-3 h-3" />{{ $list->allow_ticking ? 'shared · can tick' : 'shared · view only' }}</span></p>@endif
                                </div>
                                <span data-progress-text @class(['shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium tabular-nums', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' => $p['total'] && $p['done'] === $p['total'], 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => ! ($p['total'] && $p['done'] === $p['total'])])>{{ $p['done'] }}/{{ $p['total'] }}</span>
                            </div>
                            <div data-progress-bar class="mt-3 h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden" role="progressbar" aria-valuenow="{{ $p['percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $list->title }} progress">
                                <div data-progress-fill class="h-full rounded-full bg-emerald-500 transition-all duration-300" style="width: {{ $p['percent'] }}%"></div>
                            </div>
                        </div>

                        <ul class="divide-y divide-gray-200 dark:divide-gray-800 border-t border-gray-200 dark:border-gray-800">
                            @forelse ($list->items as $item)
                                <li class="relative px-5 py-2.5">
                                    <div class="flex items-start gap-3">
                                        <form method="POST" action="{{ route('admin.checklist-items.toggle', $item) }}" data-checklist-toggle>
                                            @csrf @method('PATCH')
                                            <button type="submit" role="checkbox" data-title="{{ $item->title }}" aria-checked="{{ $item->is_done ? 'true' : 'false' }}" aria-label="{{ $item->is_done ? 'Mark not done' : 'Mark done' }}: {{ $item->title }}"
                                                    @class(['mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-md border transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500', 'border-emerald-600 bg-emerald-600 text-white' => $item->is_done, 'border-gray-400 dark:border-gray-600 hover:border-emerald-500' => ! $item->is_done])>
                                                @if ($item->is_done)<svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>@endif
                                            </button>
                                        </form>
                                        <div class="min-w-0 flex-1 pr-10 text-sm">
                                            <p data-item-title @class(['break-words', 'text-gray-900 dark:text-gray-100' => ! $item->is_done, 'text-gray-500 dark:text-gray-500 line-through' => $item->is_done])>
                                                @if ($item->url)
                                                    <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 dark:text-emerald-400 hover:underline">{{ $item->title }}<x-icon name="external" class="ml-1 inline w-3.5 h-3.5 align-[-2px]" /><span class="sr-only"> (opens in a new tab)</span></a>
                                                @else
                                                    {{ $item->title }}
                                                @endif
                                            </p>
                                            @if ($item->note)<p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">{{ $item->note }}</p>@endif

                                            <details class="[&>summary]:absolute [&>summary]:right-5 [&>summary]:top-3">
                                                <summary class="cursor-pointer {{ $quiet }}">Edit</summary>
                                                <form method="POST" action="{{ route('admin.checklist-items.update', $item) }}" class="mt-2 -mr-10 space-y-2">
                                                    @csrf @method('PATCH')
                                                    <input name="title" value="{{ $item->title }}" required maxlength="255" aria-label="Item title" class="{{ $input }}">
                                                    <input name="url" type="url" value="{{ $item->url }}" maxlength="2048" placeholder="https://… (optional link)" aria-label="Item link" class="{{ $input }}">
                                                    <input name="note" value="{{ $item->note }}" maxlength="500" placeholder="Note (optional)" aria-label="Item note" class="{{ $input }}">
                                                    <div class="flex items-center gap-3">
                                                        <button type="submit" class="{{ $btn }}">Save</button>
                                                        <button type="submit" form="del-item-{{ $item->id }}" class="text-xs font-medium text-red-700 dark:text-red-400 hover:underline">Delete item</button>
                                                    </div>
                                                </form>
                                                <form id="del-item-{{ $item->id }}" method="POST" action="{{ route('admin.checklist-items.destroy', $item) }}" class="hidden">@csrf @method('DELETE')</form>
                                            </details>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="px-5 py-6 text-sm text-gray-600 dark:text-gray-400">Nothing here yet. Add the first item below.</li>
                            @endforelse
                        </ul>

                        {{-- Add item --}}
                        <form method="POST" action="{{ route('admin.checklists.items.store', $list) }}" class="space-y-2 border-t border-gray-200 dark:border-gray-800 p-5">
                            @csrf
                            <label for="add-title-{{ $list->id }}" class="sr-only">New item</label>
                            <input id="add-title-{{ $list->id }}" name="title" required maxlength="255" placeholder="Add an item…" class="{{ $input }}">
                            <div class="grid grid-cols-5 gap-2">
                                <input name="url" type="url" maxlength="2048" placeholder="Link https://… (optional)" aria-label="Link" class="col-span-3 {{ $input }}">
                                <input name="note" maxlength="500" placeholder="Note" aria-label="Note" class="col-span-2 {{ $input }}">
                            </div>
                            <button type="submit" class="{{ $btn }}">Add item</button>
                        </form>

                        {{-- Share --}}
                        <details class="border-t border-gray-200 dark:border-gray-800 px-5 py-3" @if ($list->is_public || $errors->has('slug')) open @endif>
                            <summary class="cursor-pointer {{ $quiet }}">Share @if ($list->is_public) · <span class="text-emerald-700 dark:text-emerald-400">on</span>@endif</summary>
                            <form method="POST" action="{{ route('admin.checklists.sharing', $list) }}" class="mt-3 space-y-3">
                                @csrf @method('PATCH')
                                <div>
                                    <label for="slug-{{ $list->id }}" class="block text-xs text-gray-600 dark:text-gray-400">Personalised link <span class="text-red-700 dark:text-red-400" aria-hidden="true">*</span></label>
                                    <div class="mt-1 flex items-center overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700">
                                        <span class="whitespace-nowrap bg-gray-50 dark:bg-gray-800 px-2 py-2 text-xs text-gray-600 dark:text-gray-400">/checklist/</span>
                                        <input id="slug-{{ $list->id }}" name="slug" value="{{ $list->slug }}" required minlength="3" maxlength="60" class="block w-full border-0 bg-white dark:bg-gray-950 dark:text-gray-100 text-sm focus:ring-emerald-500">
                                    </div>
                                </div>
                                <label class="flex items-start gap-2 text-sm text-gray-800 dark:text-gray-200"><input type="checkbox" name="is_public" value="1" @checked($list->is_public) class="mt-0.5 rounded border-gray-300 dark:border-gray-700 text-emerald-600 focus:ring-emerald-500"><span>Anyone with the link can view</span></label>
                                <label class="flex items-start gap-2 text-sm text-gray-800 dark:text-gray-200"><input type="checkbox" name="allow_ticking" value="1" @checked($list->allow_ticking) class="mt-0.5 rounded border-gray-300 dark:border-gray-700 text-emerald-600 focus:ring-emerald-500"><span>…and they can tick items off<span class="block text-xs text-gray-600 dark:text-gray-400">They cannot add, edit or delete anything.</span></span></label>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Share the link only with people you trust: anyone who has it can open it. A longer, less guessable slug is safer.</p>
                                <button type="submit" class="{{ $btn }}">Save sharing</button>
                            </form>
                            @if ($list->is_public)
                                <div class="mt-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-3">
                                    <p class="break-all font-mono text-xs text-gray-800 dark:text-gray-200">{{ $list->publicUrl() }}</p>
                                    <div class="mt-2 flex items-center gap-4">
                                        <button type="button" data-copy="{{ $list->publicUrl() }}" class="{{ $quiet }}">Copy link</button>
                                        <a href="{{ $list->publicUrl() }}" target="_blank" rel="noopener noreferrer" class="{{ $quiet }}">Open <span class="sr-only">(new tab)</span></a>
                                    </div>
                                </div>
                            @endif
                        </details>

                        {{-- List settings --}}
                        <details class="border-t border-gray-200 dark:border-gray-800 px-5 py-3">
                            <summary class="cursor-pointer {{ $quiet }}">List settings</summary>
                            <form method="POST" action="{{ route('admin.checklists.update', $list) }}" class="mt-3 space-y-2">
                                @csrf @method('PATCH')
                                <input name="title" value="{{ $list->title }}" required maxlength="120" aria-label="List title" class="{{ $input }}">
                                <input name="description" value="{{ $list->description }}" maxlength="300" placeholder="Description (optional)" aria-label="List description" class="{{ $input }}">
                                <button type="submit" class="{{ $btn }}">Save</button>
                            </form>
                            <div class="mt-3 flex flex-wrap items-center gap-4">
                                <form method="POST" action="{{ route('admin.checklists.reset', $list) }}" x-data @submit="if (!confirm('Uncheck every item in this list?')) $event.preventDefault()">
                                    @csrf<button type="submit" class="{{ $quiet }}">Uncheck all</button>
                                </form>
                                <form method="POST" action="{{ route('admin.checklists.destroy', $list) }}" x-data @submit="if (!confirm('Delete this list and all its items?')) $event.preventDefault()">
                                    @csrf @method('DELETE')<button type="submit" class="text-xs font-medium text-red-700 dark:text-red-400 hover:underline">Delete list</button>
                                </form>
                            </div>
                        </details>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
