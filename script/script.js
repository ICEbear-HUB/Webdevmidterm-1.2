/* =========================
   TOAST NOTIFICATION SYSTEM
========================= */
function showToast(message, type = "success") {
    let container = document.getElementById("toast-container");
    if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container";
        document.body.appendChild(container);
    }

    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;

    const icon = type === "success" ? "✓" : (type === "warning" ? "⚠" : "✕");

    toast.innerHTML = `
        <span class="toast-icon">${icon}</span>
        <span class="toast-message">${message}</span>
        <button class="toast-close" aria-label="Close notification">&times;</button>
    `;

    const closeBtn = toast.querySelector(".toast-close");
    closeBtn.addEventListener("click", () => {
        removeToast(toast);
    });

    container.appendChild(toast);

    // Trigger enter animation
    requestAnimationFrame(() => {
        toast.classList.add("toast-show");
    });

    // Auto dismiss after 3.5 seconds
    setTimeout(() => {
        removeToast(toast);
    }, 3500);
}

function removeToast(toast) {
    if (!toast || !toast.parentNode) return;
    toast.classList.remove("toast-show");
    toast.classList.add("toast-hide");
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 300);
}

/* =========================
   LOGIN REQUIRED MODAL POPUP
========================= */
function openLoginModal() {
    const modal = document.getElementById("loginModal");
    if (modal) {
        modal.classList.add("modal-active");
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden"; // prevent background scroll
    }
}

function closeLoginModal() {
    const modal = document.getElementById("loginModal");
    if (modal) {
        modal.classList.remove("modal-active");
        modal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = ""; // restore background scroll
    }
}

/* =========================
   ORDER NOW SMOOTH SCROLL
========================= */
function orderNow() {
    const productsSection = document.getElementById("products");
    if (productsSection) {
        productsSection.scrollIntoView({
            behavior: "smooth"
        });
    }
}

/* =========================
   MOBILE NAV TOGGLE (DRAWER)
========================= */
document.addEventListener("DOMContentLoaded", function () {
    const navToggle = document.getElementById("navToggle");
    const navMenu = document.getElementById("navMenu");

    if (navToggle && navMenu) {
        navToggle.addEventListener("click", function () {
            navToggle.classList.toggle("active");
            navMenu.classList.toggle("nav-open");
        });

        // Close when clicking a menu link
        navMenu.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                navToggle.classList.remove("active");
                navMenu.classList.remove("nav-open");
            });
        });
    }

    /* =========================
       QUANTITY PICKER (+ / -)
    ========================= */
    document.querySelectorAll(".product").forEach(function (card) {
        const minusBtn = card.querySelector(".qty-minus");
        const plusBtn = card.querySelector(".qty-plus");
        const qtyInput = card.querySelector(".qty-input");
        const addBtn = card.querySelector(".add-to-cart-btn");

        if (minusBtn && plusBtn && qtyInput) {
            minusBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                let val = parseInt(qtyInput.value, 10) || 1;
                if (val > 1) {
                    qtyInput.value = val - 1;
                }
            });

            plusBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                let val = parseInt(qtyInput.value, 10) || 1;
                const max = parseInt(qtyInput.getAttribute("max"), 10) || 999;
                if (val < max) {
                    qtyInput.value = val + 1;
                } else {
                    showToast(`Maximum available stock reached (${max})`, "warning");
                }
            });
        }

        /* =========================
           ADD TO CART BUTTON
        ========================= */
        if (addBtn) {
            addBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                const productId = addBtn.getAttribute("data-product-id") || card.getAttribute("data-product-id");
                const qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;

                if (!productId) return;

                addBtn.disabled = true;
                const originalText = addBtn.textContent;
                addBtn.textContent = "ADDING...";

                fetch("add_to_cart.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "product_id=" + encodeURIComponent(productId) + "&quantity=" + encodeURIComponent(qty)
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        if (data.requiresLogin) {
                            openLoginModal();
                            return;
                        }

                        if (data.success) {
                            const cartCountEl = document.querySelector(".cart-count");
                            if (cartCountEl) {
                                cartCountEl.textContent = data.cartCount;
                                cartCountEl.classList.remove("cart-bump");
                                void cartCountEl.offsetWidth;
                                cartCountEl.classList.add("cart-bump");
                            }
                            showToast(data.message, "success");
                            if (qtyInput) qtyInput.value = 1;
                        } else {
                            showToast(data.message || "Could not add to cart.", "error");
                        }
                    })
                    .catch(function () {
                        showToast("Could not connect to server. Please try again.", "error");
                    })
                    .finally(function () {
                        addBtn.disabled = false;
                        addBtn.textContent = originalText;
                    });
            });
        }
    });

    /* =========================
       LOGIN MODAL LISTENERS
    ========================= */
    const loginModal = document.getElementById("loginModal");
    const loginModalClose = document.getElementById("loginModalClose");
    const loginModalCancel = document.getElementById("loginModalCancel");

    if (loginModal) {
        if (loginModalClose) {
            loginModalClose.addEventListener("click", closeLoginModal);
        }
        if (loginModalCancel) {
            loginModalCancel.addEventListener("click", closeLoginModal);
        }
        // Click outside the modal card to close
        loginModal.addEventListener("click", function (e) {
            if (e.target === loginModal) {
                closeLoginModal();
            }
        });
        // ESC key to close
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && loginModal.classList.contains("modal-active")) {
                closeLoginModal();
            }
        });
    }

    /* =========================
       NAVIGATION ACTIVE EFFECT
    ========================= */
    const navLinks = document.querySelectorAll("nav a");
    navLinks.forEach(function (link) {
        link.addEventListener("click", function () {
            navLinks.forEach(function (item) {
                item.style.color = "white";
            });
            link.style.color = "#6cff00";
        });
    });
});

console.log("ACE PLUS website loaded successfully!");
