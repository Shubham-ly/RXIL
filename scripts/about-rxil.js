// Have to use var instead of let or const because of redeclaration error in javascript
var moreInfoPopup = document.querySelector('#moreInfoPopup');
var overlay = document.querySelector('#overlay');
var showMoreButtons = document.querySelectorAll('#showMore')
var closeButton = document.querySelector('#moreInfoPopup #close')

function closePopup() {
	overlay?.classList.add('hidden');
	moreInfoPopup.classList.add('hidden');
}

overlay?.addEventListener('click', (e) => closePopup())
closeButton?.addEventListener('click', (e) => closePopup())

showMoreButtons?.forEach(btn => {
	btn.addEventListener('click', (e) => {
		e.stopPropagation();
		overlay.classList.remove('hidden')
		moreInfoPopup.classList.remove('hidden')

		const parent = e.currentTarget.parentElement
		const avatar = moreInfoPopup.querySelector('#avatar')
		const name = moreInfoPopup.querySelector('#name')
		const role = moreInfoPopup.querySelector('#role')
		const body = moreInfoPopup.querySelector('#body')

		avatar.style.backgroundImage = parent.style.backgroundImage
		name.innerText = parent.querySelector('#name').innerText
		role.innerText = parent.querySelector('#role').innerText
		body.innerText = parent.querySelector('#description')?.innerText

	})
})