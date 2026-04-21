let cart = [];

// 1. Bildirim (Toast) Fonksiyonu
function showToast(message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    // Yeşil, yarı saydam ve şık tasarım
    toast.className = 'alert alert-success border-0 shadow-lg animate__animated animate__fadeInRight';
    toast.style.minWidth = '250px';
    toast.style.marginBottom = '10px';
    toast.style.background = 'rgba(25, 135, 84, 0.9)';
    toast.style.color = 'white';
    toast.style.backdropFilter = 'blur(5px)';
    toast.style.zIndex = '1085';

    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>${message}</div>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.5s ease';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// 2. Sepete Ekleme Olayı (SADECE BİR KEZ TANIMLANDI - Çift eklemeyi önler)
document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', () => {
        const product = {
            name: button.dataset.name,
            price: parseFloat(button.dataset.price),
            image: button.dataset.image,
            quantity: 1
        };

        const existingProduct = cart.find(item => item.name === product.name);
        if (existingProduct) {
            existingProduct.quantity++;
        } else {
            cart.push(product);
        }

        updateCartUI();
        showToast(`${product.name} added successfully!`);
    });
});

// 3. Sepet Arayüzünü Güncelleme
function updateCartUI() {
    const container = document.getElementById('cart-items-container');
    const countElement = document.getElementById('cart-count');
    const totalElement = document.getElementById('cart-total-price');
    const showBtn = document.getElementById('show-checkout-btn');

    container.innerHTML = '';
    let total = 0;
    let count = 0;

    cart.forEach((item, index) => {
        total += item.price * item.quantity;
        count += item.quantity;

        container.innerHTML += `
            <div class="d-flex align-items-center mb-3 bg-secondary bg-opacity-50 p-2 rounded">
                <img src="${item.image}" width="50" height="50" class="rounded me-3" style="object-fit: cover;">
                <div class="flex-grow-1 text-white">
                    <h6 class="mb-0 small fw-bold">${item.name}</h6>
                    <small class="text-light">$${(item.price * item.quantity).toFixed(2)}</small>
                </div>
                <div class="d-flex align-items-center text-white">
                    <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="changeQty(${index}, -1)">-</button>
                    <span class="mx-2 small">${item.quantity}</span>
                    <button class="btn btn-sm btn-outline-success py-0 px-2" onclick="changeQty(${index}, 1)">+</button>
                </div>
            </div>`;
    });

    if (cart.length === 0) {
        container.innerHTML = '<p class="text-center text-white mt-5">Your cart is empty.</p>';
        countElement.style.display = 'none';
        if (showBtn) showBtn.style.display = 'none';
        document.getElementById('checkout-form').style.display = 'none';
    } else {
        countElement.style.display = 'block';
        countElement.innerText = count;
        if (showBtn) showBtn.style.display = 'block';
    }
    totalElement.innerText = `$${total.toFixed(2)}`;
}

// 4. Miktarı Artır/Azalt
window.changeQty = function (index, delta) {
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    updateCartUI();
};

// 5. Ödeme Formunu Göster
window.toggleCheckout = function () {
    if (cart.length === 0) {
        alert("Sepetiňiz boş!");
        return;
    }
    document.getElementById('checkout-form').style.display = 'block';
    document.getElementById('show-checkout-btn').style.display = 'none';
};

// 6. TELEGRAM'A GÖNDERME
window.submitOrder = function () {
    const nameInput = document.getElementById('customer-name');
    const phoneInput = document.getElementById('customer-phone');

    if (!nameInput || !phoneInput || !nameInput.value || !phoneInput.value) {
        alert("Adyňyzy we telefon belgiňizi ýazmagyňyzy haýyş edýäris..");
        return;
    }

    const botToken = "8238051221:AAFol55lO0dihN1xbY3kIzN2fX4EdA9k8eQ";
    const chatId = "7884520299";

    let message = "🆕 *Täze sargyt geldi!*\n\n";
    message += `👤 *Müşteri:* ${nameInput.value}\n`;
    message += `📞 *Telefon:* ${phoneInput.value}\n\n`;
    message += "🛍️ *Önümler:*\n";

    cart.forEach(item => {
        message += `• ${item.name} (${item.quantity} sany) - $${(item.price * item.quantity).toFixed(2)}\n`;
    });

    const total = document.getElementById('cart-total-price').innerText;
    message += `\n💰 *Jemi:* ${total}`;

    const url = `https://api.telegram.org/bot${botToken}/sendMessage?chat_id=${chatId}&text=${encodeURIComponent(message)}&parse_mode=Markdown`;

    fetch(url, {
        method: 'GET',
        mode: 'cors',
        cache: 'no-cache'
    })
        .then(response => {
            if (response.ok) {
                alert("Sargyt ugradyldy! Mümkin bolan iň gysga wagtda siziň telefon belgiňize jaň ediler");
                cart = [];
                updateCartUI();
            } else {
                alert("Error: Mesaj ugradylyp bilinmedi.");
            }
        })
        .catch(err => {
            alert("Baglanyşyk errory! VPNli barlap görüň.");
        });
};