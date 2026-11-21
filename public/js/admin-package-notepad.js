document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.package-builder');
    if (!form) return;
    function attach() {
        form.querySelectorAll('textarea:not(.visually-hidden):not([data-notepad])').forEach(input => {
            input.dataset.notepad = 'true';
            const field = input.closest('.field');
            if (!field) return;
            const toolbar = document.createElement('div');
            toolbar.className = 'package-notepad-tools';
            toolbar.setAttribute('role', 'group');
            toolbar.setAttribute('aria-label', 'Notepad editing tools');
            const count = document.createElement('output');
            let history = [input.value], index = 0;
            const record = () => {
                if (input.value !== history[index]) {
                    history = history.slice(0, index + 1);
                    history.push(input.value);
                    if (history.length > 100) history.shift();
                    index = history.length - 1;
                }
                count.textContent = `${input.value.trim() ? input.value.trim().split(/\s+/).length : 0} words · ${input.value.length}${input.maxLength > 0 ? '/' + input.maxLength : ''} characters`;
            };
            const changed = () => { input.dispatchEvent(new Event('input', {bubbles:true})); input.dispatchEvent(new Event('change', {bubbles:true})); };
            const add = (label, action) => {
                const button = document.createElement('button'); button.type = 'button'; button.textContent = label;
                button.addEventListener('click', () => { action(button); }); toolbar.append(button); return button;
            };
            add('Undo', () => { if(index>0){input.value=history[--index];changed();input.focus();} });
            add('Redo', () => { if(index<history.length-1){input.value=history[++index];changed();input.focus();} });
            const list = numbered => {
                record();
                const start = input.value.lastIndexOf('\n', input.selectionStart - 1) + 1;
                const endIndex = input.value.indexOf('\n', input.selectionEnd);
                const end = endIndex < 0 ? input.value.length : endIndex;
                const text = input.value.slice(start, end).split('\n').map((line,i) => `${numbered ? (i+1)+'.' : '•'} ${line.replace(/^\s*(?:[•*-]|\d+\.)\s+/, '')}`).join('\n');
                if (input.maxLength > 0 && input.value.length - (end-start) + text.length > input.maxLength) return;
                input.setRangeText(text,start,end,'select'); changed();input.focus();
            };
            // Metadata remains plain text; lists are intended for longer notes.
            if (!['summary','package-meta-description'].includes(input.id)) { add('• List',()=>list(false)); add('1. List',()=>list(true)); }
            const expand = add('Expand', button => {
                const open = field.classList.toggle('notepad-expanded'); button.textContent=open?'Close expanded view':'Expand'; button.setAttribute('aria-expanded',String(open)); input.focus();
            });
            expand.setAttribute('aria-expanded','false');
            field.addEventListener('keydown',event=>{if(event.key==='Escape'&&field.classList.contains('notepad-expanded')){event.preventDefault();event.stopPropagation();expand.click();expand.focus();}});
            toolbar.append(count);
            // Preserve the label immediately before its input for itinerary/FAQ renumbering.
            field.insertBefore(toolbar, field.firstChild);
            input.addEventListener('input',record);
            input.addEventListener('focus',record);
            record();
        });
    }
    attach();
    new MutationObserver(attach).observe(form,{childList:true,subtree:true});
});
