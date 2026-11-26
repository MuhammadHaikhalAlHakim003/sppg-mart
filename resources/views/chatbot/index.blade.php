@extends('layouts.app')

@section('title', 'Asisten Informasi - SPPG MART')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-5 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-800 to-cyan-700 px-6 py-7 text-white shadow-lg sm:px-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-100">SPPG MART · Asisten Informasi</p>
        <h1 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Asisten Informasi SPPG</h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-sky-50">
            Ajukan pertanyaan seputar program Makan Bergizi Gratis berdasarkan dokumen referensi yang tersedia.
        </p>
    </div>

    <section class="flex min-h-[620px] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-sky-100 text-xl" aria-hidden="true">✦</span>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-slate-900">Asisten AI</h2>
                <p class="text-xs text-slate-500">Jawaban dilengkapi sumber dokumen jika tersedia</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Siap digunakan</span>
        </header>

        <div id="assistant-messages" class="flex-1 space-y-4 overflow-y-auto bg-slate-50 px-4 py-5 sm:px-6" aria-live="polite">
            <div class="flex justify-start">
                <div class="max-w-[90%] rounded-2xl rounded-bl-md border border-slate-200 bg-white px-4 py-3 shadow-sm sm:max-w-[80%]">
                    <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">Halo! Saya Asisten Informasi SPPG. Silakan ajukan pertanyaan mengenai ketentuan, petunjuk teknis, atau informasi program Makan Bergizi Gratis.</p>
                    <p class="mt-2 text-[11px] text-slate-400">Asisten Informasi</p>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 bg-white p-4 sm:p-5">
            <div class="mb-3 flex flex-wrap gap-2">
                <button type="button" data-suggestion="Siapa saja yang menjadi penerima manfaat program Makan Bergizi Gratis?" class="rounded-full border border-slate-200 px-3 py-1.5 text-left text-xs text-slate-600 transition hover:border-sky-300 hover:bg-sky-50">Penerima manfaat MBG</button>
                <button type="button" data-suggestion="Apa saja ketentuan dalam petunjuk teknis MBG?" class="rounded-full border border-slate-200 px-3 py-1.5 text-left text-xs text-slate-600 transition hover:border-sky-300 hover:bg-sky-50">Ketentuan juknis MBG</button>
                <button type="button" data-suggestion="Bagaimana mekanisme pemberian makanan dalam program MBG?" class="rounded-full border border-slate-200 px-3 py-1.5 text-left text-xs text-slate-600 transition hover:border-sky-300 hover:bg-sky-50">Mekanisme pemberian</button>
            </div>

            <form id="assistant-form" action="{{ route('chatbot.send') }}" method="POST">
                @csrf
                <label for="assistant-question" class="sr-only">Tulis pertanyaan</label>
                <div class="flex items-end gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-2 focus-within:border-sky-400 focus-within:ring-4 focus-within:ring-sky-100">
                    <textarea id="assistant-question" name="question" rows="1" maxlength="4000"
                        placeholder="Tulis pertanyaan tentang MBG atau SPPG..."
                        class="max-h-36 min-h-11 flex-1 resize-none bg-transparent px-3 py-3 text-sm text-slate-800 outline-none placeholder:text-slate-400"
                        required></textarea>
                    <button id="assistant-send" type="submit" class="flex h-11 shrink-0 items-center justify-center rounded-xl bg-sky-700 px-5 text-sm font-bold text-white transition hover:bg-sky-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                        Kirim
                    </button>
                </div>
                <p id="assistant-status" class="mt-2 min-h-4 text-xs text-slate-400">Jawaban dibuat berdasarkan dokumen yang tersedia pada basis pengetahuan.</p>
            </form>
        </div>
    </section>
</div>

<script>
(() => {
    const form = document.getElementById('assistant-form');
    const input = document.getElementById('assistant-question');
    const sendButton = document.getElementById('assistant-send');
    const messages = document.getElementById('assistant-messages');
    const status = document.getElementById('assistant-status');

    if (!form || !input || !sendButton || !messages || !status) return;

    const history = [];
    const csrf = form.querySelector('input[name="_token"]').value;

    function addMessage(role, content, sources = [], model = null) {
        const row = document.createElement('div');
        row.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'}`;

        const bubble = document.createElement('div');
        bubble.className = role === 'user'
            ? 'max-w-[90%] rounded-2xl rounded-br-md bg-sky-700 px-4 py-3 text-white shadow-sm sm:max-w-[80%]'
            : 'max-w-[95%] rounded-2xl rounded-bl-md border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm sm:max-w-[85%]';

        const text = document.createElement('p');
        text.className = 'whitespace-pre-wrap break-words text-sm leading-relaxed';
        text.textContent = content;
        bubble.appendChild(text);

        if (role === 'assistant' && sources.length) {
            const sourceBox = document.createElement('div');
            sourceBox.className = 'mt-4 border-t border-slate-100 pt-3';

            const sourceTitle = document.createElement('p');
            sourceTitle.className = 'mb-2 text-xs font-bold text-slate-600';
            sourceTitle.textContent = `Sumber referensi (${sources.length})`;
            sourceBox.appendChild(sourceTitle);

            sources.forEach((source, index) => {
                const item = document.createElement('div');
                item.className = 'mb-2 rounded-lg bg-slate-50 px-3 py-2';

                const heading = document.createElement('p');
                heading.className = 'text-xs font-semibold text-slate-700';
                const filename = source.source || source.filename || `Dokumen ${index + 1}`;
                const page = source.page ? ` · Halaman ${source.page}` : '';
                heading.textContent = `${filename}${page}`;
                item.appendChild(heading);

                if (source.similarity !== undefined && source.similarity !== null) {
                    const score = document.createElement('p');
                    score.className = 'mt-1 text-[11px] text-slate-400';
                    const numericScore = Number(source.similarity);
                    score.textContent = `Similarity: ${Number.isFinite(numericScore) ? numericScore.toFixed(4) : source.similarity}`;
                    item.appendChild(score);
                }

                if (source.text) {
                    const excerpt = document.createElement('p');
                    excerpt.className = 'mt-1 whitespace-pre-wrap text-xs leading-relaxed text-slate-600';
                    excerpt.textContent = source.text.length > 700 ? `${source.text.slice(0, 700)}…` : source.text;
                    item.appendChild(excerpt);
                }

                sourceBox.appendChild(item);
            });

            bubble.appendChild(sourceBox);
        }

        const footer = document.createElement('p');
        footer.className = `mt-2 text-[10px] ${role === 'user' ? 'text-right text-sky-100' : 'text-slate-400'}`;
        footer.textContent = role === 'user' ? 'Anda' : `Asisten AI${model ? ` · ${model}` : ''}`;
        bubble.appendChild(footer);

        row.appendChild(bubble);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
    }

    function setBusy(busy) {
        sendButton.disabled = busy;
        input.disabled = busy;
        sendButton.textContent = busy ? 'Memproses…' : 'Kirim';
    }

    async function submitQuestion(question) {
        const cleanQuestion = question.trim();
        if (!cleanQuestion || sendButton.disabled) return;

        addMessage('user', cleanQuestion);
        history.push({ role: 'user', content: cleanQuestion });
        const historyForRequest = history.slice(-10);

        input.value = '';
        input.style.height = 'auto';
        setBusy(true);
        status.textContent = 'Asisten sedang mencari informasi dan menyusun jawaban...';

        const loading = document.createElement('p');
        loading.id = 'assistant-loading';
        loading.className = 'text-xs text-slate-500';
        loading.textContent = 'Asisten sedang mengetik...';
        messages.appendChild(loading);
        messages.scrollTop = messages.scrollHeight;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    question: cleanQuestion,
                    history: historyForRequest.slice(0, -1),
                }),
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || data.errors?.question?.[0] || 'Pertanyaan gagal diproses.');
            }

            if (!data.answer) {
                throw new Error('Layanan AI tidak mengirimkan jawaban.');
            }

            addMessage('assistant', data.answer, Array.isArray(data.sources) ? data.sources : [], data.model || null);
            history.push({ role: 'assistant', content: data.answer });
            if (history.length > 20) history.splice(0, history.length - 20);
            status.textContent = 'Jawaban berhasil dibuat.';
        } catch (error) {
            addMessage('assistant', error.message || 'Terjadi kesalahan saat menghubungi layanan AI.');
            // Hapus pertanyaan terakhir dari riwayat agar pengguna dapat mencoba kembali.
            history.pop();
            status.textContent = 'Terjadi kendala. Silakan coba kembali.';
        } finally {
            document.getElementById('assistant-loading')?.remove();
            setBusy(false);
            input.focus();
        }
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        submitQuestion(input.value);
    });

    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 144)}px`;
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    document.querySelectorAll('[data-suggestion]').forEach((button) => {
        button.addEventListener('click', () => {
            input.value = button.dataset.suggestion;
            input.focus();
            input.dispatchEvent(new Event('input'));
        });
    });
})();
</script>
@endsection
