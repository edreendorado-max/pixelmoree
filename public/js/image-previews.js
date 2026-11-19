document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[type="file"]:not([data-image-crop]):not(#memberArtworkFile)').forEach((input) => {
    let previewUrl = null;
    const preview = document.createElement('img');
    preview.alt = 'Selected image preview';
    preview.hidden = true;
    preview.style.cssText = 'display:block;max-width:min(100%,420px);max-height:280px;object-fit:contain;margin:.65rem 0;border-radius:10px';
    input.insertAdjacentElement('afterend', preview);
    input.addEventListener('change', () => {
      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = null;
      const file = input.files?.[0];
      if (!file || !file.type.startsWith('image/')) {
        preview.removeAttribute('src');
        preview.hidden = true;
        return;
      }
      previewUrl = URL.createObjectURL(file);
      preview.src = previewUrl;
      preview.hidden = false;
    });
  });
});
