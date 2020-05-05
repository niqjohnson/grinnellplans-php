/**
 * This file holds general-use JS that will be included on every page.
 * Keep it lightweight!
 */


function toggleShowHide(target, caller, showText, hideText) {
	// Where's my dollar sign :(
	target = document.getElementById(target);

	if (target.style.display != 'none') {
		target.style.display = 'none';
		linkText = showText;
	} else {
		target.style.display = '';
		linkText = hideText;
	}
	caller.innerHTML = linkText;
}

let mobileNav = {
	init: function() {
		let mobileNavTrigger = document.querySelectorAll('#mobile_nav_trigger')[0];
		let body = document.querySelectorAll('body')[0];
		body.dataset.menuVisible = this.isVisible;
		this.bindEvents(mobileNavTrigger, body);
	},
	bindEvents: function(mobileNavTrigger, body) {
		mobileNavTrigger.addEventListener('click', (event) => {
			if (this.isVisible === true) {
				this.hideMenu(body);
			} else {
				this.showMenu(body);
			}
			body.dataset.menuVisible = this.isVisible;
		});
	},
	showMenu: function(body) {
		body.style.top = `-${window.scrollY}px`;
		body.style.position = 'fixed';
		this.isVisible = true;
	},
	hideMenu: function(body) {
		window.scrollTo(0, parseInt(body.style.top || '0') * -1);
		body.style.position = '';
		body.style.top = '';
		this.isVisible = false;
	},
	isVisible: false
}

window.onload = (event) => {
	mobileNav.init();
};
