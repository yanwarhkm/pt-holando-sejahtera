/* =========================================================
   BEKEN SEEDS
   PT KENTANG HOLANDO SEJAHTERA
   MAIN JAVASCRIPT
   ---------------------------------------------------------
   FITUR:
   - Katalog Produk
   - Search & Filter
   - CRUD Produk
   - Keranjang
   - Checkout
   - WhatsApp
   - QR Scanner BPSB
   - Verifikasi Sertifikat BPSB
   - CRUD BPSB
   - Pembudidaya
   - Testimoni
   - Panduan
   - Galeri
   - Admin Dashboard
========================================================= */

/* =========================================================
   DATABASE PRODUK
========================================================= */
const defaultProducts = [
    {
        id: 1,
        name: "Bibit Kentang Granola G0",
        gen: "G0",
        variety: "Granola",
        price: 2500,
        unit: "knol",
        img: "SS.JPG",
        description: "Benih kentang kelas G0 varietas Granola untuk kebutuhan pembibitan berkualitas."
    },
    {
        id: 2,
        name: "Bibit Kentang Granola G2",
        gen: "G2",
        variety: "Granola",
        price: 40000,
        unit: "kg",
        img: "S.JPG",
        description: "Benih kentang kelas G2 varietas Granola untuk budidaya kentang."
    },
    {
        id: 3,
        name: "Bibit Kentang Electra G2",
        gen: "G2",
        variety: "Electra",
        price: 50000,
        unit: "kg",
        img: "M.JPG",
        description: "Benih kentang kelas G2 varietas Electra dengan kualitas terpilih."
    },
    {
        id: 4,
        name: "Bibit Kentang Granola G3",
        gen: "G3",
        variety: "Granola",
        price: 30000,
        unit: "kg",
        img: "L.JPG",
        description: "Benih kentang kelas G3 varietas Granola untuk kebutuhan budidaya."
    },
    {
        id: 5,
        name: "Bibit Kentang Granola G3 Size XL",
        gen: "G3",
        variety: "Granola",
        price: 35000,
        unit: "kg",
        img: "XL.JPG",
        description: "Benih kentang Granola kelas G3 dengan ukuran XL."
    }
];

const defaultBpsbDatabase = {
    "BPSB-JABAR/KNT/2026/042": {
        status: "VALID & TERVERIFIKASI",
        info: "Kelas G2 - Varietas Granola Lembang. Lulus uji laboratorium.",
        date: "2026",
        region: "Jawa Barat"
    },
    "BPSB-JATENG/KNT/2026/108": {
        status: "VALID & TERVERIFIKASI",
        info: "Kelas G3 - Varietas Atlantic. Pengawasan mutu BPSB Jawa Tengah.",
        date: "2026",
        region: "Jawa Tengah"
    }
};

let products = JSON.parse(localStorage.getItem("holando_products")) || defaultProducts;
let bpsbDatabase = JSON.parse(localStorage.getItem("holando_bpsb")) || defaultBpsbDatabase;
let cart = JSON.parse(localStorage.getItem("holando_cart")) || [];
let orders = JSON.parse(localStorage.getItem("holando_orders")) || [];
let users = JSON.parse(localStorage.getItem("holando_users")) || [];
let testimonials = JSON.parse(localStorage.getItem("holando_testimonials")) || [];

/* =========================================================
   SCANNER STATE
========================================================= */
let bpsbScanner = null;
let scannerRunning = false;
let lastScannedCode = "";
let lastScanTime = 0;

/* =========================================================
   UTILITAS
========================================================= */
function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0
    }).format(Number(value) || 0);
}

function escapeHTML(value) {
    return String(value ?? "")
        .replace(/[&<>"']/g, char => {
            const map = {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;"
            };
            return map[char];
        });
}

function showToast(message, type = "success") {
    const container = document.getElementById("toastContainer");
    if (!container) return;
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    let icon = "fa-circle-check";
    if (type === "error") icon = "fa-circle-exclamation";
    if (type === "warning") icon = "fa-triangle-exclamation";
    toast.innerHTML = `
        <i class="fa-solid ${icon}"></i>
        <span>${escapeHTML(message)}</span>
    `;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = "0";
        toast.style.transform = "translateY(10px)";
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/* =========================================================
   NAVIGASI MOBILE
========================================================= */
function toggleMobileMenu() {
    const nav = document.getElementById("mainNavLinks");
    if (nav) nav.classList.toggle("active");
}

function closeMobileMenu() {
    const nav = document.getElementById("mainNavLinks");
    if (nav) nav.classList.remove("active");
}

/* =========================================================
   PRODUK
========================================================= */
function renderProducts(list) {
    const grid = document.getElementById("productGrid");
    if (!grid) return;
    grid.innerHTML = "";

    if (!Array.isArray(list) || list.length === 0) {
        grid.innerHTML = `
            <div class="empty-products">
                <div class="empty-products-icon">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <h3>Produk tidak ditemukan</h3>
                <p>Coba ubah kata kunci atau filter produk.</p>
            </div>
        `;
        updateProductCount(0);
        return;
    }

    list.forEach((product, index) => {
        const card = document.createElement("article");
        card.className = "product-card";
        card.dataset.id = product.id;
        card.style.animationDelay = `${index * 0.08}s`;

        const name = escapeHTML(product.name);
        const gen = escapeHTML(product.gen || "-");
        const variety = escapeHTML(product.variety || "-");
        const unit = escapeHTML(product.unit || "unit");
        const description = escapeHTML(product.description || `Benih kentang varietas ${product.variety || "-"} kelas ${product.gen || "-"}.`);
        const image = escapeHTML(product.img || "");

        card.innerHTML = `
            <div class="product-img-wrapper">
                <img class="product-img" src="${image}" alt="${name}" loading="lazy" onerror="this.style.opacity='0.2';">
                <span class="product-badge">
                    <i class="fa-solid fa-seedling"></i> ${gen}
                </span>
            </div>
            <div class="product-body">
                <div class="product-meta">
                    <span><i class="fa-solid fa-leaf"></i> ${variety}</span>
                    <span><i class="fa-solid fa-layer-group"></i> ${gen}</span>
                </div>
                <h3 class="product-title">${name}</h3>
                <p class="product-description">${description}</p>
                <div class="product-price">
                    ${formatRupiah(product.price)}<small>/ ${unit}</small>
                </div>
                <div class="product-actions">
                    <button type="button" class="btn btn-outline btn-detail" onclick="openDetailModal(${product.id})">
                        <i class="fa-solid fa-eye"></i> Detail
                    </button>
                    <button type="button" class="btn btn-primary btn-add-cart" onclick="addToCart(${product.id})">
                        <i class="fa-solid fa-cart-plus"></i> Beli
                    </button>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });

    updateProductCount(list.length);
}

function updateProductCount(count) {
    const el = document.getElementById("productCount");
    if (el) el.textContent = `${count} produk`;
    const info = document.getElementById("productResultInfo");
    if (info) info.textContent = count === 0 ? "Produk tidak tersedia" : `Menampilkan ${count} produk`;
}

function filterProducts() {
    const searchInput = document.getElementById("productSearch");
    const search = searchInput ? searchInput.value.toLowerCase().trim() : "";
    const selectedGen = Array.from(document.querySelectorAll(".filter-gen:checked")).map(c => c.value);
    const selectedVar = Array.from(document.querySelectorAll(".filter-var:checked")).map(c => c.value);
    const sortSelect = document.getElementById("productSort");
    const sort = sortSelect ? sortSelect.value : "default";

    let filtered = products.filter(product => {
        const searchable = [product.name, product.gen, product.variety, product.description].join(" ").toLowerCase();
        const matchesSearch = !search || searchable.includes(search);
        const matchesGen = selectedGen.length === 0 || selectedGen.includes(product.gen);
        const matchesVar = selectedVar.length === 0 || selectedVar.includes(product.variety);
        return matchesSearch && matchesGen && matchesVar;
    });

    if (sort === "price-low") filtered.sort((a, b) => Number(a.price) - Number(b.price));
    if (sort === "price-high") filtered.sort((a, b) => Number(b.price) - Number(a.price));
    if (sort === "name") filtered.sort((a, b) => String(a.name).localeCompare(String(b.name), "id"));

    renderProducts(filtered);
}

function resetProductFilter() {
    const search = document.getElementById("productSearch");
    if (search) search.value = "";
    document.querySelectorAll(".filter-gen, .filter-var").forEach(cb => cb.checked = false);
    const sort = document.getElementById("productSort");
    if (sort) sort.value = "default";
    renderProducts(products);
    showToast("Filter produk telah direset.");
}

function initializeProductEvents() {
    const search = document.getElementById("productSearch");
    if (search) search.addEventListener("input", filterProducts);
    const sort = document.getElementById("productSort");
    if (sort) sort.addEventListener("change", filterProducts);
    document.querySelectorAll(".filter-gen, .filter-var").forEach(cb => {
        cb.addEventListener("change", filterProducts);
    });
}

/* =========================================================
   KALKULATOR BENIH
========================================================= */
function calculateSeed() {
    const landInput = document.getElementById("lahanSize");
    const sizeInput = document.getElementById("sizeType");
    const result = document.getElementById("calcResult");
    if (!landInput || !result) return;

    const land = Number(landInput.value);
    const size = sizeInput ? sizeInput.value : "M";

    if (!land || land <= 0) {
        result.innerHTML = `
            <div class="result-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                Masukkan luas lahan terlebih dahulu.
            </div>
        `;
        return;
    }

    const spacing = 0.25;
    const plantsPerM2 = 1 / spacing;
    const total = Math.ceil(land * plantsPerM2);
    const extra = Math.ceil(total * 0.10);
    const recommended = total + extra;

    result.innerHTML = `
        <div class="calc-result-success">
            <div class="calc-result-icon"><i class="fa-solid fa-seedling"></i></div>
            <div>
                <span>Estimasi kebutuhan benih</span>
                <strong>${recommended.toLocaleString("id-ID")} benih</strong>
                <small>Ukuran ${escapeHTML(size)} • termasuk cadangan ±10%</small>
            </div>
        </div>
    `;
}

/* =========================================================
   VERIFIKASI BPSB
========================================================= */
function normalizeBPSBCode(code) {
    return String(code || "").trim().toUpperCase().replace(/\s+/g, "");
}

function verifyBPSB(codeFromScanner = null) {
    const input = document.getElementById("bpsbCode");
    const checkerResult = document.getElementById("checkerResult");
    const scannerResult = document.getElementById("scannerResult");

    let code = codeFromScanner;
    if (!code) code = input ? input.value : "";
    code = normalizeBPSBCode(code);

    if (!code) {
        showVerificationResult(false, "Kode BPSB belum dimasukkan.", "", checkerResult, scannerResult);
        return false;
    }
    if (input) input.value = code;

    const data = bpsbDatabase[code];
    if (data) {
        showVerificationResult(true, data.status, data.info, checkerResult, scannerResult, code, data);
        return true;
    }

    showVerificationResult(false, "KODE TIDAK DITEMUKAN", "Kode sertifikat belum ditemukan di database verifikasi Beken Seeds.", checkerResult, scannerResult, code);
    return false;
}

function showVerificationResult(valid, status, info, checkerResult, scannerResult, code = "", data = null) {
    const icon = valid ? "fa-circle-check" : "fa-circle-xmark";
    const title = valid ? "Sertifikat Terverifikasi" : "Verifikasi Tidak Berhasil";
    const colorClass = valid ? "verification-valid" : "verification-invalid";

    const html = `
        <div class="verification-result ${colorClass}">
            <div class="verification-result-icon"><i class="fa-solid ${icon}"></i></div>
            <div class="verification-result-content">
                <span class="verification-label">${valid ? "STATUS SERTIFIKAT" : "STATUS VERIFIKASI"}</span>
                <h3>${escapeHTML(title)}</h3>
                <strong>${escapeHTML(status)}</strong>
                ${code ? `
                    <div class="verification-code">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>${escapeHTML(code)}</span>
                    </div>
                ` : ""}
                <p>${escapeHTML(info)}</p>
                ${data ? `
                    <div class="verification-meta">
                        ${data.region ? `<span><i class="fa-solid fa-location-dot"></i> ${escapeHTML(data.region)}</span>` : ""}
                        ${data.date ? `<span><i class="fa-solid fa-calendar"></i> ${escapeHTML(data.date)}</span>` : ""}
                    </div>
                ` : ""}
            </div>
        </div>
    `;

    if (checkerResult) { checkerResult.innerHTML = html; checkerResult.style.display = "block"; }
    if (scannerResult) { scannerResult.innerHTML = html; scannerResult.style.display = "block"; }
    showToast(valid ? "Sertifikat BPSB berhasil diverifikasi." : "Kode BPSB tidak ditemukan.", valid ? "success" : "warning");
}

/* =========================================================
   QR SCANNER
========================================================= */
function startBPSBScanner() {
    if (scannerRunning) return;
    const scannerElement = document.getElementById("bpsbScanner");
    const placeholder = document.getElementById("scannerPlaceholder");
    const startBtn = document.getElementById("startScannerBtn");
    const stopBtn = document.getElementById("stopScannerBtn");
    const status = document.getElementById("scannerStatus");

    if (!scannerElement) { showToast("Area scanner tidak ditemukan.", "error"); return; }
    if (typeof Html5Qrcode === "undefined") { showToast("Library scanner belum dimuat.", "error"); return; }

    bpsbScanner = new Html5Qrcode("bpsbScanner");
    const config = {
        fps: 10,
        qrbox: (w, h) => ({ width: Math.floor(Math.min(w, h) * 0.7), height: Math.floor(Math.min(w, h) * 0.7) }),
        aspectRatio: 1.0
    };

    bpsbScanner.start({ facingMode: "environment" }, config, onBPSBScanSuccess, () => {})
        .then(() => {
            scannerRunning = true;
            if (placeholder) placeholder.style.display = "none";
            if (startBtn) startBtn.style.display = "none";
            if (stopBtn) stopBtn.style.display = "inline-flex";
            if (status) {
                status.innerHTML = `<i class="fa-solid fa-camera"></i> <span>Kamera aktif. Arahkan ke QR Code BPSB.</span>`;
                status.className = "scanner-status scanner-active";
            }
            showToast("Scanner kamera aktif.");
        })
        .catch(err => {
            console.error("Scanner error:", err);
            scannerRunning = false;
            if (status) {
                status.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> <span>Kamera tidak dapat digunakan. Pastikan izin kamera diberikan.</span>`;
                status.className = "scanner-status scanner-error";
            }
            showToast("Kamera tidak dapat diakses.", "error");
        });
}

function onBPSBScanSuccess(decodedText) {
    const now = Date.now();
    if (decodedText === lastScannedCode && now - lastScanTime < 3000) return;
    lastScannedCode = decodedText;
    lastScanTime = now;

    const cleanCode = normalizeBPSBCode(decodedText);
    const input = document.getElementById("bpsbCode");
    if (input) input.value = cleanCode;
    verifyBPSB(cleanCode);

    const status = document.getElementById("scannerStatus");
    if (status) {
        status.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>QR berhasil dibaca.</span>`;
        status.className = "scanner-status scanner-success";
    }
    setTimeout(() => stopBPSBScanner(false), 1000);
}

function stopBPSBScanner(showMessage = true) {
    const placeholder = document.getElementById("scannerPlaceholder");
    const startBtn = document.getElementById("startScannerBtn");
    const stopBtn = document.getElementById("stopScannerBtn");
    const status = document.getElementById("scannerStatus");

    function resetUI(showMsg) {
        if (placeholder) placeholder.style.display = "flex";
        if (startBtn) startBtn.style.display = "inline-flex";
        if (stopBtn) stopBtn.style.display = "none";
        if (status) {
            status.innerHTML = `<i class="fa-solid fa-circle-info"></i> <span>Scanner siap digunakan.</span>`;
            status.className = "scanner-status";
        }
        if (showMsg) showToast("Scanner dihentikan.");
    }

    if (bpsbScanner && scannerRunning) {
        bpsbScanner.stop()
            .then(() => { scannerRunning = false; resetUI(showMessage); })
            .catch(() => { scannerRunning = false; resetUI(showMessage); });
    } else {
        scannerRunning = false;
        resetUI(showMessage);
    }
}

/* =========================================================
   KERANJANG
========================================================= */
function addToCart(productId) {
    const product = products.find(item => Number(item.id) === Number(productId));
    if (!product) { showToast("Produk tidak ditemukan.", "error"); return; }

    const existing = cart.find(item => Number(item.productId) === Number(productId));
    if (existing) existing.qty += 1;
    else cart.push({ productId: product.id, qty: 1 });

    saveCart();
    updateCartBadge();
    renderCart();
    showToast(`${product.name} ditambahkan ke keranjang.`);
}

function saveCart() {
    localStorage.setItem("holando_cart", JSON.stringify(cart));
}

function updateCartBadge() {
    const total = cart.reduce((sum, item) => sum + Number(item.qty || 0), 0);
    const badge = document.getElementById("cartBadge");
    const mobileBadge = document.getElementById("mobileCartBadge");
    if (badge) badge.textContent = total;
    if (mobileBadge) mobileBadge.textContent = total;
}

function openCart() {
    const modal = document.getElementById("cartModal");
    if (!modal) return;
    renderCart();
    modal.classList.add("active");
    document.body.classList.add("modal-open");
}

function closeCart() {
    const modal = document.getElementById("cartModal");
    if (!modal) return;
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
}

function renderCart() {
    const container = document.getElementById("cartContainer");
    const totalEl = document.getElementById("cartTotal");
    if (!container) return;

    if (!Array.isArray(cart) || cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <h3>Keranjang masih kosong</h3>
                <p>Silakan pilih produk dari katalog.</p>
            </div>
        `;
        if (totalEl) totalEl.textContent = formatRupiah(0);
        return;
    }

    let total = 0;
    container.innerHTML = "";
    cart.forEach(item => {
        const product = products.find(p => Number(p.id) === Number(item.productId));
        if (!product) return;
        const qty = Number(item.qty) || 1;
        const subtotal = Number(product.price) * qty;
        total += subtotal;

        const row = document.createElement("div");
        row.className = "cart-item-row";
        row.innerHTML = `
            <div class="cart-item-image">
                <img src="${escapeHTML(product.img || "")}" alt="${escapeHTML(product.name)}" onerror="this.style.opacity='0.2';">
            </div>
            <div class="cart-item-info">
                <h4>${escapeHTML(product.name)}</h4>
                <span>${formatRupiah(product.price)} / ${escapeHTML(product.unit || "unit")}</span>
                <div class="qty-control">
                    <button type="button" onclick="updateQty(${product.id}, -1)"><i class="fa-solid fa-minus"></i></button>
                    <strong>${qty}</strong>
                    <button type="button" onclick="updateQty(${product.id}, 1)"><i class="fa-solid fa-plus"></i></button>
                </div>
            </div>
            <div class="cart-item-subtotal">
                <strong>${formatRupiah(subtotal)}</strong>
                <button type="button" class="cart-remove-btn" onclick="updateQty(${product.id}, -${qty})" title="Hapus">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
    });
    if (totalEl) totalEl.textContent = formatRupiah(total);
}

function updateQty(productId, change) {
    const item = cart.find(ci => Number(ci.productId) === Number(productId));
    if (!item) return;
    item.qty = Number(item.qty) + Number(change);
    if (item.qty <= 0) {
        cart = cart.filter(ci => Number(ci.productId) !== Number(productId));
    }
    saveCart();
    updateCartBadge();
    renderCart();
}

/* =========================================================
   CHECKOUT
========================================================= */
function processPayment() {
    if (!cart || cart.length === 0) { showToast("Keranjang masih kosong.", "warning"); return; }

    const name = document.getElementById("buyerName")?.value.trim();
    const phone = document.getElementById("buyerPhone")?.value.trim();
    const address = document.getElementById("buyerAddress")?.value.trim();
    const payment = document.getElementById("paymentMethod")?.value;

    if (!name || !phone || !address) { showToast("Lengkapi data pembeli terlebih dahulu.", "warning"); return; }

    let total = 0;
    const orderItems = cart.map(item => {
        const product = products.find(p => Number(p.id) === Number(item.productId));
        if (!product) return null;
        const subtotal = Number(product.price) * Number(item.qty);
        total += subtotal;
        return { productId: product.id, name: product.name, qty: item.qty, price: product.price, subtotal };
    }).filter(Boolean);

    const orderNumber = "BKS-" + Date.now().toString().slice(-8);
    const order = {
        id: Date.now(),
        orderNumber,
        customer: name,
        phone,
        address,
        payment,
        items: orderItems,
        total,
        status: "Menunggu",
        date: new Date().toLocaleString("id-ID")
    };

    orders.push(order);
    localStorage.setItem("holando_orders", JSON.stringify(orders));
    cart = [];
    saveCart();
    updateCartBadge();
    renderCart();
    renderAdminOrders();
    updateAdminStats();
    checkGuideStatus();

    const successModal = document.getElementById("successOrderModal");
    const orderNumEl = document.getElementById("successOrderNumber");
    if (orderNumEl) orderNumEl.textContent = orderNumber;
    closeCart();
    if (successModal) successModal.classList.add("active");
    showToast("Pesanan berhasil dibuat.");
}

function closeSuccessOrder() {
    const modal = document.getElementById("successOrderModal");
    if (modal) modal.classList.remove("active");
}

function checkoutWA() {
    if (!cart || cart.length === 0) { showToast("Keranjang masih kosong.", "warning"); return; }

    const name = document.getElementById("buyerName")?.value.trim();
    const phone = document.getElementById("buyerPhone")?.value.trim();
    const address = document.getElementById("buyerAddress")?.value.trim();

    if (!name || !phone || !address) { showToast("Lengkapi data pembeli terlebih dahulu.", "warning"); return; }

    let message = `Halo Beken Seeds,%0A%0ASaya ingin melakukan pemesanan:%0A%0A`;
    let total = 0;
    cart.forEach(item => {
        const product = products.find(p => Number(p.id) === Number(item.productId));
        if (!product) return;
        const subtotal = Number(product.price) * Number(item.qty);
        total += subtotal;
        message += `• ${product.name}%0A  ${item.qty} x ${formatRupiah(product.price)}%0A  Subtotal: ${formatRupiah(subtotal)}%0A%0A`;
    });
    message += `Total: ${formatRupiah(total)}%0A%0ANama: ${name}%0AWhatsApp: ${phone}%0AAlamat: ${address}%0A%0ATerima kasih.`;

    const waNumber = "6285797187917";
    window.open(`https://wa.me/${waNumber}?text=${encodeURIComponent(message.replace(/%0A/g, "\n"))}`, "_blank");
}

/* =========================================================
   DETAIL PRODUK
========================================================= */
function openDetailModal(productId) {
    const product = products.find(p => Number(p.id) === Number(productId));
    const modal = document.getElementById("detailModal");
    const title = document.getElementById("modalDetailTitle");
    const body = document.getElementById("modalDetailBody");
    if (!product || !modal || !body) return;

    if (title) title.textContent = product.name;
    body.innerHTML = `
        <div class="product-detail-modal">
            <div class="product-detail-image">
                <img src="${escapeHTML(product.img || "")}" alt="${escapeHTML(product.name)}">
            </div>
            <div class="product-detail-info">
                <div class="product-meta">
                    <span><i class="fa-solid fa-seedling"></i> ${escapeHTML(product.gen || "-")}</span>
                    <span><i class="fa-solid fa-leaf"></i> ${escapeHTML(product.variety || "-")}</span>
                </div>
                <h2>${escapeHTML(product.name)}</h2>
                <p>${escapeHTML(product.description || "Produk benih kentang Beken Seeds.")}</p>
                <div class="detail-price">
                    ${formatRupiah(product.price)}<small>/ ${escapeHTML(product.unit || "unit")}</small>
                </div>
                <button class="btn-primary full-btn" onclick="addToCart(${product.id}); closeDetailModal();">
                    <i class="fa-solid fa-cart-plus"></i> Tambahkan ke Keranjang
                </button>
            </div>
        </div>
    `;
    modal.classList.add("active");
    document.body.classList.add("modal-open");
}

function closeDetailModal() {
    const modal = document.getElementById("detailModal");
    if (modal) modal.classList.remove("active");
    document.body.classList.remove("modal-open");
}

/* =========================================================
   LOGIN
========================================================= */
function openUserLoginModal() {
    const modal = document.getElementById("userLoginModal");
    if (modal) modal.classList.add("active");
}

function closeUserLoginModal() {
    const modal = document.getElementById("userLoginModal");
    if (modal) modal.classList.remove("active");
}

function handleUserLogin(e) {
    e.preventDefault();
    const identifier = document.getElementById("userEmailOrPhone")?.value.trim();
    const password = document.getElementById("userPassword")?.value;
    if (!identifier || !password) { showToast("Lengkapi data login.", "warning"); return; }

    const user = users.find(u => (u.email === identifier || u.phone === identifier) && u.password === password);
    if (user) {
        localStorage.setItem("holando_current_user", JSON.stringify(user));
        closeUserLoginModal();
        showToast(`Selamat datang, ${user.name}.`);
    } else {
        const newUser = {
            id: Date.now(),
            name: identifier,
            email: identifier,
            phone: identifier,
            password,
            createdAt: new Date().toISOString()
        };
        users.push(newUser);
        localStorage.setItem("holando_users", JSON.stringify(users));
        localStorage.setItem("holando_current_user", JSON.stringify(newUser));
        closeUserLoginModal();
        updateAdminStats();
        showToast("Akun berhasil dibuat.");
    }
}

/* =========================================================
   PENDAFTARAN PEMBUDIDAYA
========================================================= */
function registerFarmer(e) {
    e.preventDefault();
    const name = document.getElementById("farmerName")?.value.trim();
    const phone = document.getElementById("farmerPhone")?.value.trim();
    const location = document.getElementById("farmerLocation")?.value.trim();
    const land = document.getElementById("farmerLand")?.value;

    if (!name || !phone || !location || !land) { showToast("Lengkapi semua data.", "warning"); return; }

    const farmers = JSON.parse(localStorage.getItem("holando_farmers") || "[]");
    farmers.push({
        id: Date.now(),
        name, phone, location,
        land: Number(land),
        date: new Date().toLocaleString("id-ID")
    });
    localStorage.setItem("holando_farmers", JSON.stringify(farmers));

    const modal = document.getElementById("successMitraModal");
    if (modal) modal.classList.add("active");
    e.target.reset();
    showToast("Pendaftaran pembudidaya berhasil.");
}

function closeSuccessMitra() {
    const modal = document.getElementById("successMitraModal");
    if (modal) modal.classList.remove("active");
}

/* =========================================================
   TESTIMONI
========================================================= */
function submitTestimonial(e) {
    e.preventDefault();
    const name = document.getElementById("testimonialName")?.value.trim();
    const message = document.getElementById("testimonialMessage")?.value.trim();
    if (!name || !message) { showToast("Lengkapi testimoni.", "warning"); return; }

    testimonials.push({
        id: Date.now(),
        name,
        message,
        rating: 5,
        date: new Date().toLocaleDateString("id-ID")
    });
    localStorage.setItem("holando_testimonials", JSON.stringify(testimonials));
    renderTestimonials();
    e.target.reset();
    showToast("Terima kasih atas testimoninya.");
}

function renderTestimonials() {
    const container = document.getElementById("testimonialList");
    if (!container) return;
    if (!testimonials || testimonials.length === 0) {
        container.innerHTML = `
            <div class="empty-testimonials">
                <i class="fa-solid fa-comments"></i>
                <h3>Belum ada testimoni</h3>
                <p>Jadilah pembudidaya pertama yang memberikan testimoni.</p>
            </div>
        `;
        return;
    }
    container.innerHTML = testimonials.slice().reverse().map(item => `
        <div class="testimonial-card">
            <div class="testimonial-avatar">${escapeHTML(String(item.name).charAt(0).toUpperCase())}</div>
            <div class="testimonial-content">
                <div class="testimonial-header">
                    <div>
                        <h4>${escapeHTML(item.name)}</h4>
                        <small>${escapeHTML(item.date || "")}</small>
                    </div>
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
                <p>"${escapeHTML(item.message)}"</p>
            </div>
        </div>
    `).join("");
}

/* =========================================================
   PANDUAN
========================================================= */
function unlockGuideWithPurchase() {
    const catalog = document.getElementById("katalog");
    if (catalog) catalog.scrollIntoView({ behavior: "smooth" });
    showToast("Silakan pilih produk benih terlebih dahulu.");
}

function checkGuideStatus() {
    const paywall = document.getElementById("guidePaywall");
    const wrapper = document.getElementById("guideContentWrapper");
    const hasPurchase = orders.some(o => o.status !== "Dibatalkan");
    if (paywall && wrapper) {
        paywall.style.display = hasPurchase ? "none" : "block";
        if (hasPurchase) wrapper.classList.add("guide-unlocked");
    }
}

/* =========================================================
   GALERI
========================================================= */
function openGalleryImage(image, title) {
    const modal = document.getElementById("galleryModal");
    const img = document.getElementById("galleryModalImage");
    const ttl = document.getElementById("galleryModalTitle");
    if (!modal) return;
    if (img) { img.src = image; img.alt = title || "Galeri Beken Seeds"; }
    if (ttl) ttl.textContent = title || "";
    modal.classList.add("active");
}

function closeGalleryImage() {
    const modal = document.getElementById("galleryModal");
    if (modal) modal.classList.remove("active");
}

/* =========================================================
   ADMIN DASHBOARD
========================================================= */
function openAdminDashboard() {
    const pwd = prompt("Masukkan password admin:");
    if (pwd !== "admin123" && pwd !== "holando2026") {
        if (pwd !== null) showToast("Password admin salah.", "error");
        return;
    }
    const modal = document.getElementById("adminDashboardModal");
    if (!modal) return;
    updateAdminStats();
    renderAdminOrders();
    renderAdminProducts();
    renderAdminBpsb();
    modal.classList.add("active");
    document.body.classList.add("modal-open");
}

function closeAdminDashboard() {
    const modal = document.getElementById("adminDashboardModal");
    if (modal) modal.classList.remove("active");
    document.body.classList.remove("modal-open");
}

function switchAdminTab(tabId, btn) {
    document.querySelectorAll(".admin-tab-content").forEach(t => t.classList.remove("active"));
    document.querySelectorAll(".admin-tab-btn").forEach(b => b.classList.remove("active"));
    const tab = document.getElementById(tabId);
    if (tab) tab.classList.add("active");
    if (btn) btn.classList.add("active");
    if (tabId === "tabProducts") renderAdminProducts();
    if (tabId === "tabBpsb") renderAdminBpsb();
    if (tabId === "tabOrders") renderAdminOrders();
}

function updateAdminStats() {
    const statOrders = document.getElementById("statOrders");
    const statUsers = document.getElementById("statUsers");
    const statProducts = document.getElementById("statProducts");
    const statRevenue = document.getElementById("statRevenue");

    if (statOrders) statOrders.textContent = orders.length;
    if (statUsers) statUsers.textContent = users.length;
    if (statProducts) statProducts.textContent = products.length;

    const revenue = orders.reduce((sum, o) => o.status === "Dibatalkan" ? sum : sum + Number(o.total || 0), 0);
    if (statRevenue) statRevenue.textContent = formatRupiah(revenue);
}

function renderAdminOrders() {
    const container = document.getElementById("adminOrdersContainer");
    if (!container) return;
    if (!orders || orders.length === 0) {
        container.innerHTML = `
            <div class="admin-empty">
                <i class="fa-solid fa-cart-shopping"></i>
                <h3>Belum ada pesanan</h3>
                <p>Pesanan pelanggan akan muncul di sini.</p>
            </div>
        `;
        return;
    }
    container.innerHTML = orders.slice().reverse().map(order => {
        const statusClass = (order.status || "").toLowerCase().replace(/\s+/g, "-");
        const items = (order.items || []).map(i => `
            <div class="admin-order-item">
                <span>${escapeHTML(i.name)} × ${i.qty}</span>
                <strong>${formatRupiah(i.subtotal)}</strong>
            </div>
        `).join("");
        return `
            <div class="admin-order">
                <div class="admin-order-header">
                    <div>
                        <strong>${escapeHTML(order.orderNumber)}</strong>
                        <small>${escapeHTML(order.date || "")}</small>
                    </div>
                    <span class="order-status ${statusClass}">${escapeHTML(order.status || "Menunggu")}</span>
                </div>
                <div class="admin-order-body">
                    <p><strong>Pembeli:</strong> ${escapeHTML(order.customer)}</p>
                    <p><strong>WhatsApp:</strong> ${escapeHTML(order.phone)}</p>
                    <p><strong>Alamat:</strong> ${escapeHTML(order.address)}</p>
                    <p><strong>Pembayaran:</strong> ${escapeHTML(order.payment || "-")}</p>
                    <div class="admin-order-items">${items}</div>
                    <div class="admin-order-total">Total: <strong>${formatRupiah(order.total)}</strong></div>
                </div>
                <div class="admin-order-buttons">
                    <button class="btn-primary" onclick="updateOrderStatus(${order.id}, 'Diproses')">Proses</button>
                    <button class="btn-outline" onclick="updateOrderStatus(${order.id}, 'Selesai')">Selesai</button>
                    <button class="btn-danger" onclick="deleteOrder(${order.id})"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
        `;
    }).join("");
}

function updateOrderStatus(orderId, status) {
    const order = orders.find(o => Number(o.id) === Number(orderId));
    if (!order) return;
    order.status = status;
    localStorage.setItem("holando_orders", JSON.stringify(orders));
    renderAdminOrders();
    updateAdminStats();
    checkGuideStatus();
    showToast(`Status pesanan diubah menjadi ${status}.`);
}

function deleteOrder(orderId) {
    if (!confirm("Hapus pesanan ini?")) return;
    orders = orders.filter(o => Number(o.id) !== Number(orderId));
    localStorage.setItem("holando_orders", JSON.stringify(orders));
    renderAdminOrders();
    updateAdminStats();
    checkGuideStatus();
    showToast("Pesanan berhasil dihapus.");
}

function renderAdminProducts() {
    const tab = document.getElementById("tabProducts");
    if (!tab) return;
    let container = document.getElementById("adminProductsContainer");
    if (!container) {
        container = document.createElement("div");
        container.id = "adminProductsContainer";
        container.className = "admin-products-list";
        tab.appendChild(container);
    }
    container.innerHTML = `
        <div class="admin-products-title">
            <h3><i class="fa-solid fa-seedling"></i> Daftar Produk</h3>
            <span>${products.length} produk</span>
        </div>
        ${products.length === 0 ? `
            <div class="admin-empty">
                <i class="fa-solid fa-box-open"></i>
                <p>Belum ada produk.</p>
            </div>
        ` : products.map(p => `
            <div class="admin-product">
                <img src="${escapeHTML(p.img || "")}" alt="${escapeHTML(p.name)}">
                <div class="admin-product-info">
                    <strong>${escapeHTML(p.name)}</strong>
                    <span>${escapeHTML(p.gen)} • ${escapeHTML(p.variety)}</span>
                    <b>${formatRupiah(p.price)} / ${escapeHTML(p.unit)}</b>
                </div>
                <div class="admin-product-actions">
                    <button class="btn-outline" onclick="editProduct(${p.id})"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-danger" onclick="deleteProduct(${p.id})"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
        `).join("")}
    `;
}

function addNewProduct(e) {
    e.preventDefault();
    const name = document.getElementById("newProdName")?.value.trim();
    const gen = document.getElementById("newProdGen")?.value;
    const variety = document.getElementById("newProdVar")?.value;
    const price = Number(document.getElementById("newProdPrice")?.value);
    const unit = document.getElementById("newProdUnit")?.value.trim();
    const img = document.getElementById("newProdImg")?.value.trim();
    const desc = document.getElementById("newProdDesc")?.value.trim();

    if (!name || !price || !unit || !desc) { showToast("Lengkapi data produk.", "warning"); return; }

    products.push({
        id: Date.now(),
        name, gen, variety, price, unit,
        img: img || "SS.JPG",
        description: desc
    });
    localStorage.setItem("holando_products", JSON.stringify(products));
    renderProducts(products);
    renderAdminProducts();
    updateAdminStats();
    e.target.reset();
    const unitInput = document.getElementById("newProdUnit");
    if (unitInput) unitInput.value = "kg";
    showToast("Produk berhasil ditambahkan.");
}

function editProduct(productId) {
    const product = products.find(p => Number(p.id) === Number(productId));
    if (!product) return;
    const name = prompt("Nama produk:", product.name);
    if (name === null) return;
    const price = prompt("Harga:", product.price);
    if (price === null) return;
    const desc = prompt("Deskripsi:", product.description || "");
    if (desc === null) return;
    const unit = prompt("Satuan:", product.unit || "kg");
    if (unit === null) return;

    product.name = name.trim() || product.name;
    product.price = Number(price) || product.price;
    product.description = desc.trim();
    product.unit = unit.trim() || product.unit;

    localStorage.setItem("holando_products", JSON.stringify(products));
    renderProducts(products);
    renderAdminProducts();
    updateAdminStats();
    showToast("Produk berhasil diperbarui.");
}

function deleteProduct(productId) {
    const product = products.find(p => Number(p.id) === Number(productId));
    if (!product) return;
    if (!confirm(`Hapus produk "${product.name}"?`)) return;
    products = products.filter(p => Number(p.id) !== Number(productId));
    cart = cart.filter(item => Number(item.productId) !== Number(productId));
    localStorage.setItem("holando_products", JSON.stringify(products));
    saveCart();
    updateCartBadge();
    renderProducts(products);
    renderAdminProducts();
    updateAdminStats();
    showToast("Produk berhasil dihapus.");
}

function renderAdminBpsb() {
    const tab = document.getElementById("tabBpsb");
    if (!tab) return;
    let container = document.getElementById("adminBpsbContainer");
    if (!container) {
        container = document.createElement("div");
        container.id = "adminBpsbContainer";
        container.className = "admin-bpsb-list";
        tab.appendChild(container);
    }
    const entries = Object.entries(bpsbDatabase);
    container.innerHTML = `
        <div class="admin-products-title">
            <h3><i class="fa-solid fa-shield-halved"></i> Database Sertifikat BPSB</h3>
            <span>${entries.length} sertifikat</span>
        </div>
        ${entries.length === 0 ? `
            <div class="admin-empty">
                <i class="fa-solid fa-shield"></i>
                <p>Belum ada sertifikat.</p>
            </div>
        ` : entries.map(([code, data]) => `
            <div class="admin-bpsb">
                <div class="admin-bpsb-icon"><i class="fa-solid fa-qrcode"></i></div>
                <div class="admin-bpsb-info">
                    <strong>${escapeHTML(code)}</strong>
                    <span>${escapeHTML(data.status || "")}</span>
                    <p>${escapeHTML(data.info || "")}</p>
                </div>
                <button class="btn-danger" onclick="deleteBpsbCode('${escapeHTML(code)}')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `).join("")}
    `;
}

function addNewBpsbCode(e) {
    e.preventDefault();
    const codeInput = document.getElementById("newBpsbNumber");
    const infoInput = document.getElementById("newBpsbInfo");
    if (!codeInput || !infoInput) return;

    const code = normalizeBPSBCode(codeInput.value);
    const info = infoInput.value.trim();
    if (!code || !info) { showToast("Lengkapi data sertifikat.", "warning"); return; }
    if (bpsbDatabase[code]) { showToast("Kode BPSB sudah ada.", "warning"); return; }

    bpsbDatabase[code] = {
        status: "VALID & TERVERIFIKASI",
        info,
        date: new Date().getFullYear().toString(),
        region: "Data Beken Seeds"
    };
    localStorage.setItem("holando_bpsb", JSON.stringify(bpsbDatabase));
    renderAdminBpsb();
    codeInput.value = "";
    infoInput.value = "";
    showToast("Sertifikat BPSB berhasil ditambahkan.");
}

function deleteBpsbCode(code) {
    const normalized = normalizeBPSBCode(code);
    if (!bpsbDatabase[normalized]) return;
    if (!confirm(`Hapus sertifikat ${normalized}?`)) return;
    delete bpsbDatabase[normalized];
    localStorage.setItem("holando_bpsb", JSON.stringify(bpsbDatabase));
    renderAdminBpsb();
    showToast("Sertifikat BPSB berhasil dihapus.");
}