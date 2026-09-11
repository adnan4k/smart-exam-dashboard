{{--
    Inline LaTeX for question and answer text.

    Authors write maths inside ordinary sentences - "What is $\sqrt{x^2+y^2}$
    when $x=3$?" - and it is stored exactly like that. Anything rendered inside
    a [data-math] element is typeset here, in the browser, so the stored text
    stays portable for the API and the mobile client.

    Loaded synchronously and before any other script on purpose: Quill's formula
    button looks for window.katex the moment the editor is built.
--}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" crossorigin="anonymous"></script>

<style>
    .math-text .katex { font-size: 1.05em; }
    .math-preview {
        display: block;
        border: 1px dashed #cbd5e1;
        border-radius: 0.5rem;
        background: #f8fafc;
        color: #334155;
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
        line-height: 1.6;
        overflow-x: auto;
    }
    .math-preview-label {
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-right: 0.35rem;
    }
    .math-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        border-radius: 0.4rem;
        padding: 0.15rem 0.45rem;
        font-size: 0.68rem;
        font-weight: 600;
        line-height: 1.4;
        transition: all 180ms cubic-bezier(0.4, 0, 0.2, 1);
    }
    .math-btn:hover {
        border-color: var(--color-brand, #58706D);
        color: var(--color-brand, #58706D);
        background: #f0f4f2;
    }
</style>

<script>
    (function () {
        const OPTIONS = {
            delimiters: [
                { left: '$$', right: '$$', display: true },
                { left: '\\[', right: '\\]', display: true },
                { left: '$',  right: '$',  display: false },
                { left: '\\(', right: '\\)', display: false },
            ],
            ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code', 'option', 'input'],
            throwOnError: false,
            errorColor: '#e11d48',
        };

        const ready = () => typeof window.renderMathInElement === 'function';

        /* KaTeX's auto-render has no notion of an escaped delimiter: given
           "costs \\$5 and \\$9" it would treat everything between the two dollars
           as one formula. So an escaped dollar is swapped for a placeholder
           before typesetting and put back as a plain "$" afterwards. */
        const LITERAL_DOLLAR = '\uE000';

        function restoreDollars(el) {
            const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null);
            const nodes = [];

            while (walker.nextNode()) nodes.push(walker.currentNode);

            nodes.forEach((node) => {
                if (node.nodeValue.indexOf(LITERAL_DOLLAR) !== -1) {
                    node.nodeValue = node.nodeValue.split(LITERAL_DOLLAR).join('$');
                }
            });
        }

        /**
         * Typesets one [data-math] element from its raw source, which is kept in
         * data-math-src. Rendering always restarts from that source, so running
         * this twice - and Livewire morphs make that routine - is harmless.
         */
        function renderOne(el) {
            const source = el.dataset.mathSrc !== undefined ? el.dataset.mathSrc : el.textContent;

            el.dataset.mathSrc = source;
            el.textContent = source.split('\\$').join(LITERAL_DOLLAR);

            try {
                window.renderMathInElement(el, OPTIONS);
            } catch (e) {
                /* A half typed formula is normal while authoring. */
            }

            restoreDollars(el);
        }

        /** Typesets every [data-math] element inside root (or the whole page). */
        function render(root) {
            if (! ready()) return;

            const scope = root && root.nodeType === 1 ? root : document.body;
            const targets = [];

            if (scope.matches && scope.matches('[data-math]')) targets.push(scope);
            if (scope.querySelectorAll) targets.push(...scope.querySelectorAll('[data-math]'));

            targets.forEach(renderOne);
        }

        window.MathText = {
            options: OPTIONS,
            render,

            /** Live preview: drop raw text into el and typeset it. */
            preview(el, text) {
                if (! el) return;

                el.dataset.mathSrc = text == null ? '' : String(text);

                if (ready()) {
                    renderOne(el);
                } else {
                    el.textContent = el.dataset.mathSrc;
                }
            },

            /**
             * Wraps whatever is selected in an input/textarea with $…$ (or any
             * snippet), so an author never has to remember the delimiters.
             */
            insert(field, snippet) {
                if (! field) return;

                const start = field.selectionStart ?? field.value.length;
                const end = field.selectionEnd ?? field.value.length;
                const selected = field.value.slice(start, end);
                const at = snippet.indexOf('@');
                const body = snippet.replace('@', selected);

                // With text selected the caret lands after the formula; with
                // nothing selected it lands where the author has to type.
                const caret = selected
                    ? start + body.length
                    : start + (at >= 0 ? at : body.length);

                field.value = field.value.slice(0, start) + body + field.value.slice(end);
                field.focus();
                field.setSelectionRange(caret, caret);

                // Livewire and any inline preview listen for these.
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            },
        };

        function hookLivewire() {
            if (! window.Livewire || typeof window.Livewire.hook !== 'function') return false;

            window.Livewire.hook('morphed', ({ el }) => render(el));
            window.Livewire.hook('morph.added', ({ el }) => render(el));

            return true;
        }

        document.addEventListener('DOMContentLoaded', () => { render(); hookLivewire(); });
        document.addEventListener('livewire:init', hookLivewire);
        document.addEventListener('livewire:navigated', () => render());
    })();
</script>
