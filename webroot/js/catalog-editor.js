(() => {
    const initialize = () => {
    const root = document.querySelector('[data-catalog-editor]');
    if (!root || !window.axios || !window.Swal) return;

    const csrf = document.querySelector('input[name="_csrfToken"]')?.value;
    const client = window.axios.create({ headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', ...(csrf ? { 'X-CSRF-Token': csrf } : {}) } });
    const preserveKey = 'catops-content-scroll';
    const savedScroll = sessionStorage.getItem(preserveKey);
    if (savedScroll !== null) { sessionStorage.removeItem(preserveKey); window.scrollTo({ top: Number(savedScroll), behavior: 'instant' }); }
    const refreshAtCurrentPosition = () => { sessionStorage.setItem(preserveKey, String(window.scrollY)); window.location.reload(); };
    const alertError = (message) => window.Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: message });
    const setBusy = (form, busy) => form.querySelectorAll('button, input, select, textarea').forEach((field) => { field.disabled = busy; });

    const sendForm = async (form) => {
        if (form.dataset.confirm) {
            const confirmation = await window.Swal.fire({ icon: 'warning', title: '¿Confirmar acción?', text: form.dataset.confirm, showCancelButton: true, confirmButtonText: 'Sí, continuar', cancelButtonText: 'Cancelar' });
            if (!confirmation.isConfirmed) return;
        }
        const formData = new FormData(form);
        setBusy(form, true);
        window.Swal.fire({ title: 'Guardando cambios', text: 'Espera un momento...', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => window.Swal.showLoading() });
        try {
            const response = await client.post(form.action, formData);
            if (!response.data?.success) throw new Error(response.data?.message || 'No fue posible guardar los cambios.');
            await window.Swal.fire({ icon: 'success', title: 'Listo', text: response.data.message || 'Cambios guardados.', timer: 1500, showConfirmButton: false });
            if (form.dataset.refresh === 'true' || response.data.refresh) refreshAtCurrentPosition();
        } catch (error) {
            alertError(error.response?.data?.message || error.message || 'Ocurrió un error inesperado.');
        } finally { setBusy(form, false); }
    };

    root.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-async-content]')) return;
        event.preventDefault(); sendForm(form);
    });

    root.addEventListener('click', async (event) => {
        const action = event.target.closest('[data-async-action]');
        if (!(action instanceof HTMLButtonElement)) return;
        event.preventDefault();
        const confirmation = await window.Swal.fire({ icon: 'warning', title: '¿Confirmar acción?', text: action.dataset.confirm || 'Esta acción no se puede deshacer.', showCancelButton: true, confirmButtonText: 'Sí, continuar', cancelButtonText: 'Cancelar' });
        if (!confirmation.isConfirmed) return;
        action.disabled = true;
        window.Swal.fire({ title: 'Guardando cambios', text: 'Espera un momento...', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => window.Swal.showLoading() });
        try {
            const data = new FormData(); if (csrf) data.append('_csrfToken', csrf);
            const response = await client.post(action.dataset.asyncAction, data);
            if (!response.data?.success) throw new Error(response.data?.message || 'No fue posible completar la acción.');
            await window.Swal.fire({ icon: 'success', title: 'Listo', text: response.data.message || 'Cambios guardados.', timer: 1500, showConfirmButton: false });
            if (action.dataset.refresh === 'true' || response.data.refresh) refreshAtCurrentPosition();
        } catch (error) {
            alertError(error.response?.data?.message || error.message || 'Ocurrió un error inesperado.');
        } finally { action.disabled = false; }
    });

    const reorder = async (list, itemSelector, idKey, status) => {
        const ids = [...list.querySelectorAll(itemSelector)].map((item) => item.dataset[idKey]).filter(Boolean);
        if (!ids.length) return;
        const data = new FormData(); if (csrf) data.append('_csrfToken', csrf);
        ids.forEach((id) => data.append(idKey === 'categoryId' ? 'category_ids[]' : 'product_ids[]', id));
        if (status) status.textContent = 'Guardando orden...';
        try { await client.post(list.dataset.reorderUrl, data); if (status) status.textContent = 'Orden guardado.'; }
        catch (error) { if (status) status.textContent = 'No se pudo guardar el orden.'; alertError(error.response?.data?.message || 'No fue posible guardar el orden.'); }
    };
    if (window.Sortable) {
        const setup = (list, selector, idKey, handle, status) => list && new window.Sortable(list, { animation: 160, handle, draggable: selector, ghostClass: 'product-sort-ghost', chosenClass: 'product-sort-chosen', onEnd: () => reorder(list, selector, idKey, status) });
        setup(document.getElementById('catalog-categories-sortable'), '.category-editor-card', 'categoryId', '.category-drag-handle', document.querySelector('[data-category-sort-status]'));
        setup(document.getElementById('catalog-products-sortable'), '.product-editor-card', 'productId', '.product-drag-handle', document.querySelector('[data-product-sort-status]'));
    }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
})();
