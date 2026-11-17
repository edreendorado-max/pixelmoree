document.addEventListener('DOMContentLoaded', () => {
  const dialog = document.querySelector('[data-image-crop-dialog]');
  if (!(dialog instanceof HTMLDialogElement) || typeof dialog.showModal !== 'function') return;

  const viewport = dialog.querySelector('[data-image-crop-viewport]');
  const image = dialog.querySelector('[data-image-crop-image]');
  const zoomInput = dialog.querySelector('[data-image-crop-zoom]');
  const zoomValue = dialog.querySelector('[data-image-crop-zoom-value]');
  const errorMessage = dialog.querySelector('[data-image-crop-error]');
  const applyButton = dialog.querySelector('[data-image-crop-apply]');
  const configs = {
    avatar: { input: document.getElementById('profile-avatar'), ratio: 1, outputWidth: 1024 },
    banner: { input: document.getElementById('profile-banner'), ratio: 3, outputWidth: 1800 },
  };
  const previewUrls = { avatar: null, banner: null };
  let crop = null;
  let drag = null;

  if (!viewport || !(image instanceof HTMLImageElement) || !(zoomInput instanceof HTMLInputElement)
      || !(zoomValue instanceof HTMLOutputElement) || !(errorMessage instanceof HTMLElement)
      || !(applyButton instanceof HTMLButtonElement)) return;

  const showError = (message) => {
    errorMessage.textContent = message;
    errorMessage.hidden = false;
  };

  const clearError = () => {
    errorMessage.textContent = '';
    errorMessage.hidden = true;
  };

  const clampOffsets = () => {
    if (!crop) return;
    const scale = crop.baseScale * crop.zoom;
    const width = crop.image.naturalWidth * scale;
    const height = crop.image.naturalHeight * scale;
    crop.x = Math.min(0, Math.max(viewport.clientWidth - width, crop.x));
    crop.y = Math.min(0, Math.max(viewport.clientHeight - height, crop.y));
  };

  const renderCrop = () => {
    if (!crop) return;
    const scale = crop.baseScale * crop.zoom;
    clampOffsets();
    image.style.width = `${crop.image.naturalWidth * scale}px`;
    image.style.height = `${crop.image.naturalHeight * scale}px`;
    image.style.left = `${crop.x}px`;
    image.style.top = `${crop.y}px`;
  };

  const closeEditor = (discard) => {
    if (!crop) return;
    const activeCrop = crop;
    crop = null;
    drag = null;
    image.onload = null;
    image.onerror = null;
    image.removeAttribute('src');
    URL.revokeObjectURL(activeCrop.sourceUrl);
    viewport.classList.remove('is-square', 'is-dragging');
    if (discard) activeCrop.input.value = '';
    if (dialog.open) dialog.close();
  };

  const updatePreview = (type, file) => {
    const previewUrl = URL.createObjectURL(file);
    if (previewUrls[type]) URL.revokeObjectURL(previewUrls[type]);
    previewUrls[type] = previewUrl;
    document.querySelectorAll(`[data-image-preview="${type}"]`).forEach((container) => {
      let preview = container.querySelector('[data-image-preview-image]');
      if (!(preview instanceof HTMLImageElement)) {
        preview = document.createElement('img');
        preview.dataset.imagePreviewImage = '';
        if (type === 'banner') container.insertBefore(preview, container.firstElementChild);
        else container.replaceChildren(preview);
      }
      preview.src = previewUrl;
      preview.alt = type === 'banner' ? 'Profile banner preview' : 'Profile image preview';
    });
  };

  const startCrop = (type, file, input) => {
    if (crop) closeEditor(true);
    const config = configs[type];
    if (!config) return;
    clearError();
    viewport.classList.toggle('is-square', type === 'avatar');
    viewport.style.aspectRatio = `${config.ratio} / 1`;
    zoomInput.value = '1';
    zoomValue.value = '100%';
    applyButton.disabled = false;
    applyButton.textContent = 'Use this crop';
    crop = {
      type,
      config,
      input,
      image: new Image(),
      sourceUrl: URL.createObjectURL(file),
      baseScale: 1,
      zoom: 1,
      x: 0,
      y: 0,
    };
    dialog.showModal();
    const activeCrop = crop;
    image.onload = () => {
      if (crop !== activeCrop) return;
      const scale = Math.max(viewport.clientWidth / image.naturalWidth, viewport.clientHeight / image.naturalHeight);
      activeCrop.image = image;
      activeCrop.baseScale = scale;
      activeCrop.x = (viewport.clientWidth - image.naturalWidth * scale) / 2;
      activeCrop.y = (viewport.clientHeight - image.naturalHeight * scale) / 2;
      renderCrop();
    };
    image.onerror = () => {
      if (crop !== activeCrop) return;
      showError('This image could not be opened. Choose a JPG, PNG, GIF, or WebP image and try again.');
    };
    image.src = activeCrop.sourceUrl;
  };

  Object.entries(configs).forEach(([type, config]) => {
    if (!(config.input instanceof HTMLInputElement)) return;
    config.input.addEventListener('change', () => {
      const file = config.input.files?.[0];
      if (file) startCrop(type, file, config.input);
    });
  });

  viewport.addEventListener('pointerdown', (event) => {
    if (!crop || event.button !== 0) return;
    drag = { pointerId: event.pointerId, startX: event.clientX, startY: event.clientY, x: crop.x, y: crop.y };
    viewport.classList.add('is-dragging');
    viewport.setPointerCapture(event.pointerId);
  });

  viewport.addEventListener('pointermove', (event) => {
    if (!crop || !drag || drag.pointerId !== event.pointerId) return;
    crop.x = drag.x + event.clientX - drag.startX;
    crop.y = drag.y + event.clientY - drag.startY;
    renderCrop();
  });

  const endDrag = (event) => {
    if (!drag || drag.pointerId !== event.pointerId) return;
    drag = null;
    viewport.classList.remove('is-dragging');
  };
  viewport.addEventListener('pointerup', endDrag);
  viewport.addEventListener('pointercancel', endDrag);

  window.addEventListener('resize', () => {
    if (!crop || !image.naturalWidth || !image.naturalHeight) return;
    const previousScale = crop.baseScale * crop.zoom;
    const sourceCenterX = (viewport.clientWidth / 2 - crop.x) / previousScale;
    const sourceCenterY = (viewport.clientHeight / 2 - crop.y) / previousScale;
    crop.baseScale = Math.max(
      viewport.clientWidth / image.naturalWidth,
      viewport.clientHeight / image.naturalHeight,
    );
    const nextScale = crop.baseScale * crop.zoom;
    crop.x = viewport.clientWidth / 2 - sourceCenterX * nextScale;
    crop.y = viewport.clientHeight / 2 - sourceCenterY * nextScale;
    renderCrop();
  });

  zoomInput.addEventListener('input', () => {
    if (!crop) return;
    const previousScale = crop.baseScale * crop.zoom;
    const centerX = viewport.clientWidth / 2;
    const centerY = viewport.clientHeight / 2;
    const sourceCenterX = (centerX - crop.x) / previousScale;
    const sourceCenterY = (centerY - crop.y) / previousScale;
    crop.zoom = Number(zoomInput.value);
    const nextScale = crop.baseScale * crop.zoom;
    crop.x = centerX - sourceCenterX * nextScale;
    crop.y = centerY - sourceCenterY * nextScale;
    zoomValue.value = `${Math.round(crop.zoom * 100)}%`;
    renderCrop();
  });

  applyButton.addEventListener('click', () => {
    if (!crop || !image.naturalWidth || !image.naturalHeight) return;
    clearError();
    applyButton.disabled = true;
    applyButton.textContent = 'Preparing image...';
    const activeCrop = crop;
    const scale = activeCrop.baseScale * activeCrop.zoom;
    const canvas = document.createElement('canvas');
    canvas.width = activeCrop.config.outputWidth;
    canvas.height = Math.round(canvas.width / activeCrop.config.ratio);
    const context = canvas.getContext('2d');
    if (!context) {
      applyButton.disabled = false;
      applyButton.textContent = 'Use this crop';
      showError('Your browser could not prepare the cropped image. Please try a different browser.');
      return;
    }
    context.drawImage(
      image,
      -activeCrop.x / scale,
      -activeCrop.y / scale,
      viewport.clientWidth / scale,
      viewport.clientHeight / scale,
      0,
      0,
      canvas.width,
      canvas.height,
    );
    canvas.toBlob((blob) => {
      if (crop !== activeCrop) return;
      if (!blob) {
        applyButton.disabled = false;
        applyButton.textContent = 'Use this crop';
        showError('Your browser could not create the cropped image. Please try again.');
        return;
      }
      try {
        const baseName = activeCrop.input.files[0].name.replace(/\.[^.]+$/, '');
        const croppedFile = new File([blob], `${baseName}-cropped.jpg`, { type: 'image/jpeg' });
        const transfer = new DataTransfer();
        transfer.items.add(croppedFile);
        activeCrop.input.files = transfer.files;
        updatePreview(activeCrop.type, croppedFile);
        closeEditor(false);
      } catch (error) {
        applyButton.disabled = false;
        applyButton.textContent = 'Use this crop';
        showError('Your browser could not attach the cropped image to the upload. Please try a current browser.');
      }
    }, 'image/jpeg', 0.92);
  });

  dialog.querySelectorAll('[data-image-crop-cancel]').forEach((button) => {
    button.addEventListener('click', () => closeEditor(true));
  });

  dialog.addEventListener('cancel', (event) => {
    event.preventDefault();
    closeEditor(true);
  });
});
