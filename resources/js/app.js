const body = document.body;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const sidebar = document.getElementById('admin-sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const searchModal = document.getElementById('admin-search-modal');
const searchInput = document.getElementById('admin-global-search');
const searchEmpty = document.getElementById('admin-search-empty');
const dataModal = document.getElementById('admin-data-modal');
const dataModalBody = document.getElementById('admin-data-modal-body');
const dataModalTitle = document.getElementById('admin-data-modal-title');
const alertModal = document.getElementById('admin-alert-modal');
const alertTitle = document.getElementById('admin-alert-title');
const alertMessage = document.getElementById('admin-alert-message');
const alertEyebrow = document.getElementById('admin-alert-eyebrow');
const alertConfirm = document.getElementById('admin-alert-confirm');
const alertCancel = document.getElementById('admin-alert-cancel');
const alertIcon = document.getElementById('admin-alert-icon');
let lastFocusedElement = null;
let alertResolver = null;
let dataModalCloseTimer = null;
let searchModalCloseTimer = null;
let alertModalCloseTimer = null;

const isDesktopSidebar = () => window.matchMedia('(min-width: 901px)').matches;
body.classList.remove('sidebar-collapsed');
localStorage.removeItem('ettra-sidebar-collapsed');
const formatRupiah = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value) || 0);

const updateSidebarState = () => {
    const open = body.classList.contains('sidebar-open');
    document.querySelectorAll('[data-sidebar-open]').forEach((el) => el.setAttribute('aria-expanded', String(open)));
    sidebarOverlay?.setAttribute('aria-hidden', String(!open));
};
const openSidebar = () => { body.classList.add('sidebar-open'); updateSidebarState(); };
const closeSidebar = () => { body.classList.remove('sidebar-open'); updateSidebarState(); };
const closeDropdowns = (exceptId = null) => {
    document.querySelectorAll('[data-dropdown-trigger]').forEach((trigger) => {
        const id = trigger.getAttribute('data-dropdown-trigger');
        const menu = document.getElementById(id);
        if (!menu || id === exceptId) return;
        menu.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
    });
};

const toggleDropdown = (trigger) => {
    const id = trigger.getAttribute('data-dropdown-trigger');
    const menu = document.getElementById(id);
    if (!menu) return;
    const open = menu.hidden;
    closeDropdowns(open ? id : null);
    menu.hidden = !open;
    trigger.setAttribute('aria-expanded', String(open));
};

const wait = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

const alertIconMarkup = (type) => {
    const icons = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.6 2.6L16.5 9"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3 9 17H3L12 3Z"/><path d="M12 9v4M12 17h.01"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>',
    };
    return icons[type] || icons.info;
};

const showAlert = ({ title = 'Informasi', message = '', type = 'info', eyebrow = 'Informasi Sistem', confirmText = 'Mengerti', cancelText = 'Batal', confirm = false } = {}) => {
    if (!alertModal) return Promise.resolve(true);
    alertTitle.textContent = title;
    alertMessage.textContent = message;
    alertEyebrow.textContent = eyebrow;
    alertConfirm.textContent = confirmText;
    alertCancel.textContent = cancelText;
    alertCancel.hidden = !confirm;
    alertModal.dataset.type = type;
    if (alertIcon) alertIcon.innerHTML = alertIconMarkup(type);
    clearTimeout(alertModalCloseTimer);
    alertModal.hidden = false;
    alertModal.setAttribute('aria-hidden', 'false');
    body.classList.add('modal-open');
    window.requestAnimationFrame(() => {
        alertModal.classList.add('is-open');
        window.setTimeout(() => alertConfirm?.focus(), 40);
    });
    return new Promise((resolve) => { alertResolver = resolve; });
};

const resolveAlert = (value) => {
    if (!alertModal || alertModal.hidden) return;
    alertModal.classList.remove('is-open');
    alertModal.setAttribute('aria-hidden', 'true');
    const resolver = alertResolver;
    alertResolver = null;
    resolver?.(value);
    clearTimeout(alertModalCloseTimer);
    alertModalCloseTimer = window.setTimeout(() => {
        alertModal.hidden = true;
        if (dataModal?.hidden && searchModal?.hidden) body.classList.remove('modal-open');
    }, 170);
};

const showComingSoon = (message) => showAlert({
    title: 'Fitur belum dikerjakan',
    message,
    type: 'warning',
    eyebrow: 'Informasi Pengembangan',
    confirmText: 'Saya Mengerti',
});

const openSearch = () => {
    if (!searchModal) return;
    lastFocusedElement = document.activeElement;
    clearTimeout(searchModalCloseTimer);
    searchModal.hidden = false;
    body.classList.add('modal-open');
    requestAnimationFrame(() => {
        searchModal.classList.add('is-open');
        window.setTimeout(() => searchInput?.focus(), 40);
    });
};
const closeSearch = () => {
    if (!searchModal || searchModal.hidden) return;
    searchModal.classList.remove('is-open');
    clearTimeout(searchModalCloseTimer);
    searchModalCloseTimer = window.setTimeout(() => {
        searchModal.hidden = true;
        if (dataModal?.hidden && alertModal?.hidden) body.classList.remove('modal-open');
        if (searchInput) { searchInput.value = ''; filterSearchItems(''); }
        if (lastFocusedElement instanceof HTMLElement) lastFocusedElement.focus();
    }, 160);
};
const filterSearchItems = (query) => {
    const normalized = query.trim().toLowerCase();
    let visible = 0;
    searchModal?.querySelectorAll('.search-quick-item').forEach((item) => {
        const match = item.textContent.toLowerCase().includes(normalized);
        item.hidden = !match;
        if (match) visible += 1;
    });
    if (searchEmpty) searchEmpty.hidden = visible > 0;
};

const closeDataModal = () => {
    if (!dataModal || dataModal.hidden) return;
    dataModal.classList.remove('is-open');
    dataModal.setAttribute('aria-hidden', 'true');
    clearTimeout(dataModalCloseTimer);
    dataModalCloseTimer = window.setTimeout(() => {
        dataModal.hidden = true;
        if (searchModal?.hidden && alertModal?.hidden) body.classList.remove('modal-open');
        if (dataModalBody) dataModalBody.innerHTML = '';
        if (lastFocusedElement instanceof HTMLElement) lastFocusedElement.focus();
    }, 170);
};

const extractModalContent = (html) => {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const page = doc.querySelector('[data-admin-page-content]') || doc.querySelector('.admin-container');
    const form = page?.querySelector('form');
    const error = page?.querySelector('.user-alert--danger, .auth-alert--error');
    const heading = page?.querySelector('.dashboard-heading h2, .admin-header__title, h1, h2');
    if (!form) return { form: null, title: heading?.textContent?.trim() || 'Form Data', error: null };
    const wrapper = document.createElement('div');
    if (error) wrapper.append(error.cloneNode(true));
    wrapper.append(form.cloneNode(true));
    return { form: wrapper, title: heading?.textContent?.trim() || 'Form Data', error };
};

const openDataModalFromUrl = async (url, title = null) => {
    if (!dataModal || !dataModalBody) return;
    lastFocusedElement = document.activeElement;
    clearTimeout(dataModalCloseTimer);
    if (dataModalTitle) dataModalTitle.textContent = title || 'Form Data';
    dataModalBody.innerHTML = '<div class="admin-modal__skeleton"><span class="admin-modal__loader" aria-hidden="true"></span><span>Menyiapkan form...</span></div>';
    dataModal.hidden = false;
    dataModal.setAttribute('aria-hidden', 'false');
    body.classList.add('modal-open');
    requestAnimationFrame(() => dataModal.classList.add('is-open'));

    try {
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' });
        if (response.status === 401 || response.status === 419) { window.location.replace(body.dataset.loginUrl || '/admin/login'); return; }
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const extracted = extractModalContent(await response.text());
        if (!extracted.form) throw new Error('Form tidak ditemukan.');
        dataModalBody.innerHTML = '';
        dataModalBody.append(extracted.form);
        if (dataModalTitle) dataModalTitle.textContent = title || extracted.title;
        initializeDynamic(dataModalBody);
        requestAnimationFrame(() => window.setTimeout(() => dataModalBody.querySelector('input:not([type="hidden"]), select, textarea')?.focus(), 40));
    } catch (error) {
        closeDataModal();
        await wait(180);
        await showAlert({ title: 'Form tidak dapat dibuka', message: 'Terjadi kendala saat mengambil form. Coba kembali tanpa meninggalkan halaman.', type: 'error' });
    }
};

const flashFromDocument = (doc) => {
    const node = doc.querySelector('.user-alert--success, [data-flash-success], .auth-alert--success');
    return node?.textContent?.replace(/\s+/g, ' ').trim() || null;
};

const softRefresh = async (url = window.location.href, { push = false } = {}) => {
    const current = document.querySelector('[data-admin-page-content]');
    let response;

    try {
        response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            cache: 'no-store',
        });
    } catch (error) {
        current?.classList.remove('is-page-leaving');
        await showAlert({
            title: 'Data belum diperbarui',
            message: 'Koneksi ke server terganggu. Silakan coba kembali.',
            type: 'error',
        });
        return;
    }

    if (response.status === 401 || response.status === 419) {
        window.location.replace(body.dataset.loginUrl || '/admin/login');
        return;
    }

    if (!response.ok) {
        current?.classList.remove('is-page-leaving');
        await showAlert({
            title: 'Halaman tidak dapat dibuka',
            message: `Server mengembalikan status ${response.status}. Silakan coba kembali.`,
            type: 'error',
        });
        return;
    }

    try {
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const incoming = doc.querySelector('[data-admin-page-content]');

        if (!incoming || !current) {
            window.location.assign(response.url || url);
            return;
        }

        current.classList.add('is-page-leaving');
        await wait(85);

        const currentHeader = document.querySelector('[data-admin-header-inner]');
        const incomingHeader = doc.querySelector('[data-admin-header-inner]');
        if (currentHeader && incomingHeader) {
            currentHeader.innerHTML = incomingHeader.innerHTML;
        }

        const currentNav = document.querySelector('[data-admin-sidebar-nav]');
        const incomingNav = doc.querySelector('[data-admin-sidebar-nav]');
        if (currentNav && incomingNav) {
            currentNav.innerHTML = incomingNav.innerHTML;
        }

        current.innerHTML = incoming.innerHTML;
        document.title = doc.title || document.title;

        if (push) {
            history.pushState({ ettraSoft: true }, '', response.url || url);
        }

        closeDropdowns();
        if (!isDesktopSidebar()) {
            closeSidebar();
        }

        initializeDynamic(current);

        current.classList.remove('is-page-leaving');
        current.classList.add('is-page-entering');
        requestAnimationFrame(() => current.classList.add('is-page-entering-active'));
        window.setTimeout(() => {
            current.classList.remove('is-page-entering', 'is-page-entering-active');
        }, 260);

        if (push) {
            window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
        }
    } catch (error) {
        console.error('Ettra soft navigation failed:', error);
        current?.classList.remove('is-page-leaving');
        window.location.assign(response.url || url);
    }
};

const submitFetchForm = async (form, { modal = false } = {}) => {
    const submit = form.querySelector('[type="submit"]');
    submit?.setAttribute('disabled', 'disabled');
    submit?.classList.add('is-busy');
    try {
        const response = await fetch(form.action, {
            method: (form.method || 'POST').toUpperCase(),
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            credentials: 'same-origin',
            cache: 'no-store',
        });
        if (response.status === 401 || response.status === 419) { window.location.replace(body.dataset.loginUrl || '/admin/login'); return false; }
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const finalUrl = response.url || '';
        const stillFormPage = /\/(create|edit|generate)(\?|$)/.test(finalUrl) || (modal && doc.querySelector('[data-admin-page-content] form') && doc.querySelector('.user-alert--danger'));
        if (!response.ok || stillFormPage) {
            if (modal && dataModalBody) {
                const extracted = extractModalContent(html);
                if (extracted.form) {
                    dataModalBody.innerHTML = '';
                    dataModalBody.append(extracted.form);
                    initializeDynamic(dataModalBody);
                    return false;
                }
            }
            const errorText = doc.querySelector('.user-alert--danger, .category-field-error')?.textContent?.trim() || 'Data belum dapat disimpan. Periksa kembali isian.';
            await showAlert({ title: 'Data belum tersimpan', message: errorText, type: 'error' });
            return false;
        }
        const successMessage = flashFromDocument(doc) || 'Perubahan berhasil disimpan.';
        if (modal) closeDataModal();
        await softRefresh(window.location.href);
        await showAlert({ title: 'Berhasil', message: successMessage, type: 'success', eyebrow: 'Data Tersimpan' });
        return true;
    } catch (error) {
        await showAlert({ title: 'Gagal menyimpan data', message: 'Tidak dapat menghubungi server. Coba kembali.', type: 'error' });
        return false;
    } finally {
        submit?.removeAttribute('disabled');
        submit?.classList.remove('is-busy');
    }
};

const handleLogout = async (form, { skipConfirm = false } = {}) => {
    if (!skipConfirm) {
        const confirmed = await showAlert({ title: 'Keluar dari sistem?', message: 'Sesi Anda akan diakhiri dan halaman internal tidak dapat dibuka kembali tanpa login.', type: 'warning', confirm: true, confirmText: 'Ya, Keluar', cancelText: 'Tetap Masuk', eyebrow: 'Konfirmasi Logout' });
        if (!confirmed) return;
    }
    try {
        const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' });
        const data = await response.json().catch(() => ({}));
        window.location.replace(data.redirect || body.dataset.loginUrl || '/admin/login');
    } catch { window.location.replace(body.dataset.loginUrl || '/admin/login'); }
};

const initializeProductPricing = (root = document) => {
    root.querySelectorAll('[data-product-pricing]:not([data-bound-pricing])').forEach((container) => {
        container.dataset.boundPricing = '1';
        const cost = container.querySelector('[data-product-cost-price]');
        const selling = container.querySelector('[data-product-selling-price]');
        const profit = container.querySelector('[data-product-profit]');
        const margin = container.querySelector('[data-product-margin]');
        if (!(cost instanceof HTMLInputElement) || !(selling instanceof HTMLInputElement)) return;
        const update = () => { const c=Number(cost.value), s=Number(selling.value); const p=s-c; if(profit) profit.textContent=Number.isFinite(p)?formatRupiah(p):'-'; if(margin) margin.textContent=s>0?`${((p/s)*100).toLocaleString('id-ID',{maximumFractionDigits:1})}%`:'-'; };
        cost.addEventListener('input', update); selling.addEventListener('input', update); update();
    });
};

const initializeProductImagePreview = (root = document) => {
    root.querySelectorAll('[data-product-image-preview]:not([data-bound-preview])').forEach((preview) => {
        preview.dataset.boundPreview='1';
        const form = preview.closest('form') || root;
        const primary = form.querySelector('[data-product-primary-input]');
        const gallery = form.querySelector('[data-product-gallery-input]');
        const render=()=>{ preview.innerHTML=''; const files=[]; if(primary?.files?.[0]) files.push(primary.files[0]); if(gallery?.files) files.push(...gallery.files); preview.hidden=files.length===0; files.forEach((f)=>{const div=document.createElement('div');div.className='product-upload-preview__item';const img=document.createElement('img');img.src=URL.createObjectURL(f);const cap=document.createElement('span');cap.textContent=f.name;div.append(img,cap);preview.append(div);});};
        primary?.addEventListener('change',render); gallery?.addEventListener('change',render);
    });
};

const initializeVariantForms = (root = document) => {
    root.querySelectorAll('[data-product-variant-form]:not([data-bound-variant])').forEach((form) => {
        form.dataset.boundVariant='1'; const product=form.querySelector('[data-variant-product]'), color=form.querySelector('[data-variant-color]'), size=form.querySelector('[data-variant-size]'), sku=form.querySelector('[data-variant-sku]'), add=form.querySelector('[data-variant-additional-price]'), weight=form.querySelector('[data-variant-weight]'), skuOut=form.querySelector('[data-variant-sku-preview]'), priceOut=form.querySelector('[data-variant-final-price]'), weightOut=form.querySelector('[data-variant-effective-weight]');
        if(!product||!color||!size)return; const opt=s=>s.options[s.selectedIndex]; let touched=sku?.value?.trim()!=='';
        const update=()=>{const generated=[opt(product)?.dataset.code,opt(color)?.dataset.code,opt(size)?.dataset.code].filter(Boolean).join('-').toUpperCase(); if(skuOut)skuOut.textContent=sku?.value?.trim()||generated||'SKU otomatis'; const price=Number(opt(product)?.dataset.price||0)+Number(add?.value||0); if(priceOut)priceOut.textContent=formatRupiah(price); const w=Number(weight?.value||opt(product)?.dataset.weight||0); if(weightOut)weightOut.textContent=w?`${w.toLocaleString('id-ID')} g`:'-'; if(sku&&!touched)sku.placeholder=generated||'Otomatis';};
        sku?.addEventListener('input',()=>{touched=sku.value.trim()!=='';update();}); [product,color,size,add,weight].forEach(x=>{x?.addEventListener('change',update);x?.addEventListener('input',update);}); update();
    });
    root.querySelectorAll('[data-variant-generator]:not([data-bound-generator])').forEach((form)=>{form.dataset.boundGenerator='1'; const update=()=>{const c=form.querySelectorAll('[data-variant-choice="color"]:checked').length,s=form.querySelectorAll('[data-variant-choice="size"]:checked').length; const out=form.querySelector('[data-variant-combination-count]');if(out)out.textContent=String(c*s);const co=form.querySelector('[data-variant-color-count]');if(co)co.textContent=String(c);const so=form.querySelector('[data-variant-size-count]');if(so)so.textContent=String(s);const product=form.querySelector('[data-generator-product]');const price=form.querySelector('[data-generator-final-price]');if(product&&price){price.textContent=formatRupiah(Number(product.options[product.selectedIndex]?.dataset.price||0)+Number(form.querySelector('[data-generator-additional-price]')?.value||0));}}; form.querySelectorAll('[data-variant-choice]').forEach(x=>x.addEventListener('change',update)); form.querySelector('[data-generator-product]')?.addEventListener('change',update); form.querySelector('[data-generator-additional-price]')?.addEventListener('input',update); form.querySelectorAll('[data-variant-select-all]').forEach(b=>b.addEventListener('click',()=>{const type=b.dataset.variantSelectAll;const checks=[...form.querySelectorAll(`[data-variant-choice="${type}"]`)];const on=checks.some(x=>!x.checked);checks.forEach(x=>x.checked=on);update();})); update();});
};

const initializePasswordToggles = (root=document) => root.querySelectorAll('[data-user-password-toggle]:not([data-bound-pass])').forEach((button)=>{button.dataset.boundPass='1';button.addEventListener('click',()=>{const field=button.closest('.user-password-field')?.querySelector('[data-user-password]');if(!field)return;field.type=field.type==='password'?'text':'password';});});

const initializePromotionForm = (root=document) => root.querySelectorAll('[data-promotion-form]:not([data-bound-promo])').forEach((form)=>{form.dataset.boundPromo='1';const target=form.querySelector('[data-promotion-target]');const product=form.querySelector('[data-promotion-product]');const category=form.querySelector('[data-promotion-category]');const update=()=>{if(product)product.hidden=target?.value!=='product';if(category)category.hidden=target?.value!=='category';};target?.addEventListener('change',update);update();});

const initializeStockTransferForm = (root=document) => root.querySelectorAll('[data-stock-transfer-form]:not([data-bound-transfer])').forEach((form)=>{form.dataset.boundTransfer='1';const holder=form.querySelector('[data-transfer-items]'), template=form.querySelector('[data-transfer-item-template]'), source=form.querySelector('[data-transfer-source]');let index=0;const filter=()=>{holder?.querySelectorAll('[data-transfer-variant]').forEach(select=>[...select.options].forEach((o,i)=>{if(i===0)return;o.hidden=source?.value!==''&&o.dataset.warehouse!==source.value;}));};const add=()=>{if(!template||!holder)return;const frag=template.content.cloneNode(true);frag.querySelectorAll('[name]').forEach(x=>x.name=x.name.replace('__INDEX__',String(index)));holder.append(frag);index++;filter();};form.querySelector('[data-transfer-add-item]')?.addEventListener('click',add);source?.addEventListener('change',filter);holder?.addEventListener('click',(e)=>{const b=e.target.closest('[data-transfer-remove-item]');if(b)b.closest('.transfer-item-row')?.remove();});add();});

const initializeSalesForm = (root=document) => root.querySelectorAll('[data-sales-form]:not([data-bound-sale])').forEach((form)=>{form.dataset.boundSale='1';const holder=form.querySelector('[data-sale-items]'),template=form.querySelector('[data-sale-item-template]'),warehouse=form.querySelector('[data-sale-warehouse]'),channel=form.querySelector('[data-sale-channel]'),payment=form.querySelector('[data-sale-payment]'),estimate=form.querySelector('[data-sale-estimate]');let index=0;const refresh=()=>{let total=0;holder?.querySelectorAll('.sale-item-row').forEach(row=>{const select=row.querySelector('[data-sale-variant]'),qty=row.querySelector('[data-sale-qty]'),out=row.querySelector('[data-sale-line-price]');const option=select?.options[select.selectedIndex];const line=Number(option?.dataset.price||0)*Number(qty?.value||0);total+=line;if(out)out.textContent=formatRupiah(line);[...select.options].forEach((o,i)=>{if(i===0)return;o.hidden=warehouse?.value!==''&&o.dataset.warehouse!==warehouse.value;});});if(estimate)estimate.textContent=formatRupiah(total);if(channel?.value==='online'&&payment?.value==='cash')payment.value='bank_transfer';};const add=()=>{if(!template||!holder)return;const frag=template.content.cloneNode(true);frag.querySelectorAll('[name]').forEach(x=>x.name=x.name.replace('__INDEX__',String(index)));holder.append(frag);index++;refresh();};form.querySelector('[data-sale-add-item]')?.addEventListener('click',add);holder?.addEventListener('click',e=>{const b=e.target.closest('[data-sale-remove-item]');if(b){b.closest('.sale-item-row')?.remove();refresh();}});holder?.addEventListener('change',refresh);holder?.addEventListener('input',refresh);warehouse?.addEventListener('change',refresh);channel?.addEventListener('change',refresh);payment?.addEventListener('change',refresh);add();});


const initializeCustomerReturnForm = (root=document) => root.querySelectorAll('[data-customer-return-form]:not([data-bound-return])').forEach((form)=>{
    form.dataset.boundReturn='1';
    const order=form.querySelector('[data-return-order]');
    const empty=form.querySelector('[data-return-empty]');
    const update=()=>{
        const selected=order?.value || '';
        let visible=false;
        form.querySelectorAll('[data-return-order-block]').forEach((block)=>{
            const active=selected!=='' && block.dataset.returnOrderBlock===selected;
            block.hidden=!active;
            block.querySelectorAll('[data-return-input]').forEach(input=>input.disabled=!active);
            if(active) visible=true;
        });
        if(empty) empty.hidden=visible;
    };
    order?.addEventListener('change',update);
    update();
});

const initializeDynamic = (root=document) => {
    initializeProductPricing(root);
    initializeProductImagePreview(root);
    initializeVariantForms(root);
    initializePasswordToggles(root);
    initializePromotionForm(root);
    initializeStockTransferForm(root);
    initializeSalesForm(root);
    initializeCustomerReturnForm(root);
};

const shouldSoftNavigate = (link, event) => {
    if (!link?.href || link.origin !== window.location.origin) return false;
    if (event?.metaKey || event?.ctrlKey || event?.shiftKey || event?.altKey) return false;
    if (link.hasAttribute('download') || link.target === '_blank' || link.dataset.noSoftNav !== undefined) return false;
    if (!link.pathname.startsWith('/admin/')) return false;
    if (link.pathname === '/admin/login' || link.pathname.includes('/document/') || link.pathname.endsWith('/proof')) return false;
    if (/\/(create|edit|generate)$/.test(link.pathname)) return false;
    if (link.hash && link.pathname === window.location.pathname && link.search === window.location.search) return false;
    return true;
};

// Delegated interactions: works after AJAX page refreshes too.
document.addEventListener('click', async (event) => {
    const sidebarOpen = event.target.closest('[data-sidebar-open]');
    if (sidebarOpen) {
        if (!isDesktopSidebar()) {
            event.preventDefault();
            openSidebar();
        }
        return;
    }
    const sidebarClose = event.target.closest('[data-sidebar-close]');
    if (sidebarClose) { event.preventDefault(); closeSidebar(); return; }
    const dropdown = event.target.closest('[data-dropdown-trigger]');
    if (dropdown) { event.stopPropagation(); toggleDropdown(dropdown); return; }
    if (!event.target.closest('.admin-dropdown')) closeDropdowns();
    if (event.target.closest('[data-search-open]')) { event.preventDefault(); openSearch(); return; }
    if (event.target.closest('[data-search-close]')) { event.preventDefault(); closeSearch(); return; }
    if (event.target.closest('[data-data-modal-close]')) { event.preventDefault(); closeDataModal(); return; }
    const modalCancelLink = event.target.closest('#admin-data-modal a');
    if (modalCancelLink && /^(batal|kembali)$/i.test(modalCancelLink.textContent.trim())) { event.preventDefault(); closeDataModal(); return; }
    const coming = event.target.closest('[data-coming-soon]');
    if (coming) { event.preventDefault(); closeSidebar(); closeDropdowns(); await showComingSoon(coming.getAttribute('data-coming-soon') || 'Fitur ini akan dikerjakan pada tahap berikutnya.'); return; }
    const modalLink = event.target.closest('a[data-modal-form]') || event.target.closest('.admin-main a[href*="/create"]') || event.target.closest('.admin-main a[href*="/edit"]') || event.target.closest('.admin-main a[href$="/generate"]');
    if (modalLink && modalLink.href && modalLink.origin === window.location.origin) { event.preventDefault(); await openDataModalFromUrl(modalLink.href, modalLink.dataset.modalTitle || modalLink.textContent.trim()); return; }
    const internalLink = event.target.closest('a[href]');
    if (internalLink && shouldSoftNavigate(internalLink, event)) {
        event.preventDefault();
        closeSearch();
        await softRefresh(internalLink.href, { push: true });
        return;
    }
    const pagination = event.target.closest('.pagination a, nav[role="navigation"] a');
    if (pagination && pagination.origin === window.location.origin) { event.preventDefault(); await softRefresh(pagination.href,{push:true}); return; }
});

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.matches('[data-logout-form]')) { event.preventDefault(); await handleLogout(form); return; }
    if (form.closest('#admin-data-modal')) { event.preventDefault(); await submitFetchForm(form,{modal:true}); return; }
    if (form.matches('[data-ajax-filter]') || (form.method.toLowerCase()==='get' && form.closest('.admin-main'))) {
        event.preventDefault(); const params=new URLSearchParams(new FormData(form)); const url=`${form.action}?${params.toString()}`; await softRefresh(url,{push:true}); return;
    }
    if (form.matches('[data-ajax-action], [data-confirm-form]') || (form.closest('.admin-main') && form.method.toLowerCase()!=='get' && !form.matches('[data-native-submit]'))) {
        event.preventDefault(); const message=form.dataset.confirmMessage; if(message){const ok=await showAlert({title:'Konfirmasi Tindakan',message,type:'warning',confirm:true,confirmText:'Lanjutkan',cancelText:'Batal'});if(!ok)return;} await submitFetchForm(form,{modal:false}); return;
    }
});

searchInput?.addEventListener('input', (e) => filterSearchItems(e.target.value));
alertConfirm?.addEventListener('click', () => resolveAlert(true));
alertCancel?.addEventListener('click', () => resolveAlert(false));
sidebarOverlay?.addEventListener('click', closeSidebar);

document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase()==='k') { event.preventDefault(); openSearch(); }
    if (event.key==='Escape') { if(!dataModal?.hidden) closeDataModal(); else if(!searchModal?.hidden) closeSearch(); else { closeSidebar(); closeDropdowns(); if(!alertModal?.hidden) resolveAlert(false); } }
});

window.addEventListener('resize', () => { if (isDesktopSidebar()) closeSidebar(); });
window.addEventListener('popstate', (event) => { if (!event.state?.ettraBoundary) softRefresh(window.location.href); });
window.addEventListener('pageshow', (event) => { if(event.persisted) window.location.reload(); });

// Boundary created after successful login: the first Back action asks whether the user wants to logout.
if (body.dataset.authenticated==='1' && sessionStorage.getItem('ettra-login-boundary')==='1') {
    sessionStorage.removeItem('ettra-login-boundary');
    history.replaceState({ettraBoundary:true},'',window.location.href);
    history.pushState({ettraCurrent:true},'',window.location.href);
    window.addEventListener('popstate', async function authBoundary(event){
        if(!event.state?.ettraBoundary)return;
        history.pushState({ettraCurrent:true},'',window.location.href);
        const ok=await showAlert({title:'Ingin keluar dari sistem?',message:'Anda sedang mencoba kembali melewati halaman awal sesi. Untuk keamanan, keluar terlebih dahulu jika ingin meninggalkan area Admin.',type:'warning',confirm:true,confirmText:'Ya, Logout',cancelText:'Tetap di Sistem',eyebrow:'Navigasi Sesi'});
        if(ok){const form=document.querySelector('[data-logout-form]');if(form)await handleLogout(form,{skipConfirm:true});}
    });
}

initializeDynamic();
updateSidebarState();
