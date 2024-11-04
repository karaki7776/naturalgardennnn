/*============Scroll reveal animation==============*/
const sr= ScrollReveal({
    origin:'top',
    distance:'60px',
    duration:2500,
    delay:400,
    //reset:true
})
sr.reveal('.home__content')
sr.reveal('.home__image',{delay:500})
sr.reveal('.about__img',{origin:'left'})
sr.reveal('.about-details',{origin:'right'})
sr.reveal('.icon-card-img',{interval:'100'})
sr.reveal('.category-card, .product-item , .contact-us-h1 , .footer__content',{delay:500})
sr.reveal('.contact-form',{origin:'left'})
sr.reveal('.contact-image',{origin:'right'})