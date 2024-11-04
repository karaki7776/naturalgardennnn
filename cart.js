// إعداد عربة التسوق
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// إضافة المنتج إلى العربة عند النقر على زر Add to Cart
document.querySelectorAll('.product__button').forEach(button => {
    button.addEventListener('click', function() {
        const product = this.closest('.product-item');
        const productId = product.getAttribute('data-id');
        const productName = product.getAttribute('data-name');
        const productPrice = product.getAttribute('data-price');
        const productImage = product.getAttribute('data-image');

        const cartItem = {
            id: productId,
            name: productName,
            price: productPrice,
            image: productImage
        };

        // إضافة المنتج إلى العربة
        cart.push(cartItem);
        localStorage.setItem('cart', JSON.stringify(cart));

        // الانتقال إلى صفحة العربة
        window.location.href = 'cart.html';
    });
});