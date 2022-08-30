console.log("Hello World!")

const homeCarousel = new Flickity('.home-carousel', {
	wrapAround: true,
	draggable: false,
	autoPlay: true,
	pauseAutoPlayOnHover: false
})

const userReviewsCarousel = new Flickity('.user-reviews-carousel', {
	wrapAround: true,
})
