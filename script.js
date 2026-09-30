/* =========================================================
   TESTIMONI
========================================================= */

function submitTestimonial(e) {
    if (e) e.preventDefault();

    const nameInput = document.getElementById("testimonialName");
    const messageInput = document.getElementById("testimonialMessage");

    const name = nameInput?.value?.trim() || "";
    const message = messageInput?.value?.trim() || "";

    if (!name || !message) {
        if (typeof showToast === "function") {
            showToast("Lengkapi testimoni.", "warning");
        }
        return;
    }

    // Pastikan testimonials selalu berupa array
    if (!Array.isArray(window.testimonials)) {
        window.testimonials = [];
    }

    const newTestimonial = {
        id: Date.now(),
        name: name,
        message: message,
        rating: 5,
        date: new Date().toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric"
        })
    };

    window.testimonials.push(newTestimonial);

    localStorage.setItem(
        "holando_testimonials",
        JSON.stringify(window.testimonials)
    );

    renderTestimonials();

    if (e?.target && typeof e.target.reset === "function") {
        e.target.reset();
    }

    if (typeof showToast === "function") {
        showToast("Terima kasih atas testimoninya.", "success");
    }
}


/* =========================================================
   RENDER TESTIMONI
========================================================= */

function renderTestimonials() {
    const container = document.getElementById("testimonialList");

    if (!container) return;

    // Ambil dari localStorage jika belum tersedia
    if (!Array.isArray(window.testimonials)) {
        try {
            const saved = localStorage.getItem("holando_testimonials");

            window.testimonials = saved
                ? JSON.parse(saved)
                : [];
        } catch (error) {
            console.error(
                "Gagal membaca data testimoni:",
                error
            );

            window.testimonials = [];
        }
    }

    if (!Array.isArray(window.testimonials)) {
        window.testimonials = [];
    }

    if (window.testimonials.length === 0) {
        container.innerHTML = `
            <div class="empty-testimonials">
                <i class="fa-solid fa-comments"></i>
                <h3>Belum ada testimoni</h3>
                <p>
                    Jadilah pembudidaya pertama yang memberikan testimoni.
                </p>
            </div>
        `;
        return;
    }

    const testimonialsData = window.testimonials
        .slice()
        .reverse();

    container.innerHTML = testimonialsData
        .map(item => {

            const name = String(item?.name || "Pembudidaya");
            const message = String(item?.message || "");
            const date = String(item?.date || "");

            let rating = Number(item?.rating || 5);

            // Batasi rating 1–5
            rating = Math.max(
                1,
                Math.min(5, rating)
            );

            const avatar = name
                .charAt(0)
                .toUpperCase();

            const stars = Array.from(
                { length: 5 },
                (_, index) => `
                    <i class="fa-solid fa-star ${index < rating ? "active" : ""}"></i>
                `
            ).join("");

            return `
                <div class="testimonial-card">

                    <div class="testimonial-avatar">
                        ${
                            typeof escapeHTML === "function"
                                ? escapeHTML(avatar)
                                : avatar
                        }
                    </div>

                    <div class="testimonial-content">

                        <div class="testimonial-header">

                            <div>
                                <h4>
                                    ${
                                        typeof escapeHTML === "function"
                                            ? escapeHTML(name)
                                            : name
                                    }
                                </h4>

                                <small>
                                    ${
                                        typeof escapeHTML === "function"
                                            ? escapeHTML(date)
                                            : date
                                    }
                                </small>
                            </div>

                            <div class="stars">
                                ${stars}
                            </div>

                        </div>

                        <p>
                            "${
                                typeof escapeHTML === "function"
                                    ? escapeHTML(message)
                                    : message
                            }"
                        </p>

                    </div>

                </div>
            `;
        })
        .join("");
}


/* =========================================================
   PANDUAN PRODUKSI G2 - G3
   BERDASARKAN STANDAR TEKNOLOGI PRODUKSI
   PT KENTANG HOLANDO SEJAHTERA
========================================================= */

/*
   productionGuides tetap menggunakan data yang
   sudah kamu buat sebelumnya.

   Pastikan const productionGuides = [...] berada
   sebelum fungsi renderProductionGuides().
*/


/* =========================================================
   RENDER 6 PANDUAN
========================================================= */

function renderProductionGuides() {
    const container = document.getElementById(
        "productionGuideList"
    );

    if (!container) return;

    if (
        !Array.isArray(window.productionGuides) &&
        typeof productionGuides === "undefined"
    ) {
        console.error(
            "productionGuides belum tersedia."
        );
        return;
    }

    const guides =
        Array.isArray(window.productionGuides)
            ? window.productionGuides
            : productionGuides;

    if (!Array.isArray(guides) || guides.length === 0) {
        container.innerHTML = `
            <div class="empty-testimonials">
                <i class="fa-solid fa-book-open"></i>
                <h3>Panduan belum tersedia</h3>
                <p>
                    Data panduan produksi belum tersedia.
                </p>
            </div>
        `;
        return;
    }

    container.innerHTML = guides
        .map((guide, index) => {

            const guideId = Number(guide.id);

            const number =
                String(guide.number || "00");

            const icon =
                String(
                    guide.icon ||
                    "fa-book-open"
                );

            const title =
                String(
                    guide.title ||
                    "Panduan Produksi"
                );

            const description =
                String(
                    guide.shortDescription ||
                    ""
                );

            const sections =
                Array.isArray(guide.sections)
                    ? guide.sections
                    : [];

            return `
                <article
                    class="production-guide-card ${
                        index === 0 ? "active" : ""
                    }"
                    data-guide-id="${guideId}"
                >

                    <button
                        type="button"
                        class="production-guide-header"
                        onclick="toggleProductionGuide(${guideId})"
                        aria-expanded="${
                            index === 0
                                ? "true"
                                : "false"
                        }"
                    >

                        <div class="production-guide-number">
                            ${
                                typeof escapeHTML === "function"
                                    ? escapeHTML(number)
                                    : number
                            }
                        </div>

                        <div class="production-guide-icon">
                            <i class="fa-solid ${icon}"></i>
                        </div>

                        <div class="production-guide-title">

                            <span>
                                PANDUAN ${
                                    typeof escapeHTML === "function"
                                        ? escapeHTML(number)
                                        : number
                                }
                            </span>

                            <h3>
                                ${
                                    typeof escapeHTML === "function"
                                        ? escapeHTML(title)
                                        : title
                                }
                            </h3>

                            <p>
                                ${
                                    typeof escapeHTML === "function"
                                        ? escapeHTML(description)
                                        : description
                                }
                            </p>

                        </div>

                        <div class="production-guide-arrow">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>

                    </button>

                    <div class="production-guide-content">

                        <div class="production-guide-inner">

                            ${
                                sections.length
                                    ? sections
                                        .map(section => {

                                            const sectionTitle =
                                                String(
                                                    section?.title ||
                                                    ""
                                                );

                                            const sectionContent =
                                                String(
                                                    section?.content ||
                                                    ""
                                                );

                                            return `
                                                <div
                                                    class="production-guide-section"
                                                >

                                                    <h4>
                                                        <i
                                                            class="fa-solid fa-circle-check"
                                                        ></i>

                                                        ${
                                                            typeof escapeHTML === "function"
                                                                ? escapeHTML(sectionTitle)
                                                                : sectionTitle
                                                        }
                                                    </h4>

                                                    <div
                                                        class="production-guide-text"
                                                    >
                                                        ${sectionContent}
                                                    </div>

                                                </div>
                                            `;
                                        })
                                        .join("")
                                    : `
                                        <p>
                                            Informasi panduan
                                            belum tersedia.
                                        </p>
                                    `
                            }

                        </div>

                    </div>

                </article>
            `;
        })
        .join("");
}


/* =========================================================
   BUKA / TUTUP PANDUAN
========================================================= */

function toggleProductionGuide(id) {

    const cards = document.querySelectorAll(
        ".production-guide-card"
    );

    if (!cards.length) return;

    cards.forEach(card => {

        const cardId = Number(
            card.dataset.guideId
        );

        const button =
            card.querySelector(
                ".production-guide-header"
            );

        if (cardId === Number(id)) {

            const isActive =
                card.classList.contains("active");

            card.classList.toggle(
                "active"
            );

            if (button) {
                button.setAttribute(
                    "aria-expanded",
                    String(!isActive)
                );
            }

        } else {

            card.classList.remove(
                "active"
            );

            if (button) {
                button.setAttribute(
                    "aria-expanded",
                    "false"
                );
            }
        }
    });
}


/* =========================================================
   BUKA PANDUAN TERTENTU
========================================================= */

function openProductionGuide(id) {

    const card = document.querySelector(
        `.production-guide-card[data-guide-id="${Number(id)}"]`
    );

    if (!card) return;

    document
        .querySelectorAll(".production-guide-card")
        .forEach(item => {

            item.classList.remove(
                "active"
            );

            const button =
                item.querySelector(
                    ".production-guide-header"
                );

            if (button) {
                button.setAttribute(
                    "aria-expanded",
                    "false"
                );
            }
        });

    card.classList.add(
        "active"
    );

    const button =
        card.querySelector(
            ".production-guide-header"
        );

    if (button) {
        button.setAttribute(
            "aria-expanded",
            "true"
        );
    }

    setTimeout(() => {
        card.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });
    }, 50);
}


/* =========================================================
   AKSES PANDUAN
   TETAP MENGGUNAKAN SISTEM PEMBELIAN
========================================================= */

function unlockGuideWithPurchase() {

    const catalog =
        document.getElementById(
            "katalog"
        );

    if (catalog) {

        catalog.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }

    if (typeof showToast === "function") {
        showToast(
            "Silakan pilih produk benih terlebih dahulu.",
            "warning"
        );
    }
}


/* =========================================================
   CEK STATUS AKSES PANDUAN
========================================================= */

function checkGuideStatus() {

    const paywall =
        document.getElementById(
            "guidePaywall"
        );

    const wrapper =
        document.getElementById(
            "guideContentWrapper"
        );

    if (!paywall || !wrapper) {
        return;
    }

    // Pastikan orders tersedia
    let currentOrders = [];

    if (Array.isArray(window.orders)) {

        currentOrders =
            window.orders;

    } else {

        try {

            const savedOrders =
                localStorage.getItem(
                    "holando_orders"
                );

            currentOrders =
                savedOrders
                    ? JSON.parse(savedOrders)
                    : [];

        } catch (error) {

            console.error(
                "Gagal membaca data pesanan:",
                error
            );

            currentOrders = [];
        }
    }

    if (!Array.isArray(currentOrders)) {
        currentOrders = [];
    }

    /*
       Panduan terbuka apabila terdapat
       minimal satu pesanan yang tidak dibatalkan.
    */
    const hasPurchase =
        currentOrders.some(order => {

            if (!order) return false;

            const status =
                String(
                    order.status || ""
                ).toLowerCase();

            return (
                status !== "dibatalkan" &&
                status !== "cancelled"
            );
        });

    if (hasPurchase) {

        paywall.style.display =
            "none";

        wrapper.style.display =
            "block";

        wrapper.classList.add(
            "guide-unlocked"
        );

        wrapper.classList.add(
            "unlocked"
        );

    } else {

        paywall.style.display =
            "block";

        wrapper.style.display =
            "block";

        wrapper.classList.remove(
            "guide-unlocked"
        );

        wrapper.classList.remove(
            "unlocked"
        );
    }
}


/* =========================================================
   INISIALISASI PANDUAN
========================================================= */

function initializeProductionGuides() {

    try {

        renderProductionGuides();

        checkGuideStatus();

    } catch (error) {

        console.error(
            "Gagal menginisialisasi panduan:",
            error
        );
    }
}


/* =========================================================
   GALERI
========================================================= */

function openGalleryImage(
    image,
    title
) {

    const modal =
        document.getElementById(
            "galleryModal"
        );

    const img =
        document.getElementById(
            "galleryModalImage"
        );

    const ttl =
        document.getElementById(
            "galleryModalTitle"
        );

    if (!modal) return;

    if (img) {

        img.src = image || "";

        img.alt =
            title ||
            "Galeri Beken Seeds";
    }

    if (ttl) {

        ttl.textContent =
            title || "";
    }

    modal.classList.add(
        "active"
    );

    // Mencegah halaman belakang ikut scroll
    document.body.classList.add(
        "modal-open"
    );
}


/* =========================================================
   TUTUP GALERI
========================================================= */

function closeGalleryImage() {

    const modal =
        document.getElementById(
            "galleryModal"
        );

    if (modal) {

        modal.classList.remove(
            "active"
        );
    }

    document.body.classList.remove(
        "modal-open"
    );
}


/* =========================================================
   TUTUP GALERI KETIKA KLIK BACKDROP
========================================================= */

document.addEventListener(
    "click",
    function (e) {

        const modal =
            document.getElementById(
                "galleryModal"
            );

        if (
            modal &&
            e.target === modal
        ) {
            closeGalleryImage();
        }
    }
);


/* =========================================================
   TUTUP GALERI DENGAN ESC
========================================================= */

document.addEventListener(
    "keydown",
    function (e) {

        if (e.key !== "Escape") {
            return;
        }

        const modal =
            document.getElementById(
                "galleryModal"
            );

        if (
            modal &&
            modal.classList.contains("active")
        ) {
            closeGalleryImage();
        }
    }
);/* =========================================================
   TESTIMONI
========================================================= */

function submitTestimonial(e) {
    if (e) e.preventDefault();

    const nameInput = document.getElementById("testimonialName");
    const messageInput = document.getElementById("testimonialMessage");

    const name = nameInput?.value?.trim() || "";
    const message = messageInput?.value?.trim() || "";

    if (!name || !message) {
        if (typeof showToast === "function") {
            showToast("Lengkapi testimoni.", "warning");
        }
        return;
    }

    // Pastikan testimonials selalu berupa array
    if (!Array.isArray(window.testimonials)) {
        window.testimonials = [];
    }

    const newTestimonial = {
        id: Date.now(),
        name: name,
        message: message,
        rating: 5,
        date: new Date().toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric"
        })
    };

    window.testimonials.push(newTestimonial);

    localStorage.setItem(
        "holando_testimonials",
        JSON.stringify(window.testimonials)
    );

    renderTestimonials();

    if (e?.target && typeof e.target.reset === "function") {
        e.target.reset();
    }

    if (typeof showToast === "function") {
        showToast("Terima kasih atas testimoninya.", "success");
    }
}


/* =========================================================
   RENDER TESTIMONI
========================================================= */

function renderTestimonials() {
    const container = document.getElementById("testimonialList");

    if (!container) return;

    // Ambil dari localStorage jika belum tersedia
    if (!Array.isArray(window.testimonials)) {
        try {
            const saved = localStorage.getItem("holando_testimonials");

            window.testimonials = saved
                ? JSON.parse(saved)
                : [];
        } catch (error) {
            console.error(
                "Gagal membaca data testimoni:",
                error
            );

            window.testimonials = [];
        }
    }

    if (!Array.isArray(window.testimonials)) {
        window.testimonials = [];
    }

    if (window.testimonials.length === 0) {
        container.innerHTML = `
            <div class="empty-testimonials">
                <i class="fa-solid fa-comments"></i>
                <h3>Belum ada testimoni</h3>
                <p>
                    Jadilah pembudidaya pertama yang memberikan testimoni.
                </p>
            </div>
        `;
        return;
    }

    const testimonialsData = window.testimonials
        .slice()
        .reverse();

    container.innerHTML = testimonialsData
        .map(item => {

            const name = String(item?.name || "Pembudidaya");
            const message = String(item?.message || "");
            const date = String(item?.date || "");

            let rating = Number(item?.rating || 5);

            // Batasi rating 1–5
            rating = Math.max(
                1,
                Math.min(5, rating)
            );

            const avatar = name
                .charAt(0)
                .toUpperCase();

            const stars = Array.from(
                { length: 5 },
                (_, index) => `
                    <i class="fa-solid fa-star ${index < rating ? "active" : ""}"></i>
                `
            ).join("");

            return `
                <div class="testimonial-card">

                    <div class="testimonial-avatar">
                        ${
                            typeof escapeHTML === "function"
                                ? escapeHTML(avatar)
                                : avatar
                        }
                    </div>

                    <div class="testimonial-content">

                        <div class="testimonial-header">

                            <div>
                                <h4>
                                    ${
                                        typeof escapeHTML === "function"
                                            ? escapeHTML(name)
                                            : name
                                    }
                                </h4>

                                <small>
                                    ${
                                        typeof escapeHTML === "function"
                                            ? escapeHTML(date)
                                            : date
                                    }
                                </small>
                            </div>

                            <div class="stars">
                                ${stars}
                            </div>

                        </div>

                        <p>
                            "${
                                typeof escapeHTML === "function"
                                    ? escapeHTML(message)
                                    : message
                            }"
                        </p>

                    </div>

                </div>
            `;
        })
        .join("");
}


/* =========================================================
   PANDUAN PRODUKSI G2 - G3
   BERDASARKAN STANDAR TEKNOLOGI PRODUKSI
   PT KENTANG HOLANDO SEJAHTERA
========================================================= */

/*
   productionGuides tetap menggunakan data yang
   sudah kamu buat sebelumnya.

   Pastikan const productionGuides = [...] berada
   sebelum fungsi renderProductionGuides().
*/


/* =========================================================
   RENDER 6 PANDUAN
========================================================= */

function renderProductionGuides() {
    const container = document.getElementById(
        "productionGuideList"
    );

    if (!container) return;

    if (
        !Array.isArray(window.productionGuides) &&
        typeof productionGuides === "undefined"
    ) {
        console.error(
            "productionGuides belum tersedia."
        );
        return;
    }

    const guides =
        Array.isArray(window.productionGuides)
            ? window.productionGuides
            : productionGuides;

    if (!Array.isArray(guides) || guides.length === 0) {
        container.innerHTML = `
            <div class="empty-testimonials">
                <i class="fa-solid fa-book-open"></i>
                <h3>Panduan belum tersedia</h3>
                <p>
                    Data panduan produksi belum tersedia.
                </p>
            </div>
        `;
        return;
    }

    container.innerHTML = guides
        .map((guide, index) => {

            const guideId = Number(guide.id);

            const number =
                String(guide.number || "00");

            const icon =
                String(
                    guide.icon ||
                    "fa-book-open"
                );

            const title =
                String(
                    guide.title ||
                    "Panduan Produksi"
                );

            const description =
                String(
                    guide.shortDescription ||
                    ""
                );

            const sections =
                Array.isArray(guide.sections)
                    ? guide.sections
                    : [];

            return `
                <article
                    class="production-guide-card ${
                        index === 0 ? "active" : ""
                    }"
                    data-guide-id="${guideId}"
                >

                    <button
                        type="button"
                        class="production-guide-header"
                        onclick="toggleProductionGuide(${guideId})"
                        aria-expanded="${
                            index === 0
                                ? "true"
                                : "false"
                        }"
                    >

                        <div class="production-guide-number">
                            ${
                                typeof escapeHTML === "function"
                                    ? escapeHTML(number)
                                    : number
                            }
                        </div>

                        <div class="production-guide-icon">
                            <i class="fa-solid ${icon}"></i>
                        </div>

                        <div class="production-guide-title">

                            <span>
                                PANDUAN ${
                                    typeof escapeHTML === "function"
                                        ? escapeHTML(number)
                                        : number
                                }
                            </span>

                            <h3>
                                ${
                                    typeof escapeHTML === "function"
                                        ? escapeHTML(title)
                                        : title
                                }
                            </h3>

                            <p>
                                ${
                                    typeof escapeHTML === "function"
                                        ? escapeHTML(description)
                                        : description
                                }
                            </p>

                        </div>

                        <div class="production-guide-arrow">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>

                    </button>

                    <div class="production-guide-content">

                        <div class="production-guide-inner">

                            ${
                                sections.length
                                    ? sections
                                        .map(section => {

                                            const sectionTitle =
                                                String(
                                                    section?.title ||
                                                    ""
                                                );

                                            const sectionContent =
                                                String(
                                                    section?.content ||
                                                    ""
                                                );

                                            return `
                                                <div
                                                    class="production-guide-section"
                                                >

                                                    <h4>
                                                        <i
                                                            class="fa-solid fa-circle-check"
                                                        ></i>

                                                        ${
                                                            typeof escapeHTML === "function"
                                                                ? escapeHTML(sectionTitle)
                                                                : sectionTitle
                                                        }
                                                    </h4>

                                                    <div
                                                        class="production-guide-text"
                                                    >
                                                        ${sectionContent}
                                                    </div>

                                                </div>
                                            `;
                                        })
                                        .join("")
                                    : `
                                        <p>
                                            Informasi panduan
                                            belum tersedia.
                                        </p>
                                    `
                            }

                        </div>

                    </div>

                </article>
            `;
        })
        .join("");
}


/* =========================================================
   BUKA / TUTUP PANDUAN
========================================================= */

function toggleProductionGuide(id) {

    const cards = document.querySelectorAll(
        ".production-guide-card"
    );

    if (!cards.length) return;

    cards.forEach(card => {

        const cardId = Number(
            card.dataset.guideId
        );

        const button =
            card.querySelector(
                ".production-guide-header"
            );

        if (cardId === Number(id)) {

            const isActive =
                card.classList.contains("active");

            card.classList.toggle(
                "active"
            );

            if (button) {
                button.setAttribute(
                    "aria-expanded",
                    String(!isActive)
                );
            }

        } else {

            card.classList.remove(
                "active"
            );

            if (button) {
                button.setAttribute(
                    "aria-expanded",
                    "false"
                );
            }
        }
    });
}


/* =========================================================
   BUKA PANDUAN TERTENTU
========================================================= */

function openProductionGuide(id) {

    const card = document.querySelector(
        `.production-guide-card[data-guide-id="${Number(id)}"]`
    );

    if (!card) return;

    document
        .querySelectorAll(".production-guide-card")
        .forEach(item => {

            item.classList.remove(
                "active"
            );

            const button =
                item.querySelector(
                    ".production-guide-header"
                );

            if (button) {
                button.setAttribute(
                    "aria-expanded",
                    "false"
                );
            }
        });

    card.classList.add(
        "active"
    );

    const button =
        card.querySelector(
            ".production-guide-header"
        );

    if (button) {
        button.setAttribute(
            "aria-expanded",
            "true"
        );
    }

    setTimeout(() => {
        card.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });
    }, 50);
}


/* =========================================================
   AKSES PANDUAN
   TETAP MENGGUNAKAN SISTEM PEMBELIAN
========================================================= */

function unlockGuideWithPurchase() {

    const catalog =
        document.getElementById(
            "katalog"
        );

    if (catalog) {

        catalog.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }

    if (typeof showToast === "function") {
        showToast(
            "Silakan pilih produk benih terlebih dahulu.",
            "warning"
        );
    }
}


/* =========================================================
   CEK STATUS AKSES PANDUAN
========================================================= */

function checkGuideStatus() {

    const paywall =
        document.getElementById(
            "guidePaywall"
        );

    const wrapper =
        document.getElementById(
            "guideContentWrapper"
        );

    if (!paywall || !wrapper) {
        return;
    }

    // Pastikan orders tersedia
    let currentOrders = [];

    if (Array.isArray(window.orders)) {

        currentOrders =
            window.orders;

    } else {

        try {

            const savedOrders =
                localStorage.getItem(
                    "holando_orders"
                );

            currentOrders =
                savedOrders
                    ? JSON.parse(savedOrders)
                    : [];

        } catch (error) {

            console.error(
                "Gagal membaca data pesanan:",
                error
            );

            currentOrders = [];
        }
    }

    if (!Array.isArray(currentOrders)) {
        currentOrders = [];
    }

    /*
       Panduan terbuka apabila terdapat
       minimal satu pesanan yang tidak dibatalkan.
    */
    const hasPurchase =
        currentOrders.some(order => {

            if (!order) return false;

            const status =
                String(
                    order.status || ""
                ).toLowerCase();

            return (
                status !== "dibatalkan" &&
                status !== "cancelled"
            );
        });

    if (hasPurchase) {

        paywall.style.display =
            "none";

        wrapper.style.display =
            "block";

        wrapper.classList.add(
            "guide-unlocked"
        );

        wrapper.classList.add(
            "unlocked"
        );

    } else {

        paywall.style.display =
            "block";

        wrapper.style.display =
            "block";

        wrapper.classList.remove(
            "guide-unlocked"
        );

        wrapper.classList.remove(
            "unlocked"
        );
    }
}


/* =========================================================
   INISIALISASI PANDUAN
========================================================= */

function initializeProductionGuides() {

    try {

        renderProductionGuides();

        checkGuideStatus();

    } catch (error) {

        console.error(
            "Gagal menginisialisasi panduan:",
            error
        );
    }
}


/* =========================================================
   GALERI
========================================================= */

function openGalleryImage(
    image,
    title
) {

    const modal =
        document.getElementById(
            "galleryModal"
        );

    const img =
        document.getElementById(
            "galleryModalImage"
        );

    const ttl =
        document.getElementById(
            "galleryModalTitle"
        );

    if (!modal) return;

    if (img) {

        img.src = image || "";

        img.alt =
            title ||
            "Galeri Beken Seeds";
    }

    if (ttl) {

        ttl.textContent =
            title || "";
    }

    modal.classList.add(
        "active"
    );

    // Mencegah halaman belakang ikut scroll
    document.body.classList.add(
        "modal-open"
    );
}


/* =========================================================
   TUTUP GALERI
========================================================= */

function closeGalleryImage() {

    const modal =
        document.getElementById(
            "galleryModal"
        );

    if (modal) {

        modal.classList.remove(
            "active"
        );
    }

    document.body.classList.remove(
        "modal-open"
    );
}


/* =========================================================
   TUTUP GALERI KETIKA KLIK BACKDROP
========================================================= */

document.addEventListener(
    "click",
    function (e) {

        const modal =
            document.getElementById(
                "galleryModal"
            );

        if (
            modal &&
            e.target === modal
        ) {
            closeGalleryImage();
        }
    }
);


/* =========================================================
   TUTUP GALERI DENGAN ESC
========================================================= */

document.addEventListener(
    "keydown",
    function (e) {

        if (e.key !== "Escape") {
            return;
        }

        const modal =
            document.getElementById(
                "galleryModal"
            );

        if (
            modal &&
            modal.classList.contains("active")
        ) {
            closeGalleryImage();
        }
    }
);/* =========================================================
   TESTIMONI
========================================================= */

function submitTestimonial(e) {
    if (e) e.preventDefault();

    const nameInput = document.getElementById("testimonialName");
    const messageInput = document.getElementById("testimonialMessage");

    const name = nameInput?.value?.trim() || "";
    const message = messageInput?.value?.trim() || "";

    if (!name || !message) {
        if (typeof showToast === "function") {
            showToast("Lengkapi testimoni.", "warning");
        }
        return;
    }

    // Pastikan testimonials selalu berupa array
    if (!Array.isArray(window.testimonials)) {
        window.testimonials = [];
    }

    const newTestimonial = {
        id: Date.now(),
        name: name,
        message: message,
        rating: 5,
        date: new Date().toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric"
        })
    };

    window.testimonials.push(newTestimonial);

    localStorage.setItem(
        "holando_testimonials",
        JSON.stringify(window.testimonials)
    );

    renderTestimonials();

    if (e?.target && typeof e.target.reset === "function") {
        e.target.reset();
    }

    if (typeof showToast === "function") {
        showToast("Terima kasih atas testimoninya.", "success");
    }
}


/* =========================================================
   RENDER TESTIMONI
========================================================= */

function renderTestimonials() {
    const container = document.getElementById("testimonialList");

    if (!container) return;

    // Ambil dari localStorage jika belum tersedia
    if (!Array.isArray(window.testimonials)) {
        try {
            const saved = localStorage.getItem("holando_testimonials");

            window.testimonials = saved
                ? JSON.parse(saved)
                : [];
        } catch (error) {
            console.error(
                "Gagal membaca data testimoni:",
                error
            );

            window.testimonials = [];
        }
    }

    if (!Array.isArray(window.testimonials)) {
        window.testimonials = [];
    }

    if (window.testimonials.length === 0) {
        container.innerHTML = `
            <div class="empty-testimonials">
                <i class="fa-solid fa-comments"></i>
                <h3>Belum ada testimoni</h3>
                <p>
                    Jadilah pembudidaya pertama yang memberikan testimoni.
                </p>
            </div>
        `;
        return;
    }

    const testimonialsData = window.testimonials
        .slice()
        .reverse();

    container.innerHTML = testimonialsData
        .map(item => {

            const name = String(item?.name || "Pembudidaya");
            const message = String(item?.message || "");
            const date = String(item?.date || "");

            let rating = Number(item?.rating || 5);

            // Batasi rating 1–5
            rating = Math.max(
                1,
                Math.min(5, rating)
            );

            const avatar = name
                .charAt(0)
                .toUpperCase();

            const stars = Array.from(
                { length: 5 },
                (_, index) => `
                    <i class="fa-solid fa-star ${index < rating ? "active" : ""}"></i>
                `
            ).join("");

            return `
                <div class="testimonial-card">

                    <div class="testimonial-avatar">
                        ${
                            typeof escapeHTML === "function"
                                ? escapeHTML(avatar)
                                : avatar
                        }
                    </div>

                    <div class="testimonial-content">

                        <div class="testimonial-header">

                            <div>
                                <h4>
                                    ${
                                        typeof escapeHTML === "function"
                                            ? escapeHTML(name)
                                            : name
                                    }
                                </h4>

                                <small>
                                    ${
                                        typeof escapeHTML === "function"
                                            ? escapeHTML(date)
                                            : date
                                    }
                                </small>
                            </div>

                            <div class="stars">
                                ${stars}
                            </div>

                        </div>

                        <p>
                            "${
                                typeof escapeHTML === "function"
                                    ? escapeHTML(message)
                                    : message
                            }"
                        </p>

                    </div>

                </div>
            `;
        })
        .join("");
}


/* =========================================================
   PANDUAN PRODUKSI G2 - G3
   BERDASARKAN STANDAR TEKNOLOGI PRODUKSI
   PT KENTANG HOLANDO SEJAHTERA
========================================================= */

/*
   productionGuides tetap menggunakan data yang
   sudah kamu buat sebelumnya.

   Pastikan const productionGuides = [...] berada
   sebelum fungsi renderProductionGuides().
*/


/* =========================================================
   RENDER 6 PANDUAN
========================================================= */

function renderProductionGuides() {
    const container = document.getElementById(
        "productionGuideList"
    );

    if (!container) return;

    if (
        !Array.isArray(window.productionGuides) &&
        typeof productionGuides === "undefined"
    ) {
        console.error(
            "productionGuides belum tersedia."
        );
        return;
    }

    const guides =
        Array.isArray(window.productionGuides)
            ? window.productionGuides
            : productionGuides;

    if (!Array.isArray(guides) || guides.length === 0) {
        container.innerHTML = `
            <div class="empty-testimonials">
                <i class="fa-solid fa-book-open"></i>
                <h3>Panduan belum tersedia</h3>
                <p>
                    Data panduan produksi belum tersedia.
                </p>
            </div>
        `;
        return;
    }

    container.innerHTML = guides
        .map((guide, index) => {

            const guideId = Number(guide.id);

            const number =
                String(guide.number || "00");

            const icon =
                String(
                    guide.icon ||
                    "fa-book-open"
                );

            const title =
                String(
                    guide.title ||
                    "Panduan Produksi"
                );

            const description =
                String(
                    guide.shortDescription ||
                    ""
                );

            const sections =
                Array.isArray(guide.sections)
                    ? guide.sections
                    : [];

            return `
                <article
                    class="production-guide-card ${
                        index === 0 ? "active" : ""
                    }"
                    data-guide-id="${guideId}"
                >

                    <button
                        type="button"
                        class="production-guide-header"
                        onclick="toggleProductionGuide(${guideId})"
                        aria-expanded="${
                            index === 0
                                ? "true"
                                : "false"
                        }"
                    >

                        <div class="production-guide-number">
                            ${
                                typeof escapeHTML === "function"
                                    ? escapeHTML(number)
                                    : number
                            }
                        </div>

                        <div class="production-guide-icon">
                            <i class="fa-solid ${icon}"></i>
                        </div>

                        <div class="production-guide-title">

                            <span>
                                PANDUAN ${
                                    typeof escapeHTML === "function"
                                        ? escapeHTML(number)
                                        : number
                                }
                            </span>

                            <h3>
                                ${
                                    typeof escapeHTML === "function"
                                        ? escapeHTML(title)
                                        : title
                                }
                            </h3>

                            <p>
                                ${
                                    typeof escapeHTML === "function"
                                        ? escapeHTML(description)
                                        : description
                                }
                            </p>

                        </div>

                        <div class="production-guide-arrow">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>

                    </button>

                    <div class="production-guide-content">

                        <div class="production-guide-inner">

                            ${
                                sections.length
                                    ? sections
                                        .map(section => {

                                            const sectionTitle =
                                                String(
                                                    section?.title ||
                                                    ""
                                                );

                                            const sectionContent =
                                                String(
                                                    section?.content ||
                                                    ""
                                                );

                                            return `
                                                <div
                                                    class="production-guide-section"
                                                >

                                                    <h4>
                                                        <i
                                                            class="fa-solid fa-circle-check"
                                                        ></i>

                                                        ${
                                                            typeof escapeHTML === "function"
                                                                ? escapeHTML(sectionTitle)
                                                                : sectionTitle
                                                        }
                                                    </h4>

                                                    <div
                                                        class="production-guide-text"
                                                    >
                                                        ${sectionContent}
                                                    </div>

                                                </div>
                                            `;
                                        })
                                        .join("")
                                    : `
                                        <p>
                                            Informasi panduan
                                            belum tersedia.
                                        </p>
                                    `
                            }

                        </div>

                    </div>

                </article>
            `;
        })
        .join("");
}


/* =========================================================
   BUKA / TUTUP PANDUAN
========================================================= */

function toggleProductionGuide(id) {

    const cards = document.querySelectorAll(
        ".production-guide-card"
    );

    if (!cards.length) return;

    cards.forEach(card => {

        const cardId = Number(
            card.dataset.guideId
        );

        const button =
            card.querySelector(
                ".production-guide-header"
            );

        if (cardId === Number(id)) {

            const isActive =
                card.classList.contains("active");

            card.classList.toggle(
                "active"
            );

            if (button) {
                button.setAttribute(
                    "aria-expanded",
                    String(!isActive)
                );
            }

        } else {

            card.classList.remove(
                "active"
            );

            if (button) {
                button.setAttribute(
                    "aria-expanded",
                    "false"
                );
            }
        }
    });
}


/* =========================================================
   BUKA PANDUAN TERTENTU
========================================================= */

function openProductionGuide(id) {

    const card = document.querySelector(
        `.production-guide-card[data-guide-id="${Number(id)}"]`
    );

    if (!card) return;

    document
        .querySelectorAll(".production-guide-card")
        .forEach(item => {

            item.classList.remove(
                "active"
            );

            const button =
                item.querySelector(
                    ".production-guide-header"
                );

            if (button) {
                button.setAttribute(
                    "aria-expanded",
                    "false"
                );
            }
        });

    card.classList.add(
        "active"
    );

    const button =
        card.querySelector(
            ".production-guide-header"
        );

    if (button) {
        button.setAttribute(
            "aria-expanded",
            "true"
        );
    }

    setTimeout(() => {
        card.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });
    }, 50);
}


/* =========================================================
   AKSES PANDUAN
   TETAP MENGGUNAKAN SISTEM PEMBELIAN
========================================================= */

function unlockGuideWithPurchase() {

    const catalog =
        document.getElementById(
            "katalog"
        );

    if (catalog) {

        catalog.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }

    if (typeof showToast === "function") {
        showToast(
            "Silakan pilih produk benih terlebih dahulu.",
            "warning"
        );
    }
}


/* =========================================================
   CEK STATUS AKSES PANDUAN
========================================================= */

function checkGuideStatus() {

    const paywall =
        document.getElementById(
            "guidePaywall"
        );

    const wrapper =
        document.getElementById(
            "guideContentWrapper"
        );

    if (!paywall || !wrapper) {
        return;
    }

    // Pastikan orders tersedia
    let currentOrders = [];

    if (Array.isArray(window.orders)) {

        currentOrders =
            window.orders;

    } else {

        try {

            const savedOrders =
                localStorage.getItem(
                    "holando_orders"
                );

            currentOrders =
                savedOrders
                    ? JSON.parse(savedOrders)
                    : [];

        } catch (error) {

            console.error(
                "Gagal membaca data pesanan:",
                error
            );

            currentOrders = [];
        }
    }

    if (!Array.isArray(currentOrders)) {
        currentOrders = [];
    }

    /*
       Panduan terbuka apabila terdapat
       minimal satu pesanan yang tidak dibatalkan.
    */
    const hasPurchase =
        currentOrders.some(order => {

            if (!order) return false;

            const status =
                String(
                    order.status || ""
                ).toLowerCase();

            return (
                status !== "dibatalkan" &&
                status !== "cancelled"
            );
        });

    if (hasPurchase) {

        paywall.style.display =
            "none";

        wrapper.style.display =
            "block";

        wrapper.classList.add(
            "guide-unlocked"
        );

        wrapper.classList.add(
            "unlocked"
        );

    } else {

        paywall.style.display =
            "block";

        wrapper.style.display =
            "block";

        wrapper.classList.remove(
            "guide-unlocked"
        );

        wrapper.classList.remove(
            "unlocked"
        );
    }
}


/* =========================================================
   INISIALISASI PANDUAN
========================================================= */

function initializeProductionGuides() {

    try {

        renderProductionGuides();

        checkGuideStatus();

    } catch (error) {

        console.error(
            "Gagal menginisialisasi panduan:",
            error
        );
    }
}


/* =========================================================
   GALERI
========================================================= */

function openGalleryImage(
    image,
    title
) {

    const modal =
        document.getElementById(
            "galleryModal"
        );

    const img =
        document.getElementById(
            "galleryModalImage"
        );

    const ttl =
        document.getElementById(
            "galleryModalTitle"
        );

    if (!modal) return;

    if (img) {

        img.src = image || "";

        img.alt =
            title ||
            "Galeri Beken Seeds";
    }

    if (ttl) {

        ttl.textContent =
            title || "";
    }

    modal.classList.add(
        "active"
    );

    // Mencegah halaman belakang ikut scroll
    document.body.classList.add(
        "modal-open"
    );
}


/* =========================================================
   TUTUP GALERI
========================================================= */

function closeGalleryImage() {

    const modal =
        document.getElementById(
            "galleryModal"
        );

    if (modal) {

        modal.classList.remove(
            "active"
        );
    }

    document.body.classList.remove(
        "modal-open"
    );
}


/* =========================================================
   TUTUP GALERI KETIKA KLIK BACKDROP
========================================================= */

document.addEventListener(
    "click",
    function (e) {

        const modal =
            document.getElementById(
                "galleryModal"
            );

        if (
            modal &&
            e.target === modal
        ) {
            closeGalleryImage();
        }
    }
);


/* =========================================================
   TUTUP GALERI DENGAN ESC
========================================================= */

document.addEventListener(
    "keydown",
    function (e) {

        if (e.key !== "Escape") {
            return;
        }

        const modal =
            document.getElementById(
                "galleryModal"
            );

        if (
            modal &&
            modal.classList.contains("active")
        ) {
            closeGalleryImage();
        }
    }
);