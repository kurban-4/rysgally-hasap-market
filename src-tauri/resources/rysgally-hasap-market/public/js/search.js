document.getElementById('product-search').addEventListener('input', function (e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.col');

    cards.forEach(card => {
        const productName = card.querySelector('.card-title');

        if (productName) {
            const text = productName.textContent.toLowerCase();

            if (text.includes(searchTerm)) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
        }
    });
});
