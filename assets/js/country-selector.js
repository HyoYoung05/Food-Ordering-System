document.addEventListener('DOMContentLoaded', async () => {
  const phoneSelect = document.querySelector('select[name="phoneCountry"]');
  const countrySelect = document.querySelector('select[name="country"]');
  if (!phoneSelect || !countrySelect) return;

  const flagEmoji = code => code
    .toUpperCase()
    .replace(/./g, character => String.fromCodePoint(127397 + character.charCodeAt()));

  const populate = countries => {
    const normalized = countries
      .filter(country => country.name && country.alpha2Code)
      .sort((a, b) => a.name.localeCompare(b.name));

    const previousCountry = countrySelect.value || 'Philippines';
    const previousDialCode = phoneSelect.value || '+63';

    countrySelect.innerHTML = normalized.map(country =>
      `<option value="${escapeOption(country.name)}">${flagEmoji(country.alpha2Code)} ${escapeOption(country.name)}</option>`
    ).join('');

    phoneSelect.innerHTML = normalized
      .filter(country => Array.isArray(country.callingCodes) && country.callingCodes.length)
      .flatMap(country => country.callingCodes.map(code => {
        const dialCode = `+${String(code).replace(/^\+/, '')}`;
        return `<option value="${escapeOption(dialCode)}" data-country="${escapeOption(country.name)}">${flagEmoji(country.alpha2Code)} ${escapeOption(country.name)} (${escapeOption(dialCode)})</option>`;
      })).join('');

    const countryOption = [...countrySelect.options].find(option => option.value === previousCountry);
    const phoneOption = [...phoneSelect.options].find(option => option.value === previousDialCode && option.dataset.country === 'Philippines')
      || [...phoneSelect.options].find(option => option.value === previousDialCode);
    if (countryOption) countryOption.selected = true;
    if (phoneOption) phoneOption.selected = true;
  };

  function escapeOption(value) {
    return String(value).replace(/[&<>"']/g, character => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[character]);
  }

  try {
    const cached = sessionStorage.getItem('savorly_countries');
    const countries = cached
      ? JSON.parse(cached)
      : await fetch('https://countries.dev/countries?fields=name,alpha2Code,callingCodes')
        .then(response => {
          if (!response.ok) throw new Error('Country service unavailable');
          return response.json();
        });
    if (!cached) sessionStorage.setItem('savorly_countries', JSON.stringify(countries));
    populate(countries);
  } catch (error) {
    console.warn('Using the built-in country list:', error.message);
  }
});
