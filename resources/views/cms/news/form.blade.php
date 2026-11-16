@php
    $article = $article ?? null;
    $initialBody = \App\Cms\CmsNewsHtml::editorHtml((string) old('body', $article->body ?? ''));
    $categories = \App\Cms\CmsNewsCategories::all();
    $selectedCategory = old('category', $article->category ?? \App\Cms\CmsNewsCategories::DEFAULT);
@endphp
<div
    class="space-y-6"
    x-data="{
        title: @js(old('title', $article->title ?? '')),
        excerpt: @js(old('excerpt', $article->excerpt ?? '')),
        category: @js($selectedCategory),
        bodyHtml: @js($initialBody),
        showPreview: true,
        init() {
            this.$refs.editor.innerHTML = this.bodyHtml || '<p></p>';
            this.syncBody();
        },
        syncBody() {
            this.bodyHtml = this.$refs.editor.innerHTML;
            this.$refs.bodyField.value = this.bodyHtml;
        },
        command(name, value = null) {
            this.$refs.editor.focus();
            document.execCommand(name, false, value);
            this.syncBody();
        },
        block(tag) {
            this.command('formatBlock', tag);
        },
        addLink() {
            const url = window.prompt('Enter a web address starting with https:// or http://');
            if (!url) {
                return;
            }
            if (!/^https?:\/\//i.test(url)) {
                window.alert('Links must start with http:// or https://');
                return;
            }
            this.command('createLink', url);
        },
        onPaste(event) {
            event.preventDefault();
            const text = (event.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
            this.syncBody();
        }
    }"
>
    <section class="rounded-lg border border-green-100 bg-white p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">A. Article information</h3>
        <div class="mt-4 space-y-5">
            <div>
                <x-cms.label for="title" :required="true">Article title</x-cms.label>
                <input id="title" name="title" x-model="title" value="{{ old('title', $article->title ?? '') }}" required maxlength="255"
                       aria-invalid="{{ $errors->has('title') ? 'true' : 'false' }}"
                       aria-describedby="{{ $errors->has('title') ? 'title-error' : '' }}"
                       class="cms-input {{ $errors->has('title') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="title" />
            </div>
            <div>
                <x-cms.label for="category" :required="true">Category</x-cms.label>
                <x-cms.help>Choose the public category for this article. Values can be extended later without changing the database type.</x-cms.help>
                <select id="category" name="category" x-model="category" required
                        class="cms-input mt-1 {{ $errors->has('category') ? 'is-invalid' : '' }}">
                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption }}" @selected($selectedCategory === $categoryOption)>{{ $categoryOption }}</option>
                    @endforeach
                </select>
                <x-cms.field-error name="category" />
            </div>
            <div>
                <x-cms.label for="excerpt" :required="true">Short excerpt</x-cms.label>
                <x-cms.help>Short summary shown on listing pages. Maximum 500 characters. Plain text only.</x-cms.help>
                <textarea id="excerpt" name="excerpt" x-model="excerpt" required maxlength="500" rows="3"
                          aria-invalid="{{ $errors->has('excerpt') ? 'true' : 'false' }}"
                          class="cms-input mt-1 {{ $errors->has('excerpt') ? 'is-invalid' : '' }}">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                <x-cms.field-error name="excerpt" />
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-green-100 bg-white p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">B. Featured image</h3>
        <p class="mt-1 text-sm text-gray-600">Optional. Stored privately. The public website only displays the image after the article is published and the image is cleared for public use.</p>
        <div class="mt-4 space-y-4">
            @include('cms.partials.image-upload', [
                'label' => $article?->featuredImage ? 'Replace featured image' : 'Featured image',
                'currentUrl' => $article?->featuredImage ? route('cms.media.preview', $article->featuredImage) : null,
                'currentName' => $article?->featuredImage?->original_name,
            ])
            <div>
                <x-cms.label for="featured_image_alt">Alternative text</x-cms.label>
                <x-cms.help>Describe the image for people who cannot see it. Recommended whenever an image is attached.</x-cms.help>
                <input id="featured_image_alt" name="featured_image_alt" maxlength="255"
                       value="{{ old('featured_image_alt', $article->featured_image_alt ?? '') }}"
                       class="cms-input mt-1 {{ $errors->has('featured_image_alt') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="featured_image_alt" />
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-green-100 bg-white p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">C. Article content</h3>
        <div class="mt-4">
            <x-cms.label for="body-editor" :required="true">Article body</x-cms.label>
            <x-cms.help>Use the toolbar for headings, emphasis, lists and web links. Formatting is sanitised when you save. Existing plain-text articles remain readable.</x-cms.help>
            <div class="cms-editor-toolbar mt-2" role="toolbar" aria-label="Article formatting">
                <button type="button" @click="block('p')">Paragraph</button>
                <button type="button" @click="block('h2')">Heading 2</button>
                <button type="button" @click="block('h3')">Heading 3</button>
                <button type="button" @click="command('bold')"><span aria-hidden="true"><strong>B</strong></span><span class="sr-only">Bold</span></button>
                <button type="button" @click="command('italic')"><span aria-hidden="true"><em>I</em></span><span class="sr-only">Italic</span></button>
                <button type="button" @click="command('insertUnorderedList')">Bulleted list</button>
                <button type="button" @click="command('insertOrderedList')">Numbered list</button>
                <button type="button" @click="addLink()">Link</button>
                <button type="button" @click="command('undo')">Undo</button>
                <button type="button" @click="command('redo')">Redo</button>
            </div>
            <div
                id="body-editor"
                x-ref="editor"
                class="cms-editor-surface cms-news-body {{ $errors->has('body') ? 'is-invalid' : '' }}"
                contenteditable="true"
                role="textbox"
                aria-multiline="true"
                aria-required="true"
                aria-invalid="{{ $errors->has('body') ? 'true' : 'false' }}"
                @input="syncBody()"
                @blur="syncBody()"
                @paste="onPaste($event)"
            ></div>
            <textarea id="body" name="body" x-ref="bodyField" class="sr-only" required minlength="20" tabindex="-1" aria-hidden="true">{{ old('body', $article->body ?? '') }}</textarea>
            <x-cms.field-error name="body" />
        </div>

        <div class="mt-5 rounded-lg border border-green-100 bg-green-50/30 p-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-semibold text-green-950">Live preview</p>
                <button type="button" class="cms-btn cms-btn-secondary" @click="showPreview = !showPreview" x-text="showPreview ? 'Hide preview' : 'Show preview'"></button>
            </div>
            <div x-show="showPreview" class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-green-800">Staff preview only — not published</p>
                <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-green-700" x-text="category"></p>
                <h3 class="mt-2 text-2xl font-bold text-green-950" x-text="title || 'Article title'"></h3>
                <p class="mt-2 text-gray-700" x-text="excerpt || 'Excerpt will appear here.'"></p>
                <div class="cms-news-body mt-4 text-gray-800" x-html="bodyHtml"></div>
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-green-100 bg-green-50/40 p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">D. Publication</h3>
        <p class="mt-1 text-sm text-gray-600">Leave blank to show the article as soon as it is published. Use a start date to delay public display, and an end date to remove it automatically.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <x-cms.label for="published_from">Publication start date</x-cms.label>
                <input id="published_from" type="datetime-local" name="published_from"
                       value="{{ old('published_from', isset($article->published_from) ? $article->published_from->format('Y-m-d\\TH:i') : '') }}"
                       class="cms-input {{ $errors->has('published_from') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="published_from" />
            </div>
            <div>
                <x-cms.label for="published_until">Publication end date</x-cms.label>
                <input id="published_until" type="datetime-local" name="published_until"
                       value="{{ old('published_until', isset($article->published_until) ? $article->published_until->format('Y-m-d\\TH:i') : '') }}"
                       class="cms-input {{ $errors->has('published_until') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="published_until" />
            </div>
        </div>
    </section>

    <details class="rounded-lg border border-gray-200 bg-gray-50 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-gray-800">E. Advanced settings</summary>
        <div class="mt-3">
            <x-cms.label for="slug">Web address (slug)</x-cms.label>
            <x-cms.help>Optional. Letters, numbers and hyphens only. Leave blank to generate from the title.</x-cms.help>
            <input id="slug" name="slug" value="{{ old('slug', $article->slug ?? '') }}" maxlength="191"
                   class="cms-input mt-1 {{ $errors->has('slug') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="slug" />
        </div>
    </details>
</div>
