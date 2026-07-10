document.addEventListener('beforeinput', event => {
  const input = event.target.closest?.('[data-digits-only]');
  if (!input || event.inputType !== 'insertText') return;
  if (event.data && /\D/.test(event.data)) event.preventDefault();
});

document.addEventListener('input', event => {
  const input = event.target.closest?.('[data-digits-only]');
  if (!input) return;
  const digits = input.value.replace(/\D/g, '');
  input.value = input.maxLength > 0 ? digits.slice(0, input.maxLength) : digits;
});
