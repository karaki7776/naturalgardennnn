document.querySelectorAll('.favorite-link').forEach(link => {
    link.addEventListener('click', function(event) {
        const heartIcon = this.querySelector('.fa-heart');
        heartIcon.classList.toggle('favorited');
    });
});