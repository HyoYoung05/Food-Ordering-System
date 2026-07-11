document.addEventListener('DOMContentLoaded', () => {
  const targetNames = new Set(['phoneCountry', 'country']);

  document.querySelectorAll('select').forEach(select => {
    if (!targetNames.has(select.name) || select.dataset.searchableReady) return;
    select.dataset.searchableReady = 'true';

    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-select';
    const input = document.createElement('input');
    input.type = 'search';
    input.className = 'searchable-select-input';
    input.placeholder = select.name === 'phoneCountry' ? 'Search country or calling code' : 'Search country';
    input.autocomplete = 'off';
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    const results = document.createElement('div');
    results.className = 'searchable-select-results hidden';
    results.setAttribute('role', 'listbox');
    select.parentNode.insertBefore(wrapper, select);
    wrapper.append(input, select, results);
    select.classList.add('searchable-native-select');

    let matches = [], activeIndex = -1;
    const escapeHtml = value => String(value).replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[character]));
    const selectedText = () => select.selectedOptions[0]?.textContent.trim() || '';
    const close = () => { results.classList.add('hidden'); input.setAttribute('aria-expanded', 'false'); activeIndex = -1; };
    const choose = option => {
      select.value = option.value;
      input.value = option.textContent.trim();
      select.dispatchEvent(new Event('change', { bubbles: true }));
      close();
    };
    const render = (query = '') => {
      const needle = query.toLowerCase().trim();
      matches = [...select.options].filter(option => !needle || `${option.textContent} ${option.value} ${option.dataset.country || ''}`.toLowerCase().includes(needle)).slice(0, 80);
      activeIndex = -1;
      results.innerHTML = matches.length ? matches.map((option, index) => `<button type="button" role="option" data-search-option="${index}" aria-selected="${option.selected}">${escapeHtml(option.textContent)}</button>`).join('') : '<p>No matching country</p>';
      results.classList.remove('hidden');
      input.setAttribute('aria-expanded', 'true');
    };
    const refresh = () => { if (document.activeElement !== input) input.value = selectedText(); };

    input.addEventListener('focus', () => { input.select(); render(''); });
    input.addEventListener('input', () => render(input.value));
    input.addEventListener('keydown', event => {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        if (results.classList.contains('hidden')) render(input.value);
        activeIndex = Math.max(0, Math.min(matches.length - 1, activeIndex + (event.key === 'ArrowDown' ? 1 : -1)));
        results.querySelectorAll('button').forEach((button, index) => button.classList.toggle('active', index === activeIndex));
        results.querySelectorAll('button')[activeIndex]?.scrollIntoView({ block: 'nearest' });
      } else if (event.key === 'Enter' && matches.length) {
        event.preventDefault(); choose(matches[activeIndex >= 0 ? activeIndex : 0]);
      } else if (event.key === 'Escape') {
        input.value = selectedText(); close();
      }
    });
    results.addEventListener('mousedown', event => event.preventDefault());
    results.addEventListener('click', event => { const button = event.target.closest('[data-search-option]'); if (button) choose(matches[Number(button.dataset.searchOption)]); });
    select.addEventListener('change', refresh);
    document.addEventListener('click', event => { if (!wrapper.contains(event.target)) { input.value = selectedText(); close(); } });
    new MutationObserver(() => { refresh(); if (!results.classList.contains('hidden')) render(input.value); }).observe(select, { childList: true, subtree: true });
    refresh();
  });
});
